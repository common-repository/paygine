<?php
declare(strict_types = 1);

/*
 * Plugin Name: Method payment module
 * Plugin URI: https://methodpay.ru/
 * Description: Receive payments via Visa / Mastercard / MIR easily with Method bank cards processing
 * Version: 3.0.0
 * Author: Method
 * Tested up to: 6.6.1
 * License: GPL3
 *
 * Text Domain: paygine-payment_method
 * Domain Path: /languages
 */

if(!class_exists('B2P\Client'))
    require_once __DIR__ . '/sdk/sdk_autoload.php';

use B2P\Client;
use B2P\Responses\Error;
use B2P\Models\Enums\CurrencyCode;
use B2P\Models\Interfaces\CreditOrder;

defined('ABSPATH') or die("No script kiddies please!");

add_action('plugins_loaded', 'init_woocommerce_paygine', 0);

function init_woocommerce_paygine() {
    if(!class_exists('WC_Payment_Gateway')) return;

    load_plugin_textdomain('paygine-payment_method', false, dirname(plugin_basename(__FILE__)) . '/languages');

    class woocommerce_paygine extends WC_Payment_Gateway {
        const PLUGIN_URL = 'https://methodpay.ru';

        protected string $api_operation;
        protected array $query_params = [];
        protected string $notify_url;
        protected string $sector;
        protected string $password;
        protected string $testmode;
        protected string $hash_algo;
        protected bool $notify_customer_enabled;
        protected string $registered_status;
        protected string $authorized_status;
        protected string $loan_status;
        protected string $completed_status;
        protected string $agreement_status;
        protected string $payment_expected_status;
        protected string $canceled_status;
        protected string $payment_method;
        protected string $tax;
        protected int $currency;
        protected array $fiscal_positions;
        protected array $shop_cart;
        protected string $logo_field;
        protected string $remove_logo_field;

        public function __construct() {
            require_once ABSPATH . 'wp-admin/includes/file.php';

            $this->id = 'paygine';
            $this->method_title = __('Method', 'paygine-payment_method');
            $this->method_description = sprintf(__('To accept payments through the plugin, you need to apply to connect to Method on the website <a href="%1$s" target="_blank">%1$s</a> and enter into an agreement with the company.<br/>Support email: <a href="mailto:%2$s">%2$s</a>', 'paygine-payment_method'), self::PLUGIN_URL, 'helpline@paygine.ru');
            $this->icon = plugins_url('assets/img/method.svg', __FILE__);
            $this->has_fields = true;
            $this->notify_url = add_query_arg('wc-api', 'paygine_notify', home_url('/'));
            $this->supports = ['refunds', 'products'];

            $this->init_form_fields();
            $this->init_settings();

            $this->title = $this->settings['title'] ? $this->settings['title'] : $this->method_title;
            $this->description = $this->settings['description'] ? $this->settings['description'] : sprintf(__('Payments with bank cards via the <a href="%s" target="_blank">Method</a> payment system.', 'paygine-payment_method'), self::PLUGIN_URL);
            $this->logo = $this->settings['logo'] ?? '';
            $this->sector = $this->settings['sector'];
            $this->password = $this->settings['password'];
            $this->testmode = $this->settings['testmode'];
            $this->hash_algo = ($this->settings['hash_algo'] === 'sha256') ? '1' : '0';
            $this->payment_method = $this->settings['payment_method'] ?? '';
            $this->tax = $this->settings['tax'] ?? '6';
            $this->notify_customer_enabled = $this->settings['notify_customer_enabled'] === 'yes';
            $this->currency = $this->get_currency(get_woocommerce_currency());
            $this->logo_field = $this->plugin_id . $this->id . '_logo';
            $this->remove_logo_field = $this->plugin_id . $this->id . '_remove_logo';
            $this->registered_status = $this->settings['registered_status'] ?? '';
            $this->authorized_status = $this->settings['authorized_status'] ?? '';
            $this->loan_status = $this->settings['loan_status'] ?? '';
            $this->completed_status = $this->settings['completed_status'] ?? '';
            $this->canceled_status = $this->settings['canceled_status'] ?? '';
            $this->agreement_status = $this->settings['agreement_status'] ?? '';
            $this->payment_expected_status = $this->settings['payment_expected_status'] ?? '';

            switch($this->payment_method) {
                case 'purchaseWithInstallment':
                case 'authorizeWithInstallment':
                    $this->title = __('Pay for your order in installments', 'paygine-payment_method');
                    $this->icon = plugins_url('assets/img/svkb.svg', __FILE__);
                    break;
                default:
                    if($this->logo)
                        $this->icon = wp_get_attachment_url($this->logo);
                    break;
            }

            $this->getClient();

            add_action('woocommerce_api_paygine_notify', [$this, 'callback_notify']);
            add_action('woocommerce_api_paygine_complete_action', [$this, 'process_complete']);
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
            add_action('woocommerce_order_item_add_action_buttons', [$this, 'wc_order_item_add_complete_button']);
            add_filter('woocommerce_generate_image_html', 'woocommerce_generate_image_html');
            add_action("woocommerce_order_status_changed", [$this, 'wc_paygine_order_payment_change'], 10, 4);
        }

        public function init_form_fields() {
            $wc_statuses = wc_get_order_statuses();

            $this->form_fields = [
                'enabled' => [
                    'title' => __('Enable/Disable', 'paygine-payment_method'),
                    'type' => 'checkbox',
                    'label' => __('Enable Method checkout method', 'paygine-payment_method'),
                    'default' => 'yes'
                ],
                'title' => [
                    'title' => __('Title', 'paygine-payment_method'),
                    'type' => 'text',
                    'description' => __('Custom title for payment type', 'paygine-payment_method'),
                    'desc_tip' => true,
                    'placeholder' => $this->method_title,
                    'default' => ''
                ],
                'description' => [
                    'title' => __('Description', 'paygine-payment_method'),
                    'type' => 'textarea',
                    'description' => __('Custom description for payment type', 'paygine-payment_method'),
                    'desc_tip' => true,
                    'css' => 'width: 400px;',
                    'default' => ''
                ],
                'logo' => [
                    'title' => __('Logo', 'paygine-payment_method'),
                    'type' => 'image',
                    'description' => __('Custom logo for payment type', 'paygine-payment_method'),
                    'desc_tip' => true,
                    'default' => ''
                ],
                'sector' => [
                    'title' => __('Sector ID', 'paygine-payment_method'),
                    'type' => 'text',
                    'description' => __('Your shop identifier at Method', 'paygine-payment_method'),
                    'desc_tip' => true,
                    'placeholder' => '1234',
                    'default' => ''
                ],
                'password' => [
                    'title' => __('Password', 'paygine-payment_method'),
                    'type' => 'text',
                    'description' => __('Password to use for digital signature', 'paygine-payment_method'),
                    'desc_tip' => true,
                    'placeholder' => 'test',
                    'default' => ''
                ],
                'testmode' => [
                    'title' => __('Test Mode', 'paygine-payment_method'),
                    'type' => 'select',
                    'options' => [
                        '1' => __('Test mode - real payments will not be processed', 'paygine-payment_method'),
                        '0' => __('Production mode - payments will be processed', 'paygine-payment_method')
                    ],
                    'description' => __('Select test or live mode', 'paygine-payment_method'),
                    'desc_tip' => true,
                    'default' => '1'
                ],
                'payment_method' => [
                    'title' => __('Payment method', 'paygine-payment_method'),
                    'type' => 'select',
                    'options' => [
                        'purchase' => __('Standard acquiring (one-stage payment)', 'paygine-payment_method'),
                        'authorize' => __('Standard acquiring (two-stage payment)', 'paygine-payment_method') . ' *',
                        'purchaseWithInstallment' => __('Plait (one-stage payment)', 'paygine-payment_method'),
                        'authorizeWithInstallment' => __('Plait (two-stage payment)', 'paygine-payment_method') . ' *',
                        'purchaseSBP' => __('Fast Payment System', 'paygine-payment_method'),
                        'loan' => __('Loan', 'paygine-payment_method')
                    ],
                    'description' => '* ' . __('Payment occurs after confirmation by the manager in the personal account', 'paygine-payment_method'),
                    'desc_tip' => true,
                    'default' => 'purchase'
                ],
                'tax' => [
                    'title' => __('TAX', 'paygine-payment_method'),
                    'type' => 'select',
                    'options' => [
                        1 => __('VAT rate 20%', 'paygine-payment_method'),
                        2 => __('VAT rate 10%', 'paygine-payment_method'),
                        3 => __('VAT rate calc. 20/120', 'paygine-payment_method'),
                        4 => __('VAT rate calc. 10/110', 'paygine-payment_method'),
                        5 => __('VAT rate 0%', 'paygine-payment_method'),
                        6 => __('Not subject to VAT', 'paygine-payment_method'),
                    ],
                    'default' => '6'
                ],
                'notify_url' => [
                    'title' => __('Notify URL', 'paygine-payment_method'),
                    'type' => 'text',
                    'description' => __('Report this URL to Method technical support to receive payment notifications', 'paygine-payment_method'),
                    'desc_tip' => true,
                    'custom_attributes' => [
                        'readonly' => 'readonly',
                    ],
                    'default' => $this->notify_url
                ],
                'hash_algo' => [
                    'title' => __('Data encryption algorithm', 'paygine-payment_method'),
                    'type' => 'select',
                    'options' => [
                        'md5' => 'MD5',
                        'sha256' => 'SHA256',
                    ],
                    'description' => __('Must match your sector encryption settings in your personal account', 'paygine-payment_method'),
                    'desc_tip' => true,
                    'default' => 'md5'
                ],
                'custom_statuses_header' => [
                    'title' => __('Custom statuses for orders', 'paygine-payment_method'),
                    'type' => 'title'
                ],
                'registered_status' => [
                    'title' => __('Order registered', 'paygine-payment_method'),
                    'type' => 'select',
                    'options' => $wc_statuses,
                    'default' => 'wc-pending'
                ],
                'authorized_status' => [
                    'title' => __('Order authorized', 'paygine-payment_method'),
                    'type' => 'select',
                    'options' => $wc_statuses,
                    'default' => 'wc-on-hold'
                ],
                'loan_status' => [
                    'title' => __('Loan agreement approved but not signed', 'paygine-payment_method'),
                    'type' => 'select',
                    'options' => $wc_statuses,
                    'default' => 'wc-processing'
                ],
                'completed_status' => [
                    'title' => __('Order successfully paid', 'paygine-payment_method'),
                    'type' => 'select',
                    'options' => $wc_statuses,
                    'default' => 'wc-completed'
                ],
                'canceled_status' => [
                    'title' => __('Order canceled', 'paygine-payment_method'),
                    'type' => 'select',
                    'options' => $wc_statuses,
                    'default' => 'wc-refunded'
                ],
                'notify_customer_enabled' => [
                    'title' => __('Issuing an invoice for payment', 'paygine-payment_method'),
                    'type' => 'checkbox',
                    'label' => __('Enable issuing an invoice for payment by email of the payer', 'paygine-payment_method'),
                    'default' => 'no'
                ],
                'agreement_status' => [
                    'title' => __('Order agreement', 'paygine-payment_method'),
                    'type' => 'select',
                    'options' => $wc_statuses,
                    'default' => 'wc-on-hold'
                ],
                'payment_expected_status' => [
                    'title' => __('Payment expected', 'paygine-payment_method'),
                    'type' => 'select',
                    'options' => $wc_statuses,
                    'default' => 'wc-pending'
                ]
            ];

            wp_enqueue_script('admin-paygine-script', plugins_url('assets/js/admin.js', __FILE__));
        }

        protected function getClient() {
            try {
                if (isset($this->sector) && isset($this->password))
                    $this->client = new Client((int)$this->sector, $this->password, (bool)$this->testmode, (bool)$this->hash_algo);
            } catch (Exception $e) {
                return new WP_Error('error', __($e->getMessage()));
            }

            return $this->client;
        }

        public function payment_fields() {
            if($this->payment_method === 'purchaseWithInstallment' || $this->payment_method === 'authorizeWithInstallment')
                echo '<iframe style="width:100%;height:180px;border:none;min-width: 440px;" src="' . plugins_url('svkb_widget.php', __FILE__) . '?amount=' . print_r((isset(WC()->cart->cart_contents_total)) ? WC()->cart->cart_contents_total : '', true) . '"></iframe>';
        }

        public function admin_options() {?>
            <h3><?php _e('Method', 'paygine-payment_method');?></h3>
            <p><?php echo sprintf(__('Payments with bank cards via the <a href="%s" target="_blank">Method</a> payment system.', 'paygine-payment_method'), self::PLUGIN_URL);?></p>
            <table class="form-table">
                <?php $this->generate_settings_html();?>
            </table>
        <?php }

        public function process_admin_options() {
            if(!empty($_POST[$this->remove_logo_field]) && !empty($this->logo)){
                if(wp_delete_attachment($this->logo))
                    $this->update_option('logo');
            } else if (!empty($_FILES[$this->logo_field]['name'])) {
                require_once(ABSPATH.'wp-admin/includes/image.php');
                require_once(ABSPATH.'wp-admin/includes/file.php');
                require_once(ABSPATH.'wp-admin/includes/media.php');

                if(!file_is_valid_image($_FILES[$this->logo_field]['tmp_name']))
                    return false;

                $attachment_id = media_handle_upload($this->logo_field, 0);
                if (is_wp_error($attachment_id))
                    return $attachment_id->get_error_message();

                if(!empty($this->logo))
                    wp_delete_attachment($this->logo);

                $this->update_option('logo', $attachment_id);
            }

            $this->init_settings();

            $post_data = $this->get_post_data();

            foreach ($this->get_form_fields() as $key => $field) {
                if(in_array($this->get_field_type($field), ['title', 'image'])) continue;

                try {
                    $this->settings[$key] = $this->get_field_value($key, $field, $post_data);
                } catch (Exception $e) {
                    $this->add_error($e->getMessage());
                }
            }

            return update_option($this->get_option_key(), apply_filters('woocommerce_settings_api_sanitized_fields_' . $this->id, $this->settings));
        }

        public function generate_image_html($key, $data) {
            $field_key = $this->get_field_key($key);
            $defaults = [
                'title' => '',
                'disabled' => false,
                'class' => '',
                'css' => '',
                'placeholder' => '',
                'type' => 'file',
                'desc_tip' => false,
                'description' => '',
                'custom_attributes' => [],
            ];

            $data = wp_parse_args($data, $defaults);

            ob_start();?>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="<?php echo esc_attr($field_key);?>"><?php echo wp_kses_post($data['title']);?> <?php echo $this->get_tooltip_html($data);?></label>
                </th>
                <td class="forminp">
                    <fieldset>
                        <legend class="screen-reader-text"><span><?php echo wp_kses_post($data['title']);?></span></legend>
                        <input class="input-text regular-input" type="file" name="<?php echo esc_attr($field_key);?>" id="<?php echo esc_attr($field_key);?>" accept="image/*">
                    </fieldset>
                    <?php if(!empty($this->logo)){
                        $logo_meta = wp_get_attachment_metadata($this->logo);?>
                        <img src="<?php echo wp_get_attachment_url($this->logo)?>" alt="" width="100" style="margin: 10px">
                        <div>(<?php echo $logo_meta['width'] . 'x' . $logo_meta['height'];?>)</div>
                        <br>
                        <label for="<?php echo esc_attr($this->remove_logo_field);?>">
                            <input class="" type="checkbox" name="<?php echo esc_attr($this->remove_logo_field);?>" id="<?php echo esc_attr($this->remove_logo_field);?>" style="" value="1"> <?php echo wp_kses_post( __('Remove logo', 'paygine-payment_method'));?>
                        </label>
                    <?php } else { ?>
                        <img src="<?php echo plugins_url('assets/img/method.svg', plugin_basename(__FILE__));?>" alt="" width="100" style="margin: 10px">
                    <?php } ?>
                </td>
            </tr>
            <?php return ob_get_clean();
        }

        function wc_order_item_add_complete_button($order) {
            if($order->get_payment_method() === $this->id && get_post_meta($order->get_id(), 'paygine_order_state', true) === 'AUTHORIZED') {?>
                <script src="<?php echo plugins_url('assets/js/scripts.js', plugin_basename(__FILE__));?>"></script>
                <input type="hidden" id="nonce_paygine_complete" value="<?php echo wp_create_nonce('paygine_complete_action' . $order->get_id());?>">
                <button type="button" id="button_paygine_complete" class="button custom-items"><?php echo __('Complete payment', 'paygine-payment_method');?></button>
            <?php }
        }

        public function process_payment($order_id) {
            $wc_order = wc_get_order($order_id);

            if($this->notify_customer_enabled && $wc_order->get_status() !== $this->agreement_status && !get_post_meta($order_id, 'paygine_order_moderated', true)) {
                $wc_order->add_order_note(__('Order created successfully', 'paygine-payment_method'));
                $wc_order->update_status($this->agreement_status);

                update_post_meta($wc_order->get_id(), 'paygine_order_moderated', 'yes');

                WC()->cart->empty_cart();

                return [
                    'result' => 'success',
                    'redirect' => $this->get_return_url($wc_order),
                ];
            }

            $register_order_id = get_post_meta($order_id, 'paygine_order_id', true);

            if($register_order_id) {
                $payment_url = $this->process_payment_payform((int)$register_order_id, $order_id);
            } else {
                $pay_order_id = $this->process_payment_registration($wc_order);
                $payment_url = $this->process_payment_payform($pay_order_id, $order_id);
            }

            return [
                'result' => 'success',
                'redirect' => $payment_url
            ];
        }

        public function wc_paygine_order_payment_change($order_id, $status_from, $status_to, $order) {
            if($this->notify_customer_enabled && $order->payment_method === 'paygine' && ($status_from === 'on-hold' && $status_to === 'pending'))
                $this->process_payment_registration($order);
        }

        public function process_payment_registration($order): int {
            $this->calc_fiscal_position_shop_cart($order, $this->client->centifyAmount($order->get_total()));

            $register_data = [
                'reference' => $order->get_id(),
                'amount' => $this->client->centifyAmount($order->get_total()),
                'currency' => $this->currency,
                'email' => $order->get_billing_email(),
                'description' => sprintf(__('Order #%s', 'paygine-payment_method'), ltrim($order->get_order_number(), '#')),
                'url' => $this->notify_url,
                'mode' => 0,
                'fiscal_positions' => $this->fiscal_positions
            ];

            if ($this->notify_customer_enabled)
                $register_data['notify_customer'] = 1;

            try {
                $response = $this->client->register($register_data);
                if($response instanceof Error)
                    throw new Exception($response->description->getValue());

                $b2p_order_id = (int)$response->id;
                if (!$b2p_order_id)
                    throw new Exception(__('Failed to get Method order ID', 'paygine-payment_method'));
            } catch (Exception $e) {
                wc_add_notice($e->getMessage(), 'error');
            }

            $order->update_status($this->{strtolower($response->getState()) . '_status'});
            update_post_meta($order->get_id(), 'paygine_order_id', $b2p_order_id);
            update_post_meta($order->get_id(), 'paygine_order_state', $response->getState());
            $order->add_order_note(__('Order registered successfully', 'paygine-payment_method') . " (ID: $b2p_order_id)");

            return $b2p_order_id;
        }

        public function process_payment_payform($pay_order_id, $reference): string {
            $operation_params = ['id' => $pay_order_id];
            if (str_contains($this->payment_method, 'WithInstallment') && $this->shop_cart)
                $operation_params['shop_cart'] = base64_encode(json_encode($this->shop_cart));
            if (str_contains($this->payment_method, 'loan'))
                $operation_params['reference'] = $reference;

            $url = call_user_func([$this->client, $this->payment_method], $operation_params);

            $parsed_url = parse_url($url);
            $this->api_operation = $parsed_url['path'];
            parse_str($parsed_url['query'], $this->query_params);

            return $url;
        }

        public function callback_notify() {
            $wc_order = [];
            $checkout_url = apply_filters('woocommerce_get_checkout_url', wc_get_checkout_url());

            try {
                if ($isNotifyRequest = $this->isNotifyRequest()) {
                    $input = file_get_contents("php://input");
                    $ct_order_id = $this->client->handleResponse($input)->order_id->getValue();
                } else {
                    if(isset($_REQUEST['error'])) {
                        $message = __('Failed to pay for the order', 'paygine-payment_method') . ":\n" . $_REQUEST['error'];

                        wc_add_notice(nl2br($message), 'error');
                        wp_redirect($checkout_url);
                    }

                    $ct_order_id = (int)$_REQUEST['id'];
                    if(!$ct_order_id)
                        throw new Exception(__('Failed to get Method order ID', 'paygine-payment_method'));

                    $pc_ref_id = (int)$_REQUEST['reference'];
                    if(!$pc_ref_id)
                        throw new Exception(__('Undefined order ID', 'paygine-payment_method'));

                    $pc_order_id = (int)get_post_meta($pc_ref_id, 'paygine_order_id', true);
                    if($ct_order_id !== $pc_order_id)
                        throw new Exception(__('Request data is not valid', 'paygine-payment_method'));
                }

                $ct_order = $this->client->order(['id' => $ct_order_id]);
                if($ct_order instanceof Error)
                    throw new Exception($ct_order->description->getValue());

                $wc_order = wc_get_order((int)$ct_order->reference);
                if(!$wc_order)
                    throw new Exception(__('Failed to get order information', 'paygine-payment_method'));

                $wc_order->update_meta_data('paygine_order_state', $ct_order->getState());

                $paid = false;

                if($ct_order->getState() !== get_post_meta($wc_order->get_id(), 'paygine_order_state', true)) {
                    if($ct_order instanceof CreditOrder) {
                        $wc_order->update_status($ct_order->isPaid() ? $this->completed_status : $this->loan_status);
                        $wc_order->add_order_note($ct_order->isPaid() ? __('The loan agreement was successfully completed and signed', 'paygine-payment_method') : __('The loan agreement was successfully completed, but not signed', 'paygine-payment_method'));

                        $paid = true;
                    } else {
                        $wc_order->update_status($this->{strtolower($ct_order->getState()) . '_status'});
                        $wc_order->add_order_note(__('Payment ' . strtolower($ct_order->getState()) . ' successfully', 'paygine-payment_method'));
                        $paid = $ct_order->isPaid();
                    }

                    $wc_order->save();
                }

                if ($isNotifyRequest) {
                    echo 'ok';
                } else {
                    if($paid) {
                        WC()->cart->empty_cart();
                        wp_redirect($this->get_return_url($wc_order));
                    } else {
                        wc_add_notice(nl2br(__('Failed to pay incorrect card', 'paygine-payment_method')), 'error');
                        wp_redirect($checkout_url);
                    }
                }
            } catch (\throwable $e) {
                if ($isNotifyRequest) {
                    echo 'ok';
                } else {
                    $message = __('Failed to pay for the order', 'paygine-payment_method') . ":\n" . $e->getMessage();

                    if($wc_order)
                        $wc_order->add_order_note(nl2br($message));

                    wc_add_notice(nl2br($message), 'error');
                    wp_redirect($checkout_url);
                }
            }

            exit;
        }

        public function process_complete() {
            try {
                $order_id = $_REQUEST['order_id'];
                if(!$order_id)
                    throw new Exception(__('Undefined order ID', 'paygine-payment_method'));

                if (!wp_verify_nonce($_REQUEST['paygine_nonce_value'], 'paygine_complete_action' . $order_id))
                    throw new Exception(__('Operation failed. Please refresh the page', 'paygine-payment_method'));

                $wc_order = wc_get_order($order_id);
                if(!$wc_order)
                    throw new Exception(__('Failed to get order information', 'paygine-payment_method'));

                $pc_order_id = get_post_meta($wc_order->get_id(), 'paygine_order_id', true);
                if(!$pc_order_id)
                    throw new Exception(__('Failed to get Method order ID', 'paygine-payment_method'));

                $ct_order = $this->client->order(['id' => $pc_order_id]);
                if($ct_order instanceof Error)
                    throw new Exception($ct_order->description->getValue());

                $complete_result = $ct_order->complete();
                if(!$complete_result)
                    throw new Exception(__('Unable to debit funds', 'paygine-payment_method'));

                if($ct_order instanceof CreditOrder) {
                    $success_message = __('The loan agreement was successfully completed and signed', 'paygine-payment_method');
                } else {
                    $wc_order->update_status($this->completed_status);
                    $success_message = __('Funds for the order have been successfully debited', 'paygine-payment_method');
                }

                $wc_order->update_meta_data('paygine_order_state', 'COMPLETED');
                $wc_order->add_order_note($success_message);

                $wc_order->save();

                echo json_encode([
                    'success' => true,
                    'message' => nl2br($success_message)
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => nl2br($e->getMessage())
                ]);

                return new WP_Error('error', __($e->getMessage()));
            }

            exit;
        }

        public function process_refund($order_id, $amount = null, $reason = '') {
            try {
                $wc_order = wc_get_order($order_id);
                if(!$wc_order)
                    throw new Exception(__('Failed to get order information', 'paygine-payment_method'));

                if (!$_REQUEST['security'])
                    throw new Exception(__('Operation failed. Please refresh the page', 'paygine-payment_method'));

                if($amount !== $wc_order->get_total())
                    return new WP_Error('error', __('Error, please enter the refund amount. Refunds must be made for the full amount of the order', 'paygine-payment_method'));

                $pc_order_id = get_post_meta($wc_order->get_id(), 'paygine_order_id', true);
                if(!$pc_order_id)
                    throw new Exception(__('Failed to get Method order ID', 'paygine-payment_method'));

                $ct_order = $this->client->order(['id' => $pc_order_id]);
                if($ct_order instanceof Error)
                    throw new Exception($ct_order->description->getValue());

                $reverse_result = $ct_order->reverse();
                if($reverse_result instanceof Error)
                    return new WP_Error('error', __('Unable to issue a refund on a credit order', 'paygine-payment_method'));
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => nl2br($e->getMessage())
                ]);

                return new WP_Error('error', __($e->getMessage()));
            }

            $wc_order->update_meta_data('paygine_order_state', 'CANCELED');
            $comment = __('Payment canceled successfully', 'paygine-payment_method');

            if($reason){
                $comment .= PHP_EOL . esc_html($reason);
            }

            $wc_order->add_order_note($comment);

            $wc_order->save();

            return json_encode([
                'success' => true,
                'message' => nl2br($comment)
            ]);
        }

        public function isNotifyRequest(): bool {
            try {
                $input = file_get_contents("php://input");
                if ($input && $this->client->handleResponse($input)) return true;
                return false;
            } catch (\throwable $e) {
                return false;
            }
        }

        private function calc_fiscal_position_shop_cart($order, $order_amount) {
            $fiscal_positions = [];
            $fiscal_amount = 0;
            $shop_cart = [];

            $basket_items = $order->get_items();
            $shipping_amount = $order->get_shipping_total();

            foreach ($basket_items as $b_key => $basket_item) {
                $basket_item_data = $basket_item->get_data();

                $fiscal_positions[$b_key]['quantity'] = $basket_item_data['quantity'];
                $fiscal_amount += $basket_item_data['quantity'] * ($fiscal_positions[$b_key]['amount'] = $this->client->centifyAmount($basket_item->get_product()->get_price()));
                $fiscal_positions[$b_key]['tax'] = (int)$this->tax;
                $fiscal_positions[$b_key]['name'] = str_ireplace([';', '|'], '', $basket_item_data['name']);

                $shop_cart[] = [
                    'name' => $basket_item_data['name'],
                    'goodCost' => (int)$basket_item->get_product()->get_price(),
                    'quantityGoods' => $basket_item_data['quantity']
                ];
            }

            if ($shipping_amount) {
                $fiscal_positions[] = [
                    'quantity' => 1,
                    'amount' => $this->client->centifyAmount($shipping_amount),
                    'tax' => (int)$this->tax,
                    'name' => 'Доставка'
                ];
                $fiscal_amount += $this->client->centifyAmount($shipping_amount);
                $shop_cart[] = [
                    'name' => 'Доставка',
                    'goodCost' => (int)$shipping_amount,
                    'quantityGoods' => 1
                ];
            }

            if ($fiscal_diff = abs($fiscal_amount - $order_amount)) {
                $fiscal_positions[] = [1, $fiscal_diff, (int)$this->tax, 'Скидка', 14];
                $shop_cart = [];
            }

            $this->fiscal_positions = $fiscal_positions;
            $this->shop_cart = $shop_cart;
        }

        public function get_currency($wc_currency): int {
            if (isset(CurrencyCode::cases()[$wc_currency])) {
                return CurrencyCode::cases()[$wc_currency];
            } else throw new Exception('wrong currency');
        }
    }

    function add_paygine_gateway($methods) {
        $methods[] = 'woocommerce_paygine';

        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'add_paygine_gateway');

    add_filter('plugin_action_links_' . plugin_basename( __FILE__ ),
        function($links) {
            if (!class_exists('woocommerce'))
                return $links;

            array_unshift($links, sprintf('<a href="%1$s">%2$s</a>', admin_url('admin.php?page=wc-settings&tab=checkout&section=paygine'), __('Settings', 'paygine-payment_method')));

            return $links;
        }
    );
}
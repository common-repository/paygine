'use strict';

document.addEventListener('DOMContentLoaded', function() {
    const notifyCustomerEnabledBtn = document.getElementById("woocommerce_paygine_notify_customer_enabled");

    if(notifyCustomerEnabledBtn) {
        const agreementStatus = document.getElementById("woocommerce_paygine_agreement_status");
        const expectedStatus = document.getElementById("woocommerce_paygine_payment_expected_status");

        notifyCustomerEnabledBtn.addEventListener('click', () => {
            if (notifyCustomerEnabledBtn.checked) {
                showStatus(agreementStatus, expectedStatus);
            } else {
                hideStatus(agreementStatus, expectedStatus);
            }
        });

        if (notifyCustomerEnabledBtn.checked) {
            showStatus(agreementStatus, expectedStatus);
        } else {
            hideStatus(agreementStatus, expectedStatus);
        }
    }

    function showStatus(agreement, expected) {
        agreement.closest('tr').style.display = 'table-row';
        expected.closest('tr').style.display = 'table-row';
    }

    function hideStatus(agreement, expected) {
        agreement.closest('tr').style.display = 'none';
        expected.closest('tr').style.display = 'none';
    }
});
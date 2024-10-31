jQuery(document).ready(function ($) {
    $('#button_paygine_complete').on('click', function () {
        let data = {
            action: 'paygine_make_complete',
            paygine_nonce_value: $("#nonce_paygine_complete").val(),
            order_id: $('#post_ID').val()
        };

        let result = confirm("Списать захолдированные средства?");

        if (result) {
            jQuery.post("/?wc-api=paygine_complete_action", data, function (response) {
                alert(JSON.parse(response).message);

                document.location.reload(true);
            });
        }
    })
});
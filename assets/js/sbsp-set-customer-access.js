if (typeof jQuery === 'undefined') {
    var jq = document.createElement('script');
    jq.src = "https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js";
    jq.onload = initSetCustomerAccess;
    document.head.appendChild(jq);
} else {
    initSetCustomerAccess();
}

function initSetCustomerAccess() {
    jQuery(function($) {
        $(document).ready(function() {

            $('.type-shop_order').on('click', '.sbsp-customer-access-btn', function(event) {
                event.preventDefault();
                event.stopPropagation();
            });

            $('.sbsp-customer-access-btn').click(function(event) {
                var type = $(this).data('type');
                var value = $(this).data('value');
                var $button = $(this);

                $.ajax({
                    url: sbsp_set_customer_access_object.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'sbsp_set_customer_access',
                        _ajax_nonce: sbsp_set_customer_access_object.nonce,
                        type: type,
                        value: value
                    },
                    success: function(response) {
                        if(response.data == 'blocked'){
                            $button.removeClass('sbsp-customer-access-allowed');
                            $button.addClass('sbsp-customer-access-blocked');
                        }else{
                            $button.removeClass('sbsp-customer-access-blocked');
                            $button.addClass('sbsp-customer-access-allowed');
                        }
                    }
                });
            });

        });
    });
}
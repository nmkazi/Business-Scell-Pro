if (typeof jQuery === 'undefined') {
    var jq = document.createElement('script');
    jq.src = "https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js";
    jq.onload = initCustomerCourierHistoryCheck;
    document.head.appendChild(jq);
} else {
    initCustomerCourierHistoryCheck();
}

function initCustomerCourierHistoryCheck() {
    jQuery(function($) {
        $(document).ready(function() {

            $('.sbsp-customer-courier-history').each(function() {
                var customer_courier_history_div = $(this);
                var phone = $(this).data('phone');
            
                // Send AJAX request
                $.ajax({
                  url: sbsp_customer_courier_history_check_object.ajax_url, // wp_localize_script provides this
                  type: 'POST',
                  data: {
                    action: 'sbsp_customer_courier_history_check', // Your custom action hook
                    _ajax_nonce: sbsp_customer_courier_history_check_object.nonce,
                    phone: phone
                  },
                  success: function(response) {
                    if(response.success){
                        var customer_courier_history = response.data;
                        customer_courier_history_div.find('.sbsp-customer-success').first().css('width', customer_courier_history.success_percent + '%');
                        customer_courier_history_div.find('.sbsp-customer-cancel').first().css('width', customer_courier_history.cancel_percent + '%');

                        $message = 'Total: ' + customer_courier_history.total_order + ' Success: ' + customer_courier_history.total_success + ' Cancel: ' + customer_courier_history.total_cancel;

                        customer_courier_history_div.find('.sbsp-customer-courier-history-text').first().text($message);
                    }
                  },
                  error: function(error) {
                    console.error('Error for phone ' + phone + ':', error);
                  }
                });
            });

        });
    });
}
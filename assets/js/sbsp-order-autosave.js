if (typeof jQuery === 'undefined') {
    var jq = document.createElement('script');
    jq.src = "https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js";
    jq.onload = initSBSPOrderAutosaveScript;
    document.head.appendChild(jq);
} else {
    initSBSPOrderAutosaveScript();
}

function initSBSPOrderAutosaveScript() {
    jQuery(function($) {
        $(document).ready(function() {
            var timeout = 1000;
    
            function autosave() {
                var billing_first_name = $('#billing_first_name').val();
                var billing_address_1 = $('#billing_address_1').val();
                var billing_phone = $('#billing_phone').val();
                var billing_city = $('#billing_city').val();
                var billing_state = $('#billing_state').val();
                var billing_country = $('#billing_country').val();
                var billing_email = $('#billing_email').val();
    
                // Check if phone is empty and log all fields
                if (!billing_phone || billing_phone.length < 11) {
                    // console.log('Missing required field: billing_phone');
                    /*console.log('Current field values:', {
                        billing_first_name: billing_first_name || 'empty',
                        billing_address_1: billing_address_1 || 'empty',
                        billing_phone: billing_phone || 'empty',
                        billing_city: billing_city || 'empty',
                        billing_state: billing_state || 'empty',
                        billing_country: billing_country || 'empty',
                        billing_email: billing_email || 'empty'
                    });*/
                    return; // Do not send request if phone is missing
                }
    
                var data = {
                    'action': 'autosave_order',
                    '_ajax_nonce': sbsp_ajax_object.nonce,
                    'billing_first_name': billing_first_name,
                    'billing_address_1': billing_address_1,
                    'billing_phone': billing_phone,
                    'billing_city': billing_city,
                    'billing_state': billing_state,
                    'billing_country': billing_country,
                    'billing_email': billing_email
                };
    
                $.post(sbsp_ajax_object.ajax_url, data, function(response) {
                    if (response.success) {
                        console.log(response.data.message + '. Order ID:', response.data.order_id);
                    } else {
                        console.log('Autosave failed:', response.data);
                    }
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    console.log('Request failed:', textStatus, errorThrown);
                });
            }
    
            $(document.body).on('added_to_cart removed_from_cart updated_cart_totals shipping_method_selected updated_shipping_method wc_fragments_refreshed', function() {
                //console.log('Cart has been updated!');
                setTimeout(autosave, timeout);
            });
    
            // Trigger autosave on field input or change
            $('#billing_first_name, #billing_address_1, #billing_phone, #billing_country, #billing_state, #billing_city, #billing_email').on('input change', function() {
                clearTimeout(timeout);
                //console.log("Input field changed");
                timeout = setTimeout(autosave, timeout); // Adjust delay as needed
            });
    
            // Trigger autosave on page load for autocomplete
            $(window).on('load', function() {
                //console.log("On load start");
                setTimeout(autosave, timeout); // Adjust delay for reliability
                //console.log("On load end");
            });
        });
    });
}
jQuery( document ).ready(function( $ ) {
    $("#vt_add_product").click(function () {
        $(".vt-product-wrapper").toggle();
    });
    let sPageURL = window.location.search.substring(1),
        sURLVariables = sPageURL.split('&');
    if(sURLVariables[0] === 'action=edit' || sURLVariables[0] === 'action=view'){
        $(".vt-product-wrapper").toggle();
    }

    $(".vt-product-inner .close svg, #vt_add_product_cancel").click(function () {
        $(".vt-product-wrapper").hide();
    });

    const usb_swiper_add_loader = ( current_obj) => {
        current_obj.append('<span class="vt-loader"></span>');
    };

    const usb_swiper_remove_loader = ( current_obj) => {
        current_obj.children('.vt-loader').remove();
    };

    const set_notification = ( message, type ='success', message_type='' ) => {
        var notification = "<p class='notification "+type+"'><strong>"+message_type+"</strong>"+message+"</p>"
        $('.vt-form-notification').empty().append(notification);

        $([document.documentElement, document.body]).animate({ scrollTop: ( $(".vt-form-notification").offset().top) - 10 }, 1000);
    }

    $(".vt_delete_product").click(function () {
        let product_id = $(this).attr('data-id');

        let product_label = $('.product-item-'+product_id+' .product-title').html();

        var message = usb_swiper_settings.confirm_message;
        message = message.replace("{#product_title#}", product_label);

        if (confirm(message) === true) {
            var data = {
                'action': 'vt_delete_product',
                'product_id' : product_id
            };

            $.post(usb_swiper_settings.ajax_url, data, function(response) {
                if(response.status) {
                    set_notification(response.message, 'success');
                    $('tr.product-item-'+product_id).remove();
                } else {
                    set_notification(response.message, 'error', response.message_type);
                }
            });
        }
    });
});
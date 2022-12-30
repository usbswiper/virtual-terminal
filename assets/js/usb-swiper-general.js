jQuery( document ).ready(function( $ ) {
    $("#vt_add_product").click(function () {
        $(".vt-product-wrapper").toggle();
    });
    let sPageURL = window.location.search.substring(1),
        sURLVariables = sPageURL.split('&');
    if(sURLVariables[0] === 'action=edit'){
        $(".vt-product-wrapper").toggle();
    }

    $(".vt-product-inner .close svg,#vt_add_product_cancel").click(function () {
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

        var data = {
            'action': 'vt_delete_product',
            'product_id' : product_id
        };

        $.post(usb_swiper_settings.ajax_url, data, function(response) {
            if(response.type === "success") {
                set_notification(response.message, 'success');
                window.location.reload(true);
            }
            else {
                set_notification(response.message, 'error', response.message_type);
            }
        });
    });
});
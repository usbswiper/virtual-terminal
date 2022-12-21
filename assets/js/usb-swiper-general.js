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

        /*post it*/
        $.post(usb_swiper_settings.ajax_url, data, function(response) {
            if(response.type === "success") {
                set_notification(response.message, 'success');
                window.location.reload();
            }
            else {
                set_notification(response.message, 'error', response.message_type);
            }
        });
    });

    $( "#vt_add_product_form_sss" ).submit(function( event ) {
        var form = $(this);
        var form_id = form.attr('id');
        var submitButton = form.find('#vt_add_product_submit');
        usb_swiper_add_loader(submitButton);

        jQuery.ajax({
            url: usb_swiper_settings.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: form.serialize()+"&action=vt_add_product",
        }).done(function ( response ) {

            if( response.status) {
                set_notification(response.message, 'success');
                document.getElementById(form_id).reset();
                $(".vt-resend-email-form ").hide();
            } else{
                set_notification(response.message, 'error', response.message_type);
            }
            usb_swiper_remove_loader(submitButton);
            $(".vt-product-wrapper").hide();
            $('#vt_product_image').val('');

        });

        event.preventDefault();
    });
});
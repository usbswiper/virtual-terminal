jQuery( document ).ready(function( $ ) {
    $("#send_email_btn").click(function () {
        $(".vt-resend-email-form ").toggle();
    });
    $(".vt-resend-email-form-wrapper .close svg,#vt_send_email_cancel").click(function () {
        $(".vt-resend-email-form ").hide();
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

    $( "#vt_resend_email_form" ).submit(function( event ) {
        var form = $(this);
        var form_id = form.attr('id');
        var submitButton = form.find('#vt_send_email_submit');
        usb_swiper_add_loader(submitButton);

        jQuery.ajax({
            url: usb_swiper_settings.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: $(this).serialize()+"&action=send_transaction_email",
        }).done(function ( response ) {

            if( response.status) {
                set_notification(response.message, 'success');
                document.getElementById(form_id).reset();
                $(".vt-resend-email-form ").hide();
            } else{
                set_notification(response.message, 'error', response.message_type);
            }

            usb_swiper_remove_loader(submitButton);

        });

        event.preventDefault();
    });
});
jQuery( document ).ready(function( $ ) {
    $('.select2-original').select2({
        placeholder: "Choose Users",
        width: "50%"
    });

    $(document).on('click','.add-new-partner-fee-btn', function (){

        var current_obj = $(this);

        usb_swiper_add_loader(current_obj);
        usb_swiper_remove_notification();
        var row_id = $('table.partner-fees .partner-fee-row').length;
        var nonce = current_obj.attr('data-nonce');

        jQuery.ajax({
            url: usb_swiper_settings.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: "action=insert_new_partner_fee&nonce="+nonce+"&row_id=" + row_id,
        }).done(function ( response ) {

            if ( response.status ) {
                usb_swiper_add_notification(response.message, 'notice');

                $('table.partner-fees tbody').append( response.html);

                setTimeout( function () {
                    $('#partner_fee_total_row').val($('table.partner-fees .partner-fee-row').length);
                }, 500);

            } else{
                usb_swiper_add_notification(response.message, 'error');
            }

            usb_swiper_remove_loader(current_obj);
        });
    });

    $(document).on('click','#vt_sync_status', function (){

        var current_obj = $(this);

        usb_swiper_add_loader(current_obj);
        usb_swiper_remove_notification();
        var nonce = current_obj.attr('data-nonce');

        jQuery.ajax({
            url: usb_swiper_settings.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: "action=sync_transaction_status&nonce="+nonce,
        }).done(function ( response ) {

            if ( response.status ) {
                usb_swiper_add_notification(response.message, 'notice');
                if( undefined !== response.failed_ids ){
                    console.log(response.failed_ids);
                }
            } else{
                usb_swiper_add_notification(response.message, 'error');
            }

            usb_swiper_remove_loader(current_obj);
        });
    });

    $(document).on('click','.remove-partner-fee', function (){
        var current_obj = $(this);

        if( confirm(usb_swiper_settings.remove_fee_message) ) {

            usb_swiper_add_loader(current_obj);
            usb_swiper_remove_notification();
            var row_id = current_obj.attr('data-id');
            var nonce = current_obj.attr('data-nonce');

            jQuery.ajax({
                url: usb_swiper_settings.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: "action=remove_partner_fee&nonce="+nonce+"&row_id=" + row_id,
            }).done(function ( response ) {

                if ( response.status ) {

                    $('table.partner-fees tbody').html('').html( response.html);

                    setTimeout( function () {
                        $('#partner_fee_total_row').val($('table.partner-fees .partner-fee-row').length);
                    }, 500);

                    usb_swiper_add_notification(response.message, 'notice');
                } else{
                    usb_swiper_add_notification(response.message, 'error');
                }

                usb_swiper_remove_loader(current_obj);
            });
        }

    })

    let usb_swiper_add_loader = ( current_obj) => {
        current_obj.append('<span class="loader"></span>');
    };

    let usb_swiper_remove_loader = ( current_obj) => {
        current_obj.children('.loader').remove();
    };

    let usb_swiper_remove_notification =() => {
        $('.notification-wrap').html('');
    }

    let usb_swiper_add_notification = ( message, type ) => {
        let notification = $('.notification-wrap');
        let notification_html = '<div id="message" class="updated inline '+type+'"><p>'+message+'</p></div>';
        notification.html('').html(notification_html);
    }

    $( "input#is_paypal_sandbox" ).change(function() {
        const liveField =  $('input.paypal-is-live');
        const sandboxField =  $('input.paypal-is-sandbox');
        if($(this).prop('checked') === true){
            liveField.parents('tr').hide();
            liveField.removeAttr('required');
            sandboxField.parents('tr').show();
            sandboxField.attr('required', true);
        } else{
            liveField.parents('tr').show();
            liveField.attr('required', true);
            sandboxField.parents('tr').hide();
            sandboxField.removeAttr('required');
        }
    }).change();
});

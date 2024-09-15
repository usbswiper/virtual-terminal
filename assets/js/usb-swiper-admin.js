jQuery( document ).ready(function( $ ) {

    $('.datepicker').datepicker({
        dateFormat: "yy-mm-dd"
    });

    var startDatePicker = $( "#start_date" ).datepicker({
        dateFormat: "yy-mm-dd",
        onSelect: function(selectedDate) {
            endDatePicker.datepicker("option", "minDate", selectedDate);
        }
    });

    var endDatePicker = $( "#end_date" ).datepicker({
        dateFormat: "yy-mm-dd",
        onSelect: function(selectedDate) {
            startDatePicker.datepicker("option", "maxDate", selectedDate);
        }
    });

    var reportStartDate = $('#report_start_date').datepicker({
        dateFormat: "yy-mm-dd",
        onSelect: function(selectedDate) {
            var maxEndDate = new Date(selectedDate);
            maxEndDate.setDate(maxEndDate.getDate() + 30);
            reportEndDate.datepicker("option", "minDate", selectedDate);
            reportEndDate.datepicker("option", "maxDate", maxEndDate);
        }
    });

    var reportEndDate = $('#report_end_date').datepicker({
        dateFormat: "yy-mm-dd",
    });

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

    $('[data-tooltip]').each(function() {
        var tooltip = $(this).attr('data-tooltip');
        $(this).wrap('<div class="tooltip"></div>');
        $(this).after('<span class="tooltiptext">' + tooltip + '</span>');
    });

    if( $('.merchant-report-wrap').length > 0 ) {
        update_merchat_report();
    }

    $(document).on('click', '.report-pagination', function(e) {
        e.preventDefault();
        update_merchat_report($(this).attr('data-page'));
    });

    $(document).on('click', '.submit-report-filter', function(e) {
        e.preventDefault();
        update_merchat_report($('#current_page').val());
    });

});

function usb_swiper_add_loader(targetElement) {
    let loader = jQuery('<div>', {
        class: 'usb-swiper-loader-report-page',
        html: '<div class="spinner-report-page"></div>'
    });

    targetElement.append(loader);
}

function usb_swiper_remove_loader(targetElement) {
    targetElement.find('.usb-swiper-loader-report-page').remove();
}

function update_merchat_report(page = 1,loop = 1, found = 0,total_volume = 0, amex_volume= 0, items= []){
    let merchant = jQuery('#merchant').val();
    let report_start_date = jQuery('#report_start_date').val();
    let report_end_date = jQuery('#report_end_date').val();
    let nonce = jQuery('#report_nonce').val();

    if( Number(loop) === 1 ){
        jQuery('#the-list').html('');
        usb_swiper_add_loader(jQuery('.merchant-report-wrap'));
    }
    jQuery.ajax({
        url: usb_swiper_settings.ajax_url,
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'merchant_report',
            nonce: nonce,
            page: page,
            merchant: merchant,
            start_date: report_start_date,
            end_date: report_end_date,
            offset: loop,
            found: found,
            total_volume: total_volume,
            amex_volume: amex_volume,
            items: items
        }
    }).done(function ( response ) {
        if ( response.status ) {
            if( Number(loop) < Number(response.max_page) ){
                loop = Number(loop) + 1;
                jQuery('#the-list').append(response.html);
                update_merchat_report(page, loop, response.found, response.total_volume, response.amex_volume, response.items);
            } else {
                jQuery('#the-list').append(response.html);
                jQuery('.merchant-total-volume strong').html(response.total_volume);
                jQuery('.merchant-total-amex strong').html(response.amex_volume);
                jQuery('#current_page').val(page);
                jQuery('.tablenav-pages .displaying-num').html(response.total_item);
                jQuery('.tablenav-pages .pagination-links').html(response.pagination_html);

                if(response.total_count > 20){
                    jQuery('.tablenav-pages').removeClass('one-page');
                } else if(jQuery('.tablenav-pages').hasClass('one-page')) {
                    jQuery('.tablenav-pages').addClass('one-page');
                }

                usb_swiper_remove_loader(jQuery('.merchant-report-wrap'));
            }
        } else{
            usb_swiper_add_notification(response.message, 'error');
            usb_swiper_remove_loader(jQuery('.merchant-report-wrap'));
        }
    });

}
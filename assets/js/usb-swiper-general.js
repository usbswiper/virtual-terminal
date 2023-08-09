jQuery( document ).ready(function( $ ) {

    $(document).on('click', '.send-email-btn', function () {
        const transaction_id = $(this).attr('data-transaction_id');
        $.ajax({
            url: usb_swiper_settings.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: $(this).serialize() + "&action=send_transaction_email_html&transaction_id=" + transaction_id,
        }).done(function (response) {
            if (response.status) {
                $('body').append(response.html);
                $(".vt-resend-email-form").toggle();
                vt_resend_email_form();
                close_send_email_toggle();
            }
        });
    });

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

    const close_send_email_toggle = () => {
        $(".vt-resend-email-form-wrapper .close svg,#vt_send_email_cancel").click(function () {
            $(".vt-resend-email-form ").remove();
        });
    }

    const vt_resend_email_form =() => {
        $( "#vt_resend_email_form" ).submit(function( event ) {
            event.preventDefault();
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
                    $(".vt-resend-email-form ").remove();
                } else{
                    set_notification(response.message, 'error', response.message_type);
                }
                usb_swiper_remove_loader(submitButton);
            });
        });
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

    $('#date-toggle').on('click', function() {
        var toggleIcon = $(this);
        var toggleValue = toggleIcon.hasClass('asc') ? 'desc' : 'asc';

        toggleIcon.toggleClass('asc', toggleValue === 'asc');
        toggleIcon.toggleClass('desc', toggleValue === 'desc');
        toggleIcon.html(toggleValue === 'asc' ? '&#x25B2;' : '&#x25BC;');

        var currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('date_toggle', toggleValue);
        window.location.href = currentUrl.href;
    });

    // Initialize the datepicker for start date field
    $('#start-date').datepicker({
        dateFormat: 'yy-mm-dd', // The format to submit the date in
        changeMonth: true,
        changeYear: true,
        onSelect: function(selectedDate) {
            $('#end-date').datepicker('option', 'minDate', selectedDate);
        }
    });

    // Initialize the datepicker for end date field
    $('#end-date').datepicker({
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true
    });

    // $('#vt_search').on('change', function() {
    //     var searchDate = $(this).val();
    //     var regex = /^\d{2}\/\d{2}\/\d{4}$/; // Regular expression for dd/mm/yyyy format
    //
    //     if (!regex.test(searchDate)) {
    //         alert('Please enter the date in dd/mm/yyyy format.');
    //         return false; // Prevent form submission
    //     }
    // });
});

jQuery( document ).ready(function( $ ) {

    if( $('#ae-paypal-pos-form').length > 0 ) {
        $(document).scroll(function () {
            var fh = $('#ae-paypal-pos-form').offset().top;
            var scroll = $(window).scrollTop();
            var formobj = $('#ae-paypal-pos-form .vt-col-payments');


            if (fh <= scroll) {
                formobj.addClass('fixed');
            } else {
                formobj.removeClass('fixed');
            }
        });
    }

    const set_notification = ( message, type ='success', message_type='' ) => {
        var notification = "<p class='notification "+type+"'><strong>"+message_type+"</strong>"+message+"</p>"
        $('.vt-form-notification').append(notification);
        $([document.documentElement, document.body]).animate({ scrollTop: ( $(".vt-form-notification").offset().top) - 10 }, 1000);
    }

    const render_cc_form = () => {

        let orderId;

        var usb_swiper_ppcp_style = {
            layout: usb_swiper_settings.style_layout,
            color: usb_swiper_settings.style_color,
            shape: usb_swiper_settings.style_shape,
            label: usb_swiper_settings.style_label
        };
        if (usb_swiper_settings.style_height !== '') {
            usb_swiper_ppcp_style['height'] = parseInt(usb_swiper_settings.style_height);
        }
        if (usb_swiper_settings.style_layout !== 'vertical') {
            usb_swiper_ppcp_style['tagline'] = (usb_swiper_settings.style_tagline === 'yes') ? true : false;
        }

        if (paypal.HostedFields.isEligible()) {

            paypal.HostedFields.render({
                createOrder: function () {
                    if ($('form#ae-paypal-pos-form').is('.createOrder') === false) {
                        $('form#ae-paypal-pos-form').addClass('createOrder');

                        return fetch(usb_swiper_settings.create_transaction_url, {
                            method: 'post',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: $('form#ae-paypal-pos-form').serialize(),
                        }).then(function (res) {
                            return res.json();
                        }).then(function (data) {

                            if( data.orderID ) {
                                return  data.orderID;
                            } else {
                                set_notification(data.message, 'error', data.message_type);
                            }
                        });
                    }
                },
                styles: {
                    '.valid': { 'color': 'green' },
                    '.invalid': { 'color': 'red' }
                },
                fields: {
                    number: {
                        selector: "#card-number",
                        placeholder: "4111 1111 1111 1111"
                    },
                    cvv: {
                        selector: "#cvv",
                        placeholder: "123"
                    },
                    expirationDate: {
                        selector: "#expiration-date",
                        placeholder: "MM/YY"
                    }
                }
            }).then(function (hf) {
                hf.on('cardTypeChange', function (event) {
                    if (event.cards.length === 1) {

                    }
                });

                $("form#ae-paypal-pos-form").validate({
                    rules: {},
                    messages: {},
                    submitHandler: function(form, event) {

                        event.preventDefault();
                        var state = hf.getState();
                        var contingencies = [];
                        contingencies = [usb_swiper_settings.three_d_secure_contingency];
                        $('form#ae-paypal-pos-form').addClass('processing').block({
                            message: null,
                            overlayCSS: {
                                background: '#fff',
                                opacity: 0.6
                            }
                        });
                        const firstName = document.getElementById('BillingFirstName') ? document.getElementById('BillingFirstName').value : '';
                        const lastName = document.getElementById('BillingLastName') ? document.getElementById('BillingLastName').value : '';
                        if (!firstName || !lastName) {
                            $('form#ae-paypal-pos-form').removeClass('processing paypal_cc_submiting HostedFields createOrder').unblock();
                        }
                        hf.submit({
                            contingencies: contingencies,
                            cardholderName: firstName + ' ' + lastName
                        }).then(
                            function (payload) {
                                if (payload.orderId) {
                                    $.post(usb_swiper_settings.cc_capture + "&paypal_transaction_id=" + payload.orderId + "&wc-process-transaction-nonce=" + usb_swiper_settings.usb_swiper_transaction_nonce, function (data) {
                                        if( data.result === 'success' ) {
                                            window.location.href = data.redirect;
                                        } else{
                                            set_notification(data.message, 'error', data.message_type);
                                            $('form#ae-paypal-pos-form').removeClass('processing paypal_cc_submiting HostedFields createOrder').unblock();
                                        }
                                    });
                                } else{
                                    set_notification(data.message, 'error', data.message_type);
                                    $('form#ae-paypal-pos-form').removeClass('processing paypal_cc_submiting HostedFields createOrder').unblock();
                                }
                            },
                            function (error) {
                                set_notification(error.message, 'error', error.name);
                                $('form#ae-paypal-pos-form').removeClass('processing paypal_cc_submiting HostedFields createOrder').unblock();
                            }
                        );

                        return false;
                    }
                });
            });
        } else {

            $('.usb-swiper-advanced-cc-form').hide();

            if( jQuery('#angelleye_ppcp_checkout').length > 0 ) {

                paypal.Buttons({
                    onClick: function()  {

                        if( $('form#ae-paypal-pos-form').valid() ) {
                            return true;
                        } else{
                            return false;
                        }
                    },
                    createOrder: function(data, actions) {
                        if ($('form#ae-paypal-pos-form').is('.createOrder') === false) {
                            $('form#ae-paypal-pos-form').addClass('createOrder');

                            return fetch(usb_swiper_settings.create_transaction_url, {
                                method: 'post',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: $('form#ae-paypal-pos-form').serialize(),
                            }).then(function (res) {
                                return res.json();
                            }).then(function (data) {

                                if (typeof data.success !== 'undefined') {

                                } else {
                                    return data.orderID;
                                }

                                return data.orderID;
                            });
                        }
                    },
                    onApprove: function(data, actions) {

                        if (data.orderID) {
                            $.post(usb_swiper_settings.cc_capture + "&paypal_transaction_id=" + data.orderID + "&wc-process-transaction-nonce=" + usb_swiper_settings.usb_swiper_transaction_nonce, function (data) {
                                if( data.result === 'success' ) {
                                    window.location.href = data.redirect;
                                } else{
                                    set_notification(data.message, 'error', data.message_type);
                                    $('form#ae-paypal-pos-form').removeClass('processing paypal_cc_submiting HostedFields createOrder').unblock();
                                }
                            });
                        }
                    }
                }).render('#angelleye_ppcp_checkout');

            }

        }
    }

    render_cc_form();

    const usb_swiper_add_loader = ( current_obj) => {
        current_obj.append('<span class="vt-loader"></span>');
    };

    const usb_swiper_remove_loader = ( current_obj) => {
        current_obj.children('.vt-loader').remove();
    };

    $(document).on('change','#TransactionCurrency', function () {
        $('form#ae-paypal-pos-form').addClass('processing').block({
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6
            }
        });
        window.location.href = usb_swiper_settings.vt_page_url+'?'+$(this).attr('name')+'='+$(this).val();
    });

    $('.refund-form-wrap').hide();

    $(document).on('click','.transaction-refund', function (event) {
        event.preventDefault();
        $(this).hide();
        $('.refund-form-wrap').show();
    });

    $(document).on('click','.cancel-refund', function (event) {
        event.preventDefault();
        $('.transaction-refund').show();
        $('.refund-form-wrap').hide();
    });

    $( "#vt_refund_form" ).submit(function( event ) {
        var form = $(this);
        var form_id = form.attr('id');
        var submitButton = form.find('.confirm-transaction-refund');
        usb_swiper_add_loader(submitButton);

        jQuery.ajax({
            url: usb_swiper_settings.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: $(this).serialize()+"&action=create_refund_request",
        }).done(function ( response ) {

            if( response.status) {
                set_notification(response.message, 'success');
                document.getElementById(form_id).reset();
                $('.transaction-refund').show();
                $('.refund-form-wrap').hide();
                $('.refund-details').html('').html(response.html);
            } else{
                set_notification(response.message, 'error', response.message_type);
            }

            usb_swiper_remove_loader(submitButton);
        });

        event.preventDefault();
    });
});

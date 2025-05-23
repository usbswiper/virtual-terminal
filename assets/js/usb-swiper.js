jQuery( document ).ready(function( $ ) {
    let hostedFieldsInstance;

    if(!usb_swiper_settings.is_customers) {
        var company = localStorage.getItem('Company');
        var BillingFirstName = localStorage.getItem('BillingFirstName');
        var BillingLastName = localStorage.getItem('BillingLastName');
        var BillingEmail = localStorage.getItem('BillingEmail');
        var CustomerEmail = localStorage.getItem('CustomerEmail');
        var OrderAmount = localStorage.getItem('OrderAmount');
        var Discount = localStorage.getItem('Discount');
        var NetAmount = localStorage.getItem('NetAmount');
        var ShippingAmount = localStorage.getItem('ShippingAmount');
        var HandlingAmount = localStorage.getItem('HandlingAmount');
        var TaxRate = localStorage.getItem('TaxRate');
        var InvoiceNumber = localStorage.getItem('InvoiceNumber');
        var Notes = localStorage.getItem('Notes');
        var billingInfo = localStorage.getItem('billingInfo');
        var BillingStreet = localStorage.getItem('BillingStreet');
        var BillingStreet2 = localStorage.getItem('BillingStreet2');
        var BillingCity = localStorage.getItem('BillingCity');
        var BillingState = localStorage.getItem('BillingState');
        var BillingPostalCode = localStorage.getItem('BillingPostalCode');
        var BillingCountryCode = localStorage.getItem('BillingCountryCode');
        var BillingPhoneNumber = localStorage.getItem('BillingPhoneNumber');
        var shippingDisabled = localStorage.getItem('shippingDisabled');
        var shippingSameAsBilling = localStorage.getItem('shippingSameAsBilling');
        var ShippingFirstName = localStorage.getItem('ShippingFirstName');
        var ShippingLastName = localStorage.getItem('ShippingLastName');
        var ShippingStreet = localStorage.getItem('ShippingStreet');
        var ShippingStreet2 = localStorage.getItem('ShippingStreet2');
        var ShippingCity = localStorage.getItem('ShippingCity');
        var ShippingState = localStorage.getItem('ShippingState');
        var ShippingPostalCode = localStorage.getItem('ShippingPostalCode');
        var ShippingCountryCode = localStorage.getItem('ShippingCountryCode');
        var ShippingPhoneNumber = localStorage.getItem('ShippingPhoneNumber');
        var ShippingEmail = localStorage.getItem('ShippingEmail');
        var TaxAmount = localStorage.getItem('TaxAmount');
        var GrandTotal = localStorage.getItem('GrandTotal');
        var CustomerInformation = localStorage.getItem('CustomerInformation');

        var ProductsData = localStorage.getItem('ProductsData');
        if(ProductsData){
            ProductsData = JSON.parse(ProductsData);
        }

        if (ProductsData && ProductsData.length > 0) {

            for( var i = 0; i < ProductsData.length; i++ ){

                if( i === 0 ){
                    jQuery('#VTProduct_'+i).val(ProductsData[i].name);
                    jQuery('#VTProductQuantity_'+i).val(ProductsData[i].quantity);
                    jQuery('#VTProductPrice_'+i).val(ProductsData[i].price);
                    jQuery('#VTProductID_'+i).val(ProductsData[i].id);
                    continue;
                }

                var productHtml = usb_swiper_settings.display_vt_product_wrapper;
                if(productHtml){
                    productHtml = productHtml.replace(/index_id/g,i);

                    jQuery('#vt_repeater_field').append(productHtml);
                    jQuery('#VTProduct_'+i).val(ProductsData[i].name);
                    jQuery('#VTProductQuantity_'+i).val(ProductsData[i].quantity);
                    jQuery('#VTProductPrice_'+i).val(ProductsData[i].price);
                    jQuery('#VTProductID_'+i).val(ProductsData[i].id);
                }

            }

        }
        var TransactionCurrency = localStorage.getItem('TransactionCurrency');

        if (company !== null && company !== 'undefined') $('#company').val(company);
        if (BillingFirstName !== null && BillingFirstName !== 'undefined') $('#BillingFirstName').val(BillingFirstName);
        if (BillingLastName !== null && BillingLastName !== 'undefined') $('#BillingLastName').val(BillingLastName);
        if (BillingEmail !== null && BillingEmail !== 'undefined') $('#BillingEmail').val(BillingEmail);
        if (OrderAmount !== null && OrderAmount !== 'undefined') $('#OrderAmount').val(OrderAmount);
        if (Discount !== null && Discount !== 'undefined') $('#Discount').val(Discount);
        if (NetAmount !== null && NetAmount !== 'undefined') $('#NetAmount').val(NetAmount);
        if (ShippingAmount !== null && ShippingAmount !== 'undefined') $('#ShippingAmount').val(ShippingAmount);
        if (HandlingAmount !== null && HandlingAmount !== 'undefined') $('#HandlingAmount').val(HandlingAmount);
        if (CustomerEmail !== null && CustomerEmail !== 'undefined') $('#CustomerEmail').val(CustomerEmail);
        if (TransactionCurrency !== null && TransactionCurrency !== 'undefined') $('#TransactionCurrency').val(TransactionCurrency);
        if (!isNaN(TaxRate) && TaxRate !== '' && TaxRate !== null && TaxRate !== 'undefined') {
            TaxRate = parseInt(TaxRate);
            $('#TaxRate').val(TaxRate);
        }
        if (InvoiceNumber !== null && InvoiceNumber !== 'undefined') $('#InvoiceID').val(InvoiceNumber);
        if (Notes !== null && Notes !== 'undefined') $('#Notes').val(Notes);
        if (BillingStreet !== null && BillingStreet !== 'undefined') $('#BillingStreet').val(BillingStreet);
        if (BillingStreet2 !== null && BillingStreet2 !== 'undefined') $('#BillingStreet2').val(BillingStreet2);
        if (BillingCity !== null && BillingCity !== 'undefined') $('#BillingCity').val(BillingCity);
        if (BillingState !== null && BillingState !== 'undefined') $('#BillingState').val(BillingState);
        if (BillingPostalCode !== null && BillingPostalCode !== 'undefined') $('#BillingPostalCode').val(BillingPostalCode);
        if (BillingCountryCode !== null && BillingCountryCode !== 'undefined') $('#BillingCountryCode').val(BillingCountryCode);
        if (BillingPhoneNumber !== null && BillingPhoneNumber !== 'undefined') $('#BillingPhoneNumber').val(BillingPhoneNumber);
        if (ShippingFirstName !== null && ShippingFirstName !== 'undefined') $('#ShippingFirstName').val(ShippingFirstName);
        if (ShippingLastName !== null && ShippingLastName !== 'undefined') $('#ShippingLastName').val(ShippingLastName);
        if (ShippingStreet !== null && ShippingStreet !== 'undefined') $('#ShippingStreet').val(ShippingStreet);
        if (ShippingStreet2 !== null && ShippingStreet2 !== 'undefined') $('#ShippingStreet2').val(ShippingStreet2);
        if (ShippingCity !== null && ShippingCity !== 'undefined') $('#ShippingCity').val(ShippingCity);
        if (ShippingState !== null && ShippingState !== 'undefined') $('#ShippingState').val(ShippingState);
        if (ShippingPostalCode !== null && ShippingPostalCode !== 'undefined') $('#ShippingPostalCode').val(ShippingPostalCode);
        if (ShippingCountryCode !== null && ShippingCountryCode !== 'undefined') $('#ShippingCountryCode').val(ShippingCountryCode);
        if (ShippingPhoneNumber !== null && ShippingPhoneNumber !== 'undefined') $('#ShippingPhoneNumber').val(ShippingPhoneNumber);
        if (ShippingPhoneNumber !== null && ShippingPhoneNumber !== 'undefined') $('#ShippingPhoneNumber').val(ShippingPhoneNumber);
        if (ShippingEmail !== null && ShippingEmail !== 'undefined') $('#ShippingEmail').val(ShippingEmail);
        setTimeout(function(){
            if (billingInfo !== null && billingInfo !== 'undefined') {
                $('#billingInfo').bootstrapSwitch('state', billingInfo === 'true');
            }
            if (shippingDisabled !== null && shippingDisabled !== 'undefined') {
                $('#shippingDisabled').bootstrapSwitch('state', shippingDisabled === 'true');
            }
            if (shippingSameAsBilling !== null && shippingSameAsBilling !== 'undefined') {
                $('#shippingSameAsBilling').bootstrapSwitch('state', shippingSameAsBilling === 'true');
            }
        }, 900);

        if (TaxAmount !== null && TaxAmount !== 'undefined') $('#TaxAmount').val(TaxAmount);
        if (GrandTotal !== null && GrandTotal !== 'undefined') $('#GrandTotal').val(GrandTotal);
        if (CustomerInformation !== null && CustomerInformation !== 'undefined') $('#customerInformation').val(CustomerInformation);

        if (CustomerInformation !== null && CustomerInformation !== 'undefined') {
            jQuery('.clear-customer-details').removeClass('hidden').show();
        } else {
            jQuery('.clear-customer-details').addClass('hidden').hide();
        }

        var selectedCurrencyCode = $('.usbswiper-change-currency').find('option:selected').data('currency_code');
        var selectedCurrency = $('.usbswiper-change-currency').val();
        updateCurrencySymbol(selectedCurrencyCode, selectedCurrency);
    }

    $(document).on('click', '#new-order-btn', function(event) {
        event.preventDefault();
        const confirmDiscard = confirm(usb_swiper_settings.start_new_order_conformation);
        if (confirmDiscard) {
            jQuery('input[type="text"]').val('');
            jQuery('input[type="number"]').val('');
            jQuery('#BillingState').val('');
            $('.vt-customer-search-result').remove();
            $('.clear-customer-details').hide();
            if(paypal.HostedFields && hostedFieldsInstance) {
                hostedFieldsInstance.clear('number');
                hostedFieldsInstance.clear('expirationDate');
                hostedFieldsInstance.clear('cvv');
            }
            removeLocalData();

            set_notification(usb_swiper_settings.start_new_order_success, 'success', '');
            document.querySelector('.start-new-order').style.display = 'none';
            setTimeout(function() {
                $('.vt-form-notification .notification').remove();
            }, 5000);
        }
    });

    $(document).on('click','#PayByInvoice', function (){

        var VtForm = $('form#ae-paypal-pos-form');

        if(VtForm.valid()){
            VtForm.block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });
            $(this).prop('disabled', true);
            if (VtForm.is('.createOrder') === false) {
                VtForm.addClass('createOrder');
                return fetch(usb_swiper_settings.create_transaction_url, {
                    method: 'post',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: VtForm.serialize(),
                }).then(function (res) {
                    return res.json();
                }).then(function (data) {
                    if( undefined !== data.invoiceUrl ){
                        removeLocalData();
                        window.location.href = data.invoiceUrl;
                    } else {
                        set_notification(data.message, 'error', data.message_type);
                    }
                    $(this).prop('disabled', false);
                    VtForm.removeClass('processing paypal_cc_submiting HostedFields createOrder').unblock();
                });
            } else {
                VtForm.removeClass('processing paypal_cc_submiting HostedFields createOrder').unblock();
            }
        }
    });

    $(document).on('click','#PayWithZettle', function (data){

        var currentObj = $(this);
        var notificationWrap = $('.vt-col-pay-with-zettle .zettle-response');
        var notificationObj = notificationWrap.find('ul');
        notificationObj.children('li').remove();
        currentObj.prop('disabled', true);
        var VtForm = $('form#ae-paypal-pos-form');
        vt_remove_notification();
        remove_zettle_notification(notificationObj);
        if( VtForm.valid() ) {
            VtForm.block({message: null, overlayCSS: {background: '#fff', opacity: 0.6}});
            if (VtForm.is('.createOrder') === false) {
                VtForm.addClass('createOrder');
                VtForm.unblock();
                add_zettle_notification(usb_swiper_settings.create_transaction_message, notificationObj);
                notificationWrap.show();

                return fetch(usb_swiper_settings.create_zettle_request, {
                    method: 'post',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: VtForm.serialize(),
                }).then(function (res) {
                    return res.json();
                }).then(function (response) {
                    if( response.status ) {
                        add_zettle_notification(response.data.payment_request_message, notificationObj);
                        var payment_request = response.data.payment_request;

                        const socket = new WebSocket( response.data.websocket_url );

                        if( 1 === WebSocket.OPEN ) {

                            socket.addEventListener('open', (event) => {
                                socket.send(payment_request);
                            });

                            socket.addEventListener('message', (event) => {
                                if( event.data ) {
                                    var data = JSON.parse( event.data );
                                    var messageData = data.payload;

                                    if( messageData.paymentProgress !== '' && undefined !== messageData.paymentProgress ) {
                                        add_zettle_notification(messageData.paymentProgress, notificationObj);
                                    } else if( messageData.type === 'PAYMENT_RESULT_RESPONSE' && messageData.resultStatus === 'failed' ) {
                                        add_zettle_notification(messageData.resultErrorDescription, notificationObj);
                                    }

                                    if( messageData.type === 'PAYMENT_RESULT_RESPONSE' ) {

                                        $.ajax({
                                            url: usb_swiper_settings.zettle_payment_response,
                                            type: 'POST',
                                            dataType: 'json',
                                            data: "action=zettle_payment_response&message_id="+data.messageId+"&response=" + JSON.stringify(messageData),
                                        }).done(function (response) {
                                            if( response.status ){
                                                localStorage.removeItem('vt_order_id');
                                                localStorage.removeItem('transaction_id');
                                                window.location.href = response.redirect_url;
                                            } else {
                                                set_notification( response.message, 'error'  );
                                            }

                                            currentObj.prop('disabled', false);
                                            vt_remove_notification();
                                            remove_zettle_notification(notificationObj);
                                            VtForm.removeClass('processing paypal_cc_submiting HostedFields createOrder').unblock();
                                        });
                                    }
                                }
                            });

                            socket.addEventListener('error', (event) => {

                                set_notification(usb_swiper_settings.zettle_socket_error_message, 'error', 'Error');

                                console.error('WebSocket error:', event);

                                $.ajax({
                                    url: usb_swiper_settings.zettle_payment_response,
                                    type: 'POST',
                                    dataType: 'json',
                                    data: "action=zettle_payment_failed_response&transaction_id"+response.transaction_id,
                                }).done(function (response) {
                                    if( response.status ){
                                        localStorage.removeItem('vt_order_id');
                                        localStorage.removeItem('transaction_id');
                                        window.location.href = response.redirect_url;
                                    } else {
                                        set_notification(response.message, 'error', response.message_type);
                                    }
                                    currentObj.prop('disabled', false);
                                    vt_remove_notification();
                                    remove_zettle_notification(notificationObj);
                                    VtForm.removeClass('processing paypal_cc_submiting HostedFields createOrder').unblock();
                                });
                            });

                            socket.addEventListener('close', (event) => {
                                console.log('WebSocket connection closed:', event);
                                VtForm.removeClass('processing paypal_cc_submiting HostedFields createOrder').unblock();
                            });

                        } else {
                            set_notification(response.websocket_message, 'error');
                            remove_zettle_notification(notificationObj);
                        }
                        currentObj.prop('disabled', false);
                        VtForm.removeClass('processing paypal_cc_submiting HostedFields createOrder').unblock();
                    } else {
                        currentObj.prop('disabled', false);
                        VtForm.removeClass('processing paypal_cc_submiting HostedFields createOrder').unblock();
                        remove_zettle_notification(notificationObj);
                    }
                });

            } else {
                currentObj.prop('disabled', false);
                VtForm.removeClass('processing paypal_cc_submiting HostedFields createOrder').unblock();
            }
        } else {
            currentObj.prop('disabled', false);
        }
    });

    if( $('#ae-paypal-pos-form').length > 0 ) {
        $(document).scroll(function () {
            var fh = $('#ae-paypal-pos-form').offset().top;
            var scroll = $(window).scrollTop();
            var formobj = $('#ae-paypal-pos-form .vt-col-payments');
            var pay_by_invoice = $('#ae-paypal-pos-form .vt-col-pay-by-invoice');
            var payment_div = $('#ae-paypal-pos-form .vt-payment-wrapper');

            if (fh <= scroll) {
                payment_div.addClass('fixed');
            } else {
                payment_div.removeClass('fixed');
            }
        });
    }

    const set_notification = ( message, type ='success', message_type='' ) => {
        var notification = "<p class='notification "+type+"'><strong>"+message_type+"</strong>"+message+"</p>"
        $('.vt-form-notification').empty().append(notification);

        $([document.documentElement, document.body]).animate({ scrollTop: ( $(".vt-form-notification").offset().top) - 150 }, 1000);
    }

    $.validator.addMethod("is_email", function(value, element) {
        return this.optional(element) || /^[a-zA-Z0-9._-]+@[a-zA-Z0-9-]+\.[a-zA-Z.]{2,5}$/i.test(value);
    }, usb_swiper_settings.email_validation_message);

    $.validator.addMethod("greaterThanZero", function(value, elesment) {
        return parseFloat(value) > 0;
    }, usb_swiper_settings.product_min_price);

    $.validator.addMethod("onlyDigits", function(value, element) {
        return (value && /^\d+$/.test(value));
    }, usb_swiper_settings.product_min_qty_message);


    $(document).on('keyup', 'input[name="VTProductQuantity[]"]', function () {
        let currentObj = $(this);
        let val = currentObj.val();
        if( /^\d+$/.test(val) === false) {
            currentObj.val("");
        }
    });

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

        var VtForm = $('form#ae-paypal-pos-form');

        if (paypal.HostedFields.isEligible()) {
            paypal.HostedFields.render({
                createOrder: function () {
                    if (VtForm.is('.createOrder') === false) {
                        VtForm.addClass('createOrder').block({
                            message: null,
                            overlayCSS: {
                                background: '#fff',
                                opacity: 0.6
                            }
                        });

                        var transaction_id = localStorage.getItem("transaction_id");

                        var transactionData = VtForm.serialize();
                        if(transaction_id) {
                            transactionData += '&transaction_type=retry&transaction_id='+transaction_id;
                        }

                        return fetch(usb_swiper_settings.create_transaction_url, {
                            method: 'post',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: transactionData,
                        }).then(function (res) {
                            return res.json();
                        }).then(function (data) {

                            if( data.orderID ) {
                                localStorage.setItem("vt_order_id", data.orderID);
                                localStorage.setItem("transaction_id", data.transaction_id);
                                return data.orderID;
                            } else {
                                set_notification(data.message, 'error', data.message_type);
                                VtForm.removeClass('processing paypal_cc_submiting HostedFields createOrder').unblock();
                                document.querySelector('.start-new-order').style.display = 'block';
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
                hostedFieldsInstance = hf;
                hf.on('cardTypeChange', function (event) {
                    if (event.cards.length === 1) {
                        $('#card_type').val(event.cards[0].niceType);
                    }
                });
                
                VtForm.validate({
                    rules: {
                        'VTProductQuantity[]': {
                            required: true,
                            onlyDigits: true
                        }
                    },
                    messages: {
                        'VTProductQuantity[]': {
                            onlyDigits: usb_swiper_settings.product_min_qty_message,
                            min: usb_swiper_settings.product_min_qty_message
                        }
                    },
                    submitHandler: function(form, event) {

                        event.preventDefault();
                        var state = hf.getState();
                        var contingencies = [];
                        contingencies = [usb_swiper_settings.three_d_secure_contingency];
                        VtForm.addClass('processing').block({
                            message: null,
                            overlayCSS: {
                                background: '#fff',
                                opacity: 0.6
                            }
                        });
                        const firstName = document.getElementById('BillingFirstName') ? document.getElementById('BillingFirstName').value : '';
                        const lastName = document.getElementById('BillingLastName') ? document.getElementById('BillingLastName').value : '';
                        if (!firstName || !lastName) {
                            VtForm.removeClass('processing paypal_cc_submiting HostedFields createOrder').unblock();
                        }
                        hf.submit({
                            contingencies: contingencies,
                            cardholderName: firstName + ' ' + lastName
                        }).then(
                            function (payload) {
                                localStorage.removeItem('vt_order_id');
                                if (payload.orderId) {
                                    let transaction_id = localStorage.getItem("transaction_id");
                                    $.post(usb_swiper_settings.cc_capture + "&paypal_transaction_id=" + payload.orderId + "&wc-process-transaction-nonce=" + usb_swiper_settings.usb_swiper_transaction_nonce + "&pbi_transaction_id="+transaction_id, function (data) {
                                        if( data.result === 'success' ) {
                                            removeLocalData();
                                            window.location.href = data.redirect;
                                        } else{
                                            set_notification(data.message, 'error', data.message_type);
                                            VtForm.removeClass('processing paypal_cc_submiting HostedFields createOrder').unblock();
                                            document.querySelector('.start-new-order').style.display = 'block';
                                        }
                                    });
                                } else{
                                    set_notification(payload.message, 'error', payload.message_type);
                                    VtForm.removeClass('processing paypal_cc_submiting HostedFields createOrder').unblock();
                                }
                            },
                            function (error) {
                                var message = error.message;
                                jQuery.each( error.details, function( key, value ) {
                                    message += '<span>'+value.description+'</span>';
                                });
                                var order_id = localStorage.getItem("vt_order_id");
                                var transaction_id = localStorage.getItem("transaction_id");
                                localStorage.removeItem('vt_order_id');
                                set_notification(message, 'error', error.name);
                                VtForm.removeClass('processing paypal_cc_submiting HostedFields createOrder').unblock();
                                jQuery.ajax({
                                    url: usb_swiper_settings.ajax_url,
                                    type: 'POST',
                                    dataType: 'json',
                                    data: "action=update_order_status&order_id=" + order_id+'&message='+message+'&transaction_id='+transaction_id+'&error='+JSON.stringify(error),
                                }).done(function ( response ) {
                                    VtForm.removeClass('processing paypal_cc_submiting HostedFields createOrder').unblock();
                                });
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
                        if( VtForm.valid() ) {
                            return true;
                        } else{
                            return false;
                        }
                    },
                    createOrder: function(data, actions) {
                        if (VtForm.is('.createOrder') === false) {
                            VtForm.addClass('createOrder');

                            return fetch(usb_swiper_settings.create_transaction_url, {
                                method: 'post',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: VtForm.serialize(),
                            }).then(function (res) {
                                return res.json();
                            }).then(function (data) {

                                if( data.orderID ) {
                                    localStorage.setItem("vt_order_id", data.orderID);
                                    localStorage.setItem("transaction_id", data.transaction_id);
                                    return data.orderID;
                                } else {
                                    set_notification(data.message, 'error', data.message_type);
                                    VtForm.removeClass('processing paypal_cc_submiting HostedFields createOrder').unblock();
                                    document.querySelector('.start-new-order').style.display = 'block';
                                }

                                return data.orderID;
                            });
                        } else {
                            VtForm.removeClass('processing paypal_cc_submiting HostedFields createOrder').unblock();
                        }
                    },
                    onApprove: function(data, actions) {

                        if (data.orderID) {
                            $.post(usb_swiper_settings.cc_capture + "&paypal_transaction_id=" + data.orderID + "&wc-process-transaction-nonce=" + usb_swiper_settings.usb_swiper_transaction_nonce, function (data) {
                                if( data.result === 'success' ) {
                                    removeLocalData();
                                    window.location.href = data.redirect;
                                } else{
                                    set_notification(data.message, 'error', data.message_type);
                                    VtForm.removeClass('processing paypal_cc_submiting HostedFields createOrder').unblock();
                                    document.querySelector('.start-new-order').style.display = 'block';
                                }
                            });
                        } else {
                            VtForm.removeClass('processing paypal_cc_submiting HostedFields createOrder').unblock();
                        }
                    }
                }).render('#angelleye_ppcp_checkout');
            }
        }
    }

    var VtForm = $('form#ae-paypal-pos-form');
    if( undefined !== VtForm && VtForm.length > 0 ) {
        render_cc_form();
    }

    const usb_swiper_add_loader = ( current_obj) => {
        current_obj.append('<span class="vt-loader"></span>');
    };

    const usb_swiper_remove_loader = ( current_obj ) => {
        current_obj.children('.vt-loader').remove();
    };

    $(document).on('change','.usbswiper-change-currency', function (e) {
        e.preventDefault();
        var formData = {
            Company: $('#company').val(),
            BillingFirstName: $('#BillingFirstName').val(),
            BillingLastName: $('#BillingLastName').val(),
            BillingEmail: $('#BillingEmail').val(),
            OrderAmount: $('#OrderAmount').val(),
            Discount: $('#Discount').val(),
            NetAmount: $('#NetAmount').val(),
            ShippingAmount: $('#ShippingAmount').val(),
            HandlingAmount: $('#HandlingAmount').val(),
            TaxRate: $('#TaxRate').val(),
            InvoiceNumber: $('#InvoiceID').val(),
            Notes: $('#Notes').val(),
            billingInfo: $('#billingInfo').val(),
            BillingStreet: $('#BillingStreet').val(),
            BillingStreet2: $('#BillingStreet2').val(),
            BillingCity: $('#BillingCity').val(),
            BillingState: $('#BillingState').val(),
            BillingPostalCode: $('#BillingPostalCode').val(),
            BillingCountryCode: $('#BillingCountryCode').val(),
            BillingPhoneNumber: $('#BillingPhoneNumber').val(),
            shippingDisabled: $('#shippingDisabled').val(),
            shippingSameAsBilling: $('#shippingSameAsBilling').val(),
            ShippingFirstName: $('#ShippingFirstName').val(),
            ShippingLastName: $('#ShippingLastName').val(),
            ShippingStreet: $('#ShippingStreet').val(),
            ShippingStreet2: $('#ShippingStreet2').val(),
            ShippingCity: $('#ShippingCity').val(),
            ShippingState: $('#ShippingState').val(),
            ShippingPostalCode: $('#ShippingPostalCode').val(),
            ShippingCountryCode: $('#ShippingCountryCode').val(),
            ShippingPhoneNumber: $('#ShippingPhoneNumber').val(),
            TaxAmount: $('#TaxAmount').val(),
            GrandTotal: $('#GrandTotal').val(),
            CustomerInformation: $('#customerInformation').val(),
            TransactionCurrency: $('#TransactionCurrency').val(),
        };

        var productsArray = [];

        $('#vt_repeater_field .vt-fields-wrap').each(function () {
            var productName = $(this).find('.vt-product-input').val();
            var productQuantity = $(this).find('.vt-product-quantity').val();
            var productPrice = $(this).find('.vt-product-price').val();
            var productId = $(this).find('input[name="VTProductID[]"]').val();

            var productData = {
                name: productName,
                quantity: productQuantity,
                price: productPrice,
                id: productId
            };

            productsArray.push(productData);

        });
        saveDataToLocalStorage(formData);

        var selectedCurrencyCode = $(this).find('option:selected').data('currency_code');
        var selectedCurrency = $(this).val();
        updateCurrencySymbol(selectedCurrencyCode, selectedCurrency);

        $('form#ae-paypal-pos-form').removeClass('processing').unblock();
    });

    $('.refund-form-wrap').hide();

    $(document).on('click','.transaction-refund', function (event) {
        event.preventDefault();
        $(this).hide();
        $('.refund-form-wrap').show();
    });

    $(document).on('change', '#DiscountType, #Discount', function(){
        var orderAmount = ( $('#OrderAmount').val().replace(/,/g, '') * 1 );
        var discountInput = ( $('#Discount').val().replace(/,/g, '') * 1 );
        var discountType = $('#DiscountType').val();

        var discountAmount = 0;
        var netAmount = 0;

        if ( !isNaN(orderAmount) && !isNaN(discountInput) && discountInput !== '') {
            discountInput = parseFloat(discountInput); // Convert to float
            if (discountType === 'percent') {
                discountAmount = (orderAmount * discountInput) / 100;
            } else {
                discountAmount = discountInput;
            }
        }

        if (discountAmount > orderAmount) {
            set_notification('Discount Amount is greater than Order Amount so please add valid discount amount', 'error');
            $('#pos-submit-btn').prop('disabled', true);
            $('#PayByInvoice').prop('disabled', true);
        } else {
            $('#pos-submit-btn').prop('disabled', false);
            $('#PayByInvoice').prop('disabled', false);
        }

        $('#DiscountAmount').val(discountAmount.toFixed(2));

        if ( !isNaN(orderAmount) && !isNaN(discountAmount) ) {
            netAmount = orderAmount.toFixed(2) - discountAmount.toFixed(2);
        }

        setTimeout( function () {
            $('#NetAmount').val(netAmount.toFixed(2));
            updateSalesTax();
            updateGrandTotal();
        }, 800);

    });

    $(document).on('click','.refund-form-wrap .cancel-refund', function (event) {
        event.preventDefault();
        $('.transaction-refund').show();
        $('.refund-form-wrap').hide();
    });

    $(document).on('click','#print_transaction_receipt', function (event) {
        updatePageSize();
        window.print();
    });

    $( "#vt_refund_form" ).submit(function( event ) {
        var form = $(this);
        var form_id = form.attr('id');
        var submitButton = form.find('.confirm-transaction-refund')
        usb_swiper_add_loader(submitButton);

        var transaction_type= form.find('#transaction_type').val();

        if( transaction_type === 'zettle' ) {

            var notificationWrap = $('.zettle-refund-response');
            var notificationObj = notificationWrap.find('ul');
            notificationObj.children('li').remove();

            jQuery.ajax({
                url: usb_swiper_settings.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: $(this).serialize()+"&action=create_zettle_refund_request",
            }).done(function ( response ) {

                if(response.status) {
                    var refund_request = response.data.refund_request;

                    const socket = new WebSocket( response.data.websocket_url );

                    if( 1 === WebSocket.OPEN ) {

                        add_zettle_notification(response.data.refund_request_message, notificationObj);
                        notificationWrap.show();

                        socket.addEventListener('open', (event) => {
                            socket.send(refund_request);
                        });

                        socket.addEventListener('message', (event) => {

                            if( event.data ) {
                                var data = JSON.parse( event.data );
                                var messageData = data.payload;
                                
                                if( messageData.refundProgress !== '' && undefined !== messageData.refundProgress ) {
                                    add_zettle_notification(messageData.refundProgress, notificationObj);
                                } else if( messageData.type === 'REFUND_RESULT_RESPONSE' && messageData.resultStatus === 'failed' ) {
                                    add_zettle_notification(messageData.resultErrorCode, notificationObj);
                                }

                                if( messageData.type === 'REFUND_RESULT_RESPONSE' ) {

                                    $.ajax({
                                        url: usb_swiper_settings.zettle_payment_response,
                                        type: 'POST',
                                        dataType: 'json',
                                        data: "action=zettle_refund_payment_response&message_id="+data.messageId+"&response=" + JSON.stringify(messageData),
                                    }).done( function (response) {
                                        if( response.status ){
                                            window.location.href = response.redirect_url;
                                        } else {
                                            set_notification( response.message, 'error'  );
                                        }
                                    });
                                }
                            }
                        });

                        socket.addEventListener('error', (event) => {

                        });

                        socket.addEventListener('close', (event) => {
                            console.log('WebSocket connection closed:', event);
                        });
                    } else {
                        remove_zettle_notification(notificationObj);
                        add_zettle_notification(response.websocket_message, notificationObj);
                        notificationWrap.show();
                        usb_swiper_remove_loader(submitButton);
                    }

                } else {
                    set_notification(response.message, 'error');
                }
            });
        } else {

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
                    $('.payment-status-text').html('').html(response.refund_status);
                    $(".vt-refund-popup-wrapper").hide();
                    if( Number(response.remain_amount) > 0 ){
                        $('.remain-amount-input').val(response.remain_amount);
                        $('.refund-amount-input').attr({
                            max: response.remain_amount,
                            maxlength: response.remain_amount
                        });
                    }else{
                        $('.transaction-refund-wrap').remove();
                        $('.send-email-btn-wrapper').remove();
                    }
                } else{
                    set_notification(response.message, 'error', response.message_type);
                    $(".vt-refund-popup-wrapper").hide();
                }

                usb_swiper_remove_loader(submitButton);
            });
        }

        event.preventDefault();
    });

    jQuery("form#vt_add_product_form").validate({
        rules: {
            price: {
                required: true,
                greaterThanZero: true
            }
        },
        messages: {
            price: {
                greaterThanZero: usb_swiper_settings.product_min_price,
                step: usb_swiper_settings.price_step_message
            }

        },
        submitHandler: function (form, event) {
            $('.vt-form-notification').empty()
            event.preventDefault();

            var fd = new FormData();
            fd.append('action', 'create_update_product');
            fd.append('fields', $('#vt_add_product_form').serialize());
            fd.append('product_image', $('#vt_product_image')[0].files[0]);

            jQuery.ajax({
                url: usb_swiper_settings.ajax_url,
                type: 'POST',
                data: fd,
                processData: false,
                contentType: false,
            }).done(function (response) {
                if (response.status) {
                    window.location.href = response.redirect_url;
                } else {
                    set_notification(response.message, 'error');
                }
            });
        }
    });

    jQuery("form#vt_add_taxrule_form").validate({
        rules: {
            tax_label: {
                required: true
            },
            tax_rate: {
                required: true,
                greaterThanZero: true
            }
        },
        messages: {
            tax_rate: {
                greaterThanZero: usb_swiper_settings.product_min_price,
                step: usb_swiper_settings.price_step_message
            }

        },
        submitHandler: function (form, event) {
            $('.vt-form-notification').empty()
            event.preventDefault();

            var fd = new FormData();
            fd.append('action', 'create_update_product_tax');
            fd.append('fields', $('#vt_add_taxrule_form').serialize());

            jQuery.ajax({
                url: usb_swiper_settings.ajax_url,
                type: 'POST',
                data: fd,
                processData: false,
                contentType: false,
            }).done(function (response) {
                if (response.status) {
                    window.location.href = response.redirect_url;
                } else {
                    set_notification(response.message, 'error');
                }
            });
        }
    });

    $("#vt_verification_form").validate({
        rules: {
            email_address: {
                required: true,
                email: true,
                is_email: true,
            }
        },
        messages: {},
        submitHandler: function(form, event) {

            event.preventDefault();
            var currentFormObj = $("#vt_verification_form");
            var form_id = currentFormObj.attr('id');
            var submitButton = currentFormObj.find('#vt_verification_form_submit');
            usb_swiper_add_loader(submitButton);

            jQuery.ajax({
                url: usb_swiper_settings.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: currentFormObj.serialize()+"&action=vt_verification_form",
            }).done(function ( response ) {

                if( response.status) {
                    set_notification(response.message, 'success');
                    document.getElementById(form_id).reset();
                    window.location.href = response.location_redirect
                } else{
                    set_notification(response.message, 'error', response.message_type);
                }

                usb_swiper_remove_loader(submitButton);
            });
        }
    });

    $(document).on('click','.vt-remove-fields-wrap', function (){
        $(this).parent().remove();

        let net_price = 0;
        $( ".vt-product-quantity" ).each(function(index) {
            let quantity = $(this).val();
            let wrapper_id = $(this).parents('.vt-fields-wrap').attr('id');
            let price = $('#'+wrapper_id).children('.price').children('input').val();
            net_price += Number(quantity) * Number(price);
        });

        setTimeout( function () {
            $('#NetAmount').val(net_price.toFixed(2));
            $('#OrderAmount').val(net_price.toFixed(2));
            jQuery('#Discount').trigger('change');
            updateSalesTax();
            updateGrandTotal();
        }, 800);
    });

    $(document).on('click','#vt_add_item', function () {
        let loader = $(this);
        loader.attr('disabled','disabled')
        usb_swiper_add_loader(loader);

        let data = {
            'action': 'add_vt_product_wrapper',
            'vt-add-product-nonce': $('#vt_add_product_nonce').val(),
            'data-id': $('.vt-repeater-field .vt-fields-wrap').length
        };

        $.post(usb_swiper_settings.ajax_url, data, function (response) {
            if (response.status) {
                $('#vt_repeater_field').append( response.html );
                jQuery('.vt-product-price').autoNumeric('init', {
                    mDec: '2',
                    aSign: '',
                    wEmpty: '0',
                    lZero: 'allow',
                    aForm: false,
                    vMin: '0'
                });
                usb_swiper_remove_loader(loader);
                loader.removeAttr('disabled');
            } else {
                set_notification(response.message, 'error', response.message_type);
            }
        });
    });

    $(document).on('keyup','.vt-tax-input',function(){
        let search_val = $(this).val();
        let vt_product_input = $(this);
        let nonce = $('#vt_add_tax_nonce').val();

        vt_product_input.parents('.tax_rate_wrapper').find('span[data-tip]').remove();
        $("#TaxOnShipping").prop('checked', false);
        if(search_val.length >= 2) {

            let data = {
                'action': 'vt_search_tax',
                'tax-key': search_val,
                'vt-add-tax-nonce': nonce
            };
            $.post(usb_swiper_settings.ajax_url, data, function (response) {
                if (response.status) {
                    if(response.product_select) {
                        if (vt_product_input.parents('.tax_rate_wrapper').children('.currency-sign').children().hasClass('vt-search-result')) {
                            vt_product_input.parents('.tax_rate_wrapper').children('.currency-sign').children('.vt-search-result').remove();
                            vt_product_input.parents('.tax_rate_wrapper').children('.currency-sign').append('<div class="vt-search-result">' + response.product_select + '</div>')
                        } else {
                            vt_product_input.parents('.tax_rate_wrapper').children('.currency-sign').append('<div class="vt-search-result">' + response.product_select + '</div>')
                        }
                    } else {
                        $("#TaxOnShipping").prop('checked', true);
                        vt_product_input.parents('.tax_rate_wrapper').children('.currency-sign').children('.vt-search-result').remove();
                        setTimeout( function () {
                            updateSalesTax();
                            updateGrandTotal();
                        }, 800);
                    }
                } else {
                    set_notification(response.message, 'error', response.message_type);
                }
            });
        } else  {
            vt_product_input.parents('.tax_rate_wrapper').children('.currency-sign').children('.vt-search-result').remove();
            $("#TaxOnShipping").prop('checked', true);
        }
    });

    $(document).on('keyup','.vt-product-input', function () {
        let search_val = $(this).val();
        let vt_product_input = $(this);
        let nonce = $('#vt_add_product_nonce').val();
        let repeater = $('.vt-repeater-field');
        let data = {
            'action': 'vt_search_product',
            'product-key': search_val,
            'vt-add-product-nonce': nonce
        };

        /*var isTaxable = vt_product_input.attr('data-product-taxable');*/
        //vt_product_input.attr('data-product-taxable', false);
        /*if(isTaxable === 'true' ) {
            vt_product_input.parents('.vt-fields-wrap').children('.product_quantity').find('input.vt-product-quantity').val('');
            vt_product_input.parents('.vt-fields-wrap').children('.price').find('input.vt-product-price').val('');
        }*/

        repeater.children('.vt-fields-wrap').children('.product').children('.vt-search-result').remove();

        if(search_val.length >= 3) {
            $.post(usb_swiper_settings.ajax_url, data, function (response) {
                if (response.status) {
                    if(response.product_select) {
                        if (vt_product_input.parents('.vt-fields-wrap').children('.product').children().hasClass('vt-search-result')) {
                            vt_product_input.parents('.vt-fields-wrap').children('.product').children('.vt-search-result').remove();
                            vt_product_input.parents('.vt-fields-wrap').children('.product').append('<div class="vt-search-result">' + response.product_select + '</div>')
                        } else {
                            vt_product_input.parents('.vt-fields-wrap').children('.product').append('<div class="vt-search-result">' + response.product_select + '</div>')
                        }
                    } else {
                        vt_product_input.parents('.vt-fields-wrap').children('.product').children('.vt-search-result').remove();
                        setTimeout( function () {
                            updateSalesTax();
                            updateGrandTotal();
                        }, 800);
                    }
                } else {
                    set_notification(response.message, 'error', response.message_type);
                }
            });
        }
    });

    $(document).on('change','.vt-billing-country, .vt-shipping-country', function (){

        var fieldId  = $(this).attr('id');
        var fieldName  = $(this).attr('name');

        jQuery.ajax({
            url: usb_swiper_settings.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: "action=vt_get_states&country_code="+$(this).val()+"&field_id="+fieldId,
        }).done(function ( response ) {
            if( response.state_html && response.state_html !== "" ) {
                if(response.is_shipping) {
                    $('.shipping-states-wrap').html('').html(response.state_html);
                } else{
                    $('.billing-states-wrap').html('').html(response.state_html);
                }
            }
        });
    });

    $(document).on('click','.product-item', function () {

        let product_id = $(this).attr('data-id');
        let nonce = $('#vt_add_product_nonce').val();
        let repeater = $('.vt-repeater-field');
        let product_item = $(this);
        let wrapper_id = product_item.parents('.vt-fields-wrap').attr('id');
        let wrap_id = product_item.parents('.vt-fields-wrap').attr('data-id');
        let data = {
            'action': 'vt_add_product_value_in_inputs',
            'product-id': product_id,
            'vt-add-product-nonce': nonce
        };

        $.post(usb_swiper_settings.ajax_url, data, function (response) {
            if (response.status) {
                $('#'+wrapper_id).children('.product').children('input').val(response.product_name);
                $('#'+wrapper_id).children('.product').children('input').attr('data-product-taxable', response.is_taxable);
                $('#'+wrapper_id).children('.product_quantity').children('input').val('1');
                $('#'+wrapper_id).children('.price').children('input').val(response.product_price);
                $('#'+wrapper_id).children('#VTProductID_'+wrap_id).val(response.product_id);
                repeater.children('.vt-fields-wrap').children('.product').children('.vt-search-result').remove();

                let net_price_array = [];
                let net_price = '';

                $( ".vt-product-quantity" ).each(function(index) {
                    let quantity = $(this).val();
                    let wrapper_id = $(this).parents('.vt-fields-wrap').attr('id');
                    let price = $('#'+wrapper_id).children('.price').children('input').val();
                    net_price_array[index] = Number(quantity) * Number(price);
                });

                for (let i = 0; i < net_price_array.length; i++) {
                    net_price = Number(net_price_array[i]) + Number(net_price);
                }

                setTimeout( function () {
                    $('#OrderAmount').val(net_price.toFixed(2));
                    $('#NetAmount').val(net_price.toFixed(2));
                    jQuery('#Discount').trigger('change');
                    updateSalesTax();
                    updateGrandTotal();
                }, 800);
            } else {
                set_notification(response.message, 'error', response.message_type);
            }
        });
    });

    $(document).on('click','.tax_rate_wrapper .tax-item', function () {
        let tax_input = $(this).parents('.tax_rate_wrapper').find('.vt-tax-input');

        var taxLabel = $(this).text();
        var defaultToolTipText = usb_swiper_settings.default_tax_tooltip_message;
        $(this).parents('.tax_rate_wrapper').find('label').append('<span class="tool" data-default="'+defaultToolTipText+'" data-tip="'+defaultToolTipText+taxLabel+'" tabindex="1">?</span>');

        tax_input.val($(this).attr('data-id'));
        if( undefined !== $(this).attr('data-include-tax') && '' !== $(this).attr('data-include-tax') ){
            $("#TaxOnShipping").prop('checked', true);
        }else {
            $("#TaxOnShipping").prop('checked', false);
        }
        $('.input-field-wrap.tax_rate_wrapper .vt-search-result').remove();
        setTimeout( function () {
            updateSalesTax();
            updateGrandTotal();
        }, 800);
    });

    $(document).on('change keyup','.vt-product-quantity, .vt-product-price', function () {

        let net_price_array = [];
        let net_price = '';

        $( ".vt-product-quantity" ).each(function(index) {
            let quantity_class = $(this);
            let quantity = $(this).val();
            let wrapper_id = $(this).parents('.vt-fields-wrap').attr('id');
            let price = $('#'+wrapper_id).children('.price').children('input').val();
            net_price_array[index] = Number(quantity) * Number(price.replace(/,/g, ''));
        });

        for (let i = 0; i < net_price_array.length; i++) {
            net_price = Number(net_price_array[i]) + Number(net_price);
        }

        setTimeout( function () {
            $('#OrderAmount').val(net_price.toFixed(2));
            $('#NetAmount').val(net_price.toFixed(2));
            jQuery('#Discount').trigger('change');
            updateSalesTax();
            updateGrandTotal();
        }, 800);
    });

    $(document).on("click",".capture-transaction-button",function(){
        var link= $(this).attr('data-href');
        $('.vt-capture-popup-wrapper .capture-transaction').attr('href',link);
        $('.vt-capture-popup-wrapper').show();
    });

    $(document).on("click",".vt-capture-popup-wrapper #vt_capture_cancel,.vt-capture-popup-wrapper  .close a",function(){
        $('.vt-capture-popup-wrapper .capture-transaction').attr('href',"javascript:void(0);");
        $(".vt-capture-popup-wrapper").hide();
    });

    $(document).on("click",".void-transaction-button",function(){
        var link= $(this).attr('data-href');
        $('.vt-void-popup-wrapper .void-transaction').attr('href',link);
        $('.vt-void-popup-wrapper').show();
    });

    $(document).on("click",".vt-void-popup-wrapper #vt_void_cancel,.vt-void-popup-wrapper  .close a",function(){
        $('.vt-void-popup-wrapper .void-transaction').attr('href',"javascript:void(0);");
        $(".vt-void-popup-wrapper").hide();
    });

    $(document).on("click",".confirm-transaction-refund-notification",function(){
        var refund_amount = $(this).parent().siblings('.refund-amount-field').children('#refund_amount_display').val();
        $('.vt-refund-popup-wrapper #refund_amount').val(refund_amount);
        $(".vt-refund-popup-wrapper").show();
    });

    $(document).on("click",".vt-refund-popup-wrapper .cancel-refund,.vt-refund-popup-wrapper  .close a",function(){
        $('.vt-refund-popup-wrapper .capture-transaction').attr('href',"javascript:void(0);");
        $(".vt-refund-popup-wrapper").hide();

        var form = jQuery('#vt_refund_form');
        var submitButton = form.find('.confirm-transaction-refund');
        usb_swiper_remove_loader(submitButton);

        var zettleResponse = jQuery('.zettle-refund-response');
        zettleResponse.find('ul li').remove();
        zettleResponse.hide();
    });

    $(document).on("focusout",".input-field-wrap.product .vt-product-input", function (){
        setTimeout(function() {
            $('.input-field-wrap.product .vt-search-result').remove();
        },300);
    });

    $(document).on("focusout",".input-field-wrap.tax_rate_wrapper .vt-tax-input", function (){
        setTimeout(function() {
            $('.input-field-wrap.tax_rate_wrapper .vt-search-result').remove();
        },300);
    });


    if (usb_swiper_settings.vt_page_id === usb_swiper_settings.current_page_id || usb_swiper_settings.vt_paybyinvoice_page_id === usb_swiper_settings.current_page_id) {
        if( usb_swiper_settings.timeout_option !== 'never' && parseInt(usb_swiper_settings.timeout_option) > 0 ) {

            const getTenMinuteAfterTime = new Date(new Date().getTime() + (parseInt(usb_swiper_settings.timeout_option) * 60000)).getTime();
            localStorage.removeItem('sessionExpireTimer');
            localStorage.setItem('sessionInactiveTimer', getTenMinuteAfterTime);

            $(document).on('mousemove keydown', function () {
                var InactiveTimerTime = localStorage.getItem('sessionExpireTimer');
                if (InactiveTimerTime === null || InactiveTimerTime === '' || InactiveTimerTime === undefined) {
                    const getTenMinuteAfterTime = new Date(new Date().getTime() + (parseInt(usb_swiper_settings.timeout_option) * 60000)).getTime();
                    localStorage.setItem('sessionInactiveTimer', getTenMinuteAfterTime);
                }
            });

            const timeoutInterval = setInterval(function () {
                var currentTime = new Date().getTime();
                if (currentTime >= localStorage.getItem('sessionInactiveTimer')) {
                    clearInterval(timeoutInterval);
                    $('.vt-payment-timeout-popup-wrapper').show();
                    localStorage.removeItem('sessionInactiveTimer');
                    autoSessionLogOut();
                }
            }, 1000);
        }
    }

    $(document).on('click','#vt_form_timeout, .vt-payment-timeout-popup-inner .close-btn', function (){
        localStorage.removeItem('sessionInactiveTimer');
        localStorage.removeItem('sessionExpireTimer');
        location.reload();
    });

    jQuery("form#vt_zettle_form").validate({
        rules: {
            zettle_api_key: {
                required: true,
            }
        },
        submitHandler: function (form, event) {
            return true;
        }
    });

    jQuery("form#zettle_pair_reader_form").validate({
        rules: {
            zettle_pair_reader_code: {
                required: true,
            },
            zettle_pair_reader_device_name: {
                required: true,
            }
        },
        submitHandler: function (form, event) {

            const form_id = form.id;
            const submitButton = jQuery('#vt_zettle_pair_reader_settings');
            usb_swiper_remove_loader(submitButton);
            usb_swiper_add_loader(submitButton);
            jQuery.ajax({
                url: usb_swiper_settings.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: jQuery('#'+form_id).serialize()+"&action=vt_zettle_pair_reader",
            }).done(function ( response ) {
                if(response.status) {
                    set_notification(response.message);
                    location.reload();
                } else {
                    set_notification(response.message, 'error', response.message_type);
                }
                usb_swiper_remove_loader(submitButton);
            });
        }
    });

    $(document).on('click','.notice-cancel-btn', function (event) {
        event.preventDefault();
        const currentObj = $(this);
        usb_swiper_add_loader(currentObj);
        jQuery.ajax({
            url: usb_swiper_settings.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: "action=disable_vt_form_warning",
        }).done(function ( response ) {
            if( response.status ) {
                $(".warning-description").hide();
                usb_swiper_remove_loader(currentObj);
            }
        });
    });

    $(document).on('keyup','#customerInformation', function (event) {
        let currentObj = $(this);
        let searchVal = currentObj.val();
        if(searchVal.length >= 2) {
            let data = {
                'action': 'vt_search_customer',
                'customer': searchVal,
            };
            $.post(usb_swiper_settings.ajax_url, data, function (response) {
                if (response.status) {
                    $('.vt-customer-search-result').remove();
                    if(response.customer_html) {
                        currentObj.parents('.input-field-wrap').append('<div class="vt-search-result vt-customer-search-result">' + response.customer_html + '</div>')
                    }
                } else {
                    $('.vt-customer-search-result').remove();
                }
            });
        } else {
            $('.vt-customer-search-result').remove();
        }
    });

    var checkboxLabel = $('#save_customer_details').closest('.vt-fields-wrap').find('label');
    var checkbox = $('.review_changes').closest('.vt-fields-wrap');
    checkboxLabel.text(usb_swiper_settings.default_customer_label);
    $('#save_customer_details').prop('checked', false);
    $(document).on('click','.vt-customer-search-result .customer-item', function (event) {
        let currentObj = $(this);
        let customer_id = currentObj.attr('data-customer_id');
        let customer_name = currentObj.text();
        let data = {
            'action': 'vt_get_customer_by_id',
            'customer_id': customer_id,
        };

        var originalInfo = {};

        // Store the original info (before customer selection)
        $('.personal_info, .billingInfo, .shipping_address').each(function () {
            var fieldId = $(this).attr('id');
            originalInfo[fieldId] = $(this).val();
        });

        $('.vt-input-field,.usbswiper-change-currency').on('change', function() {
            FormData = window.customerData

            if( undefined !== FormData && FormData.hasOwnProperty($(this).attr('id')) ){
                var changedFields = '';
                $.each(FormData, function(key, value) {
                    if($('#'+key).val() !== value && key !== 'customer_id'){
                        changedFields = changedFields + '<li>' + key + '</li>';
                    }
                });
                let reviewChanges = $('.review-changes');
                if( '' !== changedFields && ( undefined === reviewChanges || null === reviewChanges || reviewChanges.length < 1 ) ){
                    reviewChangesButton = $('<button class="review-changes" type="button" data-toggle="collapse" aria-controls="collapseExample" aria-expanded="false" data-target="#reviewChangesCollapse">Review Changes</button><div class="reviewChangesList collapse" id="reviewChangesCollapse"></div>');
                    checkbox.after(reviewChangesButton);
                } else if('' === changedFields){
                    $('.review-changes').remove();
                    $('.reviewChangesList').remove();
                }

            }
        });

        $.post(usb_swiper_settings.ajax_url, data, function (response) {
            $('.vt-customer-search-result').remove();
            if (response.status) {
                if (response.customer) {
                    let customer = JSON.parse(response.customer);
                    var updatedFields = [];
                    $('#customerInformation').val(customer_name);
                    updateSwitchFields('#billingInfo', customer.billingInfo);
                    updateSwitchFields('#shippingDisabled', customer.shippingDisabled);
                    updateSwitchFields('#shippingSameAsBilling', customer.shippingSameAsBilling);

                    checkboxLabel.text(usb_swiper_settings.update_customer_label);
                    $('.customer-review-tooltip').attr('data-tip',usb_swiper_settings.update_customer_tooltip);
                    $('#save_customer_details').prop('checked', false);
                    function compareValues(obj, originalObj, path = '') {
                        for (var key in obj) {
                            if (obj.hasOwnProperty(key)) {
                                var newPath = path + (path ? '.' : '') + key;
                                if (typeof obj[key] === 'object') {
                                    compareValues(obj[key], originalObj[key], newPath);
                                } else {
                                    var originalValue = originalObj[key];
                                    var newValue = obj[key];
                                    if (originalValue !== newValue) {
                                        updatedFields.push(newPath); // Track updated fields
                                    }
                                    $('#' + newPath).val(newValue); // Update field value
                                }
                            }
                        }
                    }

                    compareValues(customer, originalInfo);
                    window.customerData = customer;
                    $('.clear-customer-details').show();
                    localStorage.setItem('CustomerInformation', customer_name);
                    localStorage.setItem('CustomerEmail',customer.BillingEmail);
                    jQuery('#CustomerEmail').val(customer.BillingEmail);
                    saveDataToLocalStorage(window.customerData);
                } else {
                    $('.clear-customer-details').hide();
                }
            }
        });
    });

    $(document).on('click', '.review-changes', function (e) {
        e.preventDefault();
        var changesList = $('.reviewChangesList');
        FormData = window.customerData;

        if(changesList){
            changesList.toggleClass('show');

            if( undefined !== FormData ){
                var changedFields = '';
                $.each(FormData, function(key, value) {
                    if($('#'+key).val() !== value && key !== 'customer_id'){
                        changedFields = changedFields + '<div class="review-changes-list">' + key + '</div>';
                    }
                });
                changesList.html(changedFields);
            }
        }
    });

    $(document).ready(function() {
        $('#save_customer_details').on('change', function() {
            var checkbox = $(this);
            var fields = $('.personal_info, .billingInfo, .shipping_address');
            if (checkbox.is(':checked')) {
                fields.prop('readonly', false);
            } else {
                fields.prop('readonly', true);
            }
        });
    });

    $(document).on('click', '.clear-customer-details', function(event) {
        event.preventDefault();

        if (confirm('Are you sure you want to clear the current customer’s details from this order?')) {

            jQuery('input[type="text"]').val('');
            jQuery('input[type="number"]').val('');
            jQuery('#BillingState').val('');

            //removeLocalData();

            // Hide the customer search result and "Clear Customer" button
            $('.vt-customer-search-result').remove();
            $('.clear-customer-details').hide();
        }
    });

    $(document).on('click','.vt_delete_customer', function (event) {
       let customer_id = $(this).attr('data-id');

        if ( true === confirm(usb_swiper_settings.delete_customer_confirm_message) ) {

            let data = {
                'action': 'vt_delete_customer_by_id',
                'customer_id': customer_id,
            };

            $.post(usb_swiper_settings.ajax_url, data, function (response) {
                if(response.status) {
                    set_notification(response.message, 'success', response.message_type);
                    setTimeout( function () {
                        location.reload();
                    }, 1000);
                } else {
                    set_notification(response.message, 'error', response.message_type);
                }
            });
        }
    });

    $('#vt-customer-form').validate({
        rules: {},
        messages: {},
        submitHandler: function(form, event) {

            event.preventDefault();
            var currentFormObj = $("#vt-customer-form");
            var submitButton = currentFormObj.find('#vt_submit_button');
            usb_swiper_add_loader(submitButton);

            jQuery.ajax({
                url: usb_swiper_settings.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: currentFormObj.serialize()+"&action=vt_handle_customer_form",
            }).done(function ( response ) {

                if( response.status) {
                    set_notification(response.message, 'success');
                    setTimeout( function () {
                        window.location.href = response.redirection_url;
                    }, 1000);
                } else{
                    set_notification(response.message, 'error', response.message_type);
                }

                usb_swiper_remove_loader(submitButton);
            });

        }
    });
    if(usb_swiper_settings.is_customers) {
        let isBillingInfo = jQuery('#billingInfo').is(':checked');
        let isShippingDisabled = jQuery('#shippingDisabled').is(':checked');
        let isShippingSameAsBilling = jQuery('#shippingSameAsBilling').is(':checked');
        window.setTimeout(function(){
            jQuery('#billingInfo').bootstrapSwitch('state', isBillingInfo);
            jQuery('#shippingDisabled').bootstrapSwitch('state', isShippingDisabled);
            jQuery('#shippingSameAsBilling').bootstrapSwitch('state', isShippingSameAsBilling);
            if(!isBillingInfo) {
                jQuery('#BillingStreet, #BillingCity, #BillingState, #BillingCountryCode, #BillingPostalCode').removeAttr('required');
                jQuery('.vt-billing-address-field').parents('.input-field-wrap').hide();
                jQuery('#shippingSameAsBilling').parents('.input-field-wrap').hide();
            } else {
                jQuery('#BillingStreet, #BillingCity, #BillingState, #BillingCountryCode, #BillingPostalCode').attr('required', 'required');
                jQuery('.vt-billing-address-field').parents('.input-field-wrap').show();
                jQuery('#shippingSameAsBilling').parents('.input-field-wrap').show();
            }

            if( isShippingDisabled ) {
                if(!isShippingDisabled) {
                    jQuery('#ShippingFirstName, #ShippingLastName, #ShippingStreet, #ShippingCity, #ShippingState, #ShippingCountryCode, #ShippingPostalCode').removeAttr('required');
                    jQuery('.vt-shipping-address-field').parents('.input-field-wrap').hide();

                    if(isBillingInfo) {
                        jQuery('#shippingSameAsBilling').parents('.input-field-wrap').show();
                    } else {
                        jQuery('#shippingSameAsBilling').parents('.input-field-wrap').hide();
                    }
                } else {
                    jQuery('#ShippingFirstName, #ShippingLastName, #ShippingStreet, #ShippingCity, #ShippingState, #ShippingCountryCode, #ShippingPostalCode').attr('required', 'required');
                    jQuery('.vt-shipping-address-field').parents('.input-field-wrap').show();
                    if(isBillingInfo) {
                        jQuery('#shippingSameAsBilling').parents('.input-field-wrap').show();
                    } else {
                        jQuery('#shippingSameAsBilling').parents('.input-field-wrap').hide();
                    }
                }

                if (!isShippingSameAsBilling) {
                    jQuery('#ShippingFirstName, #ShippingLastName, #ShippingStreet, #ShippingCity, #ShippingState, #ShippingCountryCode, #ShippingPostalCode').attr('required', 'required');
                    jQuery('.vt-shipping-address-field').parents('.input-field-wrap').show();
                } else {
                    jQuery('#ShippingFirstName, #ShippingLastName, #ShippingStreet, #ShippingCity, #ShippingState, #ShippingCountryCode, #ShippingPostalCode').removeAttr('required');
                    jQuery('.vt-shipping-address-field').parents('.input-field-wrap').hide();
                }
            } else {
                jQuery('#ShippingFirstName, #ShippingLastName, #ShippingStreet, #ShippingCity, #ShippingState, #ShippingCountryCode, #ShippingPostalCode').removeAttr('required');
                jQuery('.vt-shipping-address-field').parents('.input-field-wrap').hide();
                jQuery('#shippingSameAsBilling').parents('.input-field-wrap').hide();
            }
        }, 1000);
    }

    jQuery('.vt-form-product-price').autoNumeric('init', {
        mDec: '2',
        aSep: '',      // Thousands separator (comma)
        aSign: '',
        wEmpty: '0',
        lZero: 'allow',
        aForm: false,
        vMin: '0'
    });
});

function removeInterval( LoaderInterval ) {
    clearInterval(LoaderInterval);
}

var imageUpload = document.querySelectorAll('.vt-image-upload-wrap')

for (var i = 0, len = imageUpload.length; i < len; i++) {
    customInput(imageUpload[i])
}

function customInput (el) {
    const fileInput = el.querySelector('.vt-image-upload');
    const label = document.createElement('div');
    label.className = 'upload-image-preview';
    el.appendChild(label);
    fileInput.onchange = function () {
        if (!fileInput.value) return
        let fileInputName = fileInput.getAttribute('name');
        let bigImage = false;
        var _URL = window.URL || window.webkitURL;
        var LogoFile, img;
        if ((LogoFile = fileInput.files[0])) {
            img = new Image();
            var objectUrl = _URL.createObjectURL(LogoFile);
            img.onload = function () {
                if( this.width > 250 ){
                    bigImage = true;
                    vt_set_notification(usb_swiper_settings.vt_max_image_size, 'error');
                    fileInput.value = '';
                } else {
                        if (fileInputName === 'BrandLogo') {
                            let brandLogoPreviewEl = document.getElementsByClassName('brand-logo-preview');
                            if (brandLogoPreviewEl) {
                                brandLogoPreviewEl[0].style.display = "none";
                            }
                        }
                        const file = fileInput.files[0];
                        const previewImage = URL.createObjectURL(file)
                        label.innerHTML = '<img src="' + previewImage + '" alt="preview">';
                }
                _URL.revokeObjectURL(objectUrl);
            };
            img.src = objectUrl;
        }
    }
}

function vt_remove_notification() {
    jQuery('.vt-form-notification').empty();
}

function vt_set_notification( message, type ='success', message_type='' ) {
    var notification = "<p class='notification "+type+"'><strong>"+message_type+"</strong>"+message+"</p>"
    jQuery('.vt-form-notification').empty().append(notification);

    jQuery([document.documentElement, document.body]).animate({ scrollTop: ( $(".vt-form-notification").offset().top) - 20 }, 1000);
}

function autoSessionLogOut() {

    const sessionExpireTimer = new Date(new Date().getTime() + (5 * 60000)).getTime();
    localStorage.setItem('sessionExpireTimer', sessionExpireTimer);

    var intervalId = setInterval(function () {

        var currentTime = new Date().getTime();
        var InactiveTimerTime = parseInt(localStorage.getItem('sessionExpireTimer'));

        const timeDifference = InactiveTimerTime - currentTime;
        let remainingTime = Math.floor(timeDifference / 1000);
        remainingTime %= 3600;
        const remainingMinutes = Math.floor(remainingTime / 60);
        const remainingSeconds = remainingTime % 60;

        if( remainingMinutes >= 0 || remainingSeconds >= 0 ) {
            document.querySelector("#auto_session_time").textContent = remainingMinutes + ":" + remainingSeconds;
        }

        if ( currentTime >= InactiveTimerTime ) {
            clearInterval(intervalId);
            localStorage.removeItem('sessionExpireTimer');
            window.location.href = document.querySelector('.vt-session-logout-link').getAttribute('href');
        }
    }, 1000);
}

function add_zettle_notification( message, currentObj ) {
    currentObj.children('li').removeClass('active');
    currentObj.append('<li class="active">'+message+'</li>');

    currentObj.scrollTop(currentObj[0].scrollHeight);
}

function remove_zettle_notification(currentObj){
    currentObj.children('li').removeClass('active');
    currentObj.parent('.zettle-refund-response').hide();
}

const paymentButton = document.getElementById('pos-submit-btn');

if(paymentButton) {
    function checkPaymentButton() {
        const paymentButton = document.getElementById('pos-submit-btn');
        const vtProductInput = document.getElementById('VTProduct_0');
        const VTProductQuantity = document.getElementById('VTProductQuantity_0');
        const VTProductprice = document.getElementById('VTProductPrice_0');
        const vtaddproductbtn = document.getElementById('vt_add_item');

        if (paymentButton.disabled) {
            vtProductInput.disabled = true;
            VTProductQuantity.disabled = true;
            VTProductprice.disabled = true;
            vtaddproductbtn.disabled = true;
        } else {
            vtProductInput.disabled = false;
            VTProductQuantity.disabled = false;
            VTProductprice.disabled = false;
            vtaddproductbtn.disabled = false;
        }
    }

    document.addEventListener('DOMContentLoaded', checkPaymentButton);
}


function updatePageSize() {
    var pageSize = $('#page-size').val();
    var css = '';
    if (pageSize === 'a4') {
        css = `@page { size: 210mm 297mm; }`;
    }
    else if (pageSize === 'envelope-3.5') {
        css = `@page { size: 89mm 187mm;}`;
    }

    var styleSheet = document.createElement('style');
    styleSheet.type = 'text/css';
    styleSheet.id = 'dynamic-page-size';
    styleSheet.innerText = css;

    var existingStyle = document.getElementById('dynamic-page-size');
    if (existingStyle) {
        existingStyle.parentNode.removeChild(existingStyle);
    }

    document.head.appendChild(styleSheet);
}

function updateSwitchFields(fieldId, fieldValue) {
    if (fieldValue === "true") {
        jQuery(fieldId).prop('checked', true);
        jQuery(fieldId).bootstrapSwitch('state', true);
    } else {
        jQuery(fieldId).prop('checked', false);
        jQuery(fieldId).bootstrapSwitch('state', false);
    }
}

function saveDataToLocalStorage(data) {
    for (const [key, value] of Object.entries(data)) {
        localStorage.setItem(key, value || '');
    }
}

// Function to load saved data from localStorage on page load
function loadSavedData() {
    jQuery('#company').val(localStorage.getItem('Company') || '');
    jQuery('#BillingFirstName').val(localStorage.getItem('BillingFirstName') || '');
    jQuery('#BillingLastName').val(localStorage.getItem('BillingLastName') || '');
    jQuery('#BillingEmail').val(localStorage.getItem('BillingEmail') || '');
    jQuery('#OrderAmount').val(localStorage.getItem('OrderAmount') || '');
    jQuery('#Discount').val(localStorage.getItem('Discount') || '');
    jQuery('#NetAmount').val(localStorage.getItem('NetAmount') || '');
    jQuery('#ShippingAmount').val(localStorage.getItem('ShippingAmount') || '');
    jQuery('#HandlingAmount').val(localStorage.getItem('HandlingAmount') || '');
    jQuery('#TaxRate').val(localStorage.getItem('TaxRate') || '');
    jQuery('#InvoiceID').val(localStorage.getItem('InvoiceNumber') || '');
    jQuery('#Notes').val(localStorage.getItem('Notes') || '');
    jQuery('#billingInfo').val(localStorage.getItem('billingInfo') || '');
    jQuery('#BillingStreet').val(localStorage.getItem('BillingStreet') || '');
    jQuery('#BillingStreet2').val(localStorage.getItem('BillingStreet2') || '');
    jQuery('#BillingCity').val(localStorage.getItem('BillingCity') || '');
    jQuery('#BillingState').val(localStorage.getItem('BillingState') || '');
    jQuery('#BillingPostalCode').val(localStorage.getItem('BillingPostalCode') || '');
    jQuery('#BillingCountryCode').val(localStorage.getItem('BillingCountryCode') || '');
    jQuery('#BillingPhoneNumber').val(localStorage.getItem('BillingPhoneNumber') || '');
    jQuery('#shippingDisabled').val(localStorage.getItem('shippingDisabled') || '');
    jQuery('#shippingSameAsBilling').val(localStorage.getItem('shippingSameAsBilling') || '');
    jQuery('#ShippingFirstName').val(localStorage.getItem('ShippingFirstName') || '');
    jQuery('#ShippingLastName').val(localStorage.getItem('ShippingLastName') || '');
    jQuery('#ShippingStreet').val(localStorage.getItem('ShippingStreet') || '');
    jQuery('#ShippingStreet2').val(localStorage.getItem('ShippingStreet2') || '');
    jQuery('#ShippingCity').val(localStorage.getItem('ShippingCity') || '');
    jQuery('#ShippingState').val(localStorage.getItem('ShippingState') || '');
    jQuery('#ShippingPostalCode').val(localStorage.getItem('ShippingPostalCode') || '');
    jQuery('#ShippingCountryCode').val(localStorage.getItem('ShippingCountryCode') || '');
    jQuery('#ShippingPhoneNumber').val(localStorage.getItem('ShippingPhoneNumber') || '');
    jQuery('#TaxAmount').val(localStorage.getItem('TaxAmount') || '');
    jQuery('#GrandTotal').val(localStorage.getItem('GrandTotal') || '');
    jQuery('#customerInformation').val(localStorage.getItem('CustomerInformation') || '');
}

// Function to update currency symbol based on selected currency
function updateCurrencySymbol(code, currency) {
    jQuery('.sign-symbol').text(code);
}

function removeLocalData() {
    localStorage.removeItem('transaction_id');
    localStorage.removeItem('Company');
    localStorage.removeItem('BillingFirstName');
    localStorage.removeItem('BillingLastName');
    localStorage.removeItem('BillingEmail');
    localStorage.removeItem('OrderAmount');
    localStorage.removeItem('Discount');
    localStorage.removeItem('NetAmount');
    localStorage.removeItem('ShippingAmount');
    localStorage.removeItem('HandlingAmount');
    localStorage.removeItem('TaxRate');
    localStorage.removeItem('InvoiceNumber');
    localStorage.removeItem('Notes');
    localStorage.removeItem('billingInfo');
    localStorage.removeItem('BillingStreet');
    localStorage.removeItem('BillingStreet2');
    localStorage.removeItem('BillingCity');
    localStorage.removeItem('BillingState');
    localStorage.removeItem('BillingPostalCode');
    localStorage.removeItem('BillingCountryCode');
    localStorage.removeItem('BillingPhoneNumber');
    localStorage.removeItem('shippingDisabled');
    localStorage.removeItem('shippingSameAsBilling');
    localStorage.removeItem('ShippingFirstName');
    localStorage.removeItem('ShippingLastName');
    localStorage.removeItem('ShippingStreet');
    localStorage.removeItem('ShippingStreet2');
    localStorage.removeItem('ShippingCity');
    localStorage.removeItem('ShippingState');
    localStorage.removeItem('ShippingPostalCode');
    localStorage.removeItem('ShippingCountryCode');
    localStorage.removeItem('ShippingPhoneNumber');
    localStorage.removeItem('ShippingEmail');
    localStorage.removeItem('TaxAmount');
    localStorage.removeItem('GrandTotal');
    localStorage.removeItem('ProductsData');
    localStorage.removeItem('CustomerInformation');
    localStorage.removeItem('TransactionCurrency');
}

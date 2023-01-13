jQuery( document ).ready(function( $ ) {
    var company = localStorage.getItem('Company');
    var BillingFirstName = localStorage.getItem('BillingFirstName');
    var BillingLastName = localStorage.getItem('BillingLastName');
    var BillingEmail = localStorage.getItem('BillingEmail');
    var NetAmount = localStorage.getItem('NetAmount');
    var ShippingAmount = localStorage.getItem('ShippingAmount');
    var HandlingAmount = localStorage.getItem('HandlingAmount');
    var TaxRate = localStorage.getItem('TaxRate');
    var InvoiceNumber = localStorage.getItem('InvoiceNumber');
    var ItemName = localStorage.getItem('ItemName');
    var Notes = localStorage.getItem('Notes');
    if (company !== null) $('#company').val(company);
    if (BillingFirstName !== null) $('#BillingFirstName').val(BillingFirstName);
    if (BillingLastName !== null) $('#BillingLastName').val(BillingLastName);
    if (BillingEmail !== null) $('#BillingEmail').val(BillingEmail);
    if (NetAmount !== null) $('#NetAmount').val(NetAmount);
    if (ShippingAmount !== null) $('#ShippingAmount').val(ShippingAmount);
    if (HandlingAmount !== null) $('#HandlingAmount').val(HandlingAmount);
    if (TaxRate !== null){
        TaxRate = parseInt(TaxRate);
        $('#TaxRate').val(TaxRate);
    }
    if (InvoiceNumber !== null) $('#InvoiceID').val(InvoiceNumber);
    if (ItemName !== null) $('#ItemName').val(ItemName);
    if (Notes !== null) $('#Notes').val(Notes);

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
                        window.location.href = data.invoiceUrl;
                    } else {
                        set_notification(data.message, 'error', data.message_type);
                    }
                    $(this).prop('disabled', false);
                    VtForm.unblock();
                });
            } else {
                VtForm.unblock();
            }
        }

    })

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
        $('.vt-form-notification').empty().append(notification);


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
                                return data.orderID;
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

                VtForm.validate({
                    rules: {},
                    messages: {},
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
                                localStorage.removeItem('vt_order_id')
                                if (payload.orderId) {
                                    let transaction_id = $('#transaction_id').val();
                                    $.post(usb_swiper_settings.cc_capture + "&paypal_transaction_id=" + payload.orderId + "&wc-process-transaction-nonce=" + usb_swiper_settings.usb_swiper_transaction_nonce + "&pbi_transaction_id="+transaction_id, function (data) {
                                        if( data.result === 'success' ) {
                                            localStorage.removeItem('Company');
                                            localStorage.removeItem('BillingFirstName');
                                            localStorage.removeItem('BillingLastName');
                                            localStorage.removeItem('BillingEmail');
                                            localStorage.removeItem('NetAmount');
                                            localStorage.removeItem('ShippingAmount');
                                            localStorage.removeItem('HandlingAmount');
                                            localStorage.removeItem('TaxRate');
                                            localStorage.removeItem('InvoiceNumber');
                                            localStorage.removeItem('ItemName');
                                            localStorage.removeItem('Notes');
                                            window.location.href = data.redirect;
                                        } else{
                                            set_notification(data.message, 'error', data.message_type);
                                            VtForm.removeClass('processing paypal_cc_submiting HostedFields createOrder').unblock();
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
                                localStorage.removeItem('vt_order_id');
                                set_notification(message, 'error', error.name);
                                VtForm.removeClass('processing paypal_cc_submiting HostedFields createOrder').unblock();
                                jQuery.ajax({
                                    url: usb_swiper_settings.ajax_url,
                                    type: 'POST',
                                    dataType: 'json',
                                    data: "action=update_order_status&order_id=" + order_id+'&message='+message,
                                }).done(function ( response ) {

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
                                    localStorage.removeItem('Company');
                                    localStorage.removeItem('BillingFirstName');
                                    localStorage.removeItem('BillingLastName');
                                    localStorage.removeItem('BillingEmail');
                                    localStorage.removeItem('NetAmount');
                                    localStorage.removeItem('ShippingAmount');
                                    localStorage.removeItem('HandlingAmount');
                                    localStorage.removeItem('TaxRate');
                                    localStorage.removeItem('InvoiceNumber');
                                    localStorage.removeItem('ItemName');
                                    localStorage.removeItem('Notes');
                                    window.location.href = data.redirect;
                                } else{
                                    set_notification(data.message, 'error', data.message_type);
                                    VtForm.removeClass('processing paypal_cc_submiting HostedFields createOrder').unblock();
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
        var BillingLastName = $('#BillingLastName').val();
        var BillingFirstName = $('#BillingFirstName').val();
        var BillingEmail = $('#BillingEmail').val();
        var NetAmount = $('#NetAmount').val();
        var ShippingAmount = $('#ShippingAmount').val();
        var HandlingAmount = $('#HandlingAmount').val();
        var TaxRate = $('#TaxRate').val();
        var InvoiceNumber = $('#InvoiceID').val();
        var Company = $('#company').val();
        var ItemName = $('#ItemName').val();
        var Notes = $('#Notes').val();
        localStorage.setItem('Company', Company);
        localStorage.setItem('BillingFirstName', BillingFirstName);
        localStorage.setItem('BillingLastName', BillingLastName);
        localStorage.setItem('BillingEmail', BillingEmail);
        localStorage.setItem('NetAmount', NetAmount);
        localStorage.setItem('ShippingAmount', ShippingAmount);
        localStorage.setItem('HandlingAmount', HandlingAmount);
        localStorage.setItem('TaxRate', TaxRate);
        localStorage.setItem('InvoiceNumber', InvoiceNumber);
        localStorage.setItem('ItemName', ItemName);
        localStorage.setItem('Notes', Notes);

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

    $(document).on('click','.vt-remove-fields-wrap', function (){
        $(this).parent().remove();
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
                usb_swiper_remove_loader(loader);
                loader.removeAttr('disabled')
            } else {
                set_notification(response.message, 'error', response.message_type);
            }
        });
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
                    }
                } else {
                    set_notification(response.message, 'error', response.message_type);
                }
            });
        }
    });

    $(document).on('click','.product-item', function () {

        let product_id = $(this).attr('data-id');
        let nonce = $('#vt_add_product_nonce').val();
        let repeater = $('.vt-repeater-field');
        let product_item = $(this);
        let wrapper_id = product_item.parents('.vt-fields-wrap').attr('id')
        let data = {
            'action': 'vt_add_product_value_in_inputs',
            'product-id': product_id,
            'vt-add-product-nonce': nonce
        };

        $.post(usb_swiper_settings.ajax_url, data, function (response) {
            if (response.status) {
                $('#'+wrapper_id).children('.product').children('input').val(response.product_name);
                $('#'+wrapper_id).children('.product_quantity').children('input').val('1');
                $('#'+wrapper_id).children('.price').children('input').val(response.product_price);
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

                $('#NetAmount').val(net_price);
            } else {
                set_notification(response.message, 'error', response.message_type);
            }
        });
    });

    $(document).on('change keyup','.vt-product-quantity, .vt-product-price', function () {

        let net_price_array = [];
        let net_price = '';

        $( ".vt-product-quantity" ).each(function(index) {
            let quantity_class = $(this);
            let quantity = $(this).val();
            let wrapper_id = $(this).parents('.vt-fields-wrap').attr('id');
            let price = $('#'+wrapper_id).children('.price').children('input').val();
            net_price_array[index] = Number(quantity) * Number(price);
        });


        for (let i = 0; i < net_price_array.length; i++) {
            net_price = Number(net_price_array[i]) + Number(net_price);
        }

        $('#NetAmount').val(net_price);
    });

});

function removeInterval(){
    clearInterval(LoaderInterval);
}

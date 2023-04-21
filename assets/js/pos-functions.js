jQuery(function( $ ) {

    jQuery(document).ready(function(){

        /* Auto hide Card Issue Number field div */
        jQuery('#DivCreditCardIssueNumber').hide();

        /* Auto focus on swipe field if present */
        if(jQuery('#pos-panel-swipe').length > 0)
        {
            jQuery('#swiper').focus();
        }
        else
        {
            jQuery('#BillingFirstName').focus();
        }

        /* Bootstrap Switch Plugin on checkboxes for POS form */
        if(jQuery('#ae-paypal-pos-form').html() !== undefined && jQuery('#ae-paypal-pos-form').html().length != 0)
        {
            jQuery("#ae-paypal-pos-form .checkbox").each(function() {
                jQuery(this).bootstrapSwitch();
            });
            //return false;
        }

        /* AutoNumeric JavaScript plugin */
        jQuery('#NetAmount, #ShippingAmount, #HandlingAmount').autoNumeric('init',
            {
                mDec: '2',
                aSign: '',
                wEmpty: '0',
                lZero: 'allow',
                aForm: false,
                vMin: '0'
            }
        );

        /* Toggle Billing Fields */
        jQuery('input[name="billingInfo"]').on('switchChange.bootstrapSwitch', function(event, state) {
            //console.log(this); // DOM element
            //console.log(event); // jQuery event
            //console.log(state); // true | false
            if(state) {
                jQuery('#BillingStreet, #BillingCity, #BillingState, #BillingCountryCode, #BillingPostalCode').attr('required', 'required');
                jQuery('.vt-billing-address-field').parents('.input-field-wrap').show();
            } else {
                jQuery('#BillingStreet, #BillingCity, #BillingState, #BillingCountryCode, #BillingPostalCode').removeAttr('required');
                jQuery('.vt-billing-address-field').parents('.input-field-wrap').hide();
            }
            //jQuery('#FormBillingAddress').slideToggle('400');
            return false;
        });

       /* Toggle Shipping Fields */
        jQuery('input[name="shippingDisabled"]').on('switchChange.bootstrapSwitch', function(event, state) {
            //console.log(this); // DOM element
            //console.log(event); // jQuery event
            //console.log(state); // true | false
            if(state) {
                jQuery('#ShippingFirstName, #ShippingLastName, #ShippingStreet, #ShippingCity, #ShippingState, #ShippingCountryCode, #ShippingPostalCode').removeAttr('required');
                //jQuery('#FormShippingAddress').slideUp('400');
                //jQuery('#sameAsBilling').hide();
                //jQuery('.vt-billing-address-field').parents('.input-field-wrap').show();
                jQuery('.vt-shipping-address-field').parents('.input-field-wrap').hide();
                jQuery('#shippingSameAsBilling').parents('.input-field-wrap').hide();
            } else {
                jQuery('#shippingSameAsBilling').parents('.input-field-wrap').show();
                //jQuery('#sameAsBilling').show();
                if(!jQuery('#shippingSameAsBilling').bootstrapSwitch('state')) {
                    //jQuery('#FormShippingAddress').slideDown('400');
                    jQuery('.vt-shipping-address-field').parents('.input-field-wrap').show();
                } else {
                }
            }
            return false;
        });

        /* Toggle Shipping Fields */
        jQuery('input[name="PayByInvoiceDisabled"]').on('switchChange.bootstrapSwitch', function(event, state) {
            if(state) {
                $('#PayByInvoice').show();
                $('.vt-col-payments').hide();
            } else {
                $('#PayByInvoice').hide();
                $('.vt-col-payments').show();
            }
            return false;
        });

        /* Toggle Shipping Fields */
        jQuery('input[name="shippingSameAsBilling"]').on('switchChange.bootstrapSwitch', function(event, state) {
            //console.log(this); // DOM element
            //console.log(event); // jQuery event
            //console.log(state); // true | false
            if(state)
            {
                jQuery('#ShippingFirstName, #ShippingLastName, #ShippingStreet, #ShippingCity, #ShippingState, #ShippingCountryCode, #ShippingPostalCode').removeAttr('required');
                jQuery('.vt-shipping-address-field').parents('.input-field-wrap').hide();
            }
            else
            {
                jQuery('#ShippingFirstName, #ShippingLastName, #ShippingStreet, #ShippingCity, #ShippingState, #ShippingCountryCode, #ShippingPostalCode').attr('required', 'required');
                jQuery('.vt-shipping-address-field').parents('.input-field-wrap').show();
            }
            //jQuery('#FormShippingAddress').slideToggle('400');
            return false;
        });

        /* Update Tax Amount and Grand Total on change */
        jQuery('#NetAmount, #ShippingAmount, #HandlingAmount, #TaxRate').change(function(){
            updateSalesTax();
            updateGrandTotal();
        });

        /* Swipe field */
        jQuery('#swiper').change(function(){
            ParseStripeData();
        });

        jQuery('#swiper').focus(function(){
            ClearStripeData();
        });

        jQuery('#swiper').blur(function(){
            BlurStripeField();
        });

        /* Toggle Issue Number on Credit Card Type change */
        jQuery('#CreditCardType').change(function(){
            ToggleIssueNumber();
        });

        jQuery('#pos-reset-btn').on('click', function (e) {
            if( jQuery('#pos-submit-btn').is(':visible') )
            {
                jQuery('#posResetConfirmModal')
                    .modal({backdrop: 'static', keyboard: false})
                    .one('click', '#resetPos', function (e) {
                        //reset function
                        window.location = 'index.php';
                    });
            }
            else
            {
                window.location = 'index.php';
            }
            return false;
        });

        /* Toggle defaults on checkboxes */
        window.setTimeout(function(){
            if(jQuery('#billingInfo').attr('data-default-checked') != 'TRUE')
            {
                jQuery('#billingInfo').bootstrapSwitch('toggleState');
            }
            if(jQuery('#shippingDisabled').attr('data-default-checked') == 'TRUE')
            {
                if(jQuery('#shippingSameAsBilling').attr('data-default-checked') == 'TRUE')
                {
                    jQuery('#shippingSameAsBilling').bootstrapSwitch('state', true, true);
                }
                jQuery('#shippingDisabled').bootstrapSwitch('toggleState');
            }
            else
            {
                if(jQuery('#shippingSameAsBilling').attr('data-default-checked') == 'TRUE' && jQuery('#shippingDisabled').attr('data-default-checked') != 'TRUE')
                {
                    jQuery('#shippingSameAsBilling').bootstrapSwitch('toggleState');
                }
            }
        }, 700);
        if(jQuery('#PayByInvoiceDisabled').attr('data-default-checked') != 'TRUE'){
            jQuery('#PayByInvoiceDisabled').bootstrapSwitch('toggleState');
        }

        if(jQuery('#shippingDisabled').is(':checked'))
        {
            jQuery('#ShippingFirstName, #ShippingLastName, #ShippingStreet, #ShippingCity, #ShippingState, #ShippingCountryCode, #ShippingPostalCode').removeAttr('required');
            jQuery('#FormShippingAddress').hide();
            jQuery('#sameAsBilling').hide();
        }

        if(jQuery('#sameAsBilling').is(':checked'))
        {
            jQuery('#ShippingFirstName, #ShippingLastName, #ShippingStreet, #ShippingCity, #ShippingState, #ShippingCountryCode, #ShippingPostalCode').removeAttr('required');
            jQuery('#FormShippingAddress').hide();
        }

    });
});

document.onkeydown = function(e) {
    var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
    if(key == 13) {
        e.preventDefault();
        if(document.activeElement.name == 'swiper' && jQuery('#swiper').val() != '')
        {
            BlurStripeField();
        }
        else
        {
            var currentInput = document.activeElement;
            var inputs = jQuery(currentInput).closest('form').find(':input:visible');
            inputs.eq( inputs.index(currentInput)+ 1 ).focus();
        }
        return false;
    }
};

jQuery('#Notes').keydown( function(e) {
    var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
    if (key == 13) {
        e.preventDefault();
    }
});

/* Validate Credit Card Number field */
function ValidateCreditCardNumber()
{
    var CardNo = jQuery('#CreditCardNumber').val();
    var CardType = jQuery('#CreditCardType').val();
    return (checkCreditCard(CardNo,CardType)) ? true : false;
}

/* Clear data from card stripe swiped */
function ClearStripeData() {
    var TrackData = jQuery('#swiper');
    TrackData.val('');
}

/* Blur swipe field */
function BlurStripeField() {
    if(jQuery('#swiper').val() != '')
    {
        jQuery('#CreditCardSecurityCode').focus();
        ClearStripeData();
    }
}

/* Parse data from card stripe swiped */
function ParseStripeData() {
    var TrackData = jQuery('#swiper').val();
    var p = new SwipeParserObj(TrackData);

    if(p.hasTrack1)
    {
        // Populate form fields using track 1 data
        var CardType = null;

        if(p.account.charAt(0) == 4)
            CardType = 'Visa';
        else if(p.account.charAt(0) == 5)
            CardType = 'MasterCard';
        else if(p.account.charAt(0) == 3)
            CardType = 'Amex';
        else if(p.account.charAt(0) == 6)
            CardType = 'Discover';
        else
            CardType = 'Visa';

        jQuery('#BillingFirstName').val(p.firstname);
        jQuery('#BillingLastName').val(p.surname);
        jQuery('#CreditCardExpMo').val(p.exp_month);
        jQuery('#CreditCardExpYear').val(p.exp_year);
        jQuery('#CreditCardNumber').val(p.account);
        jQuery('#CreditCardType').val(CardType);

        console.log('update input');

        jQuery('#card-number iframe').contents().find('#credit-card-number').val('adsadasdasd');
        console.log(jQuery('#card-number iframe').contents().find('#credit-card-number').val());
    }
    else
    {
        jQuery('#BillingFirstName').val('');
        jQuery('#BillingLastName').val('');
        jQuery('#CreditCardExpMo').val('');
        jQuery('#CreditCardExpYear').val('');
        jQuery('#CreditCardNumber').val('');
        jQuery('#CreditCardType').val('');
    }

    ToggleIssueNumber();
}

/* Toggle Issue Number */
function ToggleIssueNumber()
{
    var creditCardType = jQuery('#CreditCardType').val();
    if( creditCardType == 'Solo' || creditCardType == 'Switch')
    {
        jQuery('#DivCreditCardIssueNumber').show();
        jQuery('#CreditCardIssueNumber').attr('required', 'required');
    }
    else
    {
        jQuery('#DivCreditCardIssueNumber').hide();
        jQuery('#CreditCardIssueNumber').removeAttr('required');
    }
    return false;
}

/* Update Sales Tax */
function updateSalesTax()
{
    var currencySign = jQuery('#ae-paypal-pos-form').attr('data-currency-sign');
    var taxAmount = ( jQuery('#TaxRate').val().replace(/,/g, '') / 100 ) * jQuery('#NetAmount').val().replace(/,/g, '');
    if(!taxAmount) taxAmount = 0;
    jQuery('#TaxAmountDisplay').html('<i>(' + currencySign + ' ' + roundNumber(taxAmount, 2).replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,") + ')</i>');
    var taxAmountRounded = roundNumber(taxAmount,2);
    if( NaN === taxAmountRounded || undefined === taxAmountRounded || null === taxAmountRounded){
        taxAmountRounded = roundNumber(0,2);
    }
    jQuery('#TaxAmount').val(taxAmountRounded);
    return false;
}

/* Update Grand Total */
function updateGrandTotal()
{
    var currencySign = jQuery('#ae-paypal-pos-form').attr('data-currency-sign');
    var netAmount = ( jQuery('#NetAmount').val().replace(/,/g, '') * 1 );
    var shippingAmount = ( jQuery('#ShippingAmount').val().replace(/,/g, '') * 1 );
    var handlingAmount = ( jQuery('#HandlingAmount').val().replace(/,/g, '') * 1 );
    var taxAmount = jQuery('#TaxAmount').val().replace(/,/g, '') * 1;
    var grandTotal = (netAmount + shippingAmount + handlingAmount + taxAmount);
    var grandTotal = grandTotal.toFixed(2);
    jQuery('#GrandTotalDisplay').html('<strong>' + currencySign + ' ' + grandTotal.replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,") + '</strong>');
    jQuery('#GrandTotal').val(grandTotal);
    return false;
}

/* Round Number */
function roundNumber(num, dec)
{
    var result = Math.round(num*Math.pow(10,dec))/Math.pow(10,dec);
    return result.toFixed(2);
}
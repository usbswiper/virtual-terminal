<div class="vt-payment-timeout-popup-wrapper">
    <div class="vt-payment-timeout-popup-inner">
        <a href="javascript:void(0)" class="close-btn"><span class="dashicons dashicons-no"></span></a>
        <div class="vt-notification-content">
            <div class="input-field-wrap ">
                <h3><strong><?php _e("Your Session Is About to Expire","usb-swiper"); ?></strong></h3>
                <p><?php echo sprintf( __("You've been inactive for a while. For your security, we will automatically log you out after <strong class='auto-session-timer'>%s minutes</strong>.","usb-swiper"), "<span id='auto_session_time'></span>" ) ?></p>
                <p><?php _e("Do you want to stay logged in?","usb-swiper"); ?></p>
            </div>
            <div class="input-field-wrap button-wrap">
                <button type="button" class="vt-button vt-payment-form-timeout" id="vt_form_timeout" name="vt_form_timeout_btn"><?php _e('STAY LOGGED IN','usb-swiper'); ?></button>
                <a href="<?php echo wc_logout_url(); ?>" class="vt-button vt-button-link vt-session-logout-link"><?php _e('Log out','usb-swiper'); ?></a>
            </div>
        </div>
    </div>
</div>
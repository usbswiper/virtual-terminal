<?php
$default_country = 'US';

$form_fields = array(
    array(
        'type' => 'text',
        'id' => 'first_name',
        'name' => 'first_name',
        'placeholder' => __('First Name', 'usb-swiper'),
        'label' => 'First Name',
        'required' => true,
        'options' => '',
        'attributes' => '',
        'class' => 'vt-input-field'
    ),
    array(
        'type' => 'text',
        'id' => 'last_name',
        'name' => 'last_name',
        'placeholder' => __('Last Name', 'usb-swiper'),
        'label' => 'Last Name',
        'required' => true,
        'options' => '',
        'attributes' => '',
        'class' => 'vt-input-field'
    ),
	array(
		'type' => 'text',
		'id' => 'phone',
		'name' => 'phone',
		'placeholder' => __('+1-202-555-0149', 'usb-swiper'),
		'label' => 'Phone',
		'required' => true,
		'options' => '',
		'attributes' => '',
		'class' => 'vt-input-field'
	),
	array(
		'type' => 'url',
		'id' => 'url',
		'name' => 'website-url',
		'placeholder' => __('https://company.com', 'usb-swiper'),
		'label' => 'Website URL',
		'required' => true,
		'options' => '',
		'attributes' => '',
		'class' => 'vt-input-field'
	),
	array(
		'type' => 'text',
		'id' => 'company-name',
		'name' => 'company-name',
		'placeholder' => __('Company', 'usb-swiper'),
		'label' => 'Company Name',
		'required' => true,
		'options' => '',
		'attributes' => '',
		'class' => 'vt-input-field'
	),
    array(
		'type' => 'email',
		'id' => 'email_address',
		'name' => 'email-address',
		'placeholder' => __('email.address@company.com', 'usb-swiper'),
		'label' => 'Email Address',
		'required' => true,
		'options' => '',
		'attributes' => '',
		'class' => 'vt-input-field'
	),
    array(
        'type' => 'text',
        'id' => 'billing_address_1',
        'name' => 'billing_address_1',
        'label' => __( 'Address line 1', 'usb-swiper'),
        'required' => true,
        'attributes' => array(
            'maxlength' => 25
        ),
        'description' => '',
        'class' => 'vt-billing-address-field vt-input-field',
    ),
    array(
        'type' => 'text',
        'id' => 'billing_address_2',
        'name' => 'billing_address_2',
        'label' => __( 'Address line 2', 'usb-swiper'),
        'required' => false,
        'attributes' => array(
            'maxlength' => 25
        ),
        'description' => '',
        'class' => 'vt-billing-address-field vt-input-field',
    ),
    array(
        'type' => 'text',
        'id' => 'billing_city',
        'name' => 'billing_city',
        'label' => __( 'City', 'usb-swiper'),
        'required' => true,
        'attributes' => array(
            'maxlength' => 25
        ),
        'description' => '',
        'class' => 'vt-billing-address-field vt-input-field',
    ),
    array(
        'type' => 'select',
        'id' => 'billing_state',
        'name' => 'billing_state',
        'label' => __( 'State / County', 'usb-swiper'),
        'required' => true,
        'attributes' => '',
        'options' => usb_swiper_get_states($default_country),
        'description' => '',
        'default' => '',
        'class' => 'vt-billing-address-field vt-select-field vt-billing-states',
		'wrapper' =>  true,
		'wrapper_class' => 'state-field billing-states-wrap',
    ),
    array(
        'type' => 'text',
        'id' => 'billing_postcode',
        'name' => 'billing_postcode',
        'label' => __( 'Postcode / ZIP', 'usb-swiper'),
        'required' => true,
        'options' => array(),
        'attributes' => array(
            'maxlength' => 25
        ),
        'class' => 'vt-billing-address-field vt-input-field',
    ),
    array(
        'type' => 'select',
        'id' => 'billing_country',
        'name' => 'billing_country',
        'label' => __( 'Country / Region', 'usb-swiper'),
        'required' => true,
        'attributes' => '',
        'options' => usb_swiper_get_countries(),
        'description' => '',
        'default' => $default_country,
        'class' => 'vt-billing-address-field vt-select-field vt-billing-country',
    ),
	array(
		'type' => 'hidden',
		'id' => 'vt-verification-nonce',
		'name' => 'vt-verification-nonce',
		'label' => '',
        'value' => wp_create_nonce('vt-verification-form'),
		'required' => false,
	)
);
$profile_status = get_user_meta( get_current_user_id(),'vt_user_verification_status', true );
$profile_data = get_user_meta( get_current_user_id(),'verification_form_data', true );
$profile_status = filter_var( $profile_status, FILTER_VALIDATE_BOOLEAN );

if( false === $profile_status && empty( $profile_data ) || ( current_user_can( 'manage_options' ) === true ) ) {
?>
    <div class="vt-verification-form-wrapper woocommerce">
        <div class="vt-form-notification">
        </div>
        <div class="vt-verification-form">
            <form id="vt_verification_form" method="post" name="vt-verification-form">
                <?php
                if( !empty( $form_fields ) && is_array( $form_fields ) ) {
                    foreach ( $form_fields as $tab_key => $form_field ) { ?>
                        <div class="vt-fields-wrap">
                            <?php
                            echo usb_swiper_get_html_field( $form_field );
                            ?>
                        </div>
                    <?php
                    }
                }
                ?>
                <div class="vt-fields-wrap">
                    <button id="vt_verification_form_submit" type="submit" class="vt-button" name="submit">Submit</button>
                </div>
            </form>
        </div>
    </div>
<?php
} else {
	?>
    <div class="vt-form-notification">
        <p class="notification error">
			<?php _e('Profile verification Pending', 'usb-swiper' ); ?>
        </p>
    </div>
<?php
}
?>
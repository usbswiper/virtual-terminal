<?php
/**
 * Email Footer
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-footer.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 7.4.0
 */

defined( 'ABSPATH' ) || exit;
?>
</div>
</td>
</tr>
</table>
<!-- End Content -->
</td>
</tr>
</table>
<!-- End Body -->
</td>
</tr>
</table>
</td>
</tr>
<tr>
    <td align="center" valign="top">
        <!-- Footer -->
        <table border="0" cellpadding="10" cellspacing="0" width="100%" id="template_footer">
            <tr>
                <td valign="top">
                    <table border="0" cellpadding="10" cellspacing="0" width="100%">
                        <tr>
                            <td colspan="2" valign="middle" id="credit">
                                <?php

                                $user_id = get_current_user_id();
                                $company_name = get_user_meta($user_id,'brand_name', true);
                                $user_email = get_userdata($user_id)->user_email;
                                $phone = get_user_meta($user_id, 'billing_phone', true);
                                $user_address1 = get_user_meta($user_id, 'billing_address_1', true);
                                $user_address2 = get_user_meta($user_id, 'billing_address_2', true);
                                $city = get_user_meta($user_id, 'billing_city', true);
                                $state = get_user_meta($user_id, 'billing_state', true);
                                $postcode = get_user_meta($user_id, 'billing_postcode', true);
                                $country = get_user_meta($user_id, 'billing_country', true);

                                ?>
                                <div class="footer-info" style="text-align: center;">
                                    <?php if( !empty( $company_name ) ){ ?>
                                        <h2 style="margin: 0px;text-align: center;"><?php echo $company_name; ?></h2>
                                    <?php }
                                    if (!empty($user_email)){ ?>
                                    <p style="margin: 0px;"><?php echo $user_email; ?></p>
                                    <?php }
                                    if (!empty($phone)){ ?>
                                    <p style="margin: 0px;"><?php echo $phone; ?></p>
                                    <?php } ?>
                                    <p style="margin: 0px;">
                                        <?php
                                            if (!empty($user_address1)) {
                                                echo $user_address1;
                                            }
                                            if (!empty($user_address2)) {
                                                echo !empty($user_address1) ? ', ' . $user_address2 : $user_address2;
                                            }
                                        ?>
                                   </p>
                                    <p style="margin: 0px;">
                                        <?php
                                            if (!empty($city)) {
                                                echo $city;
                                            }
                                            if (!empty($state)) {
                                                echo !empty($city) ? ', ' . $state : $state;
                                            }
                                            if (!empty($postcode)) {
                                                echo (!empty($city) || !empty($state)) ? '-' . $postcode : $postcode;
                                            }
                                        ?>
                                    </p>
                                    <?php if( !empty( $country ) ){ ?>
                                        <p style="margin: 0px;"><?php echo $country; ?></p>
                                    <?php } ?>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <!-- End Footer -->
    </td>
</tr>
</table>
</div>
</td>
<td></td>
</tr>
</table>
</body>
</html>

<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://ahmedraza.dev
 * @since      1.0.0
 *
 * @package    Recurly_Manager_User_Form
 * @subpackage Recurly_Manager_User_Form/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Recurly_Manager_User_Form
 * @subpackage Recurly_Manager_User_Form/includes
 * @author     Ahmad Raza <raza.ataki@gmail.com>
 */
class Recurly_Manager_User_Form
{
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Recurly_Manager_User_Form $loader Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $plugin_name The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $version The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        if (defined('RECURLY_MANAGER_VERSION')) {
            $this->version = RECURLY_MANAGER_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'recurly-manager';
        add_action('init', [$this, 'register_shortcodes']);

    }

    /**
     * Register the shortcodes.
     */
    public function register_shortcodes()
    {
        add_shortcode('recurly_user_creation_form', array($this, 'recurly_user_creation_form'));
        add_shortcode('recurly_cancel_subscription_form', array($this, 'recurly_cancel_subscription_form'));
        add_shortcode('recurly_renew_subscription_form', array($this, 'recurly_renew_subscription_form'));
        add_shortcode('recurly_upgrade_subscription_form', array($this, 'recurly_upgrade_subscription_form'));
    }

    public function recurly_user_creation_form($atts)
    {
        if (!session_id()) {
            session_start();
        }
        $atts = shortcode_atts(array(
            'button_text' => 'Submit'
        ), $atts);

        ob_start();
        ?>
        <div class="form-container">
            <?php

            echo "<div class='recurly-form-message-wraper'>";
            if (isset($_SESSION['recurly_errors'])) {
                echo '<div class="recurly-message error-message">' . esc_html($_SESSION['recurly_errors']) . '</div>';
                // Clear the error message after displaying it
                unset($_SESSION['recurly_errors']);
            }
            if (isset($_SESSION['recurly_success'])) {
                echo '<div class="recurly-message success-message">' . esc_html($_SESSION['recurly_success']) . '</div>';
                // Clear the error message after displaying it
                unset($_SESSION['recurly_success']);
            }
            echo "<div id='recurly-errors' style='display:none'></div>";
            echo "<div id='recurly-success' style='display:none'></div>";
            echo "</div>";

            ?>
            <form id="createAccount" method="POST" >
                <input name="action" type="hidden" value="recurly_create_user_account"/>
                <div class="form-group recurly-f50-group">
                    <label for="firstname">First Name</label>
                    <input id="firstname" data-recurly="first_name" name="firstname" required="" type="text"/>
                </div>
                <div class="form-group  recurly-f50-group">
                    <label for="lastname">Last Name</label>
                    <input id="lastname" data-recurly="last_name" name="lastname" required="" type="text"/>
                </div>
                <div class="form-group recurly-f50-group">
                    <label for="email">Email</label>
                    <input id="email" name="email" required="" type="email"/>
                </div>
                <div class="form-group recurly-f50-group">
                    <label for="phone">Phone</label>
                    <input id="phone" name="phone" required="" type="text"/>
                </div>
                <div class="form-group">
                    <div id="recurly-elements">
                        <!-- Recurly Elements will be attached here -->
                    </div>
                </div>
                <div class="form-group recurly-f50-group">
                    <label for="currency">Currency</label>
                    <select id="currency" name="currency" required="">
                        <option value="USD">USD</option>
                    </select>
                </div>
                <div class="form-group recurly-f50-group">
                    <label for="subscription">Subscription</label>
                    <?php
                    $selected =  'selected';
                    $rc_membership=  '';
                    if (isset($_GET['rc-membership'])) {
                        $rc_membership = $_GET['rc-membership'];
                    }
                    ?>
                    <select id="subscription" name="subscription" required="">
                        <option value="" disabled>Select a subscription</option>
                        <option <?php  echo ('basic-membership' == $rc_membership) ? $selected : ''; ?> value="basic">Basic Membership</option>
                        <option <?php  echo ('premium-membership' == $rc_membership) ? $selected : ''; ?> value="annual">Premium Membership</option>
                        <option <?php  echo ('growth-membership' == $rc_membership) ? $selected : ''; ?> value="growth">Growth Membership</option>
                        <option value="templates-membership">Test - Lengea Templates Membership</option>
                    </select>
                </div>
                <div class="form-group recurly-f50-group">
                    <label for="company">Company</label>
                    <input id="company" name="company" required="" type="text"/>
                </div>
                <div class="form-group recurly-f50-group">
                    <label for="country">Country</label>
                    <!--                    <input id="country" data-recurly="country" name="country" required="" type="text"/>-->
                    <select class="country" data-recurly="country" name="country" required id="country">
                        <option value="">Select country...</option>
                        <option value="US">United States</option>
                    </select>
                </div>
                <div class="form-group recurly-f50-group">
                    <label for="address1">Street Address</label>
                    <input id="address1" data-recurly="address1" name="address1" required="" type="text"/>
                </div>
                <div class="form-group recurly-f50-group">
                    <label for="address2">Address 2</label>
                    <input id="address2" data-recurly="address2" name="address2" type="text"/>
                </div>
                <div class="form-group recurly-f50-group">
                    <label for="city">City</label>
                    <input id="city" data-recurly="city" name="city" required="" type="text"/>
                </div>
                <div class="form-group recurly-f50-group">
                    <label for="coupon">Coupon</label>
                    <input id="coupon" name="coupon" type="text"/>
                </div>
                <div class="form-group recurly-f50-group">
                    <label for="state">State</label>
                    <!--                    <input id="state" data-recurly="state" name="state" required="" type="text"/>-->
                    <select class="state" data-recurly="state" name="state" id="state" required="">
                        <option value="">Select state...</option>
                        <option value="AL">Alabama</option>
                        <option value="AK">Alaska</option>
                        <option value="AS">America Samoa</option>
                        <option value="AZ">Arizona</option>
                        <option value="AR">Arkansas</option>
                        <option value="AA">Armed Forces Americas</option>
                        <option value="AE">Armed Forces</option>
                        <option value="AP">Armed Forces Pacific</option>
                        <option value="CA">California</option>
                        <option value="CO">Colorado</option>
                        <option value="CT">Connecticut</option>
                        <option value="DE">Delaware</option>
                        <option value="DC">District of Columbia</option>
                        <option value="FM">Federated States of Micronesia</option>
                        <option value="FL">Florida</option>
                        <option value="GA">Georgia</option>
                        <option value="GU">Guam</option>
                        <option value="HI">Hawaii</option>
                        <option value="ID">Idaho</option>
                        <option value="IL">Illinois</option>
                        <option value="IN">Indiana</option>
                        <option value="IA">Iowa</option>
                        <option value="KS">Kansas</option>
                        <option value="KY">Kentucky</option>
                        <option value="LA">Louisiana</option>
                        <option value="ME">Maine</option>
                        <option value="MH">Marshall Islands</option>
                        <option value="MD">Maryland</option>
                        <option value="MA">Massachusetts</option>
                        <option value="MI">Michigan</option>
                        <option value="MN">Minnesota</option>
                        <option value="MS">Mississippi</option>
                        <option value="MO">Missouri</option>
                        <option value="MT">Montana</option>
                        <option value="NE">Nebraska</option>
                        <option value="NV">Nevada</option>
                        <option value="NH">New Hampshire</option>
                        <option value="NJ">New Jersey</option>
                        <option value="NM">New Mexico</option>
                        <option value="NY">New York</option>
                        <option value="NC">North Carolina</option>
                        <option value="ND">North Dakota</option>
                        <option value="MP">Northern Mariana Islands</option>
                        <option value="OH">Ohio</option>
                        <option value="OK">Oklahoma</option>
                        <option value="OR">Oregon</option>
                        <option value="PA">Pennsylvania</option>
                        <option value="PR">Puerto Rico</option>
                        <option value="PW">Palau</option>
                        <option value="RI">Rhode Island</option>
                        <option value="SC">South Carolina</option>
                        <option value="SD">South Dakota</option>
                        <option value="TN">Tennessee</option>
                        <option value="TX">Texas</option>
                        <option value="UT">Utah</option>
                        <option value="VA">Virginia</option>
                        <option value="VI">Virgin Islands</option>
                        <option value="VT">Vermont</option>
                        <option value="WA">Washington</option>
                        <option value="WI">Wisconsin</option>
                        <option value="WV">West Virginia</option>
                        <option value="WY">Wyoming</option>
                    </select>
                </div>
                <div class="form-group recurly-f50-group">
                    <label for="zip">Zip/Postal Code</label>
                    <input id="zip" name="zip" data-recurly="postal_code" required="" type="text"/>
                </div>
                <!-- Recurly.js will update this field automatically -->
                <input type="hidden" name="recurly-token" data-recurly="token">
                <div class="form-group">
                    <button class="btn" type="submit"><?php echo esc_html($atts['button_text']); ?></button>
                </div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    public function recurly_cancel_subscription_form($atts)
    {
        if (!session_id()) {
            session_start();
        }
        $atts = shortcode_atts(array(
            'button_text_cancel' => 'Cancel',
            'button_clr_cancel' => 'orangered',
        ), $atts);

        ob_start();
        ?>
        <form id="recurly-cancel-subscription-form" method="POST">
            <div class="form-group">
                <input name="action" type="hidden" value="recurly_cancel_subscription"/>
                <button class="recurly-button recurly-button-cancel"
                        type="submit"><?php echo esc_html($atts['button_text_cancel']); ?></button>
            </div>
        </form>
        <?php
        return ob_get_clean();
    }

    public function recurly_renew_subscription_form($atts)
    {
        if (!session_id()) {
            session_start();
        }

        $atts = shortcode_atts(array(
            'button_text_renew' => 'Renew',
            'button_clr_renew' => 'green',
        ), $atts);

        ob_start();
        ?>
        <form id="recurly-renew-subscription-form" method="POST">
            <div class="form-group">
                <input name="action" type="hidden" value="recurly_renew_subscription"/>
                <button class="recurly-button" type="submit"
                        style="background-color: <?php echo esc_html($atts['button_clr_renew']); ?>"><?php echo esc_html($atts['button_text_renew']); ?></button>
            </div>
        </form>
        <?php
        return ob_get_clean();
    }

    public function recurly_upgrade_subscription_form($atts)
    {
        if (!session_id()) {
            session_start();
        }
        echo "<div class='recurly-form-message-wraper'>";
        if (isset($_SESSION['recurly_errors'])) {
            echo '<div class="recurly-message error-message">' . esc_html($_SESSION['recurly_errors']) . '</div>';
            // Clear the error message after displaying it
            unset($_SESSION['recurly_errors']);
        }
        if (isset($_SESSION['recurly_success'])) {
            echo '<div class="recurly-message success-message">' . esc_html($_SESSION['recurly_success']) . '</div>';
            // Clear the error message after displaying it
            unset($_SESSION['recurly_success']);
        }
        echo "</div>";


        $atts = shortcode_atts(array(
            'button_text' => 'Upgrade'
        ), $atts);

        $current_user = wp_get_current_user();
        $user_meta = get_user_meta($current_user->ID);
        ob_start();
        ?>
        <div class="form-container">
            <form id="createAccount" method="POST">
                <input name="action" type="hidden" value="recurly_upgrade_user_subscription"/>
                <div class="form-group recurly-f50-group">
                    <label for="firstname">First Name</label>
                    <input id="firstname" data-recurly="first_name" name="firstname" required="" type="text"
                           value="<?php echo $current_user->first_name; ?>"/>
                </div>
                <div class="form-group  recurly-f50-group">
                    <label for="lastname">Last Name</label>
                    <input id="lastname" data-recurly="last_name" name="lastname" required="" type="text"
                           value="<?php echo $current_user->last_name; ?>"/>
                </div>
                <div class="form-group recurly-f50-group">
                    <label for="email">Email</label>
                    <input id="email" name="email" required="" type="email"
                           value="<?php echo $current_user->user_email; ?>"/>
                </div>
                <div class="form-group recurly-f50-group">
                    <label for="subscription">Subscription</label>

                    <?php
                    $subscription_type = $user_meta['subscription_type'][0];
                    $selected = 'selected';
                    ?>
                    <select id="subscription" name="subscription" required="">
                        <option <?php  echo ($subscription_type === 'basic') ? $selected : ''; ?> value="basic">Basic Membership</option>
                        <option <?php  echo ($subscription_type === 'annual' ) ? $selected : ''; ?> value="annual">Premium Membership</option>
                        <option <?php  echo ( $subscription_type === 'growth') ? $selected : ''; ?> value="growth">Growth Membership</option>
                        <option value="templates-membership">Test - Lengea Templates Membership</option>
                    </select>
                </div>
                <!-- Recurly.js will update this field automatically -->
                <input type="hidden" name="recurly-token" data-recurly="token">
                <div class="form-group">
                    <button class="btn" type="submit"><?php echo esc_html($atts['button_text']); ?></button>
                </div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }


}

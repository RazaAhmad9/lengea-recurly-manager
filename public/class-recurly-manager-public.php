<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://ahmedraza.dev
 * @since      1.0.0
 *
 * @package    Recurly_Manager
 * @subpackage Recurly_Manager/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Recurly_Manager
 * @subpackage Recurly_Manager/public
 * @author     Ahmad Raza <raza.ataki@gmail.com>
 */
class Recurly_Manager_Public
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    public $placeholders;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of the plugin.
     * @param string $version The version of this plugin.
     * @since    1.0.0
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

        $this->recurly_check_subscriber_role_and_handle_endpoints();

        add_filter( 'woocommerce_new_customer_data', [$this, 'recurly_woocommerce_new_customer_data_set_role']);
        
    add_action('wp', [$this,'run_update_billing_shipping_once']);



    }



    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Recurly_Manager_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Recurly_Manager_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/recurly-manager-public.css', array(), $this->version, 'all');
        wp_enqueue_style($this->plugin_name . '-recurly', 'https://js.recurly.com/v4/recurly.css', array(), $this->version, 'all');

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Recurly_Manager_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Recurly_Manager_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script($this->plugin_name . '-recurly', 'https://js.recurly.com/v4/recurly.js', array('jquery'), $this->version, true);

        $options = get_option('recurly_manager_option');
        if (is_page($options['recurly_manager_js'])) {
            wp_enqueue_script($this->plugin_name . '-create-account', plugin_dir_url(__FILE__) . 'js/recurly-manager-create-account.js', array('jquery'), $this->version, true);
        }
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/recurly-manager-public.js', array('jquery'), $this->version, true);


		wp_localize_script( $this->plugin_name . '-create-account', 'recurlyManagerCreateAccount', [
				'recurlyPublicKey' => $options['recurly_manager_public_api_key'],
			]
		);
    }

    public function recurly_check_subscriber_role_and_handle_endpoints()
    {
        if (!function_exists('wp_get_current_user')) {
            require_once(ABSPATH . 'wp-includes/pluggable.php');
        }
        if (current_user_can('subscriber')) {

            add_action('woocommerce_account_menu_items', [$this, 'recurly_add_user_profile_to_wc_tabs']);
            add_action('init', [$this, 'recurly_add_custom_wc_endpoint']);
            add_action('init', [$this, 'recurly_flush_rewrite_rules_on_activation']);

            add_action('woocommerce_account_user-profile_endpoint', [$this, 'recurly_user_profile_content']);
            add_action('woocommerce_account_edit-basic-info_endpoint', [$this, 'recurly_edit_basic_info_content']);
            add_action('woocommerce_account_your-membership_endpoint', [$this, 'recurly_user_membership_info_content']);
            add_action('woocommerce_account_upgrade-membership_endpoint', [$this, 'recurly_user_upgrade_membership_info_content']);
            // add_action('woocommerce_account_credits-history_endpoint', [$this, 'recurly_user_credit_history_info_content']);

            add_action('init', [$this, 'recurly_acf_form_head']);
        }

        add_action('wp_login', [$this, 'recurly_custom_login_redirect'], 999, 2);
        add_filter('wp_new_user_notification_email', [$this, 'recurly_user_notification_email'], 10, 3);

    }

    public function recurly_flush_rewrite_rules_on_activation()
    {
        flush_rewrite_rules();
    }

    public function recurly_custom_login_redirect($user_login, $user)
    {
        // Check if user role is 'subscriber'
        if (in_array('subscriber', (array)$user->roles)) {
            // Get the ACF field value for 'subscription_type'
            $subscription_type = get_field('subscription_type', 'user_' . $user->ID);

            // Check if 'subscription_type' is not empty
            if (!empty($subscription_type)) {
                // Get the redirect URL from the 'recurly_manager_logged_in_users' option
                $options = get_option('recurly_manager_option');
                if (!empty($options['recurly_manager_logged_in_users'])) {
                    $redirect_url = esc_url($options['recurly_manager_logged_in_users']);
                    // Redirect the user
                    wp_redirect(home_url($redirect_url));
                    exit;
                }
            }
        }
    }

    public function recurly_add_user_profile_to_wc_tabs($menu_links)
    {
        $new = array(
            'user-profile' => 'User Profile',
            'edit-basic-info' => 'Edit Basic Info',
            'your-membership' => 'Your Membership',
            'upgrade-membership' => 'Upgrade Membership',
            // 'credits-history'=>'Credits History',
        );
        $menu_links = array_slice($menu_links, 0, 5, true)
            + $new
            + array_slice($menu_links, 5, NULL, true);

        return $menu_links;
    }

    public function recurly_add_custom_wc_endpoint()
    {
        add_rewrite_endpoint('user-profile', EP_PAGES);
        add_rewrite_endpoint('edit-basic-info', EP_PAGES);
        add_rewrite_endpoint('your-membership', EP_PAGES);
        add_rewrite_endpoint('upgrade-membership', EP_PAGES);
        // add_rewrite_endpoint('credits-history', EP_PAGES);
    }

    public function recurly_user_profile_content()
    {
        $current_user = wp_get_current_user();
        $user_meta = get_user_meta($current_user->ID);
        $subscription_type = str_replace('-', ' ', ucwords($user_meta['subscription_type'][0]));


        echo '<div id="recurly_user_profile_content">';
        echo '<h3>Basic Information <a class="recurly-edit" href="/my-account/edit-basic-info/">edit</a></h3>';
        echo '<div>';
        echo '<table class="recurly-user-profile-table">';
        echo '<tr class="recurly-table-row recurly-table-row-name">';
        echo '<td>Name</td>';
        echo '<td>' . $current_user->display_name . '</td>';
        echo '</tr>';
        echo '<tr class="recurly-table-row recurly-table-row-email">';
        echo '<td>Email</td>';
        echo '<td>' . $current_user->user_email . '</td>';
        echo '</tr>';
        echo '<tr class="recurly-table-row recurly-table-row-phnumber">';
        echo '<td>Phone Number</td>';
        echo '<td>' . $user_meta['ll_phone_number'][0] . '</td>';
        echo '</tr>';
        echo '<tr class="recurly-table-row recurly-table-row-subtype">';
        echo '<td>Subscription Type</td>';
        echo '<td>' . $subscription_type . '</td>';
        echo '</tr>';
        echo '</table>';
        echo '<a class="recurly-button recurly-button-view-membership" href="/my-account/your-membership/">View Membership</a>';
        echo '</div>';
    }

    public function recurly_edit_basic_info_content()
    {
        if (is_user_logged_in()) {
            $current_user_id = get_current_user_id();
            acf_form(array(
                'post_id' => 'user_' . $current_user_id,
                'field_groups' => false,
                'post_title' => false,
                'fields' => array('field_66cdb46fff6a4', 'field_66cdb4a1adb55', 'field_66cdb4b2adb56', 'field_66cdb4c2adb57', 'field_66cdb4dbadb59', 'field_66cdb4e7adb5a', 'field_66cdb4f0adb5c', 'field_66d98019dba00'),
                'submit_value' => __('Update', 'recurly-manager'),
                'form' => true,
                'id' => 'recurly-edit-basic-info-form',
                'html_updated_message' => '<div id="message" class="recurly-updated"><p>Your profile information has been successfully updated.</p></div>',
            ));

        } else {
            echo 'You need to log in to update your profile.';
        }
    }

    public function recurly_user_membership_info_content()
    {
        $current_user = wp_get_current_user();
        $user_meta = get_user_meta($current_user->ID);
        $recurly_subscription_expiry_date = $user_meta['recurly_subscription_expiry_date'][0];

        $sub_date = new DateTime($recurly_subscription_expiry_date);
        $expires_on = $sub_date->format('d-M-Y');
        
        $total_credits = do_shortcode('[fwc_amount]');
        $remaining_credits= do_shortcode('[fwc_amount]');

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

        echo "<div class='recurly-membership-header'>";
        echo "<h3>" . esc_attr('Membership') . "</h3>";
        echo "<div  class='recurly-user-greeting'>";
        echo "<small>Welcome</small>";
        echo "<p>$current_user->display_name</p>";
        echo "</div>";
        echo "</div>"; // header ends here
        echo "<div class='recurly-membership-body'>";
        echo "<div class='recurly-body-header'>";
        echo "<div class='recurly-renew-cont'>";
       
        echo "</div>";
        echo "</div>";
        echo '';
        echo '<table class="recurly-user-profile-table">';
        echo '<tr class="recurly-table-row">';
        echo '<td><h4>Available Credits</h4></td>';
        echo '<td> <span class="recurly-monthly-credits">'. $total_credits . '</span></td>';
	    echo '<tr class="recurly-table-row recurly-table-row-country">';
	    echo '<td>Membership Type</td>';
	    echo '<td>' . $current_user->subscription_type . '</td>';
	    echo '</tr>';

	    echo '<tr class="recurly-table-row recurly-table-row-country">';
	    echo '<td>Expiring On</td>';
	    echo '<td>' . $expires_on . '</td>';
	    echo '</tr>';

//        echo '<td>';
//        echo do_shortcode("[recurly_renew_subscription_form button_text_renew='Renew Now' button_clr_renew='green']");
//        echo '<span class="recurly-expiry-date">Expiring on ' . $expires_on . '</span>';
//        echo '</td>';
        echo '</tr>';
        echo '<tr class="recurly-table-row">';
        echo '<td><h4>Basic Information <a class="recurly-edit" href="/my-account/edit-basic-info/">edit</a></h4></td>';
        echo '<td></td>';
        echo '<td></td>';
        echo '</tr>';
        echo '<tr class="recurly-table-row recurly-table-row-name">';
        echo '<td>Name</td>';
        echo '<td>' . $current_user->display_name . '</td>';
//        echo '<td><a class="recurly-button recurly-button-upgrade" href="/my-account/upgrade-membership/">Upgrade</a></td>';
        echo '</tr>';
        echo '<tr class="recurly-table-row recurly-table-row-email">';
        echo '<td>Email</td>';
        echo '<td>' . $current_user->user_email . '</td>';
//        echo '<td>' . do_shortcode('[recurly_cancel_subscription_form button_text_cancel="Cancel" button_clr_cancel="#D27356"]') . '</td>';
        echo '</tr>';
        echo '<tr class="recurly-table-row recurly-table-row-phnumber">';
        echo '<td>Phone Number</td>';
        echo '<td>' . $user_meta['ll_phone_number'][0] . '</td>';
        echo '</tr>';
        echo '<tr class="recurly-table-row recurly-table-row-country">';
        echo '<td>Country</td>';
        echo '<td>' . $user_meta['ll_country'][0] . '</td>';
        echo '</tr>';
        echo '<tr class="recurly-table-row recurly-table-row-city">';
        echo '<td>City</td>';
        echo '<td>' . $user_meta['ll_city'][0] . '</td>';
        echo '</tr>';
        echo '<tr class="recurly-table-row recurly-table-row-street-address">';
        echo '<td>Street Address</td>';
        echo '<td>' . $user_meta['ll_street_address'][0] . '</td>';
        echo '</tr>';
        echo '<tr class="recurly-table-row recurly-table-row-street-address-2">';
        echo '<td>Address 2</td>';
        echo '<td>' . $user_meta['ll_address_2'][0] . '</td>';
        echo '</tr>';
        echo '<tr class="recurly-table-row recurly-table-row-zipcode">';
        echo '<td>Zip Code/Postal</td>';
        echo '<td>' . $user_meta['ll_zip_postal_code'][0] . '</td>';
        echo '</tr>';
        echo '</table>';
        echo "</div>";
    }

    public function recurly_user_upgrade_membership_info_content()
    {
        echo do_shortcode('[recurly_upgrade_subscription_form button_text_upgrade="Upgrade" button_clr_upgrade="black"]');
    }

    // public function recurly_user_credit_history_info_content(){
    //     echo do_shortcode('[recurly_upgrade_subscription_form button_text_upgrade="Upgrade" button_clr_upgrade="black"]');
    
    // }

    public function recurly_acf_form_head()
    {
        acf_form_head();
    }

    public function recurly_user_notification_email($wp_new_user_notification_email, $user, $blogname)
    {
        // Get user data
        $user_login = $user->user_login;
        $user_email = $user->user_email;
        $first_name = ucwords(strtolower($user->first_name));
        $last_name = ucwords(strtolower($user->last_name)); 
        $subject_line = "Lengea's Membership";
        $regards = "The Lengea Team";

        // Customize the email subject
        $wp_new_user_notification_email['subject'] = sprintf(__("Welcome to Lengea's Membership - Set Your Password"));

        // Create a custom reset password URL
        $reset_key = get_password_reset_key($user);
        $reset_url = network_site_url("wp-login.php?action=rp&key=$reset_key&login=" . rawurlencode($user_login), 'login');

        // Customize the email body
        $wp_new_user_notification_email['message'] = sprintf(
            __("Hi %s %s,\n\nWelcome to %s! We'are excited to have you on board. To get started, please click the below link to set up your password and access your account:\n\n%s\n\nIf you have any questions or need help, don't hesitate to reach out. We're here to assist you every step of the way.\n\n%s"),
            $first_name, $last_name, $subject_line, $reset_url, $regards
        );

        return $wp_new_user_notification_email;
    }

    

    /**
     * Function for woocommerce_new_customer_data filter hook.
     * 
     * @param array $customer_data An array of customer data.
     *
     * @return array
     */
    public function recurly_woocommerce_new_customer_data_set_role( $customer_data ){
        $customer_data['role'] = 'subscriber';
        return $customer_data;
    }


    public function update_user_billing_shipping_info() {


        $current_user = wp_get_current_user();
        $user_meta = get_user_meta($current_user->ID);

        $user_first_name  = $user_meta['first_name'][0];
        $user_last_name = $user_meta['last_name'][0];
        $user_street_address= $user_meta['ll_street_address'][0];
        $user_street_address_2 = $user_meta['ll_address_2'][0];
        $user_city = $user_meta['ll_city'][0];
        $user_zip_postal_code = $user_meta['ll_zip_postal_code'][0];
        $user_country  = $user_meta['ll_country'][0];
        $user_phone = $user_meta['ll_phone_number'][0];
        $user_state = $user_meta['ll_state'][0];
        $user_email_address = $current_user->user_email;

        // Check if user exists
        if ($current_user) {
            // User ID
            $user_id = $current_user->ID;
            
            // Billing Information
            update_user_meta($user_id, 'billing_first_name', $user_first_name);
            update_user_meta($user_id, 'billing_last_name',  $user_last_name);
            update_user_meta($user_id, 'billing_address_1', $user_street_address);
            update_user_meta($user_id, 'billing_address_2', $user_street_address_2);    
            update_user_meta($user_id, 'billing_city', $user_city);
            update_user_meta($user_id, 'billing_postcode', $user_zip_postal_code);
            update_user_meta($user_id, 'billing_country', $user_country);
            update_user_meta($user_id, 'billing_phone', $user_phone);
            update_user_meta($user_id, 'billing_email', $user_email_address);
            update_user_meta($user_id, 'billing_state', $user_state);
            
            // Shipping Information
            update_user_meta($user_id, 'shipping_first_name', $user_first_name);
            update_user_meta($user_id, 'shipping_last_name', $user_last_name);
            update_user_meta($user_id, 'shipping_address_1', $user_street_address);
            update_user_meta($user_id, 'shipping_address_2', $user_street_address_2);
            update_user_meta($user_id, 'shipping_city', $user_city);
            update_user_meta($user_id, 'shipping_postcode', $user_phone);
            update_user_meta($user_id, 'shipping_country', $user_country);
            update_user_meta($user_id, 'shipping_phone', $user_phone);
            update_user_meta($user_id, 'shipping_state', $user_state);

        } else {
            $_SESSION['recurly_errors'] = "User not found";
        }

    }


    function run_update_billing_shipping_once() {
        // Check if the user is logged in
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $user = wp_get_current_user();
    
            // Check if the user role is 'subscriber'
            if (in_array('subscriber', (array) $user->roles)) {
    
                // Check if the function has already been run
                $has_run = get_user_meta($user_id, 'billing_shipping_info_updated', true);
    
                if (!$has_run) {
                    // Run the function only once
                   $this->update_user_billing_shipping_info();
    
                    // Mark that the function has been executed
                    update_user_meta($user_id, 'billing_shipping_info_updated', true);
                }
            }
        }
    }
    

}

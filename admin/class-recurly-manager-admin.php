<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://ahmedraza.dev
 * @since      1.0.0
 *
 * @package    Recurly_Manager
 * @subpackage Recurly_Manager/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Recurly_Manager
 * @subpackage Recurly_Manager/admin
 * @author     Ahmad Raza <raza.ataki@gmail.com>
 */
class Recurly_Manager_Admin
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

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version The version of this plugin.
     * @since    1.0.0
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        add_action('admin_menu', [$this, 'add_plugin_page']);
        add_action('admin_init', [$this, 'page_init']);
    }

    /**
     * Register the stylesheets for the admin area.
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

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/recurly-manager-admin.css', array(), $this->version, 'all');

    }

    /**
     * Register the JavaScript for the admin area.
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

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/recurly-manager-admin.js', array('jquery'), $this->version, false);

    }

    public function add_plugin_page()
    {
        add_menu_page(
            'Recurly Manager',
            'Recurly Manager',
            'manage_options',
            'recurly-manager',
            array($this, 'create_admin_page')
        );
    }

    public function create_admin_page()
    {

        ?>
        <div class="wrap">
            <?php
            if (!session_id()) {
                session_start();
            }
            if (isset($_SESSION['recurly_errors'])) {
                echo '<div class="recurly-message notice notice-warning is-dismissible">';
                foreach ($_SESSION['recurly_errors'] as $error) {
                    echo '<p>' . esc_html($error) . '</p>';
                }
                echo '</div>';
                // Clear the error message after displaying it
                unset($_SESSION['recurly_errors']);
            }
            if (isset($_SESSION['recurly_success'])) {
                echo '<div class="recurly-message notice notice-success is-dismissible">';
                foreach ($_SESSION['recurly_success'] as $error) {
                    echo '<p>' . esc_html($error) . '</p>';
                }
                echo '</div>';
                // Clear the error message after displaying it
                unset($_SESSION['recurly_success']);
            }
            ?>
            <h1>Recurly Manager</h1>
            <form id="recurly-manager-form" method="post" action="options.php">
                <?php
                settings_fields('recurly_manager_option_group');
                do_settings_sections('recurly-manager');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function page_init()
    {
        register_setting(
            'recurly_manager_option_group',
            'recurly_manager_option',
            array($this, 'sanitize')
        );

        add_settings_section(
            'recurly_manager_section_1',
            'Recurly Manager Settings',
            array($this, 'print_section_info'),
            'recurly-manager'
        );
        add_settings_field(
            'recurly_manager_api_key',
            'Recurly Private API',
            array($this, 'api_setting_field_callback'),
            'recurly-manager',
            'recurly_manager_section_1'
        );
	    add_settings_field(
		    'recurly_manager_public_api_key',
		    'Recurly Public API',
		    array($this, 'recurly_manager_public_api_key_callback'),
		    'recurly-manager',
		    'recurly_manager_section_1'
	    );

        add_settings_section(
            'recurly_manager_section_2',
            'Recurly Manager Fetch Users',
            array($this, 'print_fetch_users_section_info'),
            'recurly-manager'
        );

        add_settings_field(
            'recurly_manager_fetch_users',
            'Fetch Users',
            array($this, 'fetch_user_field_callback'),
            'recurly-manager',
            'recurly_manager_section_2'
        );

        add_settings_field(
            'recurly_manager_fetch_users_with_active_plan',
            'Fetch Users With Active Plan',
            array($this, 'fetch_user_with_active_plan_callback'),
            'recurly-manager',
            'recurly_manager_section_2'
        );

        add_settings_field(
            'recurly_manager_fetch_users_with_email',
            'Fetch Users With Email',
            array($this, 'fetch_user_with_email_callback'),
            'recurly-manager',
            'recurly_manager_section_2'
        );
        

        add_settings_section(
            'recurly_manager_section_3',
            'Thank You Page',
            array($this, 'print_recurly_purchase_form_section_info'),
            'recurly-manager'
        );
        add_settings_field(
            'recurly_manager_purchase_form',
            'Redirect URL <small style="color: #888888">(just page url)<small>',
            array($this, 'purchase_form_callback'),
            'recurly-manager',
            'recurly_manager_section_3'
        );

        add_settings_section(
            'recurly_manager_section_4',
            'Template Page',
            array($this, 'print_logged_in_user_info'),
            'recurly-manager'
        );
        add_settings_field(
            'recurly_manager_logged_in_users',
            'Redirect URL <small style="color: #888888">(just page url)<small>',
            array($this, 'logged_in_user_callback'),
            'recurly-manager',
            'recurly_manager_section_4'
        );

        add_settings_section(
            'recurly_manager_section_6',
            'Recurly JS',
            array($this, 'print_recurly_js_info'),
            'recurly-manager'
        );
        add_settings_field(
            'recurly_manager_js',
            'Page ID <small style="color: #888888">(e.g. 4360)<small>',
            array($this, 'recurly_js_callback'),
            'recurly-manager',
            'recurly_manager_section_6'
        );

        add_settings_section(
            'recurly_manager_section_8',
            'Manage Credits',
            array($this, 'recurly_manager_credits_info'),
            'recurly-manager'
        );

        add_settings_field(
            'recurly_manager_basic_membership_credits',
            'Basic Membership',
            array($this, 'recurly_manager_basic_membership_credits_callback'),
            'recurly-manager',
            'recurly_manager_section_8'
        );

        add_settings_field(
            'recurly_manager_annual_membership_credits',
            'Lengea Premium Annual Membership',
            array($this, 'recurly_manager_annual_membership_credits_callback'),
            'recurly-manager',
            'recurly_manager_section_8'
        );

        add_settings_field(
            'recurly_manager_growth_membership_credits',
            'Lengea Growth Accelerator Membership',
            array($this, 'recurly_manager_growth_membership_credits_callback'),
            'recurly-manager',
            'recurly_manager_section_8'
        );

        add_settings_section(
            'recurly_manager_section_7',
            'Recurly Manager Shortcodes',
            array($this, 'recurly_manager_shortcode_info'),
            'recurly-manager'
        );

        add_settings_field(
            'recurly_manager_shortcode',
            '',
            array($this, 'recurly_manager_shortcode_callback'),
            'recurly-manager',
            'recurly_manager_section_7'
        );

    }

    public function sanitize($input)
    {
        $new_input = array();
        if (isset($input['recurly_manager_api_key'])) {
            $new_input['recurly_manager_api_key'] = sanitize_text_field($input['recurly_manager_api_key']);
        }
	    if (isset($input['recurly_manager_public_api_key'])) {
		    $new_input['recurly_manager_public_api_key'] = sanitize_text_field($input['recurly_manager_public_api_key']);
	    }
        if (isset($input['recurly_manager_fetch_users'])) {
            $new_input['recurly_manager_fetch_users'] = sanitize_text_field($input['recurly_manager_fetch_users']);
        }
        if (isset($input['recurly_manager_purchase_form'])) {
            $new_input['recurly_manager_purchase_form'] = sanitize_text_field($input['recurly_manager_purchase_form']);
        }
        if (isset($input['recurly_manager_logged_in_users'])) {
            $new_input['recurly_manager_logged_in_users'] = sanitize_text_field($input['recurly_manager_logged_in_users']);
        }
        if (isset($input['recurly_manager_upgrade_subscription'])) {
            $new_input['recurly_manager_upgrade_subscription'] = sanitize_text_field($input['recurly_manager_upgrade_subscription']);
        }
        if (isset($input['recurly_manager_js'])) {
            $new_input['recurly_manager_js'] = sanitize_text_field($input['recurly_manager_js']);
        }

        if (isset($input['recurly_manager_basic_membership_credits'])) {
            $new_input['recurly_manager_basic_membership_credits'] = sanitize_text_field($input['recurly_manager_basic_membership_credits']);
        }

        if (isset($input['recurly_manager_annual_membership_credits'])) {
            $new_input['recurly_manager_annual_membership_credits'] = sanitize_text_field($input['recurly_manager_annual_membership_credits']);
        }

        if (isset($input['recurly_manager_growth_membership_credits'])) {
            $new_input['recurly_manager_growth_membership_credits'] = sanitize_text_field($input['recurly_manager_growth_membership_credits']);
        }

        return $new_input;
    }

    public function print_section_info()
    {
        echo 'Enter your api below:';
    }

    public function api_setting_field_callback()
    {
        $options = get_option('recurly_manager_option');
        printf(
            '<input type="text" id="recurly_manager_api_key" class="widefat" name="recurly_manager_option[recurly_manager_api_key]" value="%s" />',
            isset($options['recurly_manager_api_key']) ? esc_attr($options['recurly_manager_api_key']) : ''
        );
    }

	public function recurly_manager_public_api_key_callback()
	{
		$options = get_option('recurly_manager_option');
		printf(
			'<input type="text" id="recurly_manager_public_api_key" class="widefat" name="recurly_manager_option[recurly_manager_public_api_key]" value="%s" />',
			isset($options['recurly_manager_public_api_key']) ? esc_attr($options['recurly_manager_public_api_key']) : ''
		);
	}

    public function print_fetch_users_section_info()
    {
        echo 'Fetch all users from Recurly:';
    }

    public function fetch_user_field_callback()
    {
        $options = get_option('recurly_manager_option');
        printf(
            '<input disabled type="text" id="recurly_manager_fetch_users" name="recurly_manager_option[recurly_manager_fetch_users]" value="users" />',
            isset($options['recurly_manager_fetch_users']) ? esc_attr($options['recurly_manager_fetch_users']) : ''
        );
        wp_nonce_field('recurly-fetch-users-action', 'recurly-fetch-users-nonce');
        echo '<input type="submit" name="recurly-fetch-users" class="recurly-fetch-user-btn button button-primary" value="Fetch"/>';
        echo '<br/><small>This fetch button will fetch all users from recurly, not checking if it has an active membership.</small>';
    }

    public function fetch_user_with_active_plan_callback()
    {
        $options = get_option('recurly_manager_option');
        printf(
            '<input disabled type="text" id="recurly_manager_fetch_users_with_active_plan" name="recurly_manager_option[recurly_manager_fetch_users_with_active_plan]" value="users" />',
            isset($options['recurly_manager_fetch_users_with_active_plan']) ? esc_attr($options['recurly_manager_fetch_users_with_active_plan']) : ''
        );
        wp_nonce_field('recurly-fetch-active-plan-users-action', 'recurly-fetch-active-plan-users-nonce');
        echo '<input type="submit" name="recurly-fetch-users-with-active-plan" class="recurly-fetch-user-btn button button-primary" value="Fetch"/>';
        echo '<br/><small>This fetch button will fetch all users that have active subscription plan.</small>';
    }

    public function  fetch_user_with_email_callback(){

        $options = get_option('recurly_manager_option');
        printf(
            '<input type="email" id="recurly_manager_fetch_users_with_email" name="recurly_manager_option[recurly_manager_fetch_users_with_email]" value="%s" placeholder="example@website.com" />',
            isset($options['recurly_manager_fetch_users_with_email']) ? esc_attr($options['recurly_manager_fetch_users_with_email']) : ''
        );
        wp_nonce_field('recurly-fetch-users-with-email-action', 'recurly-fetch-users-with-email-nonce');
        echo '<input type="submit" name="recurly-fetch-users-with-email" class="recurly-fetch-user-btn button button-primary" value="Fetch"/>';
    }

    public function print_recurly_purchase_form_section_info()
    {
        echo 'Redirect user to thank you page:';
    }

    public function purchase_form_callback()
    {
        $options = get_option('recurly_manager_option');
        printf(
            '<input type="text" id="recurly_manager_purchase_form" placeholder="/your-page/" class="widefat" name="recurly_manager_option[recurly_manager_purchase_form]" value="%s" />',
            isset($options['recurly_manager_purchase_form']) ? esc_attr($options['recurly_manager_purchase_form']) : ''
        );
    }

    public function print_logged_in_user_info()
    {
        echo 'Redirect user to templates page:';
    }

    public function logged_in_user_callback()
    {
        $options = get_option('recurly_manager_option');
        printf(
            '<input type="text" id="recurly_manager_logged_in_users" placeholder="/your-page/" class="widefat" name="recurly_manager_option[recurly_manager_logged_in_users]" value="%s" />',
            isset($options['recurly_manager_logged_in_users']) ? esc_attr($options['recurly_manager_logged_in_users']) : ''
        );
    }


    public function print_recurly_js_info()
    {
        echo "Load recurly js on purchase form page:";
    }

    public function recurly_js_callback()
    {
        $options = get_option('recurly_manager_option');
        printf(
            '<input type="text" id="recurly_manager_js" placeholder="4360" class="widefat" name="recurly_manager_option[recurly_manager_js]" value="%s" />',
            isset($options['recurly_manager_js']) ? esc_attr($options['recurly_manager_js']) : ''
        );
    }

    public function recurly_manager_shortcode_info()
    {
        echo '';
    }

    public function recurly_manager_shortcode_callback()
    {
        echo 'Create user and purchase subscription form: <code>[recurly_user_creation_form button_text="Register Now"]</code><br/><br/>';
        echo 'Upgrade subscription form: <code>[recurly_upgrade_subscription_form button_text_upgrade="Upgrade" button_clr_upgrade="black"]</code><br/><br/>';
        echo 'Renew subscription Button: <code>[recurly_renew_subscription_form button_text_renew="Renew" button_clr_renew="green"]</code><br/><br/>';
        echo 'Cancel subscription Button: <code>[recurly_cancel_subscription_form button_text_cancel="Cancel" button_clr_cancel="red"]</code>';
    }


    public function recurly_manager_credits_info()
    {
        echo "Manager user download credits based on Subscription Plan:";
    }

    public function recurly_manager_basic_membership_credits_callback()
    {
        $options = get_option('recurly_manager_option');
        printf(
            '<input type="number" id="recurly_manager_basic_membership_credits" class="widefat" name="recurly_manager_option[recurly_manager_basic_membership_credits]" value="%s" />',
            isset($options['recurly_manager_basic_membership_credits']) ? esc_attr($options['recurly_manager_basic_membership_credits']) : ''
        );
    }
    public function recurly_manager_growth_membership_credits_callback()
    {
        $options = get_option('recurly_manager_option');
        printf(
            '<input type="number" id="recurly_manager_growth_membership_credits" class="widefat" name="recurly_manager_option[recurly_manager_growth_membership_credits]" value="%s" />',
            isset($options['recurly_manager_growth_membership_credits']) ? esc_attr($options['recurly_manager_growth_membership_credits']) : ''
        );
    }

     public function recurly_manager_annual_membership_credits_callback(){
        $options = get_option('recurly_manager_option');
        printf(
            '<input type="number" id="recurly_manager_annual_membership_credits" class="widefat" name="recurly_manager_option[recurly_manager_annual_membership_credits]" value="%s" />',
            isset($options['recurly_manager_annual_membership_credits']) ? esc_attr($options['recurly_manager_annual_membership_credits']) : ''
        );
    }

}

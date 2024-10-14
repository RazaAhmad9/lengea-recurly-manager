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
 * @package    Recurly_Manager_Create_User
 * @subpackage Recurly_Manager_Create_User/includes
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
 * @package    Recurly_Manager_Create_User
 * @subpackage Recurly_Manager_Create_User/includes
 * @author     Ahmad Raza <raza.ataki@gmail.com>
 */
class Recurly_Manager_Create_User
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Recurly_Manager_Create_User $loader Maintains and registers all hooks for the plugin.
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
//        add_action('init', [$this, 'handle_recurly_create_user_form_submission']);

    }


    public function handle_recurly_create_user_form_submission()
    {

        

        if (isset($_POST['action']) && $_POST['action'] === 'recurly_create_user_account') {

            $this->create_user_in_wordpress();
            $this->recurly_create_user_account();
            $this->create_new_purchase();
            exit;
        }

    }

    public function create_user_in_wordpress()
    {
        if (!session_id()) {
            session_start();
        }
        $options = get_option('recurly_manager_option');
        $current_url = home_url(add_query_arg(array(), $_SERVER['REQUEST_URI']));


        if (isset($_POST['email']) && isset($_POST['firstname']) && isset($_POST['lastname'])) {
            $lower_first_name = strtolower($_POST['firstname']);
            $lower_last_name = strtolower($_POST['lastname']);
            $username = sanitize_user($lower_first_name . $lower_last_name);
            $email = sanitize_email($_POST['email']);
            $company = sanitize_text_field($_POST['company']);
            $country = sanitize_text_field($_POST['country']);
            $address1 = sanitize_text_field($_POST['address1']);
            $address2 = sanitize_text_field($_POST['address2']);
            $city = sanitize_text_field($_POST['city']);
            $state = sanitize_text_field($_POST['state']);
            $postal_code = sanitize_text_field($_POST['zip']);
            $plan_code = sanitize_text_field($_POST['subscription']);
            $recurly_phone = sanitize_text_field($_POST['phone']);
            $password = wp_generate_password(12, true, false);

            // Check if the username and email are already taken
            if (username_exists($username) || email_exists($email)) {
                $_SESSION['recurly_errors'] = 'Username or Email already exists';
            }

            // Prepare user data array
            $userdata = array(
                'user_login' => $username,
                'user_email' => $email,
                'user_pass' => $password,
                'first_name' => sanitize_text_field($_POST['firstname']),
                'last_name' => sanitize_text_field($_POST['lastname']),
                'role' => 'subscriber'
            );

            $user_id = wp_insert_user($userdata);

            if (is_wp_error($user_id)) {

                unset($_SESSION['recurly_success']);
                $_SESSION['recurly_errors'] = $user_id->get_error_message();
                wp_redirect($current_url);
                die();

            } else {

                unset($_SESSION['recurly_errors']);
                $basic_membership_credits                       = $options['recurly_manager_basic_membership_credits'];
                $feewaived_membership_credits                   = $options['recurly_manager_lengeabasicmembership_feewaived_membership_credits'];
                $premium_membership_credits                     = $options['recurly_manager_premium_membership_credits'];
                $growth_membership_credits                      = $options['recurly_manager_growth_membership_credits'];
                $monthly_membership_credits                     = $options['recurly_manager_monthly_membership_credits'];
                $premium_annual_membership_credits              = $options['recurly_manager_premium_membership_credits'];
                $premium_annual_fee_membership_credits          = $options['recurly_manager_premium_annual_fee_waived_membership_credits'];
                $premium_monthly_membership_credits             = $options['recurly_manager_premium_monthly_payments_membership_credits'];
                $templates_membership_credits                   = $options['recurly_manager_templates_membership_credits'];
                $premium_split_into_four_membership_credits     = $options['recurly_manager_premium_split_into_four_payments_membership_credits'];
                $premium_split_into_multiple_membership_credits = $options['recurly_manager_premium_split_into_multiple_payments_membership_credits'];
                $premium_split_into_payments_membership_credits = $options['recurly_manager_premium_split_into_payments_membership_credits'];
                $restart_for_mineive_membership_credits         = $options['recurly_manager_restart_for_mineive_cantave_membership_credits'];
        
                switch ($plan_code) {
                    case 'basic':
                        update_user_meta($user_id, 'fwc_total_credit_amount', $basic_membership_credits);
                        break;
                    case 'lengeabasicmembership-feewaived':
                        update_user_meta($user_id, 'fwc_total_credit_amount', $feewaived_membership_credits);
                        break;
                    case 'growth':
                        update_user_meta($user_id, 'fwc_total_credit_amount', $growth_membership_credits);
                        break;
                    case 'monthly':
                        update_user_meta($user_id, 'fwc_total_credit_amount', $monthly_membership_credits);
                        break;
                    case 'annual':
                        update_user_meta($user_id, 'fwc_total_credit_amount', $premium_annual_membership_credits);
                        break;
                    case 'premium-fee-waived':
                        update_user_meta($user_id, 'fwc_total_credit_amount', $premium_annual_fee_membership_credits);
                        break;
                    case 'premium-membership-monthly':
                        update_user_meta($user_id, 'fwc_total_credit_amount', $premium_monthly_membership_credits);
                        break;
                    case 'templates-membership':
                        update_user_meta($user_id, 'fwc_total_credit_amount', $templates_membership_credits);
                        break;
                    case 'premium-split4':
                        update_user_meta($user_id, 'fwc_total_credit_amount', $premium_split_into_four_membership_credits);
                        break;  
                    case 'premium-membership':
                        update_user_meta($user_id, 'fwc_total_credit_amount', $premium_membership_credits);
                        break;  
                    case 'premium-split22-copy':
                        update_user_meta($user_id, 'fwc_total_credit_amount', $premium_split_into_multiple_membership_credits);
                        break;
                    case 'premium-split22':
                        update_user_meta($user_id, 'fwc_total_credit_amount', $premium_split_into_payments_membership_credits);
                        break;  
                    case 'premium-split22cantave':
                        update_user_meta($user_id, 'fwc_total_credit_amount', $restart_for_mineive_membership_credits);
                        break;
                    default:
                        update_user_meta($user_id, 'fwc_total_credit_amount', 15); // Or any default value you prefer
                        break;
                }

                update_field('billing_shipping_info_updated', false, 'user_' . $user_id);
                wp_send_new_user_notifications($user_id);
                // wp_redirect(home_url($options['recurly_manager_purchase_form']));
            }

        } else {
            $_SESSION['recurly_errors'] = 'All fields are required';
            die();
        }
    }

    public function recurly_create_user_account()
    {
        if (!session_id()) {
            session_start();
        }
        $options = get_option('recurly_manager_option');
        $client = new \Recurly\Client($options['recurly_manager_api_key']);
        $current_url = home_url(add_query_arg(array(), $_SERVER['REQUEST_URI']));

        $first_name = sanitize_text_field($_POST['firstname']);
        $lastname = sanitize_text_field($_POST['lastname']);
        $email = sanitize_text_field($_POST['email']);
        $phone = sanitize_text_field($_POST['phone']);
        $address1 = sanitize_text_field($_POST['address1']);
        $address2 = sanitize_text_field($_POST['address2']);
        $city = sanitize_text_field($_POST['city']);
        $state = sanitize_text_field($_POST['state']);
        $postal_code = sanitize_text_field($_POST['zip']);
        $country = sanitize_text_field($_POST['country']);
        $company = sanitize_text_field($_POST['company']);
        $lower_first_name = strtolower($first_name);
        $lower_last_name = strtolower($lastname);
        $token_id = $_POST['recurly-token'];
        $username = $lower_first_name . $lower_last_name;

        $account_create = [
            "code" => $email,
            "username" => $username,
            "first_name" => $first_name,
            "last_name" => $lastname,
            "email" => $email,
            "company" => $company,
            "vat_number" => "",
            "tax_exempt" => true,
            "preferred_locale" => "",
            "preferred_time_zone" => "",
            "billing_info" => [
                'token_id' => $token_id,
            ],
            "address" => [
                'phone' => $phone,
                "street1" => $address1,
                "street2" => $address2,
                "city" => $city,
                "region" => $state,
                "postal_code" => $postal_code,
                "country" => $country,
            ],
            "shipping_addresses" => [
                [
                    "first_name" => $first_name,
                    "last_name" => $lastname,
                    "company" => $company,
                    "email" => $email,
                    'phone' => $phone,
                    "vat_number" => "",
                    "street1" => $address1,
                    "street2" => $address2,
                    "city" => $city,
                    "region" => $state,
                    "postal_code" => $postal_code,
                    "country" => $country,
                    "nickname" => $username,
                ]

            ]
        ];


        try {
            $client->createAccount($account_create);
            unset($_SESSION['recurly_errors']);
            $_SESSION['recurly_success'] = "User has been created";
            // wp_redirect(home_url($options['recurly_manager_purchase_form']));

        } catch (\Recurly\Errors\Validation $e) {
            unset($_SESSION['recurly_success']);
            $_SESSION['recurly_errors'] = $e->getMessage();
            wp_redirect($current_url);
            die();
        }

    }


    public function create_new_purchase()
    {
        if (!session_id()) {
            session_start();
        }

        // $this->create_user_in_wordpress();
        // $this->recurly_create_user_account();

        $options = get_option('recurly_manager_option');
        $client = new \Recurly\Client($options['recurly_manager_api_key']);
        $current_url = home_url(add_query_arg(array(), $_SERVER['REQUEST_URI']));

        $token_id = $_POST['recurly-token'];

        $account_code = sanitize_text_field($_POST['email']);
        $user_email = sanitize_text_field($_POST['email']);

        $first_name = sanitize_text_field($_POST['firstname']);
        $lastname = sanitize_text_field($_POST['lastname']);
        $rjs_token_id = $token_id;
        $plan_code = sanitize_text_field($_POST['subscription']);
        $currency = sanitize_text_field($_POST['currency']);
        $company = sanitize_text_field($_POST['company']);
        $country = sanitize_text_field($_POST['country']);
        $address1 = sanitize_text_field($_POST['address1']);
        $address2 = sanitize_text_field($_POST['address2']);
        $city = sanitize_text_field($_POST['city']);
        $state = sanitize_text_field($_POST['state']);
        $postal_code = sanitize_text_field($_POST['zip']);
        $phone_number = sanitize_text_field($_POST['phone']);
        $coupon = sanitize_text_field($_POST['coupon']);


        try {
            $purchase_create = [
                "currency" => $currency,
                "account" => [
                    "code" => $account_code,
                    "first_name" => $first_name,
                    "last_name" => $lastname,
                    "billing_info" => [
                        "token_id" => $rjs_token_id
                    ],
                ],
                "subscriptions" => [
                    [
                        "plan_code" => $plan_code
                    ]
                ]
            ];

            if (!empty($coupon)) {
                $purchase_create["coupon_codes"] = [$coupon];
            }

            $invoice_collection = $client->createPurchase($purchase_create);

            $lineItems = $invoice_collection->getChargeInvoice()->getLineItems();

            $lineItem = (null !== $lineItems[0]->getEndDate()) ? $lineItems[0]->getEndDate() : (
                (null !== $lineItems[1]->getEndDate()) ? $lineItems[1]->getEndDate() : null
            );
            
            $sub_end_date = $lineItem;
            $end_date = new DateTime($sub_end_date);
            $subscription_end_date = $end_date->format('d-M-Y');

            $this->update_user_acf_fields($user_email, $plan_code, $subscription_end_date, $phone_number, $company, $country, $address1, $address2, $city, $state, $postal_code);
           
            $_SESSION['recurly_success'] = 'You have successfully purchased your plan';
            wp_redirect(home_url($options['recurly_manager_purchase_form']));

        } catch (\Recurly\Errors\Validation $e) {
            // If the request was not valid, you may want to tell your user
            // why. You can find the invalid params and reasons in err.params
            unset($_SESSION['recurly_success']);
            $_SESSION['recurly_errors'] = $e->getMessage();
            wp_redirect($current_url);
            die();

        } catch (\Recurly\RecurlyError $e) {
            // If we don't know what to do with the err, we should
            // probably re-raise and let our web framework and logger handle it
            unset($_SESSION['recurly_success']);
            $_SESSION['recurly_errors'] = $e->getMessage();
            wp_redirect($current_url);
            die();

        }
    }

    // Function to update ACF fields
    public function update_user_acf_fields($user_email, $new_subscription_type, $subscription_end_date, $ll_phone_number, $ll_company_name, $ll_country, $ll_street_address, $ll_address_2, $ll_city, $ll_state, $ll_zip_postal_code)
    {
        // Get user by email
        $user = get_user_by('email', $user_email);

        // Check if user exists
        if ($user) {
            // User ID
            $user_id = $user->ID;

            // Update the ACF field for the user
            update_field('subscription_type', $new_subscription_type, 'user_' . $user_id);
            update_field('recurly_subscription_expiry_date', $subscription_end_date, 'user_' . $user_id);
            update_field('ll_phone_number', $ll_phone_number, 'user_' . $user_id);
            update_field('ll_company_name', $ll_company_name, 'user_' . $user_id);
            update_field('ll_country', $ll_country, 'user_' . $user_id);
            update_field('ll_street_address', $ll_street_address, 'user_' . $user_id);
            update_field('ll_address_2', $ll_address_2, 'user_' . $user_id);
            update_field('ll_city', $ll_city, 'user_' . $user_id);
            update_field('ll_state', $ll_state, 'user_' . $user_id);
            update_field('ll_zip_postal_code', $ll_zip_postal_code, 'user_' . $user_id);
        } else {
            $_SESSION['recurly_errors'] = "User not found";
        }
    }  

    public function recurly_get_current_user_credits(){
       
        if (is_user_logged_in()) {
            // Get the current logged-in user's ID
            $user_id = get_current_user_id();
    
            // Get the user's roles
            $user_info = get_userdata($user_id);
            $user_roles = $user_info->roles;
    
            // Check if the user has the 'subscriber' role
            if (in_array('subscriber', $user_roles)) {
                // Get the 'fwc_total_credit_amount' meta value
                $fwc_total_credit_amount = get_user_meta($user_id, 'fwc_total_credit_amount', true);
    
                // Return the credit amount if found, otherwise return false
                return !empty($fwc_total_credit_amount) ? $fwc_total_credit_amount : 0;
            }
        }
    
        // Return false if no user is logged in or if the user is not a subscriber
        return 0;
        
 
        
    }

}

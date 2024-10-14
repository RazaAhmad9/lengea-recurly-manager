<?php

class Recurly_Manager_Create_Subscription_Plans
{
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Recurly_Manager_Create_Subscription_Plans $loader Maintains and registers all hooks for the plugin.
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

        add_action('init', [$this, 'handle_recurly_subscriptions']);

    }

    public function handle_recurly_subscriptions()
    {

        if (isset($_POST['action']) && $_POST['action'] === 'recurly_cancel_subscription') {
            $this->recurly_cancel_subscription_plan();
            exit;
        }

        if (isset($_POST['action']) && $_POST['action'] === 'recurly_renew_subscription') {
            $this->recurly_renew_subscription_plan();
        }

        if (isset($_POST['action']) && $_POST['action'] === 'recurly_upgrade_user_subscription') {
            $this->recurly_upgrade_subscription_plan();
        }

    }


    /*
     * List Subscription Plan
     * */
    public function recurly_list_subscription_plans()
    {
        if (is_user_logged_in()) {
            $options = get_option('recurly_manager_option');
            $client = new \Recurly\Client($options['recurly_manager_api_key']);

            $user = wp_get_current_user();

            // Check if the user's role is 'Subscriber'
            if (in_array('subscriber', (array)$user->roles)) {

                // Get the value of the ACF field 'subscription_type'
                $subscription_type = get_field('subscription_type', 'user_' . $user->ID);

                // Check if 'subscription_type' is not empty
                if (!empty($subscription_type)) {

                    // Get the user's email
                    $user_email = $user->user_email;

                    // Do something with the email
                    $account_code = "code-" . $user_email;
                    $options = [
                        'params' => [
                            'limit' => 200,
                        ]
                    ];
                    $account_subscriptions = $client->listAccountSubscriptions($account_code, $options);

                    foreach ($account_subscriptions as $sub) {
                        $subscription_name = $sub->getPlan()->getName();
                        $subscription_state = $sub->getState();
                        $email = $sub->getAccount()->getEmail();
                        $activated_at = $sub->getActivatedAt();
                        $get_current_period_ends_at = $sub->getCurrentPeriodEndsAt();

                        $activated_date = $this->recurly_convert_timestamp_to_datetime($activated_at);
                        $ends_date = $this->recurly_convert_timestamp_to_datetime($get_current_period_ends_at);

                        $_SESSION['recurly_success'] = [
                            'subscription_name' => $subscription_name,
                            'subscription_state' => $subscription_state,
                            'email' => $email,
                            'activated_date' => $activated_date,
                            'ends_date' => $ends_date
                        ];
                    }
                    wp_redirect(home_url($options['recurly_manager_purchase_form']));
                }
            }
        }

    }

    /*
     * Cancel Subscription Plan
     * */
    public function recurly_cancel_subscription_plan()
    {

        if (is_user_logged_in()) {
            $options = get_option('recurly_manager_option');
            $client = new \Recurly\Client($options['recurly_manager_api_key']);

            $user = wp_get_current_user();
            $current_page = $_SERVER['HTTP_REFERER'];


            // Check if the user's role is 'Subscriber'
            if (in_array('subscriber', (array)$user->roles)) {

                // Get the value of the ACF field 'subscription_type'
                $subscription_type = get_field('subscription_type', 'user_' . $user->ID);

                // Check if 'subscription_type' is not empty
                if (!empty($subscription_type)) {

                    // Get the user's email
                    $user_email = $user->user_email;
                    $subscription_id = 'uuid-' . $this->recurly_get_subscription_info('uuid');
                    $subscription_state = $this->recurly_get_subscription_info('state');

                    try {
                        $subscription = $client->cancelSubscription($subscription_id);
                        if ($subscription_state !== 'active') {
                            $_SESSION['recurly_success'] = 'Your Subscription is already Canceled on this email: ' . $user_email;
                        } else {
                            $_SESSION['recurly_success'] = 'Canceled Subscription on this email: ' . $user_email;
                        }
                        wp_redirect($current_page);
                    } catch (\Recurly\Errors\Validation $e) {
                        // If the request was not valid, you may want to tell your user
                        // why. You can find the invalid params and reasons in err.params
                        unset($_SESSION['recurly_success']);
                        $_SESSION['recurly_errors'] = $e->getMessage();
                        wp_redirect($current_page);
                    } catch (\Recurly\RecurlyError $e) {
                        // If we don't know what to do with the err, we should
                        // probably re-raise and let our web framework and logger handle it
                        unset($_SESSION['recurly_success']);
                        $_SESSION['recurly_errors'] = $e->getMessage();
                        wp_redirect($current_page);
                    }

                }
            } elseif (in_array('administrator', (array)$user->roles)) {
                unset($_SESSION['recurly_success']);
                $_SESSION['recurly_errors'] = "You don't have subscribe any plan!";
                wp_redirect($current_page);
            } else {
                unset($_SESSION['recurly_success']);
                $_SESSION['recurly_errors'] = "You don't have permission to cancel subscription plan!";
                wp_redirect($current_page);
            }
        }
    }

    /*
     * Reactive Subscription Plan
     * */
    public function recurly_renew_subscription_plan()
    {
        $is_logged_in_and_subscriber = $this->is_user_logged_in_and_subscriber();
        $current_page = $_SERVER['HTTP_REFERER'];
        if ($is_logged_in_and_subscriber) {
            $options = get_option('recurly_manager_option');
            $client = new \Recurly\Client($options['recurly_manager_api_key']);
            $subscription_id = 'uuid-' . $this->recurly_get_subscription_info('uuid');
            $subscription_state = $this->recurly_get_subscription_info('state');

            try {
                $subscription = $client->reactivateSubscription($subscription_id);
                if ($subscription_state !== 'active') {
                    $_SESSION['recurly_success'] = 'Your Subscription has been reactivated!';
                } else {
                    $_SESSION['recurly_success'] = 'Your Subscription is already activate!';
                }
                wp_redirect($current_page);
            } catch (\Recurly\Errors\Validation $e) {
                // If the request was not valid, you may want to tell your user
                // why. You can find the invalid params and reasons in err.params
                unset($_SESSION['recurly_success']);
                $_SESSION['recurly_errors'] = $e->getMessage();
                wp_redirect($current_page);
            } catch (\Recurly\RecurlyError $e) {
                // If we don't know what to do with the err, we should
                // probably re-raise and let our web framework and logger handle it
                unset($_SESSION['recurly_success']);
                $_SESSION['recurly_errors'] = $e->getMessage();
                wp_redirect($current_page);
            }

        } else {
            unset($_SESSION['recurly_success']);
            $_SESSION['recurly_errors'] = "You don't have permission to reactivate plan!";
            wp_redirect($current_page);
        }
    }

    /*
     * Update Subscription Plan
     * */
    public function recurly_upgrade_subscription_plan()
    {

        if (!session_id()) {
            session_start();
        }


        $is_logged_in_and_subscriber = $this->is_user_logged_in_and_subscriber();
        $user_id = get_current_user_id();
        $current_page = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        if ($is_logged_in_and_subscriber) {
            $new_plan_code = sanitize_text_field($_POST['subscription']);
            $options = get_option('recurly_manager_option');
            $client = new \Recurly\Client($options['recurly_manager_api_key']);

            $subscription_id = 'uuid-' . $this->recurly_get_subscription_info('uuid');

            try {
                $changes = [
                    "plan_code" => $new_plan_code,
                    "timeframe" => "now"
                ];

                $subscription = $client->createSubscriptionChange($subscription_id, $changes);

                update_field('subscription_type', $new_plan_code, 'user_' . $user_id);
                update_field('recurly_subscription_expiry_date', explode('T', $this->recurly_get_subscription_info('current_term_ends_at'))[0], 'user_' . $user_id);

                $basic_membership_credits                       = $options['recurly_manager_basic_membership_credits'];
                $feewaived_membership_credits                   = $options['recurly_manager_lengeabasicmembership_feewaived_membership_credits'];
                $premium_membership_credits                     = $options['recurly_manager_premium_membership_credits'];
                $growth_membership_credits                      = $options['recurly_manager_growth_membership_credits'];
                $monthly_membership_credits                     = $options['recurly_manager_monthly_membership_credits'];
                $premium_annual_membership_credits              = $options['recurly_manager_annual_membership_credits'];
                $premium_annual_fee_membership_credits          = $options['recurly_manager_premium_annual_fee_waived_membership_credits'];
                $premium_monthly_membership_credits             = $options['recurly_manager_premium_monthly_payments_membership_credits'];
                $templates_membership_credits                   = $options['recurly_manager_templates_membership_credits'];
                $premium_split_into_four_membership_credits     = $options['recurly_manager_premium_split_into_four_payments_membership_credits'];
                $premium_split_into_multiple_membership_credits = $options['recurly_manager_premium_split_into_multiple_payments_membership_credits'];
                $premium_split_into_payments_membership_credits = $options['recurly_manager_premium_split_into_payments_membership_credits'];
                $restart_for_mineive_membership_credits         = $options['recurly_manager_restart_for_mineive_cantave_membership_credits'];
        
                switch ($new_plan_code) {
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


                unset($_SESSION['recurly_errors']);
                $_SESSION['recurly_success'] = "Your subscription has been upgraded!";
                wp_redirect($current_page);
            } catch (\Recurly\Errors\Validation $e) {
                // If the request was not valid, you may want to tell your user
                // why. You can find the invalid params and reasons in err.params
                unset($_SESSION['recurly_success']);
                $_SESSION['recurly_errors'] = $e->getMessage();
                wp_redirect($current_page);
            } catch (\Recurly\RecurlyError $e) {
                // If we don't know what to do with the err, we should
                // probably re-raise and let our web framework and logger handle it
                unset($_SESSION['recurly_success']);
                $_SESSION['recurly_errors'] = $e->getMessage();
                wp_redirect($current_page);
            }

        } else {
            unset($_SESSION['recurly_success']);
            $_SESSION['recurly_errors'] = "You don't have permission to upgrade plan!";
            wp_redirect($current_page);
        }
    }

    public function recurly_remove_subscription_plan()
    {

        $options = get_option('recurly_manager_option');
        $client = new \Recurly\Client($options['recurly_manager_api_key']);
        $plan_id = 'vhlbjfis4tj3';
        try {
            $plan = $client->removePlan($plan_id);
            echo 'Removed Plan: ' . $plan_id . PHP_EOL;
        } catch (\Recurly\Errors\Validation $e) {
            // If the request was not valid, you may want to tell your user
            // why. You can find the invalid params and reasons in err.params
            var_dump($e);
        } catch (\Recurly\RecurlyError $e) {
            // If we don't know what to do with the err, we should
            // probably re-raise and let our web framework and logger handle it
            var_dump($e);
        }
    }

    /*
     * PHP function that takes a timestamp in the format 2024-08-28T13:02:35Z
     * and converts it into a human-readable date and time format
     * */
    public function recurly_convert_timestamp_to_datetime($timestamp)
    {
        // Create a DateTime object from the timestamp
        $date = new DateTime($timestamp);

        // Convert the DateTime object to the desired format
        $formatted_date = $date->format('F j, Y');

        return $formatted_date;
    }

    /*
     * Get subscription info
     * */
    public function recurly_get_subscription_info($info_type = 'uuid')
    {
        if (is_user_logged_in()) {
            $options = get_option('recurly_manager_option');
            $client = new \Recurly\Client($options['recurly_manager_api_key']);
            $user = wp_get_current_user();

            // Check if the user's role is 'Subscriber'
            if (in_array('subscriber', (array)$user->roles)) {
                // Sanitize the user's email
                $user_email = sanitize_email($user->user_email);

                // Create the account code
                $account_code = "code-" . $user_email;
                $options = [
                    'params' => [
                        'limit' => 200,
                    ]
                ];

                try {
                    // Get the list of account subscriptions
                    $account_subscriptions = $client->listAccountSubscriptions($account_code, $options);

                    $result = '';
                    foreach ($account_subscriptions as $sub) {
                        switch ($info_type) {
                            case 'current_term_ends_at':
                                $result = $sub->getCurrentTermEndsAt();
                                break;
                            case 'uuid':
                                $result = $sub->getUUid();
                                break;
                            case 'state':
                                $result = $sub->getState();
                                break;
                        }

                        // Break the loop if we've found the required info
                        if ($result) {
                            break;
                        }
                    }
                    return $result;
                } catch (\Exception $e) {
                    // Log error or handle the exception
                    error_log($e->getMessage());
                    return false;
                }
            }
        }

        return null;
    }


    /*
    * Check if user is logged in and subscriber
    * */
    public function is_user_logged_in_and_subscriber()
    {
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            if (in_array('subscriber', (array)$user->roles)) {
                return true;
            }
        }
        return false;
    }

}
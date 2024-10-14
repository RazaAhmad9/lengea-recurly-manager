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
 * @package    Recurly_Manager_Fetch_User
 * @subpackage Recurly_Manager_Fetch_User/includes
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
 * @package    Recurly_Manager_Fetch_User
 * @subpackage Recurly_Manager_Fetch_User/includes
 * @author     Ahmad Raza <raza.ataki@gmail.com>
 */
class Recurly_Manager_Fetch_User
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Recurly_Manager_Fetch_User $loader Maintains and registers all hooks for the plugin.
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

        add_action('admin_init', [$this, 'handle_fetch_users']);

    }

    public function handle_fetch_users()
    {

        if (isset($_POST['recurly-fetch-users'])) {
            // Check the nonce for security

            if (!isset($_POST['recurly-fetch-users-nonce']) || !wp_verify_nonce($_POST['recurly-fetch-users-nonce'], 'recurly-fetch-users-action')) {
                die('Security check failed');
            }
            $this->recurly_fetch_users();
        }

        if(isset($_POST['recurly-fetch-users-with-active-plan'])){
            if (!isset($_POST['recurly-fetch-active-plan-users-nonce']) || !wp_verify_nonce($_POST['recurly-fetch-active-plan-users-nonce'], 'recurly-fetch-active-plan-users-action')) {
                die('Security check failed');
            }
            $this->recurly_fetch_users_with_active_plan();
        }

        if(isset($_POST['recurly-fetch-users-with-email'])){
            if (!isset($_POST['recurly-fetch-users-with-email-nonce']) || !wp_verify_nonce($_POST['recurly-fetch-users-with-email-nonce'], 'recurly-fetch-users-with-email-action')) {
                die('Security check failed');
            }
            $this->recurly_fetch_single_user_with_email();
        }


    }


    public function recurly_fetch_users()
    {
        if (!session_id()) {
            session_start();
        }

        $options = get_option('recurly_manager_option');
        $client = new \Recurly\Client($options['recurly_manager_api_key']);

        $options = [
            'params' => [
                'limit' => 20
            ]
        ];

        $subscriptions = $client->listSubscriptions($options);

        foreach ($subscriptions as $sub) {
            $first_name = $sub->getAccount()->getFirstName();
            $last_name = $sub->getAccount()->getLastName();
            $email = $sub->getAccount()->getEmail();
            $sub_plan = $sub->getPlan()->getCode();
            $password = wp_generate_password();
            $username = strtolower($first_name) . strtolower($last_name);

                if(username_exists($username)){
                    $username =  $username . rand( 1, 100 );
                }

                $userdata = array(
                    'user_login' => $username,
                    'user_pass' => $password,
                    'user_email' => $email,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'role' => 'subscriber'  // Change role if needed
                );

                if (!email_exists($email)) {
                        $user_id = wp_insert_user($userdata);
                        if (!is_wp_error($user_id)) {
                            update_field('subscription_type', $sub_plan, 'user_' . $user_id);
                            $_SESSION['recurly_success'][] = 'User has been fetched of this email: ' . $email;

                        } else {
                            $_SESSION['recurly_errors'][] = 'User creation failed: ' . $user_id->get_error_message();
                        }
                } else {
                        $_SESSION['recurly_errors'][] = 'Email already exists: ' . $email;

                }
    }

    }

    public function recurly_fetch_users_with_active_plan()
    {
        if (!session_id()) {
            session_start();
        }

        $options = get_option('recurly_manager_option');
        $client = new \Recurly\Client($options['recurly_manager_api_key']);

        $options = [
            'params' => [
                'limit' => 20
            ]
        ];

        $subscriptions = $client->listSubscriptions($options);

        foreach ($subscriptions as $sub) {
            $first_name = $sub->getAccount()->getFirstName();
            $last_name = $sub->getAccount()->getLastName();
            $email = $sub->getAccount()->getEmail();
            $sub_plan = $sub->getPlan()->getCode();
            $password = wp_generate_password();
            $username = strtolower($first_name) . strtolower($last_name);
            $state = $sub->getState();

            if ($state === 'active') {

                if(username_exists($username)){
                    $username =  $username . rand( 1, 100 );
                }

                $userdata = array(
                    'user_login' => $username,
                    'user_pass' => $password,
                    'user_email' => $email,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'role' => 'subscriber'  // Change role if needed
                );

                if (!email_exists($email)) {
                        $user_id = wp_insert_user($userdata);
                        if (!is_wp_error($user_id)) {
                            update_field('subscription_type', $sub_plan, 'user_' . $user_id);
                            $_SESSION['recurly_success'][] = 'User has been fetched of this email: ' . $email;

                        } else {
                            $_SESSION['recurly_errors'][] = 'User creation failed: ' . $user_id->get_error_message();
                        }
                } else {
                        $_SESSION['recurly_errors'][] = 'Email already exists: ' . $email;

                }
        }
    }

    }

    public function recurly_fetch_single_user_with_email(){

        if (!session_id()) {
            session_start();
        }
        try {

            $options = get_option('recurly_manager_option');
            $email = isset($_POST['recurly_manager_option']['recurly_manager_fetch_users_with_email']) 
            ? sanitize_email($_POST['recurly_manager_option']['recurly_manager_fetch_users_with_email']) 
            : '';
            if (!empty($email)) {
                $options['recurly_manager_fetch_users_with_email'] = $email;
                update_option('recurly_manager_option', $options);
            } else {
                $_SESSION['recurly_errors'][] = 'Email cannot be empty.';
                return;
            }
            $client = new \Recurly\Client($options['recurly_manager_api_key']);
            $account_id = 'code-' . $options['recurly_manager_fetch_users_with_email'];
            $options = [
                'params' => [
                  'limit' => 20
                ]
              ];
            $account = $client->listAccountSubscriptions($account_id, $options);
           
            foreach ($account as $sub) {
                $first_name = $sub->getAccount()->getFirstName();
                $last_name = $sub->getAccount()->getLastName();
                $email = $sub->getAccount()->getEmail();
                $sub_plan = $sub->getPlan()->getCode();
                $password = wp_generate_password();
                $username = strtolower($first_name) . strtolower($last_name);
    
                    if(username_exists($username)){
                        $username =  $username . rand( 1, 100 );
                    }
    
                    $userdata = array(
                        'user_login' => $username,
                        'user_pass' => $password,
                        'user_email' => $email,
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'role' => 'subscriber'  // Change role if needed
                    );
    
                    if (!email_exists($email)) {
                            $user_id = wp_insert_user($userdata);
                            if (!is_wp_error($user_id)) {
                                update_field('subscription_type', $sub_plan, 'user_' . $user_id);
                                $_SESSION['recurly_success'][] = 'User has been fetched of this email: ' . $email;
    
                            } else {
                                $_SESSION['recurly_errors'][] = 'User creation failed: ' . $user_id->get_error_message();
                            }
                    } else {
                            $_SESSION['recurly_errors'][] = 'Email already exists: ' . $email;
    
                    }
        }
          
        } catch (\Recurly\Errors\NotFound $e) {
            // Could not find the resource, you may want to inform the user
            // or just return a NULL
            $_SESSION['recurly_errors'][] = 'Email cannot be empty.';
        } catch (\Recurly\RecurlyError $e) {
            // Something bad happened... tell the user so that they can fix it?
            $_SESSION['recurly_errors'][] = 'Some unexpected Recurly error happened. Try again later: ' . $e;
        }
    }

}

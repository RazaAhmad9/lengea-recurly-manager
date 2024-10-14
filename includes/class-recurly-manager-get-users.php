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
 * @package    Recurly_Manager_Get_Users
 * @subpackage Recurly_Manager_Get_Users/includes
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
 * @package    Recurly_Manager_Get_Users
 * @subpackage Recurly_Manager_Get_Users/includes
 * @author     Ahmad Raza <raza.ataki@gmail.com>
 */
class Recurly_Manager_Get_Users
{
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Recurly_Manager_Get_Users $loader Maintains and registers all hooks for the plugin.
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
        $this->handle_recurly_accounts();

    }

    /**
     * Handle new purchase
     */
    public function handle_recurly_accounts()
    {
//        add_action('wp_footer', [$this, 'fetch_all_accounts']);
//        add_action('wp_footer', [$this, 'fetch_account_by_id']);
    }

    public function fetch_all_accounts()
    {
        $options = get_option('recurly_manager_option');
        $client = new \Recurly\Client($options['recurly_manager_api_key']);

        $options = [
            'params' => [
                'limit' => 50
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


            $userdata = array(
                'user_login' => $username,
                'user_pass' => $password,
                'user_email' => $email,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'role' => 'subscriber'  // Change role if needed
            );


            if ( !username_exists( $username ) && !email_exists( $email ) ) {
                // Insert the user
                $user_id = wp_insert_user( $userdata );

                if ( !is_wp_error( $user_id ) ) {
                    echo 'User created: ' . $user_id;

                    // Update custom ACF field for the user
                    update_field('subscription_type', $sub_plan, 'user_' . $user_id);

                } else {
                    echo 'User creation failed: ' . $user_id->get_error_message();
                }
            } else {
                echo 'Username or email already exists for: ' . $email;
            }


        }
    }

    public function fetch_account_by_id()
    {
        $options = get_option('recurly_manager_option');
        $client = new \Recurly\Client($options['recurly_manager_api_key']);
        $account_id = 'vboim1qmxxop';
        try {
            $account = $client->getAccount($account_id);

            echo 'Got Account:' . PHP_EOL;
            echo "<pre>";
            var_export($account);
            echo "</pre>";
        } catch (\Recurly\Errors\NotFound $e) {
            // Could not find the resource, you may want to inform the user
            // or just return a NULL
            echo 'Could not find resource.' . PHP_EOL;
        } catch (\Recurly\RecurlyError $e) {
            // Something bad happened... tell the user so that they can fix it?
            echo 'Some unexpected Recurly error happened. Try again later.' . PHP_EOL;
        }
    }

}
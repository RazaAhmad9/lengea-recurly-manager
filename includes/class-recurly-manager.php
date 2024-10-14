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
 * @package    Recurly_Manager
 * @subpackage Recurly_Manager/includes
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
 * @package    Recurly_Manager
 * @subpackage Recurly_Manager/includes
 * @author     Ahmad Raza <raza.ataki@gmail.com>
 */
class Recurly_Manager
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Recurly_Manager_Loader $loader Maintains and registers all hooks for the plugin.
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

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
	    $this->recurly_manager_api();
	    $this->recurly_manager_create_user();
        $this->recurly_manager_create_user_form();
        $this->recurly_manager_get_users();
        $this->recurly_manager_fetch_users();
        $this->recurly_manager_fetch_subscriptions();
        $this->start_session();



    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Recurly_Manager_Loader. Orchestrates the hooks of the plugin.
     * - Recurly_Manager_i18n. Defines internationalization functionality.
     * - Recurly_Manager_Admin. Defines all hooks for the admin area.
     * - Recurly_Manager_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies()
    {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-recurly-manager-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-recurly-manager-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-recurly-manager-admin.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-recurly-manager-fetch-users.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-recurly-manager-public.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-recurly-manager-create-user.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-recurly-manager-forms.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-recurly-manager-get-users.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-recurly-manager-manage-subscription-plans.php';


	    require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-recurly-manager-api.php';

        $this->loader = new Recurly_Manager_Loader();

    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Recurly_Manager_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale()
    {

        $plugin_i18n = new Recurly_Manager_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');

    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks()
    {

        $plugin_admin = new Recurly_Manager_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks()
    {

        $plugin_public = new Recurly_Manager_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

    }

	/**
	 *
	 */
	public function recurly_manager_api() {

		new Recurly_Manager_Api();
	}
    /**
     * Run this class to create users
     */
    private function recurly_manager_create_user()
    {
        new Recurly_Manager_Create_User();
    }

    /**
     * Run this class to create user form
     */
    private function recurly_manager_create_user_form()
    {
        new Recurly_Manager_User_Form();
    }

    /**
     * Run this class to get users
     */
    private function recurly_manager_get_users()
    {
        new Recurly_Manager_Get_Users();
    }

    /**
     * Run this class to fetch users
     */
    private function recurly_manager_fetch_users()
    {
        new Recurly_Manager_Fetch_User();
    }

    /*
     * Run this class to fetch subscriptions
     * */
    private function recurly_manager_fetch_subscriptions()
    {
        new Recurly_Manager_Create_Subscription_Plans();
    }


    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @return    string    The name of the plugin.
     * @since     1.0.0
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @return    Recurly_Manager_Loader    Orchestrates the hooks of the plugin.
     * @since     1.0.0
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @return    string    The version number of the plugin.
     * @since     1.0.0
     */
    public function get_version()
    {
        return $this->version;
    }

    /* Don't remove it! */
    public function fetch_data_from_recurly_api()
    {

        $curl = curl_init();
        $url = "https://raza.recurly.com/v2/accounts";
        curl_setopt($curl, CURLOPT_URL, $url);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $apiKey = '42b527991a854f249f878518d78f90e5';
        $encodedApiKey = base64_encode($apiKey);

        $headers = array(
            'Accept: application/xml',
            'X-Api-Version: 2.29',
            'Content-Type: application/xml; charset=utf-8',
            'Authorization: Basic ' . $encodedApiKey
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);


        if (curl_errno($curl)) {
            echo 'cURL error: ' . curl_error($curl);
        } else {
            // Print the response
            var_export($response);
        }

        curl_close($curl);


    }

    // Add this function to start the session
    public function start_session()
    {
        if (!session_id()) {
            session_start();
        }
    }

}

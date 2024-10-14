<?php


if ( class_exists( 'Recurly_Manager_Api' ) ) {
	return;
}

class Recurly_Manager_Api {

	private $params;    // Store the request parameters
	private $client;
	private $options;

	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register REST API routes
	 */
	public function register_routes() {
		register_rest_route(
			'recurly-manager/v1',
			'/process-subscription',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'process_subscription' ),
				'permission_callback' => '__return_true',
				'args'                => $this->get_endpoint_args(),
			)
		);
	}

	/**
	 * Define arguments for the endpoint
	 */
	private function get_endpoint_args() {
		return array(
			'firstname'     => array(
				'required'          => true,  // Required
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => array( $this, 'validate_name' ),
			),
			'lastname'      => array(
				'required'          => true,  // Required
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => array( $this, 'validate_name' ),
			),
			'email'         => array(
				'required'          => true,  // Required
				'sanitize_callback' => 'sanitize_email',
				'validate_callback' => 'is_email',
			),
			'phone'         => array(
				'required'          => false,  // Not required
				'sanitize_callback' => 'sanitize_text_field',
			),
			'subscription'  => array(
				'required'          => true,  // Required
				'sanitize_callback' => 'sanitize_text_field',
			),
			'currency'      => array(
				'required'          => true,  // Required
				'sanitize_callback' => 'sanitize_text_field',
			),
			'address1'      => array(
				'required'          => false,  // Not required
				'sanitize_callback' => 'sanitize_text_field',
			),
			'address2'      => array(
				'required'          => false,  // Not required
				'sanitize_callback' => 'sanitize_text_field',
			),
			'city'          => array(
				'required'          => false,  // Not required
				'sanitize_callback' => 'sanitize_text_field',
			),
			'country'       => array(
				'required'          => false,  // Not required
				'sanitize_callback' => 'sanitize_text_field',
			),
			'state'         => array(
				'required'          => false,  // Not required
				'sanitize_callback' => 'sanitize_text_field',
			),
			'zip'           => array(
				'required'          => false,  // Not required
				'sanitize_callback' => 'sanitize_text_field',
			),
			'company'       => array(
				'required'          => false,  // Not required
				'sanitize_callback' => 'sanitize_text_field',
			),
			'coupon'        => array(
				'required'          => false,  // Not required
				'sanitize_callback' => 'sanitize_text_field',
			),
			'recurly-token' => array(
				// TODO: Make this required.
				'required'          => false,  // Required, as this is critical for Recurly API
				'sanitize_callback' => 'sanitize_text_field',
			),
		);
	}


	/**
	 * Process subscription endpoint callback
	 */
	public function process_subscription( WP_REST_Request $request ) {

	
		// Store the request and parameters in class properties
		$this->params = $request->get_params();

		// Now, we can break down the process step by step, starting with checking if the user exists
		$email = $this->params['email'];


		// Check if the user already exists
		if ( email_exists( $email ) ) {
			$error = new WP_Error( 'user_exists', __( 'A user with this email already exists in the site, please login to purchase/update subscriptions', 'recurly-manager' ), array( 'status' => 400 ) );

			$this->send_error_response( $error );
			wp_die();
		}


		// Check if we already have the recurly User
		$recurly_user = $this->recurly_get_user();

		

		// Create Recurly User (Only if the current user does not exists
		if ( empty( $recurly_user ) ) {
			// Create Recurly User
			$recurly_user = $this->recurly_create_user();

			

			if ( is_wp_error( $recurly_user ) ) {
				$this->send_error_response( $recurly_user );
				wp_die();
			}
		}

		if ( empty( $recurly_user ) ) {
			
			$error = new WP_Error(
				'user_not_created',
				'Failed to create user in Recurly.',
				array( 'status' => 400 )
			);
			$this->send_error_response( $error );
			wp_die();
		}


		// Create Recurly Subscription
		$invoice_collection = $this->recurly_create_new_purchase();

		
		if ( is_wp_error( $invoice_collection ) ) {

			$this->send_error_response( $invoice_collection );
			wp_die();
		}

		$subscription_expiry_date = $this->get_expiry_date_from_recurly_purchase( $invoice_collection );

	
		// Create User in WordPress
		$user_id = $this->create_user_with_params_and_recurly_info( $subscription_expiry_date );

	


		if ( is_wp_error( $user_id ) ) {
			$this->send_error_response( $user_id );
			wp_die();
		}

		// Send user notification
		wp_send_new_user_notifications( $user_id );

		// All Good, return WP API response with success message


		wp_send_json_success( array(
			'success'     => true,
			'message'     => 'You have successfully purchased your membership subscription. Please check your email for login details.',
			'user_id'     => $user_id,
			'expiry_date' => $subscription_expiry_date,
			'redirect_to' => home_url( $this->get_options( 'recurly_manager_purchase_form' ) )
		) );

		wp_die();

	}

	/**
	 * $error WP_Error
	 */
	public function send_error_response( $error ) {

		wp_send_json_error( array(
			'success'    => false,
			'message'    => $error->get_error_message(), // Show the error message returned by Recurly
			'error_code' => $error->get_error_code(),
			'status'     => $error->get_error_data()['status']
		), $error->get_error_data()['status'] ); // HTTP status code 500

	}

	/**
	 *
	 */
	public function recurly_get_user() {

		$client = $this->get_client();

		// If get_client() returned a WP_Error, pass the error response back
		if ( is_wp_error( $client ) ) {
			return $client; // This will return the API error from get_client()
		}

		// Get the email from the request parameters
		$email      = $this->params['email'];
		$account_id = 'code-' . $email;

		try {
			$account = $client->getAccount( $account_id );

		} catch ( \Recurly\Errors\NotFound $e ) {
			return null;

		} catch ( \Recurly\RecurlyError $e ) {
			return null;
		}


		return $account;

	}

	/**
	 *
	 */
	public function get_client() {

		// Check if the client is already initialized
		if ( ! $this->client ) {

			// Get the API key from options
			$recurly_manager_api_key = $this->get_options( 'recurly_manager_api_key' );

			// If the API key is missing, return a WP_Error with a custom message
			if ( ! $recurly_manager_api_key ) {
				return new WP_Error( 'missing_api_key', __( 'Recurly API key is missing in Plugin Configuration.', 'recurly-manager' ), array( 'status' => 500 ) );
			}


			// Initialize the Recurly client
			try {
				$client = new \Recurly\Client( $recurly_manager_api_key );

				// Test the client by making a simple request (e.g., fetching a known account or subscription)


				// If you don't have a known account, this is just for the test. If it fails, an error is thrown.
				$client->listAccounts();

				// If the client is valid, assign it to the class property
				$this->client = $client;


			} catch ( \Recurly\Errors\ClientError $e ) {
				// Handle client-related errors (invalid API key, unauthorized, etc.)
				return new WP_Error( 'client_error', __( 'Recurly Client Error: ' . $e->getMessage(), 'recurly-manager' ), array( 'status' => 403 ) );
			} catch ( \Exception $e ) {
				// Handle other general exceptions during Recurly client initialization
				return new WP_Error( 'client_initialization_failed', __( 'Failed to initialize Recurly client: ' . $e->getMessage(), 'recurly-manager' ), array( 'status' => 500 ) );
			}
		}

		// Return the client if it's successfully initialized
		return $this->client;
	}

	/**
	 *
	 */
	public function get_options( $option_key = null ) {

		if ( ! $this->options ) {
			$options       = get_option( 'recurly_manager_option' );
			$this->options = $options;
		}

		if ( ! empty( $option_key ) ) {
			return $this->options[ $option_key ] ?? null;
		}

		return $this->options;

	}

	public function recurly_create_user() {

		$client = $this->get_client();

		// If get_client() returned a WP_Error, pass the error response back
		if ( is_wp_error( $client ) ) {
			return $client; // This will return the API error from get_client()
		}

		try {
			$account_create = $this->get_user_data_from_param();
			$account        = $client->createAccount( $account_create );


		} catch ( \Recurly\Errors\Validation $e ) {
			return new WP_Error(
				'recurly_validation',
				'Failed to Validate User Data: ' . $e->getMessage(),
				array( 'status' => 500 )
			);
		} catch ( \Recurly\RecurlyError $e ) {
			return new WP_Error(
				'recurly_error',
				'Failed to initialize Recurly client: ' . $e->getMessage(),
				array( 'status' => 500 )
			);
		}

		return $account;

	}

	/**
	 *
	 */
	public function get_user_data_from_param() {
		$lower_first_name = strtolower( $this->params['firstname'] );
		$lower_last_name  = strtolower( $this->params['lastname'] );
		$username         = $lower_first_name . $lower_last_name;


		$user_data = [
			"code"                => $this->params['email'],
			"username"            => $username,
			"first_name"          => $this->params['firstname'],
			"last_name"           => $this->params['lastname'],
			"email"               => $this->params['email'],
			"company"             => $this->params['company'],
			"vat_number"          => "",
			"tax_exempt"          => true,
			"preferred_locale"    => "",
			"preferred_time_zone" => "",
			"billing_info"        => $this->get_user_billing_info_from_params(),
			"address"             => $this->get_user_address_from_params(),
			"shipping_addresses"  => array_merge( [
				"first_name" => $this->params['firstname'],
				"last_name"  => $this->params['lastname'],
				"email"      => $this->params['email'],
				"company"    => $this->params['company'],
				"vat_number" => "",
				"nickname"   => "Work",
			], $this->get_user_address_from_params() )
		];

		return $user_data;

	}

	/**
	 *
	 */
	public function get_user_billing_info_from_params() {
		$token_id     = $_POST['recurly-token'] ?? '';
		$billing_info = [];

		if ( ! empty( $token_id ) ) {
			$billing_info['token_id'] = $token_id;


		} else {
			$billing_info = [
				"first_name" => $this->params['firstname'],
				"last_name"  => $this->params['lastname'],
				"company"    => $this->params['company'],
				"number"     => $this->params['number'],
				"address"    => $this->get_user_address_from_params(),
			];
		}

		return $billing_info;


	}

	/**
	 *
	 */
	public function get_user_address_from_params() {

		return [
			'phone'       => $this->params['phone'],
			"street1"     => $this->params['address1'],
			"street2"     => $this->params['address2'],
			"city"        => $this->params['city'],
			"region"      => $this->params['state'],
			"postal_code" => $this->params['zip'],
			"country"     => $this->params['country'],
		];

	}

	/**
	 *
	 */
	public function recurly_create_new_purchase() {

		$client = $this->get_client();

		// If get_client() returned a WP_Error, pass the error response back
		if ( is_wp_error( $client ) ) {
			return $client; // This will return the API error from get_client()
		}

		// Get the email from the request parameters
		$email      = $this->params['email'];
		$currency   = $this->params['currency'];
		$first_name = $this->params['firstname'];
		$lastname   = $this->params['lastname'];
		$plan_code  = $this->params['subscription'];

		$coupon = $this->params['coupon'];


		try {

			$purchase_create = [
				"currency"      => $currency,
				"account"       => [
					"code"         => $email,
					"first_name"   => $first_name,
					"last_name"    => $lastname,
					"billing_info" => $this->get_user_billing_info_from_params(),
				],
				"subscriptions" => [
					[
						"plan_code" => $plan_code
					]
				]
			];

			if ( ! empty( $coupon ) ) {
				$purchase_create["coupon_codes"] = [ $coupon ];
			}
			$invoice_collection = $client->createPurchase( $purchase_create );

		} catch ( \Recurly\Errors\Validation $e ) {
			return new WP_Error( 'client_error_purchase', 'Recurly Client Purchase Error: ' . $e->getMessage(), array( 'status' => 500 ) );
		} catch ( \Recurly\RecurlyError $e ) {
			return new WP_Error( 'client_error_purchase', 'Recurly Client Purchase Error: ' . $e->getMessage(), array( 'status' => 500 ) );
		}

		return $invoice_collection;

	}

	/**
	 *
	 */
	public function get_expiry_date_from_recurly_purchase( $invoice_collection ) {

		$subscription_end_date = null;

		try {

			$lineItems = $invoice_collection->getChargeInvoice()->getLineItems();
			// Create Line Items
			$plan_code = $this->params['subscription'];

			/**
			 * @var \Recurly\Resources\LineItem $item
			 */
			foreach ( $lineItems as $item ) {


				//Check if the line item is of the same subscription plan
				if ( $item->getPlanCode() == $plan_code ) {
					$subscription_line_item = $item;
					if (
						$subscription_line_item
						&& is_object( $subscription_line_item )
						&& method_exists( $subscription_line_item, 'getEndDate' )
						&& ( null !== $item->getEndDate() )
					) {
						$end_date              = $item->getEndDate();
						$end_date              = new DateTime( $end_date );
						$subscription_end_date = $end_date->format( 'Y-m-d' );
					}
					break;
				}

			}

		} catch ( \Recurly\RecurlyError $exception ) {
			$subscription_end_date = $this->get_default_expiry_date();
		} catch ( \Exception $exception ) {
			$subscription_end_date = $this->get_default_expiry_date();
		}

		return $subscription_end_date;

	}

	/**
	 *
	 */
	public function get_default_expiry_date() {

		// TODO: Create an option to have default days till expiry
		$days_till_expiry = 365;

		$expiry_date = date( 'Y-m-d', strtotime( '+' . $days_till_expiry . ' days' ) );

		return $expiry_date;

	}

	public function create_user_with_params_and_recurly_info( $subscription_expiry_date ) {


		// Create User

		$user_id = $this->create_user_in_wordpress();

		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}

		// Update Membership Credits
		$credits = $this->get_subscription_credits_from_options( $this->params['subscription'] );

		// TODO: check the correct field id: 'fwc_credit_amnt' vs 'fwc_total_credit_amount'
		update_user_meta( $user_id, 'fwc_total_credit_amount', $credits );

		// Update user data

		$user_data = array(
			'subscription_type'                => $this->params['subscription'],
			'recurly_subscription_expiry_date' => $subscription_expiry_date,
			'll_phone_number'                  => $this->params['phone'],
			'll_company_name'                  => $this->params['company'],
			'll_country'                       => $this->params['country'],
			'll_street_address'                => $this->params['address1'],
			'll_address_2'                     => $this->params['address2'],
			'll_city'                          => $this->params['city'],
			'll_state'                         => $this->params['state'],
			'll_zip_postal_code'               => $this->params['zip'],
			'billing_shipping_info_updated'    => false, // Ask Raza why this flag is here.
		);

		foreach ( $user_data as $key => $value ) {
			update_field( $key, $value, 'user_' . $user_id );
		}

		return $user_id;

	}

	public function create_user_in_wordpress() {
		$user_id = null;
		$email   = sanitize_email( $this->params['email'] );

		// Return the Existing user
		if ( email_exists( $email ) ) {
			$user = get_user_by( 'email', $email );

			// return user id
			return $user->ID;
		}


		// Sanitize inputs
		$firstname = sanitize_text_field( $this->params['firstname'] );
		$lastname  = sanitize_text_field( $this->params['lastname'] );
		$password  = wp_generate_password();


		$username = sanitize_user( strtolower( $firstname . $lastname ) );

		if ( username_exists( $username ) ) {
			$username = $username . rand( 1, 100 );
		}

		// Prepare user data
		$userdata = array(
			'user_login' => $username,
			'user_email' => $email,
			'user_pass'  => $password,
			'first_name' => $firstname,
			'last_name'  => $lastname,
			'role'       => 'subscriber'
		);

		// Insert the new user
		$maybe_user_id = wp_insert_user( $userdata );


		if ( is_wp_error( $maybe_user_id ) ) {

			return $maybe_user_id;
		} else {
			$user_id = $maybe_user_id;
		}


		return $user_id;

	}

	/**
	 *
	 */
	public function get_subscription_credits_from_options( $plan_code ) {

		$options = $this->get_options();

		$option_id = 'recurly_manager_' . $plan_code . '_membership_credits';

		if ( isset( $options[ $option_id ] ) ) {
			return $options[ $option_id ];
		} else {
			return 0;
		}

	}

	/**
	 * Custom name validation to accommodate characters from various languages.
	 */
	public function validate_name( $param, $request, $key ) {
		// Allow letters from all languages, spaces, hyphens, and apostrophes.
		if ( ! preg_match( '/^[\p{L}\s\'\-]+$/u', $param ) ) {
			return new WP_Error( 'invalid_name', __( 'The name contains invalid characters.', 'text-domain' ) );
		}

		return true;
	}

}

<?php
/**
 * Handle LifterLMS Form submissions.
 *
 * @package  LifterLMS/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Form_Handler class.
 *
 * @since [version]
 */
class LLMS_Form_Handler {

	/**
	 * Singleton instance
	 *
	 * @var null
	 */
	protected static $instance = null;

	/**
	 * Validation class instance
	 *
	 * @var LLMS_Form_Validator
	 */
	protected $validator = null;

	/**
	 * Get Main Singleton Instance.
	 *
	 * @since [version]
	 *
	 * @return LLMS_Form_Handler
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private Constructor.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	private function __construct() {

		$this->validator = new LLMS_Form_Validator();

		add_action( 'lifterlms_before_user_update', array( $this, 'maybe_modify_edit_account_field_settings' ), 10, 3 );
		add_action( 'lifterlms_before_user_update', array( $this, 'maybe_modify_required_address_fields' ), 10, 3 );
		add_action( 'lifterlms_before_user_registration', array( $this, 'maybe_modify_required_address_fields' ), 10, 3 );

	}

	/**
	 * Retrieve fields for a given form
	 *
	 * Ensures the form exists and that the current user can access the form.
	 *
	 * @since [version]
	 *
	 * @param string $action   User action to be performed. Either "update" (for an existing user) or "registration" for a new user.
	 * @param string $location Form location ID.
	 * @param array  $args     Additional arguments passed to the short-circuit filter.
	 * @return WP_Error|array[] Array of LLMS_Form_Field arrays on success or an error object on failure.
	 */
	protected function get_fields( $action, $location, $args = array() ) {

		$fields = LLMS_Forms::instance()->get_form_fields( $location, $args );

		// Form couldn't be located.
		if ( false === $fields ) {
			// Translators: %s = form location ID.
			return new WP_Error( 'llms-form-invalid-location', sprintf( __( 'The form location "%s" is invalid.', 'lifterlms' ), $location ), $args );

		} elseif ( 'account' === $location && 'update' !== $action ) {
			// No logged in user, can't update.
			return new WP_Error( 'llms-form-no-user', __( 'You must be logged in to perform this action.', 'lifterlms' ), $args );
		}

		return $fields;

	}

	/**
	 * Insert user data into the database.
	 *
	 * @since [version]
	 *
	 * @param string  $action      Type of insert action. Either "registration" for a new user or "update" for an existing one.
	 * @param array   $posted_data User-submitted form data.
	 * @param array[] $fields      List of LifterLMS Form fields for the form.
	 * @return WP_Error|int Error on failure or WP_User ID on success.
	 */
	protected function insert( $action, $posted_data, $fields ) {

		$func     = 'registration' === $action ? 'wp_insert_user' : 'wp_update_user';
		$prepared = $this->prepare_data_for_insert( $posted_data, $fields, $action );

		$user_id = $func( $prepared['users'] );
		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}

		foreach ( $prepared['usermeta'] as $key => $val ) {
			update_user_meta( $user_id, $key, $val );
		}

		return $user_id;

	}

	/**
	 * Modify LifterLMS Fields prior to performing submit handler validations.
	 *
	 * @since [version]
	 *
	 * @param array   $posted_data User submitted form data (passed by reference).
	 * @param string  $location    Form location ID.
	 * @param array[] $fields      Array of LifterLMS Form Fields (passed by reference).
	 * @return void
	 */
	public function maybe_modify_edit_account_field_settings( &$posted_data, $location, &$fields ) {

		if ( 'account' !== $location ) {
			return;
		}

		/**
		 * If email address and passwords aren't submitted we can mark them as "optional" fields.
		 *
		 * These fields are dynamically toggled and disabled if they're not modified.
		 */
		foreach ( array( 'email_address', 'password', 'password_current' ) as $field_id ) {

			// If the field exists and it's not included (or empty) in the posted data.
			$index = LLMS_Forms::instance()->get_field_by( $fields, 'id', $field_id, 'index' );
			if ( false !== $index && empty( $posted_data[ $fields[ $index ]['name'] ] ) ) {

				// Remove the field so we don't accidentally save an empty value later.
				unset( $posted_data[ $fields[ $index ]['name'] ] );

				// Mark the field as optional (for validation purposes).
				$fields[ $index ]['required'] = false;

				// Check if there's a confirm field and do the same.
				$con_index = LLMS_Forms::instance()->get_field_by( $fields, 'id', "{$field_id}_confirm", 'index' );
				if ( false !== $con_index && empty( $posted_data[ $fields[ $con_index ]['name'] ] ) ) {
					unset( $posted_data[ $fields[ $con_index ]['name'] ] );
					$fields[ $con_index ]['required'] = false;
				}
			}
		}

	}

	/**
	 * Modify LifterLMS Fields to allow some address fields to be conditionally required
	 *
	 * Uses available country locale information to remove the "required" attribute for state
	 * and zip code fields when a user has chosen a country that doesn't use states and/or
	 * zip codes.
	 *
	 * @since [version]
	 *
	 * @param array   $posted_data User submitted form data (passed by reference).
	 * @param string  $location    Form location ID.
	 * @param array[] $fields      Array of LifterLMS Form Fields (passed by reference).
	 * @return void
	 */
	public function maybe_modify_required_address_fields( &$posted_data, $location, &$fields ) {

		// Only proceed if we have a country to review.
		if ( empty( $posted_data['llms_billing_country'] ) ) {
			return;
		}

		$country = $posted_data['llms_billing_country'];
		$info    = llms_get_country_address_info( $country );

		// Fields to chek.
		$check = array(
			'llms_billing_city'  => 'city',
			'llms_billing_state' => 'state',
			'llms_billing_zip'   => 'postcode',
		);

		foreach ( $check as $post_key => $info_key ) {

			$index = LLMS_Forms::instance()->get_field_by( $fields, 'name', $post_key, 'index' );

			// Field exists, no data was posted, and the field is disabled (is `false`) in the address info array.
			if ( false !== $index && empty( $posted_data[ $post_key ] ) && ! $info[ $info_key ] ) {
				$fields[ $index ]['required'] = false;
			}
		}

	}

	/**
	 * Prepares user-submitted data for insertion into the database.
	 *
	 * @since [version]
	 *
	 * @param array   $posted_data Sanitized & validated user-submitted form data.
	 * @param array[] $fields LifterLMS form fields list.
	 * @param string  $action Insert action, either "registration" for new users or "update" for existing, logged-in users.
	 * @return array
	 */
	protected function prepare_data_for_insert( $posted_data, $fields, $action ) {

		$forms = LLMS_Forms::instance();

		$prepared = array();

		foreach ( $fields as $field ) {


			if ( empty( $field['data_store_key'] ) ) {
				continue;
			}

			// We need to account for fields that are part of the form but are not present in the `$posted_data`
			// e.g. unchecked check boxes.
			if ( isset( $posted_data[ $field['name'] ] ) || 'checkbox' === $field['type'] ) {

				if ( ! isset( $prepared[ $field['data_store'] ] ) ) {
					$prepared[ $field['data_store'] ] = array();
				}

				$prepared[ $field['data_store'] ][ $field['data_store_key'] ] = isset( $posted_data[ $field['name'] ] ) ? $posted_data[ $field['name'] ] : array();
			}

		}

		if ( 'registration' === $action ) {

			$defaults = array(
				'role'                 => 'student',
				'show_admin_bar_front' => false,
			);

			// Add a username if we don't have a user_login field.
			if ( empty( $prepared['users']['user_login'] ) ) {
				$defaults['user_login'] = LLMS_Person_Handler::generate_username( $posted_data['email_address'] );
			}

			// Add a password if we don't have a password field.
			if ( empty( $prepared['users']['user_pass'] ) ) {
				$defaults['user_pass'] = wp_generate_password( 32, true, true );
			}

			$prepared['users'] = wp_parse_args( $prepared['users'], $defaults );

		} elseif ( 'update' === $action ) {

			$prepared['users']['ID'] = get_current_user_id();

		}

		// Record an IP Address.
		$prepared['usermeta']['llms_ip_address'] = llms_get_ip_address();

		// If terms have been agreed to, record a time stamp for the agreement.
		if ( isset( $posted_data['llms_agress_to_terms'] ) ) {
			$prepared['usermeta']['llms_agress_to_terms'] = current_time( 'mysql' );
		}

		/**
		 * Filter data added to the wp_users data via `wp_insert_user()` or `wp_update_user()`.
		 *
		 * The dynamic portion of this hook, `$action`, can be either "registration" or "update".
		 *
		 * @since 3.0.0
		 * @since [version] Moved from `LLMS_Person_Handler::insert_data()`.
		 *
		 * @param array $user_data Array of user data.
		 * @param array $posted_data Array of user-submitted data.
		 * @param string $action Submission action, either "registration" or "update".
		 */
		$prepared['users'] = apply_filters( "lifterlms_user_${action}_insert_user", $prepared['users'], $posted_data, $action );

		/**
		 * Filter meta data to be added for the user.
		 *
		 * The dynamic portion of this hook, `$action`, can be either "registration" or "update".
		 *
		 * @since 3.0.0
		 * @since [version] Moved from `LLMS_Person_Handler::insert_data()`.
		 *
		 * @param array $user_meta Array of user meta data.
		 * @param array $posted_data Array of user-submitted data.
		 * @param string $action Submission action, either "registration" or "update".
		 */
		$prepared['usermeta'] = apply_filters( "lifterlms_user_${action}_insert_user_meta", $prepared['usermeta'], $posted_data, $action );

		return $prepared;

	}

	/**
	 * Form submission handler.
	 *
	 * @since [version]
	 *
	 * @param array  $posted_data User-submitted form data.
	 * @param string $location Form location ID.
	 * @param array  $args Additional arguments passed to the short-circuit filter.
	 * @return int|WP_Error WP_User ID on success, error object on failure.
	 */
	public function submit( $posted_data, $location, $args = array() ) {

		// Determine the user action to perform.
		$action = get_current_user_id() ? 'update' : 'registration';

		// Load the form.
		$fields = $this->get_fields( $action, $location, $args );
		if ( is_wp_error( $fields ) ) {
			return $this->submit_error( $fields, $posted_data, $action );
		}

		/**
		 * Run an action immediately prior to user registration or update.
		 *
		 * The dynamic portion of this hook, `$action`, can be either "registration" or "update".
		 *
		 * @since 3.0.0
		 * @since [version] Moved from `LLMS_Person_Handler::update()` & LLMS_Person_Handler::register().
		 *               Added parameters `$fields` and `$args`.
		 *               Triggered by `do_action_ref_array()` instead of `do_action()` allowing modification
		 *               of `$posted_data` and `$fields` via hooks.
		 *
		 * @param array $posted_data Array of user-submitted data (passed by reference).
		 * @param string $location Form location.
		 * @param array[] $fields Array of LifterLMS Form Fields (passed by reference).
		 * @param array $args Additional arguments from the form retrieval function.
		 */
		do_action_ref_array( "lifterlms_before_user_${action}", array( &$posted_data, $location, &$fields, $args ) );

		// Check for all required fields.
		$required = $this->validator->validate_required_fields( $posted_data, $fields );
		if ( is_wp_error( $required ) ) {
			return $this->submit_error( $required, $posted_data, $action );
		}

		// Sanitize.
		$posted_data = $this->validator->sanitize_fields( wp_unslash( $posted_data ), $fields );

		$valid = $this->validator->validate_fields( $posted_data, $fields );
		if ( is_wp_error( $valid ) ) {
			return $this->submit_error( $valid, $posted_data, $action );
		}

		// Validate matching fields.
		$matches = $this->validator->validate_matching_fields( $posted_data, $fields );
		if ( is_wp_error( $matches ) ) {
			return $this->submit_error( $matches, $posted_data, $action );
		}

		/**
		 * Filter the validity of the form submission.
		 *
		 * The dynamic portion of this hook, `$action`, can be either "registration" or "update".
		 *
		 * @since 3.0.0
		 * @since [version]
		 *
		 * @param WP_Error|true $valid Error object containing validation errors or true when the data is valid.
		 * @param array $posted_data Array of user-submitted data.
		 * @param string $location Form location.
		 * @param array $args Additional arguments passed to the form submission handler.
		 */
		$valid = apply_filters( "lifterlms_user_${action}_data", true, $posted_data, $location, $args );
		if ( is_wp_error( $valid ) ) {
			return $this->submit_error( $valid, $posted_data, $action );
		}

		/**
		 * Run an action immediately after user registration/update fields have been validated.
		 *
		 * The dynamic portion of this hook, `$action`, can be either "registration" or "update".
		 *
		 * @since 3.0.0
		 * @since [version] Moved from `LLMS_Person_Handler::update()` & LLMS_Person_Handler::register().
		 *               Added parameters `$fields` and `$args`.
		 *
		 * @param array $posted_data Array of user-submitted data.
		 * @param string $location Form location.
		 * @param array[] $fields Array of LifterLMS Form Fields
		 * @param array $args Additional arguments from the form retrieval function.
		 */
		do_action( "lifterlms_user_${action}_after_validation", $posted_data, $location, $fields, $args );

		$user_id = $this->insert( $action, $posted_data, $fields );
		if ( is_wp_error( $user_id ) ) {
			return $this->submit_error( $user_id, $posted_data, $action );
		}

		if ( 'registration' === $action ) {

			/**
			 * Deprecated user creation hook.
			 *
			 * @since Unknown.
			 * @deprecated [version]
			 *
			 * @param int $user_id WP_User ID of the newly created user.
			 * @param array $posted_data Array of user-submitted data.
			 * @param string $location Form location.
			 */
			do_action( 'lifterlms_created_person', $user_id, $posted_data, $location );

			/**
			 * Fire an action after a user has been registered.
			 *
			 * @since 3.0.0
			 * @since [version] Moved from `LLMS_Person_Handler::register()`.
			 *
			 * @param int $user_id WP_User ID of the user.
			 * @param array $posted_data Array of user submitted data.
			 * @param string $location Form location.
			 */
			do_action( 'lifterlms_user_registered', $user_id, $posted_data, $location );

		} elseif ( 'update' === $action ) {

			/**
			 * Fire an action after a user has been updated.
			 *
			 * @since 3.0.0
			 * @since [version] Moved from `LLMS_Person_Handler::update()`.
			 *
			 * @param int $user_id WP_User ID of the user.
			 * @param array $posted_data Array of user submitted data.
			 * @param string $location Form location.
			 */
			do_action( 'lifterlms_user_updated', $user_id, $posted_data, $location );

		}

		return $user_id;

	}

	/**
	 * Ensure all errors objects encountered during form submission are filterable.
	 *
	 * @since [version]
	 *
	 * @param WP_Error $error Error object.
	 * @param array    $posted_data User-submitted form data.
	 * @param string   $action Form action, either "registration" or "update".
	 * @return WP_Error
	 */
	protected function submit_error( $error, $posted_data, $action ) {

		/**
		 * Filter the error return when the insert/update fails.
		 *
		 * The dynamic portion of this hook, `$action`, can be either "registration" or "update".
		 *
		 * @since 3.0.0
		 * @since [version] Moved from `LLMS_Person_Handler::insert_data()`.
		 *
		 * @param WP_Error $error Error object.
		 * @param array $posted_data Array of user-submitted data.
		 * @param string $action Submission action, either "registration" or "update"!
		 */
		return apply_filters( "lifterlms_user_${action}_failure", $error, $posted_data, $action );

	}

}

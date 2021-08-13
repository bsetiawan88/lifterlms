<?php
/**
 * LLMS_Meta_Box_Order_Details class
 *
 * @package LifterLMS/Admin/PostTypes/MetaBoxes/Classes
 *
 * @since 3.0.0
 * @version 3.35.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Order Details meta box
 *
 * @since 3.0.0
 * @since 3.35.0 Verify nonces and sanitize `$_POST` data.
 */
class LLMS_Meta_Box_Order_Details extends LLMS_Admin_Metabox {

	/**
	 * Configure the metabox settings
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function configure() {

		$this->id       = 'lifterlms-order-details';
		$this->title    = __( 'Order Details', 'lifterlms' );
		$this->screens  = array(
			'llms_order',
		);
		$this->context  = 'normal';
		$this->priority = 'high';

	}

	/**
	 * Not used because our metabox doesn't use the standard fields api
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public function get_fields() {
		return array();
	}

	/**
	 * Output metabox content
	 *
	 * @since 1.0.0
	 * @since 3.0.0 Unknown.
	 *
	 * @return void
	 */
	public function output() {

		$order = llms_get_post( $this->post );
		if ( ! $order || ! is_a( $order, 'LLMS_Order' ) ) {
			return;
		}

		$gateway = $order->get_gateway();

		// Setup a list of gateways that this order can be switched to.
		$gateway_feature           = $order->is_recurring() ? 'recurring_payments' : 'single_payments';
		$switchable_gateways       = array();
		$switchable_gateway_fields = array();
		foreach ( LLMS()->payment_gateways()->get_supporting_gateways( $gateway_feature ) as $id => $gateway_obj ) {
			$switchable_gateways[ $id ]       = $gateway_obj->get_admin_title();
			$switchable_gateway_fields[ $id ] = $gateway_obj->get_admin_order_fields();
		}

		include LLMS_PLUGIN_DIR . 'includes/admin/views/metaboxes/view-order-details.php';

	}

	/**
	 * Save method
	 *
	 * @since 3.0.0
	 * @since 3.10.0 Unknown.
	 * @since 3.35.0 Verify nonces and sanitize `$_POST` data.
	 *
	 * @param int $post_id Post ID of the Order.
	 * @return void
	 */
	public function save( $post_id ) {

		if ( ! llms_verify_nonce( 'lifterlms_meta_nonce', 'lifterlms_save_data' ) ) {
			return;
		}

		$order = llms_get_post( $this->post );
		if ( ! $order || ! is_a( $order, 'LLMS_Order' ) ) {
			return;
		}

		$fields = array(
			'payment_gateway',
			'gateway_customer_id',
			'gateway_subscription_id',
			'gateway_source_id',
		);

		foreach ( $fields as $key ) {

			if ( isset( $_POST[ $key ] ) ) {
				$order->set( $key, llms_filter_input( INPUT_POST, $key, FILTER_SANITIZE_STRING ) );
			}
		}

	}

}

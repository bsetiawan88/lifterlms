<?php
/**
 * View an Order.
 *
 * @package LifterLMS/Templates
 *
 * @since 3.0.0
 * @since 3.33.0 Pass the current order object instance as param for all the actions and filters, plus redundant check on order existence removed.
 * @since 3.35.0 Access `$_GET` data via `llms_filter_input()`.
 * @since 5.4.0 Inform about deleted products.
 * @since [version] Load sub-templates using hooks and template functions.
 * @version [version]
 *
 * @param LLMS_Order $order        Current order object.
 * @param array      $transactions Result array from {@see LLMS_Order::get_transactions()}.
 * @param string     $layout_class The view's layout classname. Either `llms-stack-cols` or an empty string for the default side-by-side layout.
 */

defined( 'ABSPATH' ) || exit;

$classes = array_filter(
	array_map(
		'esc_attr',
		array( 'llms-sd-section', 'llms-view-order', $layout_class )
	)
);

llms_print_notices();
?>

<div class="<?php echo implode( ' ', $classes ); ?>">

	<h2 class="order-title">
		<?php printf( __( 'Order #%d', 'lifterlms' ), $order->get( 'id' ) ); ?>
		<span class="llms-status <?php echo esc_attr( $order->get( 'status' ) ); ?>"><?php echo $order->get_status_name(); ?></span>
	</h2>

	<?php
		/**
		 * Action run prior to the display of order information.
		 *
		 * @since Unknown
		 *
		 * @param LLMS_Order $order The order being displayed.
		 */
		do_action( 'lifterlms_before_view_order_table', $order );

		/**
		 * Displays information about the order.
		 *
		 * @hooked llms_template_view_order_information 10
		 *
		 * @since [version]
		 *
		 * @param LLMS_Order $order The order being displayed.
		 */
		do_action( 'llms_view_order_information', $order );

		/**
		 * Displays user actions for the order.
		 *
		 * @hooked llms_template_view_order_information 10
		 *
		 * @since [version]
		 *
		 * @param LLMS_Order $order The order being displayed.
		 */
		do_action( 'llms_view_order_actions', $order );
	?>

	<div class="clear"></div>

	<?php
		/**
		 * Displays order transactions.
		 *
		 * @since Unknown
		 *
		 * @param LLMS_Order $order        The order being displayed.
		 * @param array      $transactions Result array from {@see LLMS_Order::get_transactions()}.
		 */
		do_action( 'llms_view_order_transactions', $order, $transactions );

		/**
		 * Action run after the display of order information.
		 *
		 * @since Unknown
		 *
		 * @param LLMS_Order $order The order being displayed.
		 */
		do_action( 'lifterlms_after_view_order_table', $order );
	?>

</div>

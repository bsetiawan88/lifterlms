<?php
/**
 * LLMS_Abstract_User_Engagement class
 *
 * @package LifterLMS/Abstracts/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Base model class for awarded engagements (certificates and achievements).
 *
 * @since [version]
 */
abstract class LLMS_Abstract_User_Engagement extends LLMS_Post_Model {

	/**
	 * Called immediately after creating / inserting a new post into the database
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	protected function after_create() {

		$this->sync( 'create' );
	}

	/**
	 * Delete the engagement
	 *
	 * @since 3.18.0
	 * @since [version] Migrated from LLMS_User_Certificate and LLMS_User_Achievement.
	 *
	 * @return void
	 */
	public function delete() {

		/**
		 * Action fired immediately prior to the deletion of a user's awarded engagement.
		 *
		 * They dynamic portion of the hook name, {@see LLMS_Abstract_User_Engagement::$model_post_type `$this->model_post_type`},
		 * refers to the engagement type, either "achievement" or "certificate".
		 *
		 * @since 3.18.0
		 * @since [version] Migrated from LLMS_User_Certificate and LLMS_User_Achievement.
		 *
		 * @param LLMS_Abstract_User_Engagement $User_Engagement Achievement or certificate class object.
		 */
		do_action( "llms_before_delete_{$this->model_post_type}", $this );

		global $wpdb;
		$id = $this->get( 'id' );
		$wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			"{$wpdb->prefix}lifterlms_user_postmeta",
			array(
				'user_id'    => $this->get_user_id(),
				'meta_key'   => $this->get_user_post_meta_key(), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value' => $id, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			),
			array( '%d', '%s', '%d' )
		);
		wp_delete_post( $id, true );

		/**
		 * Action fired immediately after the deletion of a user's awarded engagement.
		 *
		 * They dynamic portion of the hook name, {@see LLMS_Abstract_User_Engagement::$model_post_type `$this->model_post_type`},
		 * refers to the engagement type, either "achievement" or "certificate".
		 *
		 * @since 3.18.0
		 * @since [version] Migrated from LLMS_User_Certificate and LLMS_User_Achievement.
		 *
		 * @param LLMS_Abstract_User_Engagement $User_Engagement Achievement or certificate class object.
		 */
		do_action( "llms_delete_{$this->model_post_type}", $this );
	}

	/**
	 * Retrieve the date the achievement was earned (created)
	 *
	 * @since 3.14.0
	 * @since [version] Migrated from LLMS_User_Certificate and LLMS_User_Achievement.
	 *
	 * @param string $format Date format string.
	 * @return string
	 */
	public function get_earned_date( $format = null ) {

		$format = $format ? $format : get_option( 'date_format' );

		return $this->get_date( 'date', $format );
	}

	/**
	 * Get the WP Post ID of the post which triggered the earning of the certificate
	 *
	 * This would be a lesson, course, section, track, etc...
	 *
	 * @since 3.8.0
	 * @since 4.5.0 Force return to an integer.
	 * @since [version] Migrated from LLMS_User_Certificate and LLMS_User_Achievement.
	 *
	 * @return int
	 */
	public function get_related_post_id() {

		$meta = $this->get_user_postmeta();

		return isset( $meta->post_id ) ? absint( $meta->post_id ) : $this->get( 'related' );
	}

	/**
	 * Retrieves the LLMS_Abstract_User_Engagement template instance for a given post.
	 *
	 * Based on {@see llms_get_certificate()}.
	 *
	 * @since [version]
	 *
	 * @param WP_Post|int|null $post A WP_Post object or a WP_Post ID. A falsy value will use the current
	 *                               global `$post` object (if one exists).
	 * @return LLMS_Abstract_User_Engagement|bool
	 */
	protected function get_user_engagement_template( $post ) {

		$post = get_post( $post );
		if ( ! $post || "llms_{$this->model_post_type}" !== $post->post_type ) {
			return false;
		}

		return new static( $post );
	}

	/**
	 * Retrieve the user ID of the user who earned the certificate
	 *
	 * @since 3.8.0
	 * @since 3.9.0 Unknown.
	 * @since 4.5.0 Force return to an integer.
	 * @since [version] Migrated from LLMS_User_Certificate and LLMS_User_Achievement.
	 *
	 * @return int
	 */
	public function get_user_id() {

		$meta = $this->get_user_postmeta();

		return isset( $meta->user_id ) ? absint( $meta->user_id ) : $this->get( 'author' );
	}

	/**
	 * Retrieve user postmeta data for the achievement or certificate.
	 *
	 * @since 3.8.0
	 * @since [version] Migrated from LLMS_User_Certificate and LLMS_User_Achievement.
	 *
	 * @return stdClass
	 */
	public function get_user_postmeta() {

		global $wpdb;

		return $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT user_id, post_id FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE meta_value = %d AND meta_key = %s",
				$this->get( 'id' ),
				$this->get_user_post_meta_key()
			)
		);
	}

	/**
	 * Retrieve the user postmeta key recorded when the engagement is earned.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function get_user_post_meta_key() {

		return sprintf( '_%s_earned', $this->model_post_type );
	}

	/**
	 * Determines if the achievement or certificate has been awarded.
	 *
	 * @since [version]
	 *
	 * @return boolean
	 */
	public function is_awarded() {

		if ( 'publish' !== $this->get( 'status' ) ) {
			return false;
		}

		return (bool) $this->get( 'awarded' );
	}

	/**
	 * Allow child classes to merge the post content based on content from the template.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	public function merge_content() {

		return $this->get( 'content', true );
	}

	/**
	 * Update the awarded engagement by regenerating it from its template.
	 *
	 * @since [version]
	 *
	 * @param string $context Sync context. Either "update" for an update to an existing awarded engagement
	 *                        or "create" when the awarded engagement is being created.
	 * @return boolean Returns false if the parent doesn't exist, otherwise returns true.
	 */
	public function sync( $context = 'update' ) {

		$template_id = $this->get( 'parent' );
		$template    = $this->get_user_engagement_template( $template_id );
		if ( ! $template ) {
			return false;
		}

		$this->set( 'title', get_post_meta( $template_id, "_llms_{$this->model_post_type}_title", true ) );
		if ( get_post_thumbnail_id( $template_id ) !== get_post_thumbnail_id( $this->get( 'post' ) ) &&
			! set_post_thumbnail( $this->get( 'post' ), get_post_thumbnail_id( $template_id ) )
		) {
			delete_post_thumbnail( $this->get( 'post' ) );
		}

		// Copy the content, with optional merge codes and short codes, and optional block editor layout meta properties
		// from the template to this awarded engagement.
		$this->set( 'content', $template->get( 'content', true ) );
		$this->set( 'content', $this->merge_content() );
		$this->sync_meta( $template );

		/**
		 * Action run after an awarded engagement is synchronized with its template.
		 *
		 * The dynamic portion of the hook name, {@see LLMS_Abstract_User_Engagement::model_post_type `$this->model_post_type`},
		 * refers to the engagement type, either "achievement" or "certificate".
		 *
		 * @since [version]
		 *
		 * @param LLMS_Abstract_User_Engagement $engagement Awarded engagement object.
		 * @param LLMS_Abstract_User_Engagement $template   Engagement template object.
		 * @param string                        $context    The context within which the synchronization is run.
		 *                                                  Either "create" or "update".
		 */
		do_action( "llms_{$this->model_post_type}_synchronized", $this, $template, $context );

		return true;
	}

	/**
	 * This is a stub that allows extending classes to sync additional data from the template during a sync operation.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Abstract_User_Engagement $template
	 * @return void
	 */
	protected function sync_meta( $template ) {
	}
}

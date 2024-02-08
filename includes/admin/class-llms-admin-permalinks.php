<?php
/**
 * LLMS_Admin_Header class file
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Permalink settings class
 */
class LLMS_Admin_Permalinks {

	/**
	 * Permalink settings.
	 *
	 * @var array
	 */
	private $permalinks = array();

	/**
	 * Constructor.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function __construct() {
		$this->settings_init();
		$this->settings_save();
	}

	/**
	 * Show the available permalink settings
	 */
	public function settings_init() {
		add_settings_section( 'lifterlms-permalink', __( 'LifterLMS Permalinks', 'lifterlms' ), array( $this, 'settings' ), 'permalink' );

		$this->permalinks = llms_get_permalink_structure();
	}

	public function settings() {
		// TODO: Conditionally show message for Course Catalog and Membership Catalog if they are enabled
		?>
		<p><?php _e( 'LifterLMS uses custom post types and taxonomies to organize your courses and memberships. You can customize the URLs for these items here.', 'lifterlms' ); ?></p>

		<?php
		$course_catalog_id = llms_get_page_id( 'courses' );
		if ( $course_catalog_id && get_post( $course_catalog_id ) ) {
			?>
			<p>
				<?php echo esc_html__( 'Note: The Courses Catalog is currently set to a static page.', 'lifterlms' ); ?>
				<a href="<?php echo esc_url( get_edit_post_link( $course_catalog_id ) ); ?>">
				<?php echo esc_html__( 'You can edit the page slug to change its location.', 'lifterlms' ); ?>
				</a>
			</p>
			<?php
		}

		$memberships_catalog_id = llms_get_page_id( 'memberships' );
		if ( $memberships_catalog_id && get_post( $memberships_catalog_id ) ) {
			?>
			<p>
				<?php echo esc_html__( 'Note: The Memberships Catalog is currently set to a static page.', 'lifterlms' ); ?>
				<a href="<?php echo esc_url( get_edit_post_link( $memberships_catalog_id ) ); ?>">
					<?php echo esc_html__( 'You can edit the page slug to change its location.', 'lifterlms' ); ?>
				</a>
			</p>
			<?php
		}
		?>

		<table class="form-table" role="presentation">
			<tbody>
			<tr>
				<th>
					<label for="course_base">
						<?php esc_html_e( 'Course Post Type' ); ?>
					</label>
				</th>
				<td>
					<input name="llms_course_base" id="course_base" type="text" value="<?php echo esc_attr( $this->permalinks['course_base'] ); ?>" class="regular-text code" required>
				</td>
			</tr>
			<?php if ( ! $course_catalog_id || ! get_post( $course_catalog_id ) ): ?>
			<tr>
				<th>
					<label for="courses_base">
						<?php esc_html_e( 'Course Archive base' ); ?>
					</label>
				</th>
				<td>
					<input name="llms_courses_base" id="courses_base" type="text" value="<?php echo esc_attr( $this->permalinks['courses_base'] ); ?>" class="regular-text code" required>
				</td>
			</tr>
			<?php endif; ?>
			<?php if ( ! $memberships_catalog_id || ! get_post( $memberships_catalog_id ) ): ?>
			<tr>
				<th>
					<label for="memberships_base">
						<?php esc_html_e( 'Memberships Archive base' ); ?>
					</label>
				</th>
				<td>
					<input name="llms_memberships_base" id="memberships_base" type="text" value="<?php echo esc_attr( $this->permalinks['memberships_base'] ); ?>" class="regular-text code" required>
				</td>
			</tr>
			<?php endif; ?>
			<tr>
				<th>
					<label for="lesson_base">
						<?php esc_html_e( 'Lesson Post Type' ); ?>
					</label>
				</th>
				<td>
					<input name="llms_lesson_base" id="lesson_base" type="text" value="<?php echo esc_attr( $this->permalinks['lesson_base'] ); ?>" class="regular-text code" required>
				</td>
			</tr>
			<tr>
				<th>
					<label for="quiz_base">
						<?php esc_html_e( 'Quiz Post Type' ); ?>
					</label>
				</th>
				<td>
					<input name="llms_quiz_base" id="quiz_base" type="text" value="<?php echo esc_attr( $this->permalinks['quiz_base'] ); ?>" class="regular-text code" required>
				</td>
			</tr>
			<tr>
				<th>
					<label for="certificate_template_base">
						<?php esc_html_e( 'Certificate Template Post Type' ); ?>
					</label>
				</th>
				<td>
					<input name="llms_certificate_template_base" id="certificate_template_base" type="text" value="<?php echo esc_attr( $this->permalinks['certificate_template_base'] ); ?>" class="regular-text code" required>
				</td>
			</tr>
			<tr>
				<th>
					<label for="certificate_base">
						<?php esc_html_e( 'Earned Certificate Post Type' ); ?>
					</label>
				</th>
				<td>
					<input name="llms_certificate_base" id="certificate_base" type="text" value="<?php echo esc_attr( $this->permalinks['certificate_base'] ); ?>" class="regular-text code" required>
				</td>
			</tr>
			<tr>
				<th>
					<label for="course_category_base">
						<?php esc_html_e( 'Course Category base' ); ?>
					</label>
				</th>
				<td>
					<input name="llms_course_category_base" id="course_category_base" type="text" value="<?php echo esc_attr( $this->permalinks['course_category_base'] ); ?>" class="regular-text code" required>
				</td>
			</tr>
			<tr>
				<th>
					<label for="course_tag_base">
						<?php esc_html_e( 'Course Tag base' ); ?>
					</label>
				</th>
				<td>
					<input name="llms_course_tag_base" id="course_tag_base" type="text" value="<?php echo esc_attr( $this->permalinks['course_tag_base'] ); ?>" class="regular-text code" required>
				</td>
			</tr>
			<tr>
				<th>
					<label for="course_track_base">
						<?php esc_html_e( 'Course Track base' ); ?>
					</label>
				</th>
				<td>
					<input name="llms_course_track_base" id="course_track_base" type="text" value="<?php echo esc_attr( $this->permalinks['course_track_base'] ); ?>" class="regular-text code" required>
				</td>
			</tr>
			<tr>
				<th>
					<label for="course_difficulty_base">
						<?php esc_html_e( 'Course Difficulty base' ); ?>
					</label>
				</th>
				<td>
					<input name="llms_course_difficulty_base" id="course_difficulty_base" type="text" value="<?php echo esc_attr( $this->permalinks['course_difficulty_base'] ); ?>" class="regular-text code" required>
				</td>
			</tr>
			<tr>
				<th>
					<label for="membership_category_base">
						<?php esc_html_e( 'Membership Category base' ); ?>
					</label>
				</th>
				<td>
					<input name="llms_membership_category_base" id="membership_category_base" type="text" value="<?php echo esc_attr( $this->permalinks['membership_category_base'] ); ?>" class="regular-text code" required>
				</td>
			</tr>
			<tr>
				<th>
					<label for="membership_tag_base">
						<?php esc_html_e( 'Membership Tag base' ); ?>
					</label>
				</th>
				<td>
					<input name="llms_membership_tag_base" id="membership_tag_base" type="text" value="<?php echo esc_attr( $this->permalinks['membership_tag_base'] ); ?>" class="regular-text code" required>
				</td>
			</tr>
			</tbody>
		</table>

		<?php wp_nonce_field( 'llms-permalinks', 'llms-permalinks-nonce' ); ?>
		<?php
	}

	/**
	 * Save the permalink settings
	 */
	public function settings_save() {
		if ( ! is_admin() ) {
			return;
		}

		if ( isset( $_POST['llms-permalinks-nonce'] ) && wp_verify_nonce( $_POST['llms-permalinks-nonce'], 'llms-permalinks' ) ) {
			llms_switch_to_site_locale();

			$permalinks = llms_get_permalink_structure();

			$permalinks['course_base'] = isset( $_POST['llms_course_base'] ) ? sanitize_text_field( wp_unslash( $_POST['llms_course_base'] ) ) : $permalinks['course_base'];
			$permalinks['courses_base'] = isset( $_POST['llms_courses_base'] ) ? sanitize_text_field( wp_unslash( $_POST['llms_courses_base'] ) ) : $permalinks['courses_base'];
			$permalinks['memberships_base'] = isset( $_POST['llms_memberships_base'] ) ? sanitize_text_field( wp_unslash( $_POST['llms_memberships_base'] ) ) : $permalinks['memberships_base'];
			$permalinks['lesson_base'] = isset( $_POST['llms_lessons_base'] ) ? sanitize_text_field( wp_unslash( $_POST['llms_lessons_base'] ) ) : $permalinks['lessons_base'];
			$permalinks['quiz_base'] = isset( $_POST['llms_quiz_base'] ) ? sanitize_text_field( wp_unslash( $_POST['llms_quiz_base'] ) ) : $permalinks['quiz_base'];
			$permalinks['certificate_template_base'] = isset( $_POST['llms_certificate_template_base'] ) ? sanitize_text_field( wp_unslash( $_POST['llms_certificate_template_base'] ) ) : $permalinks['certificate_template_base'];
			$permalinks['certificate_base'] = isset( $_POST['llms_certificate_base'] ) ? sanitize_text_field( wp_unslash( $_POST['llms_certificate_base'] ) ) : $permalinks['certificate_base'];
			$permalinks['course_category_base'] = isset( $_POST['llms_course_category_base'] ) ? sanitize_text_field( wp_unslash( $_POST['llms_course_category_base'] ) ) : $permalinks['course_category_base'];
			$permalinks['course_tag_base'] = isset( $_POST['llms_course_tag_base'] ) ? sanitize_text_field( wp_unslash( $_POST['llms_course_tag_base'] ) ) : $permalinks['course_tag_base'];
			$permalinks['course_track_base'] = isset( $_POST['llms_course_track_base'] ) ? sanitize_text_field( wp_unslash( $_POST['llms_course_track_base'] ) ) : $permalinks['course_track_base'];
			$permalinks['course_difficulty_base'] = isset( $_POST['llms_course_difficulty_base'] ) ? sanitize_text_field( wp_unslash( $_POST['llms_course_difficulty_base'] ) ) : $permalinks['course_difficulty_base'];
			$permalinks['membership_category_base'] = isset( $_POST['llms_membership_category_base'] ) ? sanitize_text_field( wp_unslash( $_POST['llms_membership_category_base'] ) ) : $permalinks['membership_category_base'];
			$permalinks['membership_tag_base'] = isset( $_POST['llms_membership_tag_base'] ) ? sanitize_text_field( wp_unslash( $_POST['llms_membership_tag_base'] ) ) : $permalinks['membership_tag_base'];

			llms_set_permalink_structure( $permalinks );

			llms_restore_locale();
		}
	}
}

return new LLMS_Admin_Permalinks();

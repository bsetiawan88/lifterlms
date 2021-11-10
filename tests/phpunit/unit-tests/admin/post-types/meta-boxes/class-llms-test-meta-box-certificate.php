<?php
/**
 * Tests for LifterLMS Certificate Metabox.
 *
 * @package LifterLMS/Tests
 *
 * @group metabox_certificate
 * @group admin
 * @group metaboxes
 * @group metaboxes_post_type
 *
 * @since [version]
 * @version [version]
 */
class LLMS_Test_Meta_Box_Certificate extends LLMS_PostTypeMetaboxTestCase {

	/**
	 * Setup test.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->metabox = new LLMS_Meta_Box_Certificate();

	}

	/**
	 * Test the get_screens() method.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_screens() {

		$this->assertEquals( array( 'llms_certificate' ), LLMS_Unit_Test_Util::call_method( $this->metabox, 'get_screens' ) );

	}

	/**
	 * Test get fields.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_fields() {

		$post = $this->factory->post->create_and_get( array( 'post_type' => 'llms_certificate' ) );

		$this->metabox->post = $this->factory->post->create_and_get(
		);

		$this->assertEqualSets(
			array(
				'_llms_certificate_title',
				'_llms_sequential_id',
			),
			array_column(
				$this->metabox->get_fields()[0]['fields'],
				'id'
			)
		);

	}

}

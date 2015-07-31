<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'LLMS_Reviews' ) ) :

/**
 * This class handles the front end of the reviews. It is responsible
 * for outputting the HTML on the course page (if reviews are activated)
 */
class LLMS_Reviews 
{
	/**
	 * This is the constructor for this class. It takes care of attaching
	 * the functions in this file to the appropriate actions. These actions are:
	 * 1) Output after course info
	 * 2) Output after membership info
	 * 3 & 4) Add function call to the proper AJAX call
	 *
	 * @return void
	 */
	public function __construct()
	{		
		add_filter('lifterlms_single_course_after_summary', array($this,'Output'),30);
		add_filter('lifterlms_single_membership_after_summary', array($this,'Output'),30);
		add_action('wp_ajax_LLMSSubmitReview', array($this,'ProcessReview'));
		add_action('wp_ajax_nopriv_LLMSSubmitReview', array($this,'ProcessReview'));
	}

	/**
	 * This function handles the HTML output of the reviews and review form.
	 * If the option is enabled, the review form will be output,
	 * if not, nothing will happen. This function also checks to 
	 * see if a user is allowed to review more than once. 
	 */
	public static function Output()
	{
		/**
		 * Check to see if we are supposed to output the code at all
		 */
		if (get_post_meta(get_the_ID(),'_llms_display_reviews',true))
		{
			?>
			<div id="old_reviews">
			<h3>What Others Have Said</h3>
			<?php 
			$args = array(
				'posts_per_page'   => get_post_meta(get_the_ID(),'_llms_num_reviews',true),
				'post_type'        => 'llms_review',
				'post_status'      => 'publish',
				'post_parent'	   => get_the_ID(),
				'suppress_filters' => true 
			);
			$posts_array = get_posts( $args ); 

			foreach ($posts_array as $post) 
			{
				?>
				<div class="llms_review" style="margin:20px 0px; background-color:#EFEFEF; padding:10px">
					<h5 style="font-size:17px" style="margin:3px 0px"><strong><?php echo get_the_title($post->ID);?></strong></h5>
					<h6 style="font-size:13px">By: <?php echo get_the_author_meta('display_name',get_post_field('post_author', $post->ID));?></h5>
					<p style="font-size:15px"><?php echo get_post_field('post_content', $post->ID);?></p>
				</div>
				<?php				
			}
			?>
			<hr>	
			</div>
			<?php
		}

		/**
		 * Check to see if reviews are open
		 */
		if (get_post_meta(get_the_ID(),'_llms_reviews_enabled',true) && is_user_logged_in())
		{			
			/**
			 * Look for previous reviews that we have written on this course.
			 * @var array
			 */
			$args = array(
				'posts_per_page'   => 1,
				'post_type'        => 'llms_review',
				'post_status'      => 'publish',
				'post_parent'	   => get_the_ID(),
				'author'		   => get_current_user_id(), 
				'suppress_filters' => true 
			);
			$posts_array = get_posts( $args );

			/**
			 * Check to see if we are allowed to write more than one review.
			 * If we are not, check to see if we have written a review already.
			 */
			if (get_post_meta(get_the_ID(),'_llms_multiple_reviews_disabled',true) && $posts_array)
			{
				?>
				<div id="thank_you_box">
					<h2><?php echo apply_filters('llms_review_thank_you_text',__('Thank you for your review!','lifterlms')); ?></h2>
				</div>
				<?php
			}
			else
			{
				?>
				<div class="review_box" id="review_box">
				<h3>Write a Review</h3>
				<!--<form method="post" name="review_form" id="review_form">-->
					<input style="margin:10px 0px" type="text" name="review_title" placeholder="Review Title" id="review_title">
					<h5 style="color:red; display:none" id="review_title_error">Review Title is required.</h5>
					<textarea name="review_text" placeholder="Review Text" id="review_text"></textarea>
					<h5 style="color:red; display:none" id="review_text_error">Review Text is required.</h5>
					<?php wp_nonce_field('submit_review','submit_review_nonce_code'); ?>
					<input name="action" value="submit_review" type="hidden">
					<input name="post_ID" value="<?php echo get_the_ID() ?>" type="hidden" id="post_ID">
					<input type="submit" class="button" value="Leave Review" id="llms_review_submit_button">	
				<!--</form>	-->		
				</div>
				<div id="thank_you_box" style="display:none;">
					<h2><?php echo apply_filters('llms_review_thank_you_text',__('Thank you for your review!','lifterlms')); ?></h2>
				</div>
				<?php
			}			
		}		
	}

	/**
	 * This function adds the review to the database. It is 
	 * called by the AJAX handler when the submit review button 
	 * is pressed. This function gathers the data from $_POST and 
	 * then adds the review with the appropriate content.
	 *
	 * @return void
	 */
	public function ProcessReview()
	{
		$post = array(
		  'post_content'   => $_POST['review_text'], // The full text of the post.
		  'post_name'      => $_POST['review_title'], // The name (slug) for your post
		  'post_title'     => $_POST['review_title'], // The title of your post.
		  'post_status'    => 'publish',
		  'post_type'      => 'llms_review',
		  'post_parent'    => $_POST['pageID'], // Sets the parent of the new post, if any. Default 0.		  
		  'post_excerpt'   => $_POST['review_title'],
		);

		$result = wp_insert_post($post, true);
	}
}
endif;
return new LLMS_Reviews;
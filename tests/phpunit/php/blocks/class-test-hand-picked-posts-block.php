<?php
/**
 * Tests for Hand_Picked_Posts_Block class.
 *
 * @package MaterialThemeBuilder
 */

namespace MaterialThemeBuilder\Blocks;

use MaterialThemeBuilder\Plugin;

/**
 * Tests for Hand_Picked_Posts_Block class.
 */
class Test_Hand_Picked_Posts_Block extends Posts_Blocks_Tests_Base {
	/**
	 * Test init.
	 *
	 * @see Hand_Picked_Posts_Block::init()
	 */
	public function test_init() {
		$block = new Hand_Picked_Posts_Block( new Plugin() );
		$block->init();
		$this->assertEquals( 10, has_filter( 'rest_prepare_post', [ $block, 'add_extra_post_meta' ] ) );
		$this->assertEquals( 10, has_action( 'init', [ $block, 'register_block' ] ) );
	}

	/**
	 * Test register_block.
	 *
	 * @see Hand_Picked_Posts_Block::register_block()
	 */
	public function test_register_block() {
		// Unregister the block if it's registered already.
		unregister_block_type( 'material/hand-picked-posts' );

		$block = new Hand_Picked_Posts_Block( new Plugin() );
		$block->register_block();

		// Assert the block is registered.
		$this->assertTrue( in_array( 'material/hand-picked-posts', get_dynamic_block_names(), true ) );
	}

	/**
	 * Test add_extra_post_meta.
	 *
	 * @see Hand_Picked_Posts_Block::add_extra_post_meta()
	 */
	public function test_add_extra_post_meta() {
		$block = new Hand_Picked_Posts_Block( new Plugin() );

		$request  = new \WP_REST_Request( 'GET', '/' );
		$response = new \WP_REST_Response();
		$post     = get_post( self::$post_ids[0] );

		$response = $block->add_extra_post_meta( $response, $post, $request );

		// Assert the extra fields are not set.
		$this->assertTrue( empty( $response->data['authorDisplayName'] ) );
		$this->assertTrue( empty( $response->data['authorUrl'] ) );
		$this->assertTrue( empty( $response->data['commentsCount'] ) );

		// Set context param.
		$request->set_param( 'context', 'edit' );
		$response = $block->add_extra_post_meta( $response, $post, $request );

		// Assert the fields are set.
		$this->assertEquals( 'test', $response->data['authorDisplayName'] );
		$this->assertEquals( 'http://example.org/?author=' . $post->post_author, $response->data['authorUrl'] );
		$this->assertEquals( 0, $response->data['commentsCount'] );
	}

	/**
	 * Test test_render_block.
	 *
	 * @see Hand_Picked_Posts_Block::test_render_block()
	 */
	public function test_render_block() {
		$block = new Hand_Picked_Posts_Block( new Plugin() );

		$attributes = [
			'postsToShow' => 3,
			'style'       => 'grid-new',
		];

		// Only selecting 4 posts out of the 5 which have been created in the tests.
		$attributes['posts'] = [ self::$post_ids[0], self::$post_ids[2], self::$post_ids[3], self::$post_ids[4] ];

		$content = $block->render_block( $attributes );

		// Assert no content is returned if invalid style is selected.
		$this->assertContains( '<!-- No posts found -->', $content );

		unset( $attributes['posts'] );

		$content = $block->render_block( $attributes );

		// Assert no content is returned if no posts have been picked.
		$this->assertContains( '<!-- No posts found -->', $content );

		// Only selecting 4 posts out of the 5 which have been created in the tests.
		$attributes['posts'] = [ self::$post_ids[0], self::$post_ids[2], self::$post_ids[3], self::$post_ids[4] ];

		$attributes['style'] = 'grid';
		$content             = $block->render_block( $attributes );

		// Assert grid layout is loaded.
		$this->assertContains( '<div class="mdc-layout-grid', $content );

		$attributes['style'] = 'masonry';
		$content             = $block->render_block( $attributes );

		// Assert masonry layout is loaded.
		$this->assertContains( '<div class="masonry-grid', $content );

		// Assert 4 posts are rendered.
		$this->assertEquals( 4, substr_count( $content, '<div class="single-post-card single-post-basic' ) );

		$attributes['displayPostAuthor']    = true;
		$attributes['displayCommentsCount'] = true;
		$attributes['displayPostContent']   = true;
		$attributes['displayPostDate']      = true;
		$attributes['displayFeaturedImage'] = true;

		$content = $block->render_block( $attributes );

		// Assert all partials are rendered.
		$this->assertEquals( 4, substr_count( $content, 'class="mdc-button mdc-card__action mdc-card__action--button mdc-button__post-author"' ) );
		$this->assertEquals( 4, substr_count( $content, 'class="mdc-button mdc-card__action mdc-card__action--button mdc-button__comment-count"' ) );
		$this->assertEquals( 4, substr_count( $content, 'class="single-post-card__secondary' ) );
		$this->assertEquals( 4, substr_count( $content, 'class="single-post-card__subtitle' ) );

		// Images should be rendered only for the first post.
		$this->assertEquals( 1, substr_count( $content, 'class="mdc-card__media' ) );

		$content = $this->clean_content( $content );

		// Assert an article has only 1 comment.
		$this->assertEquals( 1, substr_count( $content, '<span class="mdc-button__label">1</span>' ) );

		// Assert an article has 2 comments.
		$this->assertEquals( 1, substr_count( $content, '<span class="mdc-button__label">2</span>' ) );

		$attributes['contentLayout'] = 'text-over-media';
		$content                     = $block->render_block( $attributes );
		$content                     = $this->clean_content( $content );

		// Assert the article with featured image shows content inside the image container.
		$this->assertEquals( 1, substr_count( $content, 'style="background-image: url(http://example.org/wp-content/uploads/image.jpg)"><div class="mdc-card__media-content">' ) );

		// Assert all 4 posts are rendered.
		$this->assertEquals( 4, substr_count( $content, 'class="single-post-card__title' ) );

		$attributes['contentLayout'] = 'text-under-media';
		$content                     = $block->render_block( $attributes );
		$content                     = $this->clean_content( $content );

		// Assert the article with featured image shows content below the image container.
		$this->assertEquals( 1, substr_count( $content, '<!-- mdc-card__media --><div class="single-post-card__primary">' ) );

		$attributes['align'] = 'wide';
		$content             = $block->render_block( $attributes );

		// Assert alignwide class is rendered.
		$this->assertEquals( 1, substr_count( $content, '<div class="wp-block-material-posts-list alignwide"' ) );

		$attributes['style']   = 'grid';
		$attributes['orderby'] = 'title';
		$content               = $block->render_block( $attributes );

		// Assert posts are rendered with title asc sort order.
		$this->assertGreaterThan( strpos( $content, 'Lorem ipsum dolor sit amet' ), strpos( $content, 'Nunc ac malesuada sem' ) );

		$this->assertGreaterThan( strpos( $content, 'Nunc ac malesuada sem' ), strpos( $content, 'Phasellus non pharetra nibh' ) );

		$attributes['orderby'] = 'popularity';
		$content               = $block->render_block( $attributes );

		// Assert posts with most comments are at the top in desc order.
		$this->assertGreaterThan( strpos( $content, 'Nulla eget lobortis turpis' ), strpos( $content, 'Nunc ac malesuada sem' ) );

		$this->assertGreaterThan( strpos( $content, 'Nunc ac malesuada sem' ), strpos( $content, 'Lorem ipsum dolor sit amet' ) );
	}
}
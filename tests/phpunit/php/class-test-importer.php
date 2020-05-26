<?php
/**
 * Tests for Importer class.
 *
 * @package MaterialThemeBuilder
 */

namespace MaterialThemeBuilder;

use WP_UnitTest_Factory;

/**
 * Tests for Importer class.
 */
class Test_Importer extends \WP_UnitTestCase {
	/**
	 * Importer instance
	 *
	 * @var Importer
	 */
	private $importer;

	/**
	 * Plugin instance
	 *
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();
		$this->plugin   = get_plugin_instance();
		$this->importer = new Importer( $this->plugin );

		$this->importer->xml = \simplexml_load_file( $this->get_demo_test_file() );
	}

	/**
	 * Test get_import_file
	 *
	 * @see Importer::get_import_file()
	 */
	public function test_get_import_file() {
		$file = $this->importer->get_import_file();

		$this->assertEquals( $this->plugin->dir_path . '/assets/demo-content.xml', $file );
	}

	/**
	 * Test get_image_file
	 *
	 * @see Importer::get_image_file()
	 */
	public function get_image_file() {
		$file = $this->importer->get_image_file();

		$this->assertEquals( $this->plugin->dir_url . '/assets/images/featured.png', $file );
	}

	/**
	 * Test render_page
	 *
	 * @see Importer::render_page()
	 */
	public function test_render_page() {
		$output = $this->importer->render_page();

		$this->assertContains( '<div class="notice notice-warning material-notice-container">', $output );
	}

	/**
	 * Test get_menu_items
	 *
	 * @see Importer::get_menu_items()
	 */
	public function test_get_menu_items() {
		$items = $this->importer->get_menu_items();

		$this->assertCount( 5, $items );
	}

	/**
	 * Test get_left_widgets
	 *
	 * @see Importer::get_left_widgets()
	 */
	public function test_get_left_widgets() {
		$widgets = $this->importer->get_left_widgets();

		$this->assertCount( 1, $widgets );
	}

	/**
	 * Test get_right_widgets
	 *
	 * @see Importer::get_right_widgets()
	 */
	public function test_get_right_widgets() {
		$widgets = $this->importer->get_right_widgets();

		$this->assertCount( 2, $widgets );
	}

	/**
	 * Test build_widget_ids
	 *
	 * @see Importer::build_widget_ids()
	 */
	public function test_build_widget_ids() {
		$widgets = [
			[
				'title' => '',
			],
			[
				'title' => '',
			],
		];

		$expected = [
			'search-0',
			'search-1',
		];

		$this->assertEquals( $expected, $this->importer->build_widget_ids( $widgets, 'search' ) );
	}

	/**
	 * Test import_terms
	 *
	 * @see Importer::import_terms()
	 */
	public function test_import_terms() {
		$this->importer->import_terms();

		$term = get_term_by( 'name', 'Primary', 'nav_menu' );

		$this->assertEquals( 'Primary', $term->name );
	}

	/**
	 * Test attach_meta_data
	 *
	 * @see Importer::attach_meta_data()
	 */
	public function test_attach_meta_data() {
		$post = $this->importer->xml->channel->item[0];

		$postmeta = $this->importer->attach_meta_data( $post );

		$this->assertArrayHasKey( '_mtb-demo-content', $postmeta );
	}

	/**
	 * Test insert_comments
	 *
	 * @see Importer::insert_comments()
	 */
	public function test_insert_comments() {
		$post = $this->importer->xml->channel->item[0];

		$test_post_id = $this->factory->post->create( [ 'post_title' => 'Test Post' ] );

		$comments = $this->importer->insert_comments( $post, $test_post_id );

		$this->assertNotEmpty( $comments );
	}

	/**
	 * Test insert_post
	 *
	 * @see Importer::insert_post()
	 */
	public function test_insert_post() {
		$post_data = [
			'post_title' => 'Test Post',
		];

		$post = $this->importer->xml->channel->item[0];

		$created_post = $this->importer->insert_post( $post_data, $post );

		$this->assertInternalType( 'int', $created_post );
		$this->assertNotEquals( 0, $created_post );
	}

	/**
	 * Test update_blog_info
	 *
	 * @see Importer::update_blog_info()
	 */
	public function test_update_blog_info() {
		$home_page = $this->factory->post->create(
			[
				'post_title' => 'Home',
				'post_type'  => 'page',
			]
		);
		$blog_page = $this->factory->post->create(
			[
				'post_title' => 'Blog',
				'post_type'  => 'page',
			]
		);

		$this->importer->update_blog_info();

		$this->assertEquals( 'page', get_option( 'show_on_front' ) );
		$this->assertEquals( $home_page, get_option( 'page_on_front' ) );
		$this->assertEquals( $blog_page, get_option( 'page_for_posts' ) );
	}

	/**
	 * Test upload_cover_image
	 *
	 * @see Importer::upload_cover_image()
	 */
	public function test_upload_cover_image() {
		$this->importer->image_location = $this->get_image_test_file();

		$image = $this->importer->upload_cover_image();

		$this->assertInternalType( 'int', $image );
	}

	/**
	 * Test kses_post
	 *
	 * @see Importer::kses_post()
	 */
	public function test_kses_post() {
		$content = '<div class="mdc-text-field-container"><div class="mdc-text-field mdc-text-field--outlined mdc-text-field--custom-full"><input id="mtb-name-0" name="mtb-name-0" type="text" required class="mdc-text-field__input" aria-labelledby="label-mtb-name-0" data-form="contact" data-meta="name" data-label="Name" /><div class="mdc-notched-outline"><div class="mdc-notched-outline__leading"></div><div class="mdc-notched-outline__notch"><label for="mtb-name-0" class="mdc-floating-label" id="label-mtb-name-0">Name</label></div><div class="mdc-notched-outline__trailing"></div></div></div></div>';

		$this->assertEquals( $content, $this->importer->kses_post( $content ) );

		$content = '<div class="wp-block-material-cards-collection alignwide" id="block-material-cards-collection-0"><div class="mdc-layout-grid layout-grid"><div class="mdc-layout-grid__inner"><div class="card-container mdc-layout-grid__cell--span-6"><div class="mdc-card mtb-card" style="border-radius:4px"><div class="mdc-card__primary-action mtb-card__primary-action mdc-ripple-upgraded" tabindex="0"><div tabindex="0" class="mdc-card__media mdc-card__media--16-9 mtb-card__media mtb-card-with-text-under-media" style="background-image:url(/wp-content/plugins/material-theme-builder/assets/images/featured.png)"></div><div class="mtb-card__primary"><h2 class="mtb-card__title mdc-typography mdc-typography--headline6">Nulla libero ex</h2></div><div class="mtb-card__supporting-text mtb-card__supporting-text_text-under-media mdc-typography mdc-typography--body2">Praesent non convallis tortor. Sed bibendum mauris vel nunc gravida ultrices. Sed elementum augue quis maximus ultrices quisque.</div></div></div></div><div class="card-container mdc-layout-grid__cell--span-6"><div class="mdc-card mtb-card" style="border-radius:4px"><div class="mdc-card__primary-action mtb-card__primary-action mdc-ripple-upgraded" tabindex="0"><div tabindex="0" class="mdc-card__media mdc-card__media--16-9 mtb-card__media mtb-card-with-text-under-media" style="background-image:url(/wp-content/plugins/material-theme-builder/assets/images/featured.png)"></div><div class="mtb-card__primary"><h2 class="mtb-card__title mdc-typography mdc-typography--headline6">Praesent non convallis</h2></div><div class="mtb-card__supporting-text mtb-card__supporting-text_text-under-media mdc-typography mdc-typography--body2">Nullam suscipit, quam eget hendrerit dignissim, ante urna cursus felis, sit amet volutpat mauris arcu eget ante. Sed bibendum mauris vel nunc gravida ultrices.</div></div></div></div></div></div></div>';

		$this->assertEquals( $content, $this->importer->kses_post( $content ) );
	}

	/**
	 * Load a minified version of our demo content
	 *
	 * @return string path to demo test file
	 */
	private function get_demo_test_file() {
		return trailingslashit( $this->plugin->dir_path ) . 'assets/demo-content.test.xml';
	}

	/**
	 * Load an image we can upload
	 *
	 * @return string path to demo test file
	 */
	private function get_image_test_file() {
		return 'https://www.google.com/images/branding/googlelogo/2x/googlelogo_color_272x92dp.png';
	}
}
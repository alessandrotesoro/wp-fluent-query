<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Test.
 *
 * @package   Sematico\fluent-query
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright Alessandro Tesoro
 * @license   MIT
 */
namespace Sematico\FluentQuery\Tests;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Sematico\FluentQuery\DatabaseCapsule;
use Sematico\FluentQuery\Model\Page;
use Sematico\FluentQuery\Model\Post;
use Sematico\FluentQuery\Model\User;

class PostModelTest extends \WP_UnitTestCase {
	protected $connection;
	protected $post_id;
	protected $page_id;

	public function setUp() {
		$this->connection = new DatabaseCapsule();
		$this->connection->boot();

		$my_post = array(
			'post_title'   => 'Test post',
			'post_content' => 'Post content',
			'post_status'  => 'publish',
			'post_author'  => 1,
		);

		$this->post_id = wp_insert_post( $my_post );

		$my_page = array(
			'post_title'   => 'Test page',
			'post_content' => 'Page content',
			'post_status'  => 'publish',
			'post_author'  => 1,
			'post_type'    => 'page',
		);

		$this->page_id = wp_insert_post( $my_page );
	}

	public function tearDown() {
		wp_delete_post( $this->post_id, true );
		wp_delete_post( $this->page_id, true );
	}

	public function test_can_get_posts() {
		$post = Post::first();
		$this->assertInstanceOf( Post::class, $post );
		$this->assertSame( $post->title, 'Test post' );
		$this->assertSame( $post->content, 'Post content' );
	}

	public function test_author_relationship() {
		$post = Post::first();
		$this->assertInstanceOf( BelongsTo::class, $post->author() );
		$this->assertInstanceOf( User::class, $post->author()->get()->first() );
	}

	public function test_post_type_scope() {
		$post = ( new Post() )->ofType( 'page' )->first();

		$this->assertInstanceOf( Post::class, $post );
		$this->assertSame( $post->title, 'Test page' );
		$this->assertSame( $post->content, 'Page content' );
	}

	public function test_page_model() {
		$page = Page::first();
		$this->assertInstanceOf( Post::class, $page );
		$this->assertInstanceOf( Page::class, $page );
		$this->assertSame( $page->title, 'Test page' );
		$this->assertSame( $page->content, 'Page content' );
	}
}

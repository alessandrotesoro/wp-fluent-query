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

use Sematico\FluentQuery\DatabaseCapsule;
use Sematico\FluentQuery\Model\Post;

class MetadataTest extends \WP_UnitTestCase {
	protected $connection;

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

		$my_post_second = array(
			'post_title'   => 'Test post new',
			'post_content' => 'Post content',
			'post_status'  => 'publish',
			'post_author'  => 1,
		);

		$this->post_id_second = wp_insert_post( $my_post_second );
	}

	public function tearDown() {
		wp_delete_post( $this->post_id, true );
		wp_delete_post( $this->post_id_second, true );
	}

	public function test_it_can_get_and_set_meta_value_by_key() {

		$metable = Post::first();

		$this->assertNull( $metable->getMeta( 'foo' ) );
		$metable->saveMeta( 'foo', 'bar' );
		$this->assertEquals( 'bar', $metable->getMeta( 'foo' ) );

		$metable->saveMeta( 'foo', 'baz' );
		$this->assertEquals( 'baz', $metable->getMeta( 'foo' ) );
		$this->assertCount( 3, $metable->fields()->get() );

	}

	public function test_it_can_set_many_meta_values_at_once() {

		$metable = Post::first();

		$this->assertNull( $metable->getMeta( 'bar1' ) );
		$this->assertNull( $metable->getMeta( 'baz1' ) );

		$metable->saveMeta(
			[
				'foo1' => 'bar',
				'bar1' => 'baz',
				'baz1' => [ 'foo', 'bar' ],
			]
		);

		$this->assertEquals( 'bar', $metable->getMeta( 'foo1' ) );
		$this->assertEquals( 'baz', $metable->getMeta( 'bar1' ) );
		$this->assertEquals( [ 'foo', 'bar' ], $metable->getMeta( 'baz1' ) );
	}

	public function test_it_accepts_empty_array_for_set_many_meta() {
		$metable = Post::first();
		$metable->saveMeta( 'foo', 'old' );
		$metable->saveMeta( [] ); // should not error out
		$this->assertEquals( 'old', $metable->getMeta( 'foo' ) );
	}

	public function test_it_can_set_uppercase_key() {
		$metable = Post::first();
		$metable->saveMeta( 'FOO', 'bar' );

		$this->assertTrue( $metable->hasMeta( 'FOO' ) );
		$this->assertFalse( $metable->hasMeta( 'foo' ) );
		$this->assertEquals( 'bar', $metable->getMeta( 'FOO' ) );
	}

	public function test_it_can_get_meta_all_values() {
		$metable = Post::first();
		$metable->saveMeta( 'foo', 123 );
		$metable->saveMeta( 'bar', 'hello' );
		$metable->saveMeta( 'baz', [ 'a', 'b', 'c' ] );

		$this->assertEquals(
			[
				'foo'        => '123',
				'bar'        => 'hello',
				'baz'        => [ 'a', 'b', 'c' ],
				'_pingme'    => '1', // not sure where these come from, they're there by default in the testing db.
				'_encloseme' => '1', // not sure where these come from, they're there by default in the testing db.
			],
			$metable->getAllMeta()
		);
	}

	public function test_it_updates_existing_meta_records() {
		$metable = Post::first();
		$metable->saveMeta( 'foo', 'test' );

		$record = $metable->getMeta( 'foo' );
		$this->assertSame( 'test', $record );

		$metable->saveMeta( 'foo', 'new_value' );
		$new_record = $metable->fresh( [ 'meta' ] )->getMeta( 'foo' );
		$this->assertSame( 'new_value', $new_record );
	}

	public function test_it_returns_default_value_if_no_meta_set() {
		$metable = Post::first();
		$result  = $metable->getMeta( 'foo', 'not-found' );

		$this->assertEquals( 'not-found', $result );
	}

	public function test_it_can_delete_meta() {
		$metable = Post::first();
		$metable->saveMeta( 'foo', 'bar' );

		$metable->deleteMeta( 'foo' );

		$this->assertFalse( $metable->hasMeta( 'foo' ) );
		$this->assertFalse( $metable->fresh()->hasMeta( 'foo' ) );
	}

	public function test_it_can_delete_meta_not_set() {
		$metable = Post::first();
		$metable->deleteMeta( 'foo' );

		$this->assertFalse( $metable->hasMeta( 'foo' ) );
		$this->assertFalse( $metable->fresh()->hasMeta( 'foo' ) );
	}

	public function test_it_can_delete_many_meta_at_once() {
		$metable = Post::first();
		$metable->saveMeta( 'foo', 'bar' );
		$metable->saveMeta( 'bar', 'baz' );
		$metable->saveMeta( 'baz', 'foo' );

		$metable->deleteMeta( [ 'foo', 'bar', 'baz' ] );

		$this->assertFalse( $metable->hasMeta( 'foo' ) );
		$this->assertFalse( $metable->hasMeta( 'bar' ) );
		$this->assertFalse( $metable->hasMeta( 'baz' ) );

		$metable = $metable->fresh();

		$this->assertFalse( $metable->hasMeta( 'foo' ) );
		$this->assertFalse( $metable->hasMeta( 'bar' ) );
		$this->assertFalse( $metable->hasMeta( 'baz' ) );
	}

	public function test_it_can_be_queried_by_single_meta_key() {
		$metable = Post::first();
		$metable->saveMeta( 'foo', 'bar' );
		$result = Post::whereHasMeta( 'foo' )->first();
		$this->assertSame( $metable->title, $result->title );
	}

	public function test_it_can_get_database_before_default_value() {
		$metable = Post::first();
		$metable->saveMeta( 'foo', 'baz' );
		$result = Post::first();
		$this->assertEquals( $result->getMeta( 'foo' ), 'baz' );
	}

	public function test_it_can_where_does_not_have_meta() {
		$metable = Post::first();
		$metable->saveMeta( 'foo', 'bar' );
		$result = Post::whereDoesntHaveMeta( 'foo' );

		$this->assertCount( 1, $result->get() );
	}

	public function test_it_can_be_queried_by_all_meta_keys() {
		$metable = Post::first();
		$metable->saveMeta( 'foo', 'bar' );
		$metable->saveMeta( 'baz', 'bat' );

		$result1 = Post::whereHasMetaKeys( [ 'foo', 'baz' ] )->first();
		$result2 = Post::whereHasMetaKeys( [ 'foo', 'zzz' ] )->first();

		$this->assertEquals( $metable->getKey(), $result1->getKey() );
		$this->assertNull( $result2 );
	}

	public function test_it_can_be_queried_by_meta_value() {
		$metable = Post::first();
		$metable->saveMeta( 'foo', 'bar' );
		$metable->saveMeta( 'array', [ 'a' => 'b' ] );

		$result1 = Post::whereMeta( 'foo', 'bar' )->first();
		$result2 = Post::whereMeta( 'foo', 'baz' )->first();
		$result3 = Post::whereMeta( 'array', [ 'a' => 'b' ] )->first();

		$this->assertEquals( $metable->getKey(), $result1->getKey() );
		$this->assertNull( $result2 );
		$this->assertEquals( $metable->getKey(), $result3->getKey() );
	}

	public function test_it_can_be_queried_by_numeric_meta_value() {
		$metable = Post::first();
		$metable->saveMeta( 'foo', 123 );

		$result = Post::whereMetaNumeric( 'foo', '>', 4 )->first();

		$this->assertEquals( $metable->getKey(), $result->getKey() );
	}

	public function test_it_can_be_queried_by_in_array() {
		$metable = Post::first();
		$metable->saveMeta( 'foo', 'bar' );

		$result1 = Post::whereMetaIn( 'foo', [ 'baz', 'bar' ] )->first();
		$result2 = Post::whereMetaIn( 'foo', [ 'baz', 'bat' ] )->first();

		$this->assertEquals( $metable->getKey(), $result1->getKey() );
		$this->assertNull( $result2 );
	}

	public function test_it_can_order_query_by_meta_value() {
		$metable = Post::first();
		$metable->saveMeta( 'my_val', 'b' );

		$metable2 = Post::find( $this->post_id_second );
		$metable2->saveMeta( 'my_val', 'a' );

		$results1 = Post::orderByMeta( 'my_val', 'asc' )->get();
		$results2 = Post::orderByMeta( 'my_val', 'desc' )->get();

		$this->assertEquals( [ $metable2->ID, $metable->ID ], $results1->pluck( 'ID' )->toArray() );
		$this->assertEquals( [ $metable->ID, $metable2->ID ], $results2->pluck( 'ID' )->toArray() );
	}

	public function test_it_can_order_query_by_meta_value_strict() {
		$metable = Post::first();
		$metable->saveMeta( 'my_val', 'b' );

		$metable2 = Post::find( $this->post_id_second );
		$metable2->saveMeta( 'my_val', 'a' );

		$results1 = Post::orderByMeta( 'my_val', 'asc', true )->get();
		$results2 = Post::orderByMeta( 'my_val', 'desc', true )->get();

		$this->assertEquals( [ $metable2->ID, $metable->ID ], $results1->pluck( 'ID' )->toArray() );
		$this->assertEquals( [ $metable->ID, $metable2->ID ], $results2->pluck( 'ID' )->toArray() );
	}

	public function test_it_can_order_query_by_meta_numeric() {
		$metable = Post::first();
		$metable->saveMeta( 'my_num', 2 );

		$metable2 = Post::find( $this->post_id_second );
		$metable2->saveMeta( 'my_num', 1 );

		$results1 = Post::orderByMetaNumeric( 'my_num', 'asc', true )->get();
		$results2 = Post::orderByMetaNumeric( 'my_num', 'desc', true )->get();

		$this->assertEquals( [ $metable2->ID, $metable->ID ], $results1->pluck( 'ID' )->toArray() );
		$this->assertEquals( [ $metable->ID, $metable2->ID ], $results2->pluck( 'ID' )->toArray() );
	}
}

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
use Sematico\FluentQuery\Model\Category;
use Sematico\FluentQuery\Model\Tag;
use Sematico\FluentQuery\Model\Term;
use Sematico\FluentQuery\Model\TermTaxonomy;

class TaxonomyModelTest extends \WP_UnitTestCase {
	protected $connection;

	public $tag_id;

	public function setUp() {
		$this->connection = new DatabaseCapsule();
		$this->connection->boot();

		$tag = wp_create_term( 'Test tag' );

		if ( is_array( $tag ) && isset( $tag['term_id'] ) ) {
			$this->tag_id = $tag['term_id'];
		}
	}

	public function tearDown() {
		wp_delete_term( $this->tag_id, 'post_tag' );
	}

	public function test_can_create_instance() {
		$category = Category::first();
		$this->assertInstanceOf( TermTaxonomy::class, $category );
		$this->assertInstanceOf( Category::class, $category );
	}

	public function test_can_get_categories() {
		$q    = Category::first();
		$term = $q->term()->get()->first();

		$this->assertInstanceOf( BelongsTo::class, $q->term() );
		$this->assertInstanceOf( Term::class, $term );
		$this->assertSame( 'uncategorized', $term->slug );
	}

	public function test_can_create_tag_instance() {
		$tag = Tag::first();
		$this->assertInstanceOf( TermTaxonomy::class, $tag );
		$this->assertInstanceOf( Tag::class, $tag );
	}

	public function test_can_get_tags() {
		$q    = Tag::first();
		$term = $q->term()->get()->first();

		$this->assertInstanceOf( BelongsTo::class, $q->term() );
		$this->assertInstanceOf( Term::class, $term );
		$this->assertSame( 'test-tag', $term->slug );
	}

}

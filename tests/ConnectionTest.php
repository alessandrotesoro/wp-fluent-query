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

use Illuminate\Support\Collection;
use Sematico\FluentQuery\DatabaseCapsule;

class ConnectionTest extends \WP_UnitTestCase {
	protected $connection;

	public function setUp() {
		$this->connection = new DatabaseCapsule();
		$this->connection->boot();
	}

	public function test_can_query() {
		$query = $this->connection::table( 'users' )->get();
		$this->assertTrue( $this->connection->isBooted() );
		$this->assertInstanceOf( Collection::class, $query );
		$this->assertTrue( isset( $query->first()->ID ) );
	}
}

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

use Illuminate\Database\Schema\Blueprint;
use Sematico\FluentQuery\DatabaseCapsule;

class SchemaTest extends \WP_UnitTestCase {
	protected $connection;

	public function setUp() {
		$this->connection = new DatabaseCapsule();
		$this->connection->boot();
	}

	public function tearDown() {
	}

	public function test_can_create_table() {
		$this->connection::schema()->create(
			'flights',
			function ( Blueprint $table ) {
				$table->id();
				$table->string( 'name' );
				$table->string( 'airline' );
				$table->timestamps();
			}
		);
		$this->assertTrue( $this->connection::schema()->hasTable( 'flights' ) );
	}

	public function test_can_insert_data() {
		$this->connection::table( 'flights' )->insert(
			[
				'name'    => 'Flight 123',
				'airline' => 'Airline test',
			]
		);
		$this->assertSame( count( $this->connection::table( 'flights' )->get() ), 1 );
		$this->assertSame( $this->connection::table( 'flights' )->get()->first()->name, 'Flight 123' );
		$this->assertSame( $this->connection::table( 'flights' )->get()->first()->airline, 'Airline test' );
	}

	public function test_can_drop_table() {
		$this->connection::schema()->drop( 'flights' );
		$this->assertTrue( ! $this->connection::schema()->hasTable( 'flights' ) );
	}

}

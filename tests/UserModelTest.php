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

use Illuminate\Database\Eloquent\Relations\HasMany;
use Sematico\FluentQuery\DatabaseCapsule;
use Sematico\FluentQuery\Model\User;

class UserModelTest extends \WP_UnitTestCase {
	protected $connection;

	public function setUp() {
		$this->connection = new DatabaseCapsule();
		$this->connection->boot();
	}

	public function tearDown() {
	}

	public function test_can_get_user() {
		$user = User::first();
		$this->assertInstanceOf( User::class, $user );
		$this->assertSame( $user->login, 'admin' );
	}

	public function test_posts_relationship() {
		$user = User::first();
		$this->assertInstanceOf( HasMany::class, $user->posts() );
	}

	public function test_meta_relationship() {
		$user = User::first();
		$this->assertInstanceOf( HasMany::class, $user->meta() );
		$this->assertSame( $user->nickname, 'admin' );
	}

	public function test_comments_relationship() {
		$user = User::first();
		$this->assertInstanceOf( HasMany::class, $user->comments() );
	}
}

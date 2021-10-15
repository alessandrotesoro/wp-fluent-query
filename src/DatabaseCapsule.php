<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Setup Eloquent and connect it via wpdb.
 *
 * @package   Sematico\fluent-query
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright Alessandro Tesoro
 * @license   MIT
 */
namespace Sematico\FluentQuery;

use Exception;
use Illuminate\Database\Capsule\Manager as Capsule;
use Sematico\FluentQuery\Connection\WordPressConnection;

/**
 * Setup Eloquent and connect it via wpdb.
 */
class DatabaseCapsule {

	/**
	 * If it booted or not.
	 *
	 * @var boolean
	 */
	public $booted = false;

	/**
	 * Boot the connection.
	 *
	 * @return void
	 */
	public function boot() {
		$capsule = new Capsule();

		$capsule->addConnection( [], 'wp' );
		$capsule->getDatabaseManager()->extend(
			'wp',
			function () {
				return new WordPressConnection();
			}
		);

		$capsule->getDatabaseManager()->setDefaultConnection( 'wp' );

		$capsule->setAsGlobal();
		$capsule->bootEloquent();

		$this->booted = true;
	}

	public function __call( $method, $arguments ) {
		try {
			return self::callCapsuleMethod( $method, $arguments );
		} catch ( Exception $e ) {

		}
		trigger_error( 'Call to undefined method ' . __CLASS__ . '::' . $method . '()', E_USER_ERROR );
	}

	public static function __callStatic( $method, $arguments ) {
		try {
			return self::callCapsuleMethod( $method, $arguments );
		} catch ( Exception $e ) {

		}
		trigger_error( 'Call to undefined method ' . __CLASS__ . '::' . $method . '()', E_USER_ERROR );
	}

	public static function callCapsuleMethod( $method, $arguments ) {
		if ( is_callable( Capsule::class, $method ) ) {
			return call_user_func_array( [ Capsule::class, $method ], $arguments );
		} else {
			throw new Exception( 'No Capsule method.' );
		}
	}

	public function isBooted() {
		return $this->booted;
	}

}

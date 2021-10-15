<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Bind wpdb to the eloquent connection.
 *
 * @package   Sematico\fluent-query
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright Alessandro Tesoro
 * @license   MIT
 */

namespace Sematico\FluentQuery\Connection;

use Illuminate\Database\MySqlConnection;

/**
 * Bind wpdb to the eloquent connection.
 */
class WordPressConnection extends MySqlConnection {

	private $wpdb;

	/**
	 * Get things started.
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
		parent::__construct( new WordPressPDO( $this ), DB_NAME ?? null, $wpdb->prefix );
	}

	/**
	 * Get wpdb.
	 *
	 * @return \wpdb
	 */
	public function getWpdb() {
		return $this->wpdb;
	}

	/**
	 * {@inheritdoc}
	 */
	public function select( $query, $bindings = [], $useReadPdo = true ) {
		return $this->run(
			$query,
			$bindings,
			function ( $query, $bindings ) use ( $useReadPdo ) {
				if ( $this->pretending() ) {
					return [];
				}

				$query = $this->applyBindings( $query, $bindings );

				return $this->getResults( $query );
			}
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function cursor( $query, $bindings = [], $useReadPdo = true ) {
		$results = $this->select( $query, $bindings, $useReadPdo );

		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				yield $result;
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function statement( $query, $bindings = [] ) {
		return $this->run(
			$query,
			$bindings,
			function ( $query, $bindings ) {
				if ( $this->pretending() ) {
					return true;
				}

				$this->exec( $this->applyBindings( $query, $bindings ) );

				return true;
			}
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function affectingStatement( $query, $bindings = [] ) {
		return $this->run(
			$query,
			$bindings,
			function ( $query, $bindings ) {
				if ( $this->pretending() ) {
					return true;
				}

				return $this->exec( $this->applyBindings( $query, $bindings ) );
			}
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function unprepared( $query ) {
		return $this->run(
			$query,
			[],
			function ( $query ) {
				if ( $this->pretending() ) {
					return true;
				}

				return (bool) $this->exec( $query );
			}
		);
	}

	/**
	 * Run queries through wpdb's get_results method.
	 *
	 * @param string $query
	 * @return array|object|null Database query results.
	 */
	public function getResults( $query ) {
		return $this->getWpdb()->get_results( $query );
	}

	/**
	 * Performs a MySQL database query, using current database connection.
	 *
	 * @param string $query
	 * @return int|bool Boolean true for CREATE, ALTER, TRUNCATE and DROP queries. Number of rows affected/selected for all other queries. Boolean false on error.
	 */
	public function exec( $query ) {
		return $this->getWpdb()->query( $query );
	}

	/**
	 * {@inheritdoc}
	 */
	public function applyBindings( string $query, array $bindings ) : string {
		if ( empty( $bindings ) ) {
			return $query;
		}

		$bindings = $this->prepareBindings( $bindings );

		$wpBindings = [];

		$bindingIndex = 0;
		$wpQuery      = preg_replace_callback(
			'/\?|:[a-zA-Z0-9_-]+/',
			function ( $match ) use ( $bindings, &$bindingIndex, &$wpBindings ) {
				if ( preg_match( '/^:/', $match[0] ) ) {
					$bindingKey = str_replace( ':', '', $match[0] );
				} else {
					$bindingKey = $bindingIndex;
					$bindingIndex++;
				}

				$value = $bindings[ $bindingKey ] ?? null;

				$wpBindings[] = $value;

				if ( is_int( $value ) ) {
					return '%d';
				} elseif ( is_float( $value ) ) {
					return '%f';
				}

				return '%s';
			},
			$query
		);

		return $this->getWpdb()->prepare( $wpQuery, $wpBindings );
	}

}

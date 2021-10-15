<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Metadata collection.
 *
 * @package   Sematico\fluent-query
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright Alessandro Tesoro
 * @license   MIT
 */
namespace Sematico\FluentQuery\Collection;

use Illuminate\Database\Eloquent\Collection;

/**
 * Model metadata collection.
 */
class MetaCollection extends Collection {
	/**
	 * @param string $key
	 * @return mixed
	 */
	public function __get( $key ) {
		if ( in_array( $key, static::$proxies, true ) ) {
			return parent::__get( $key );
		}

		if ( isset( $this->items ) && count( $this->items ) ) {
			$meta = $this->first(
				function ( $meta ) use ( $key ) {
					return $meta->meta_key === $key;
				}
			);

			return $meta ? $meta->meta_value : null;
		}

		return null;
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function __isset( $name ) {
		return ! is_null( $this->__get( $name ) );
	}
}

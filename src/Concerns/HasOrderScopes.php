<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Helper methods for sorting queries.
 *
 * @package   Sematico\fluent-query
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright Alessandro Tesoro
 * @license   MIT
 */
namespace Sematico\FluentQuery\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasOrderScopes {
	/**
	 * @param Builder $query
	 * @return Builder
	 */
	public function scopeNewest( Builder $query ) {
		return $query->orderBy( static::CREATED_AT, 'desc' );
	}

	/**
	 * @param Builder $query
	 * @return Builder
	 */
	public function scopeOldest( Builder $query ) {
		return $query->orderBy( static::CREATED_AT, 'asc' );
	}
}

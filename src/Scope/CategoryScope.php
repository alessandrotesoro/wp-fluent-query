<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Category model scope.
 *
 * @package   Sematico\fluent-query
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright Alessandro Tesoro
 * @license   MIT
 */
namespace Sematico\FluentQuery\Scope;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Scope taxonomy queries to automatically look for post categories.
 */
class CategoryScope implements Scope {
	/**
	 * Apply the scope to the given query.
	 *
	 * @param Builder $builder
	 * @param Model   $model
	 * @return void
	 */
	public function apply( Builder $builder, Model $model ) {
		$builder->where( 'taxonomy', 'category' );
	}
}

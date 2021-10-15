<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Category model.
 *
 * @package   Sematico\fluent-query
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright Alessandro Tesoro
 * @license   MIT
 */
namespace Sematico\FluentQuery\Model;

use Sematico\FluentQuery\Scope\CategoryScope;

/**
 * Category model.
 */
class Category extends TermTaxonomy {

	/**
	 * Automatically adjust the query to load categories.
	 *
	 * @return void
	 */
	protected static function boot() {
		parent::boot();
		static::addGlobalScope( new CategoryScope() );
	}

}

<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Page model.
 *
 * @package   Sematico\fluent-query
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright Alessandro Tesoro
 * @license   MIT
 */
namespace Sematico\FluentQuery\Model;

use Sematico\FluentQuery\Scope\PageScope;

/**
 * WordPress page model.
 */
class Page extends Post {

	/**
	 * Automatically adjust the query to load pages.
	 *
	 * @return void
	 */
	protected static function boot() {
		parent::boot();
		static::addGlobalScope( new PageScope() );
	}

}

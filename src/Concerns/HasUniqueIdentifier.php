<?php // phpcs:ignore WordPress.Files.FileName
/**
 * ID helper methods.
 *
 * @package   Sematico\fluent-query
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright Alessandro Tesoro
 * @license   MIT
 */
namespace Sematico\FluentQuery\Concerns;

/**
 * ID helper methods
 */
trait HasUniqueIdentifier {
	/**
	 * Get model id.
	 *
	 * @return int
	 */
	public function getID() {
		return (int) $this->attributes[ $this->primaryKey ];
	}
}

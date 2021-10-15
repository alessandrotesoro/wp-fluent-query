<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Term model.
 *
 * @package   Sematico\fluent-query
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright Alessandro Tesoro
 * @license   MIT
 */
namespace Sematico\FluentQuery\Model;

use Illuminate\Database\Eloquent\Model;
use Sematico\FluentQuery\Concerns\HasMetaFields;
use Sematico\FluentQuery\Concerns\HasUniqueIdentifier;

/**
 * WordPress term model.
 */
class Term extends Model {
	use HasMetaFields;
	use HasUniqueIdentifier;

	/**
	 * @var string
	 */
	protected $table = 'terms';

	/**
	 * @var string
	 */
	protected $primaryKey = 'term_id';

	/**
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * @return HasOne
	 */
	public function taxonomy() {
		return $this->hasOne( TermTaxonomy::class, 'term_id' );
	}
}

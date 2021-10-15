<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Term taxonomy model.
 *
 * @package   Sematico\fluent-query
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright Alessandro Tesoro
 * @license   MIT
 */
namespace Sematico\FluentQuery\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;
use Sematico\FluentQuery\Concerns\HasUniqueIdentifier;

/**
 * TermTaxonomy model.
 */
class TermTaxonomy extends Model {

	use HasUniqueIdentifier;

	/**
	 * @var string
	 */
	protected $table = 'term_taxonomy';

	/**
	 * @var string
	 */
	protected $primaryKey = 'term_taxonomy_id';

	/**
	 * @var array
	 */
	protected $with = [ 'term' ];

	/**
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * @return BelongsTo
	 */
	public function term() {
		return $this->belongsTo( Term::class, 'term_id' );
	}

	/**
	 * @return BelongsTo
	 */
	public function parent() {
		return $this->belongsTo( self::class, 'parent' );
	}

	/**
	 * @return BelongsToMany
	 */
	public function posts() {
		return $this->belongsToMany(
			Post::class,
			'term_relationships',
			'term_taxonomy_id',
			'object_id'
		);
	}

	/**
	 * @return TaxonomyBuilder
	 */
	public function newQuery() {
		return isset( $this->taxonomy ) && $this->taxonomy ?
			parent::newQuery()->where( 'taxonomy', $this->taxonomy ) :
			parent::newQuery();
	}

	/**
	 * Magic method to return the meta data like the post original fields.
	 *
	 * @param string $key
	 * @return string
	 */
	public function __get( $key ) {
		if ( ! isset( $this->$key ) ) {
			if ( isset( $this->term->$key ) ) {
				return $this->term->$key;
			}
		}

		return parent::__get( $key );
	}
}

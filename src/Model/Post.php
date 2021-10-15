<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Post model.
 *
 * @package   Sematico\fluent-query
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright Alessandro Tesoro
 * @license   MIT
 */
namespace Sematico\FluentQuery\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Sematico\FluentQuery\Concerns\HasAliases;
use Sematico\FluentQuery\Concerns\HasMetaFields;
use Sematico\FluentQuery\Concerns\HasOrderScopes;
use Sematico\FluentQuery\Concerns\HasUniqueIdentifier;

/**
 * WordPress post model.
 *
 * @property-read int $ID
 * @property int $post_author
 * @property Carbon $post_date
 * @property Carbon $post_date_gmt
 * @property string $post_content
 * @property string $post_title
 * @property string $post_excerpt
 * @property string $post_status
 * @property string $comment_status
 * @property string $ping_status
 * @property string $post_password
 * @property string $post_name
 * @property string $to_ping
 * @property string $pinged
 * @property Carbon $post_modified
 * @property Carbon $post_modified_gmt
 * @property string $post_content_filtered
 * @property int $post_parent
 * @property string $guid
 * @property int $menu_order
 * @property string $post_type
 * @property string $post_mime_type
 * @property int $comment_count
 * @property User $author
 * @property Collection $comments
 * @property Collection $metas
 * @property Post $parent
 * @property Collection $taxonomies
 * @method Builder|static published()
 * @method Builder|static status(string|array $status)
 * @method Builder|static ofType(string|array $type)
 */
class Post extends Model {

	use HasAliases;
	use HasOrderScopes;
	use HasMetaFields;
	use HasUniqueIdentifier;

	const CREATED_AT = 'post_date';
	const UPDATED_AT = 'post_modified';

	/**
	 * @var string
	 */
	protected $table = 'posts';

	/**
	 * @var string
	 */
	protected $primaryKey = 'ID';

	/**
	 * @var array
	 */
	protected $dates = [ 'post_date', 'post_date_gmt', 'post_modified', 'post_modified_gmt' ];

	/**
	 * @var array
	 */
	protected $with = [ 'meta' ];

	/**
	 * @var array
	 */
	protected $fillable = [
		'post_content',
		'post_title',
		'post_excerpt',
		'post_type',
		'to_ping',
		'pinged',
		'post_content_filtered',
	];

	/**
	 * @var array
	 */
	protected $appends = [
		'title',
		'slug',
		'content',
		'type',
		'url',
		'author_id',
		'parent_id',
		'created_at',
		'updated_at',
		'excerpt',
		'status',
		'terms',
	];

	/**
	 * @var array
	 */
	protected static $aliases = [
		'title'      => 'post_title',
		'content'    => 'post_content',
		'excerpt'    => 'post_excerpt',
		'slug'       => 'post_name',
		'type'       => 'post_type',
		'url'        => 'guid',
		'author_id'  => 'post_author',
		'parent_id'  => 'post_parent',
		'created_at' => 'post_date',
		'updated_at' => 'post_modified',
		'status'     => 'post_status',
	];

	/**
	 * @return BelongsTo
	 */
	public function author() {
		return $this->belongsTo( User::class, 'post_author' );
	}

	/**
	 * @return BelongsTo
	 */
	public function parent() {
		return $this->belongsTo( self::class, 'post_parent' );
	}

	/**
	 * @return HasMany
	 */
	public function children() {
		return $this->hasMany( self::class, 'post_parent' );
	}

	/**
	 * @return HasMany
	 */
	public function attachment() {
		return $this->hasMany( self::class, 'post_parent' )
			->where( 'post_type', 'attachment' );
	}

	/**
	 * @return HasMany
	 */
	public function revision() {
		return $this->hasMany( self::class, 'post_parent' )
			->where( 'post_type', 'revision' );
	}

	/**
	 * @return HasMany
	 */
	public function comments() {
		return $this->hasMany( Comment::class, 'comment_post_ID' );
	}

	/**
	 * Get posts by post_type.
	 *
	 * @param Builder      $query
	 * @param string|array $type
	 * @return Builder
	 */
	public function scopeOfType( Builder $query, $type ) {
		if ( is_array( $type ) ) {
			return $query->whereIn( 'post_type', $type );
		}

		if ( is_string( $type ) ) {
			return $query->where( 'post_type', $type );
		}

		return $query;
	}

	/**
	 * Get published posts only.
	 *
	 * @param Builder $query
	 * @return Builder
	 */
	public function scopePublished( Builder $query ) {
		return $query->where( 'post_status', 'publish' );
	}

	/**
	 * Limits the scope of the request to a particular post status.
	 *
	 * @param Builder      $query
	 * @param string|array $status
	 * @return Builder
	 */
	public function scopeStatus( Builder $query, $status ) {
		if ( is_array( $status ) ) {
			return $query->whereIn( 'post_status', $status );
		}

		if ( is_string( $status ) ) {
			return $query->where( 'post_status', $status );
		}

		return $query;
	}

	/**
	 * @return BelongsToMany
	 */
	public function taxonomies() {
		return $this->belongsToMany(
			TermTaxonomy::class,
			'term_relationships',
			'object_id',
			'term_taxonomy_id'
		);
	}

	/**
	 * Gets all the terms arranged taxonomy => terms[].
	 *
	 * @return array
	 */
	public function getTermsAttribute() {
		return $this->taxonomies->groupBy(
			function ( $taxonomy ) {
				return $taxonomy->taxonomy === 'post_tag' ? 'tag' : $taxonomy->taxonomy;
			}
		)->map(
			function ( $group ) {
				return $group->mapWithKeys(
					function ( $item ) {
						return [ $item->term->slug => $item->term->name ];
					}
				);
			}
		)->toArray();
	}

	/**
	 * Whether the post contains the term or not.
	 *
	 * @param string $taxonomy
	 * @param string $term
	 * @return bool
	 */
	public function hasTerm( $taxonomy, $term ) {
		return isset( $this->terms[ $taxonomy ] ) && isset( $this->terms[ $taxonomy ][ $term ] );
	}

}

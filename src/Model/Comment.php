<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Comment model.
 *
 * @package   Sematico\fluent-query
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright Alessandro Tesoro
 * @license   MIT
 */
namespace Sematico\FluentQuery\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Sematico\FluentQuery\Concerns\HasMetaFields;
use Sematico\FluentQuery\Concerns\HasOrderScopes;
use Sematico\FluentQuery\Concerns\HasUniqueIdentifier;

/**
 * WordPress Comment model.
 */
class Comment extends Model {

	use HasMetaFields;
	use HasOrderScopes;
	use HasUniqueIdentifier;

	const CREATED_AT = 'comment_date';
	const UPDATED_AT = null;

	/**
	 * @var string
	 */
	protected $table = 'comments';

	/**
	 * @var string
	 */
	protected $primaryKey = 'comment_ID';

	/**
	 * @var array
	 */
	protected $dates = [ 'comment_date' ];

	/**
	 * @return BelongsTo
	 */
	public function post() {
		return $this->belongsTo( Post::class, 'comment_post_ID' );
	}

	/**
	 * @return BelongsTo
	 */
	public function parent() {
		return $this->original();
	}

	/**
	 * @return BelongsTo
	 */
	public function original() {
		return $this->belongsTo( self::class, 'comment_parent' );
	}

	/**
	 * @return HasMany
	 */
	public function replies() {
		return $this->hasMany( self::class, 'comment_parent' );
	}

	/**
	 * @param mixed $value
	 * @return void
	 */
	public function setUpdatedAt( $value ) {
	}
}

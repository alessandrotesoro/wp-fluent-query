<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Metadata class.
 *
 * @package   Sematico\fluent-query
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright Alessandro Tesoro
 * @license   MIT
 */
namespace Sematico\FluentQuery\Model\Meta;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Sematico\FluentQuery\Model\Post;

/**
 * Post metadata model.
 */
class PostMeta extends BaseMeta {
	/**
	 * @var string
	 */
	protected $table = 'postmeta';

	/**
	 * @var array
	 */
	protected $fillable = [ 'meta_key', 'meta_value', 'post_id' ];

	/**
	 * @return BelongsTo
	 */
	public function post() {
		return $this->belongsTo( Post::class, 'post_id' );
	}
}

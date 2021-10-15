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
use Sematico\FluentQuery\Model\User;

/**
 * User metadata model.
 */
class UserMeta extends BaseMeta {
	/**
	 * @var string
	 */
	protected $table = 'usermeta';

	/**
	 * @var string
	 */
	protected $primaryKey = 'umeta_id';

	/**
	 * @var array
	 */
	protected $fillable = [ 'meta_key', 'meta_value', 'user_id' ];

	/**
	 * @return BelongsTo
	 */
	public function user() {
		return $this->belongsTo( User::class, 'user_id' );
	}
}

<?php // phpcs:ignore WordPress.Files.FileName
/**
 * User model.
 *
 * @package   Sematico\fluent-query
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright Alessandro Tesoro
 * @license   MIT
 */
namespace Sematico\FluentQuery\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Sematico\FluentQuery\Concerns\HasAliases;
use Sematico\FluentQuery\Concerns\HasUniqueIdentifier;
use Sematico\FluentQuery\Concerns\HasMetaFields;
use Sematico\FluentQuery\Concerns\HasOrderScopes;

/**
 * Representation of a WordPress User.
 *
 * @property-read int $ID
 * @property string $user_login
 * @property string $user_pass
 * @property string $user_nicename
 * @property string $user_email
 * @property string $user_url
 * @property Carbon $user_registered
 * @property string $user_activation_key
 * @property bool $user_status
 * @property string $display_name
 * @property Collection $meta
 * @property Collection $fields
 * @property Collection $posts
 */
class User extends Model {

	const CREATED_AT = 'user_registered';
	const UPDATED_AT = null;

	use HasAliases;
	use HasOrderScopes;
	use HasMetaFields;
	use HasUniqueIdentifier;

	/**
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * @var string
	 */
	protected $primaryKey = 'ID';

	/**
	 * @var array
	 */
	protected $hidden = [ 'user_pass' ];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'user_registered' => 'datetime',
	];

	/**
	 * @var array
	 */
	protected $with = [ 'meta' ];

	/**
	 * @var array
	 */
	protected static $aliases = [
		'login'       => 'user_login',
		'email'       => 'user_email',
		'slug'        => 'user_nicename',
		'url'         => 'user_url',
		'nickname'    => [ 'meta' => 'nickname' ],
		'first_name'  => [ 'meta' => 'first_name' ],
		'last_name'   => [ 'meta' => 'last_name' ],
		'description' => [ 'meta' => 'description' ],
		'created_at'  => 'user_registered',
	];

	/**
	 * The accessors to append to the model's array form.
	 *
	 * @var array
	 */
	protected $appends = [
		'login',
		'email',
		'slug',
		'url',
		'nickname',
		'first_name',
		'last_name',
		'created_at',
	];

	/**
	 * @return HasMany
	 */
	public function posts() {
		return $this->hasMany( Post::class, 'post_author' );
	}

	/**
	 * @return HasMany
	 */
	public function comments() {
		return $this->hasMany( Comment::class, 'user_id' );
	}

}

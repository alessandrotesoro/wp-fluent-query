<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Base metadata class.
 *
 * @package   Sematico\fluent-query
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright Alessandro Tesoro
 * @license   MIT
 */
namespace Sematico\FluentQuery\Model\Meta;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Sematico\FluentQuery\Collection\MetaCollection;

/**
 * Base metadata model class.
 */
abstract class BaseMeta extends Model {
	/**
	 * @var string
	 */
	protected $primaryKey = 'meta_id';

	/**
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * @var array
	 */
	protected $appends = [ 'value' ];

	/**
	 * @return mixed
	 */
	public function getValueAttribute() {
		try {
			$value = maybe_unserialize( $this->meta_value );
			return $value === false && $this->meta_value !== false ? $this->meta_value : $value;
		} catch ( Exception $ex ) {
			return $this->meta_value;
		}
	}

	/**
	 * @param array $models
	 * @return MetaCollection
	 */
	public function newCollection( array $models = [] ) {
		return new MetaCollection( $models );
	}
}


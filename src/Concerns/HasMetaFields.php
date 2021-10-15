<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Base metadata class.
 *
 * @package   Sematico\fluent-query
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright Alessandro Tesoro
 * @license   MIT
 */
namespace Sematico\FluentQuery\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Sematico\FluentQuery\Model\Comment;
use Sematico\FluentQuery\Model\Meta\CommentMeta;
use Sematico\FluentQuery\Model\Meta\PostMeta;
use Sematico\FluentQuery\Model\Meta\TermMeta;
use Sematico\FluentQuery\Model\Meta\UserMeta;
use Sematico\FluentQuery\Model\Post;
use Sematico\FluentQuery\Model\Term;
use Sematico\FluentQuery\Model\User;
use UnexpectedValueException;

/**
 * Trait HasMetaFields
 */
trait HasMetaFields {

	/**
	 * @var array
	 */
	protected $builtInClasses = [
		User::class    => UserMeta::class,
		Post::class    => PostMeta::class,
		Comment::class => CommentMeta::class,
		Term::class    => TermMeta::class,
	];

	/**
	 * @return HasMany
	 */
	public function fields() {
		return $this->meta();
	}

	/**
	 * @return HasMany
	 */
	public function meta() {
		return $this->hasMany( $this->getMetaClass(), $this->getMetaForeignKey() );
	}

	/**
	 * @return string
	 * @throws UnexpectedValueException
	 */
	protected function getMetaClass() {
		foreach ( $this->builtInClasses as $model => $meta ) {
			if ( $this instanceof $model ) {
				return $meta;
			}
		}

		throw new UnexpectedValueException(
			sprintf(
				'%s must extends one of the built-in models: Comment, Post, Term or User.',
				static::class
			)
		);
	}

	/**
	 * @return string
	 * @throws UnexpectedValueException
	 */
	protected function getMetaForeignKey(): string {
		foreach ( $this->builtInClasses as $model => $meta ) {
			if ( $this instanceof $model ) {
				return sprintf( '%s_id', strtolower( class_basename( $model ) ) );
			}
		}

		throw new UnexpectedValueException(
			sprintf(
				'%s must extends one of the built-in models: Comment, Post, Term or User.',
				static::class
			)
		);
	}

	/**
	 * Check if the model has the specified metadata.
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function hasMeta( string $key ) {
		return $this->meta()->get()->{ $key } !== null;
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 * @return bool
	 */
	public function saveField( $key, $value ) {
		return $this->saveMeta( $key, $value );
	}

	/**
	 * @param string|array $key
	 * @param mixed        $value
	 * @return bool
	 */
	public function saveMeta( $key, $value = null ) {
		if ( is_array( $key ) ) {
			foreach ( $key as $k => $v ) {
				$this->saveOneMeta( $k, $v );
			}
			$this->load( 'meta' );

			return true;
		}

		return $this->saveOneMeta( $key, $value );
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 * @return bool
	 */
	private function saveOneMeta( $key, $value ) {
		$meta   = $this->meta()->where( 'meta_key', $key )->firstOrNew( [ 'meta_key' => $key ] );
		$result = $meta->fill( [ 'meta_value' => maybe_serialize( $value ) ] )->save();

		$this->load( 'meta' );

		return $result;
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 * @return Model
	 */
	public function createField( $key, $value ) {
		return $this->createMeta( $key, $value );
	}

	/**
	 * @param string|array $key
	 * @param mixed        $value
	 * @return Model|Collection
	 */
	public function createMeta( $key, $value = null ) {
		if ( is_array( $key ) ) {
			return collect( $key )->map(
				function ( $value, $key ) {
					return $this->createOneMeta( $key, $value );
				}
			);
		}

		return $this->createOneMeta( $key, $value );
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 * @return Model
	 */
	private function createOneMeta( $key, $value ) {
		$meta = $this->meta()->create(
			[
				'meta_key'   => $key,
				'meta_value' => maybe_serialize( $value ),
			]
		);
		$this->load( 'meta' );

		return $meta;
	}

	/**
	 * Get specific metadata.
	 *
	 * @param string $attribute Metadata key to look for.
	 * @param mixed  $default    Default value to return if the metadata does not exist.
	 * @return mixed
	 */
	public function getMeta( $attribute, $default = null ) {
		if ( $meta = $this->meta->{$attribute} ) {
			return maybe_unserialize( $meta );
		}

		return $default;
	}

	/**
	 * Returns the list of all metadata and their value associated with the model.
	 *
	 * @return array
	 */
	public function getAllMeta() {
		return $this->meta()
				->get()
				->keyBy( 'meta_key' )
				->map(
					function( $metadata ) {
						return $metadata->getValueAttribute();
					}
				)
				->all();
	}

	/**
	 * Increment a meta value.
	 *
	 * @param string  $key
	 * @param integer $incrementalValue
	 * @return integer
	 */
	public function incrementMeta( string $key, $incrementalValue = 1 ) {
		$value = (int) $this->getMeta( $key, 0 ) + $incrementalValue;

		$this->saveMeta( $key, $value );

		return (int) $value;
	}

	/**
	 * Decrement a meta value.
	 *
	 * @param string  $key
	 * @param integer $decrementValue
	 * @return integer
	 */
	public function decrementMeta( string $key, $decrementValue = 1 ) {
		return $this->incrementMeta( $key, -1 * $decrementValue );
	}

	/**
	 * Delete a meta (or metas) for a given key (or keys).
	 *
	 * @param string|array $key
	 * @return boolean
	 */
	public function deleteMeta( $key ) {
		if ( is_array( $key ) ) {
			return $this->fields()->whereIn( 'meta_key', $key )->delete();
		}

		return $this->fields()->where( 'meta_key', $key )->delete();
	}

	/**
	 * Delete all metadata related to the model.
	 *
	 * @return bool
	 */
	public function deleteAllMeta() {
		return $this->fields()->delete();
	}

	/**
	 * Query scope to restrict the query to records which have `Meta` attached to a given key.
	 * If an array of keys is passed instead, will restrict the query to records having one or more Meta with any of the keys.
	 *
	 * @param Builder      $q
	 * @param string|array $key
	 * @return Builder
	 */
	public function scopeWhereHasMeta( Builder $q, $key ) {
		$q->whereHas(
			'meta',
			function ( Builder $q ) use ( $key ) {
				$q->whereIn( 'meta_key', (array) $key );
			}
		);

		return $q;
	}

	/**
	 * Query scope to restrict the query to records which doesnt have `Meta` attached to a given key.
	 * If an array of keys is passed instead, will restrict the query to records having one or more Meta with any of the keys.
	 *
	 * @param Builder      $q
	 * @param string|array $key
	 *
	 * @return Builder
	 */
	public function scopeWhereDoesntHaveMeta( Builder $q, $key ) {
		$q->whereDoesntHave(
			'meta',
			function ( Builder $q ) use ( $key ) {
				$q->whereIn( 'meta_key', (array) $key );
			}
		);

		return $q;
	}

	/**
	 * Query scope to restrict the query to records which have `Meta` for all of the provided keys.
	 *
	 * @param Builder $q
	 * @param array   $keys
	 *
	 * @return Builder
	 */
	public function scopeWhereHasMetaKeys( Builder $q, array $keys ) {
		$q->whereHas(
			'meta',
			function ( Builder $q ) use ( $keys ) {
				$q->whereIn( 'meta_key', $keys );
			},
			'=',
			count( $keys )
		);

		return $q;
	}

	/**
	 * Query scope to restrict the query to records which have `Meta` with a specific key and value.
	 * If the `$value` parameter is omitted, the $operator parameter will be considered the value.
	 *
	 * Values will be serialized to a string before comparison. If using the `>`, `>=`, `<`, or `<=` comparison operators, note that the value will be compared as a string.
	 *
	 * If comparing numeric values, use `scopeWhereMetaNumeric()` instead.
	 *
	 * @param Builder $query
	 * @param string  $meta
	 * @param mixed   $value
	 * @param string  $operator
	 * @param bool    $orWhere
	 * @return Builder
	 */
	public function scopeWhereMeta( Builder $query, $meta, $value = null, string $operator = '=', $orWhere = false ) {
		if ( ! is_array( $meta ) ) {
			$meta = [ $meta => $value ];
		}

		$type = 'whereHas';
		if ( $orWhere ) {
			$type = 'orWhereHas';
		}

		foreach ( $meta as $key => $value ) {
			$query->{ $type }(
				'meta',
				function ( Builder $query ) use ( $key, $value, $operator ) {
					if ( ! is_string( $key ) ) {
						return $query->where( 'meta_key', $operator, $value );
					}
					$query->where( 'meta_key', $operator, $key );

					return is_null( $value ) ? $query : $query->where( 'meta_value', $operator, maybe_serialize( $value ) );
				}
			);
		}

		return $query;
	}

	/**
	 * Same as `scopeWhereMeta()` but uses the `orWhereHas` clause.
	 *
	 * @param Builder $query
	 * @param string  $meta
	 * @param mixed   $value
	 * @param string  $operator
	 * @return Builder
	 */
	public function scopeOrWhereMeta( Builder $query, $meta, $value = null, string $operator = '=' ) {
		return $this->scopeWhereMeta( $query, $meta, $value, $operator, true );
	}

	/**
	 * Query scope to restrict the query to records which have `Meta` with a specific key and numeric value.
	 *
	 * Performs numeric comparison instead of string comparison.
	 *
	 * @param Builder   $q
	 * @param string    $key
	 * @param string    $operator
	 * @param int|float $value
	 *
	 * @return Builder
	 */
	public function scopeWhereMetaNumeric( Builder $q, string $key, string $operator, $value ) {
		// Since we are manually interpolating into the query,
		// escape the operator to protect against injection.
		$validOperators = [ '<', '<=', '>', '>=', '=', '<>', '!=' ];
		$operator       = in_array( $operator, $validOperators, true ) ? $operator : '=';
		$field          = $q->getQuery()
			->getGrammar()
			->wrap( $this->meta()->getRelated()->getTable() . '.meta_value' );

		$q->whereHas(
			'meta',
			function ( Builder $q ) use ( $key, $operator, $value, $field ) {
				$q->where( 'meta_key', $key );
				$q->whereRaw( "cast({$field} as decimal) {$operator} ?", [ (float) $value ] );
			}
		);

		return $q;
	}

	/**
	 * Query scope to restrict the query to records which have `Meta` with a specific key and a value within a specified set of options.
	 *
	 * @param Builder $q
	 * @param string  $key
	 * @param array   $values
	 * @return Builder
	 */
	public function scopeWhereMetaIn( Builder $q, string $key, array $values, $orWhere = false ) {
		$type = 'whereHas';

		if ( $orWhere ) {
			$type = 'orWhereHas';
		}

		$q->{ $type }(
			'meta',
			function ( Builder $q ) use ( $key, $values ) {
				$q->where( 'meta_key', $key );
				$q->whereIn( 'meta_value', $values );
			}
		);

		return $q;
	}

	/**
	 * Filter items that have one of the given values ( or clause ).
	 *
	 * @param Builder $query
	 * @param string  $key
	 * @param array   $values
	 * @return Builder
	 */
	public function scopeOrWhereMetaIn( Builder $query, string $key, array $values = [] ) {
		return $this->scopeWhereMetaIn( $query, $key, $values, true );
	}

	/**
	 * Clause for filter items that has given meta and value is between defined values.
	 *
	 * @param Builder $query
	 * @param string  $key
	 * @param array   $values of min and max value
	 * @param boolean $orWhere
	 * @return Builder
	 */
	public function scopeWhereMetaBetween( Builder $query, $key, $values = [ 0, 100 ], $orWhere = false ) {
		if ( count( $values ) !== 2 || ! isset( $values[0] ) || ! isset( $values[1] ) || ! is_int( $values[0] ) || ! is_int( $values[1] ) ) {
			return $query;
		}
		$type = 'whereHas';
		if ( $orWhere ) {
			$type = 'orWhereHas';
		}
		$query->{ $type }(
			'meta',
			function ( $query ) use ( $key, $values ) {
				$query->where( 'meta_key', $key );
				$query->whereBetween( 'meta_value', $values );
			}
		);
		return $query;
	}

	/**
	 * Clause (or) for filter items that has given meta and value is between defined values.
	 *
	 * @param Builder $query
	 * @param string  $key
	 * @param array   $values of min and max value
	 * @return Builder
	 */
	public function scopeOrWhereMetaBetween( Builder $query, string $key, array $values = [ 0, 100 ] ) {
		return $this->scopeWhereMetaBetween( $query, $key, $values, true );
	}

	/**
	 * Clause for filter items that has given meta and value is  not between defined values.
	 *
	 * @param Builder $query
	 * @param string  $key
	 * @param array   $values of min and max value
	 * @param boolean $orWhere
	 * @return Builder
	 */
	public function scopeWhereMetaNotBetween( Builder $query, string $key, array $values = [ 0, 100 ], $orWhere = false ) {
		if ( count( $values ) !== 2 || ! isset( $values[0] ) || ! isset( $values[1] ) || ! is_int( $values[0] ) || ! is_int( $values[1] ) ) {
			return $query;
		}
		$type = 'whereHas';
		if ( $orWhere ) {
			$type = 'orWhereHas';
		}
		$query->{$type}(
			'meta',
			function ( $query ) use ( $key, $values ) {
				$query->where( 'meta_key', $key );
				$query->whereNotBetween( 'meta_value', $values );
			}
		);
		return $query;
	}

	/**
	 * Clause (or) for filter items that has given meta and value is not between defined values.
	 *
	 * @param Builder $query
	 * @param string  $key
	 * @param array   $values of min and max value
	 * @return Builder
	 */
	public function scopeOrWhereMetaNotBetween( Builder $query, string $key, array $values = [ 0, 100 ] ) {
		return $this->scopeWhereMetaNotBetween( $query, $key, $values, true );
	}

	/**
	 * Filter items that don't have one of the given values.
	 *
	 * @param Builder $query
	 * @param string  $key
	 * @param array   $values
	 * @param boolean $orWhere
	 * @return Builder
	 */
	public function scopeWhereMetaNotIn( Builder $query, string $key, array $values = [], $orWhere = false ) {
		if ( ! is_array( $values ) ) {
			return $query;
		}
		$type = 'whereHas';
		if ( $orWhere ) {
			$type = 'orWhereHas';
		}
		$query->{$type}(
			'meta',
			function ( $query ) use ( $key, $values ) {
				$query->where( 'meta_key', $key );
				$query->whereNotIn( 'meta_value', $values );
			}
		);
		return $query;
	}

	/**
	 * Filter items that don't have one of the given values ( or clause ).
	 *
	 * @param Builder $query
	 * @param string  $key
	 * @param array   $values
	 * @return Builder
	 */
	public function scopeOrWhereMetaNotIn( Builder $query, string $key, array $values = [] ) {
		return $this->scopeWhereMetaNotIn( $query, $key, $values, true );
	}

	/**
	 * Query scope to order the query results by the string value of an attached meta.
	 *
	 * @param Builder $q
	 * @param string  $key
	 * @param string  $direction
	 * @param bool    $strict if true, will exclude records that do not have meta for the provided `$key`.
	 * @return Builder
	 */
	public function scopeOrderByMeta(
		Builder $q,
		string $key,
		string $direction = 'asc',
		bool $strict = false
	) {
		$table = $this->joinMetaTable( $q, $key, $strict ? 'inner' : 'left' );
		$q->orderBy( "{$table}.meta_value", $direction );

		return $q;
	}

	/**
	 * Query scope to order the query results by the numeric value of an attached meta.
	 *
	 * @param Builder $q
	 * @param string  $key
	 * @param string  $direction
	 * @param bool    $strict if true, will exclude records that do not have meta for the provided `$key`.
	 *
	 * @return Builder
	 */
	public function scopeOrderByMetaNumeric(
		Builder $q,
		string $key,
		string $direction = 'asc',
		bool $strict = false
	) {
		$table     = $this->joinMetaTable( $q, $key, $strict ? 'inner' : 'left' );
		$direction = strtolower( $direction ) == 'asc' ? 'asc' : 'desc';
		$field     = $q->getQuery()->getGrammar()->wrap( "{$table}.meta_value" );

		$q->orderByRaw( "cast({$field} as decimal) $direction" );

		return $q;
	}

	/**
	 * Join the meta table to the query.
	 *
	 * @param Builder $q
	 * @param string  $key
	 * @param string  $type Join type.
	 *
	 * @return string
	 */
	private function joinMetaTable( Builder $q, string $key, $type = 'left' ) {
		$relation  = $this->meta();
		$metaTable = $relation->getRelated()->getTable();

		// Create an alias for the join, to allow the same
		// table to be joined multiple times for different keys.
		$alias = $metaTable . '__' . $key;

		// If no explicit select columns are specified,
		// avoid column collision by excluding meta table from select.
		if ( ! $q->getQuery()->columns ) {
			$q->select( $this->getTable() . '.*' );
		}

		// Join the meta table to the query
		$q->join(
			"{$metaTable} as {$alias}",
			function ( JoinClause $q ) use ( $relation, $key, $alias ) {
				$q->on( $relation->getQualifiedParentKeyName(), '=', $alias . '.' . $relation->getForeignKeyName() )
				->where( $alias . '.meta_key', '=', $key );
			},
			null,
			null,
			$type
		);

		// Return the alias so that the calling context can
		// reference the table.
		return $alias;
	}

}

<?php
/**
 * The where functions.
 *
 * @since      1.0.0
 * @package    MyThemeShop
 * @subpackage MyThemeShop\Database
 * @author     MyThemeShop <admin@mythemeshop.com>
 */

namespace MyThemeShop\Database;

/**
 * Where class.
 */
trait Where {

	/**
	 * Create a where statement
	 *
	 *     ->where('name', 'ladina')
	 *     ->where('age', '>', 18)
	 *     ->where('name', 'in', ['charles', 'john', 'jeffry'])
	 *
	 * @throws \Exception If $type is not 'and', 'or', 'where'.
	 *
	 * @param mixed  $column The SQL column.
	 * @param mixed  $param1 Operator or value depending if $param2 isset.
	 * @param mixed  $param2 The value if $param1 is an operator.
	 * @param string $type the where type ( and, or ).
	 *
	 * @return self The current query builder.
	 */
	public function where( $column, $param1 = null, $param2 = null, $type = 'and' ) {

		$this->is_valid_type( $type );

		$sub_type = is_null( $param1 ) ? $type : $param1;
		if ( empty( $this->statements['wheres'] ) ) {
			$type = 'where';
		}

		// When column is an array we assume to make a bulk and where.
		if ( is_array( $column ) ) {
			$this->bulk_where( $column, $type, $sub_type );
			return $this;
		}

		$this->statements['wheres'][] = $this->generateWhere( $column, $param1, $param2, $type );

		return $this;
	}

	/**
	 * Create an or where statement
	 *
	 * @param string $column The SQL column.
	 * @param mixed  $param1 Operator or value depending if $param2 isset.
	 * @param mixed  $param2 The value if $param1 is an operator.
	 *
	 * @return self The current query builder.
	 */
	public function orWhere( $column, $param1 = null, $param2 = null ) { // @codingStandardsIgnoreLine
		return $this->where( $column, $param1, $param2, 'or' );
	}

	/**
	 * Creates a where in statement
	 *
	 *     ->whereIn('id', [42, 38, 12])
	 *
	 * @param string $column  The SQL column.
	 * @param array  $options Array of values for in statement.
	 *
	 * @return self The current query builder.
	 */
	public function whereIn( $column, $options ) { // @codingStandardsIgnoreLine
		return $this->where( $column, 'in', $options );
	}

	/**
	 * Creates a where in statement
	 *
	 *     ->orWhereIn('id', [42, 38, 12])
	 *
	 * @param string $column  The SQL column.
	 * @param array  $options Array of values for in statement.
	 *
	 * @return self The current query builder.
	 */
	public function orWhereIn( $column, $options ) { // @codingStandardsIgnoreLine
		return $this->where( $column, 'in', $options, 'or' );
	}

	/**
	 * Creates a where not in statement
	 *
	 *     ->whereNotIn('id', [42, 38, 12])
	 *
	 * @param string $column  The SQL column.
	 * @param array  $options Array of values for in statement.
	 *
	 * @return self The current query builder.
	 */
	public function whereNotIn( $column, $options ) { // @codingStandardsIgnoreLine
		return $this->where( $column, 'not in', $options );
	}

	/**
	 * Creates a where not in statement
	 *
	 *     ->orWhereNotIn('id', [42, 38, 12])
	 *
	 * @param string $column  The SQL column.
	 * @param array  $options Array of values for in statement.
	 *
	 * @return self The current query builder.
	 */
	public function orWhereNotIn( $column, $options ) { // @codingStandardsIgnoreLine
		return $this->where( $column, 'not in', $options, 'or' );
	}

	/**
	 * Creates a where between statement
	 *
	 *     ->whereBetween('id', [10, 100])
	 *
	 * @param string $column  The SQL column.
	 * @param array  $options Array of values for in statement.
	 *
	 * @return self The current query builder.
	 */
	public function whereBetween( $column, $options ) { // @codingStandardsIgnoreLine
		return $this->where( $column, 'between', $options );
	}

	/**
	 * Creates a where between statement
	 *
	 *     ->orWhereBetween('id', [10, 100])
	 *
	 * @param string $column  The SQL column.
	 * @param array  $options Array of values for in statement.
	 *
	 * @return self The current query builder.
	 */
	public function orWhereBetween( $column, $options ) { // @codingStandardsIgnoreLine
		return $this->where( $column, 'between', $options, 'or' );
	}

	/**
	 * Creates a where not between statement
	 *
	 *     ->whereNotBetween('id', [10, 100])
	 *
	 * @param string $column  The SQL column.
	 * @param array  $options Array of values for in statement.
	 *
	 * @return self The current query builder.
	 */
	public function whereNotBetween( $column, $options ) { // @codingStandardsIgnoreLine
		return $this->where( $column, 'not between', $options );
	}

	/**
	 * Creates a where not between statement
	 *
	 *     ->orWhereNotBetween('id', [10, 100])
	 *
	 * @param string $column  The SQL column.
	 * @param array  $options Array of values for in statement.
	 *
	 * @return self The current query builder.
	 */
	public function orWhereNotBetween( $column, $options ) { // @codingStandardsIgnoreLine
		return $this->where( $column, 'not between', $options, 'or' );
	}

	/**
	 * Creates a where like statement
	 *
	 *     ->whereLike('id', 'value')
	 *
	 * @codeCoverageIgnore
	 *
	 * @param string $column The SQL column.
	 * @param string $value  Value for like statement.
	 * @param string $start  (Optional) The start of like query.
	 * @param string $end    (Optional) The end of like query.
	 *
	 * @return self The current query builder.
	 */
	public function whereLike( $column, $value, $start = '%', $end = '%' ) { // @codingStandardsIgnoreLine
		return $this->where( $column, 'like', $this->esc_like( $value, $start, $end ) );
	}

	/**
	 * Creates a where like statement
	 *
	 *     ->orWhereLike('id', 'value')
	 *
	 * @codeCoverageIgnore
	 *
	 * @param string $column The SQL column.
	 * @param string $value  Value for like statement.
	 * @param string $start  (Optional) The start of like query.
	 * @param string $end    (Optional) The end of like query.
	 *
	 * @return self The current query builder.
	 */
	public function orWhereLike( $column, $value, $start = '%', $end = '%' ) { // @codingStandardsIgnoreLine
		return $this->where( $column, 'like', $this->esc_like( $value, $start, $end ), 'or' );
	}

	/**
	 * Creates a where not like statement
	 *
	 *     ->whereNotLike('id', 'value' )
	 *
	 * @codeCoverageIgnore
	 *
	 * @param string $column The SQL column.
	 * @param mixed  $value  Value for like statement.
	 * @param string $start  (Optional) The start of like query.
	 * @param string $end    (Optional) The end of like query.
	 *
	 * @return self The current query builder.
	 */
	public function whereNotLike( $column, $value, $start = '%', $end = '%' ) { // @codingStandardsIgnoreLine
		return $this->where( $column, 'not like', $this->esc_like( $value, $start, $end ) );
	}

	/**
	 * Creates a where not like statement
	 *
	 *     ->orWhereNotLike('id', 'value' )
	 *
	 * @codeCoverageIgnore
	 *
	 * @param string $column The SQL column.
	 * @param mixed  $value  Value for like statement.
	 * @param string $start  (Optional) The start of like query.
	 * @param string $end    (Optional) The end of like query.
	 *
	 * @return self The current query builder.
	 */
	public function orWhereNotLike( $column, $value, $start = '%', $end = '%' ) { // @codingStandardsIgnoreLine
		return $this->where( $column, 'not like', $this->esc_like( $value, $start, $end ), 'or' );
	}

	/**
	 * Creates a where is null statement
	 *
	 *     ->whereNull( 'name' )
	 *
	 * @param string $column The SQL column.
	 *
	 * @return self The current query builder.
	 */
	public function whereNull( $column ) { // @codingStandardsIgnoreLine
		return $this->where( $column, 'is', 'null' );
	}

	/**
	 * Creates a where is null statement
	 *
	 *     ->orWhereNull( 'name' )
	 *
	 * @param string $column The SQL column.
	 *
	 * @return self The current query builder.
	 */
	public function orWhereNull( $column ) { // @codingStandardsIgnoreLine
		return $this->where( $column, 'is', 'null', 'or' );
	}

	/**
	 * Creates a where is not null statement
	 *
	 *     ->whereNotNull( 'name' )
	 *
	 * @param string $column The SQL column.
	 *
	 * @return self The current query builder.
	 */
	public function whereNotNull( $column ) { // @codingStandardsIgnoreLine
		return $this->where( $column, 'is not', 'null' );
	}

	/**
	 * Creates a where is not null statement
	 *
	 *     ->orWhereNotNull( 'name' )
	 *
	 * @param string $column The SQL column.
	 *
	 * @return self The current query builder.
	 */
	public function orWhereNotNull( $column ) { // @codingStandardsIgnoreLine
		return $this->where( $column, 'is not', 'null', 'or' );
	}

	/**
	 * Generate Where clause
	 *
	 * @param string $column The SQL column.
	 * @param mixed  $param1 Operator or value depending if $param2 isset.
	 * @param mixed  $param2 The value if $param1 is an operator.
	 * @param string $type the where type ( and, or ).
	 *
	 * @return string
	 */
	protected function generateWhere( $column, $param1 = null, $param2 = null, $type = 'and' ) { // @codingStandardsIgnoreLine

		// when param2 is null we replace param2 with param one as the
		// value holder and make param1 to the = operator.
		if ( is_null( $param2 ) ) {
			$param2 = $param1;
			$param1 = '=';
		}

		// When param2 is an array we probably
		// have an "in" or "between" statement which has no need for duplicates.
		if ( is_array( $param2 ) ) {
			$param2 = $this->esc_array( array_unique( $param2 ) );
			$param2 = in_array( $param1, [ 'between', 'not between' ] ) ? join( ' and ', $param2 ) : '(' . join( ', ', $param2 ) . ')';
		} elseif ( is_scalar( $param2 ) ) {
			$param2 = $this->esc_value( $param2 );
		}

		return join( ' ', [ $type, $column, $param1, $param2 ] );
	}

	/**
	 * Check if the where type is valid.
	 *
	 * @param string $type Value to check.
	 *
	 * @throws \Exception If not a valid type.
	 */
	private function is_valid_type( $type ) {
		if ( ! in_array( $type, [ 'and', 'or', 'where' ] ) ) {
			throw new \Exception( 'Invalid where type "' . $type . '"' );
		}
	}

	/**
	 * Create bulk where statement.
	 *
	 * @param array  $wheres   Array of statments.
	 * @param string $type     Statement type.
	 * @param string $sub_type Statement sub-type.
	 */
	private function bulk_where( $wheres, $type, $sub_type ) {
		$subquery = [];
		foreach ( $wheres as $value ) {
			if ( ! isset( $value[2] ) ) {
				$value[2] = $value[1];
				$value[1] = '=';
			}
			$subquery[] = $this->generateWhere( $value[0], $value[1], $value[2], empty( $subquery ) ? '' : $sub_type );
		}

		$this->statements['wheres'][] = $type . ' ( ' . trim( join( ' ', $subquery ) ) . ' )';
	}
}

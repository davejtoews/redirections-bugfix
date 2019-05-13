<?php
/**
 * The Options helpers.
 *
 * @since      1.0.0
 * @package    RankMath
 * @subpackage RankMath_Redirections\Helpers
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath_Redirections\Helpers;

defined( 'ABSPATH' ) || exit;

/**
 * Options class.
 */
trait Options {

	/**
	 * Option handler.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param  string $key   Option to perform action.
	 * @param  mixed  $value Pass null to get option,
	 *                       Pass false to delete option,
	 *                       Pass value to update option.
	 * @return mixed
	 */
	public static function option( $key, $value = null ) {
		$key = 'rank_math_' . $key;

		if ( false === $value ) {
			return delete_option( $key );
		}

		if ( is_null( $value ) ) {
			return get_option( $key, [] );
		}

		return update_option( $key, $value );
	}

	/**
	 * Normalize option value.
	 *
	 * @param mixed $value Value to normalize.
	 * @return mixed
	 */
	public static function normalize_data( $value ) {

		if ( 'true' === $value || 'on' === $value ) {
			$value = true;
		} elseif ( 'false' === $value || 'off' === $value ) {
			$value = false;
		} elseif ( '0' === $value || '1' === $value ) {
			$value = intval( $value );
		}

		return $value;
	}
}

<?php
/**
 * Helper Functions.
 *
 * @since      1.0.0
 * @package    RankMath
 * @subpackage RankMath_Redirections\Core
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath_Redirections;

use RankMath_Redirections\Helpers\Api;
use RankMath_Redirections\Helpers\Conditional;
use RankMath_Redirections\Helpers\Options;
use RankMath_Redirections\Helpers\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Helper class.
 */
class Helper {

	use Conditional, Options, WordPress;

	/**
	 * Get comparison types.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return array
	 */
	public static function choices_comparison_types() {
		return array(
			'exact'    => esc_html__( 'Exact', 'redirections' ),
			'contains' => esc_html__( 'Contains', 'redirections' ),
			'start'    => esc_html__( 'Starts With', 'redirections' ),
			'end'      => esc_html__( 'End With', 'redirections' ),
			'regex'    => esc_html__( 'Regex', 'redirections' ),
		);
	}

	/**
	 * Get current page full url.
	 *
	 * @param  bool $ignore_qs Ignore Query String.
	 * @return string
	 */
	public static function get_current_page_url( $ignore_qs = false ) {
		$link = '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$link = ( is_ssl() ? 'https' : 'http' ) . $link;

		if ( $ignore_qs ) {
			$link = explode( '?', $link );
			$link = $link[0];
		}

		return $link;
	}

	/**
	 * Add notification.
	 *
	 * @param string $message Message string.
	 * @param array  $options Set of options.
	 */
	public static function add_notification( $message, $options = [] ) {
		rank_math_redirection()->notification->add( $message, $options );
	}

	/**
	 * Add notification.
	 *
	 * @param string $notification_id Notification id.
	 */
	public static function remove_notification( $notification_id ) {
		rank_math_redirection()->notification->remove_by_id( $notification_id );
	}

	/**
	 * Get Setting.
	 *
	 * @param  string $field_id The field id to get value for.
	 * @param  mixed  $default  The default value if no field found.
	 * @return mixed
	 */
	public static function get_settings( $field_id = '', $default = false ) {
		return rank_math_redirection()->settings->get( $field_id, $default );
	}

	/**
	 * Add something to JSON object.
	 *
	 * @param string $key         Unique identifier.
	 * @param mixed  $value       The data itself can be either a single or an array.
	 * @param string $object_name Name for the JavaScript object. Passed directly, so it should be qualified JS variable.
	 */
	public static function add_json( $key, $value, $object_name = 'rankMath' ) {
		rank_math_redirection()->json->add( $key, $value, $object_name );
	}

	/**
	 * Remove something from JSON object.
	 *
	 * @param string $key         Unique identifier.
	 * @param string $object_name Name for the JavaScript object. Passed directly, so it should be qualified JS variable.
	 */
	public static function remove_json( $key, $object_name = 'rankMath' ) {
		rank_math_redirection()->json->remove( $key, $object_name );
	}

		/**
	 * Gets redirection types.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return array
	 */
	public static function choices_redirection_types() {
		return array(
			'301' => esc_html__( '301 Permanent Move', 'redirections' ),
			'302' => esc_html__( '302 Temporary Move', 'redirections' ),
			'307' => esc_html__( '307 Temporary Redirect', 'redirections' ),
			'410' => esc_html__( '410 Content Deleted', 'redirections' ),
			'451' => esc_html__( '451 Content Unavailable for Legal Reasons', 'redirections' ),
		);
	}

	/**
	 * Get module by id.
	 *
	 * @param  string $id ID to get module.
	 * @return object Module class object.
	 */
	public static function get_module( $id ) {
		return rank_math_redirection()->manager->get_module( $id );
	}
}

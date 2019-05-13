<?php
/**
 * The WordPress helpers.
 *
 * @since      1.0.0
 * @package    RankMath
 * @subpackage RankMath_Redirections\Helpers
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath_Redirections\Helpers;

use RankMath_Redirections\Post;
use RankMath_Redirections\Term;
use RankMath_Redirections\User;
use RankMath_Redirections\Helper;
use MyThemeShop\Helpers\WordPress as WP_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * WordPress class.
 */
trait WordPress {

	/**
	 * Get admin url.
	 *
	 * @param  string $page Page id.
	 * @param  array  $args Pass arguments to query string.
	 * @return string
	 */
	public static function get_admin_url( $page = '', $args = array() ) {
		$page = $page ? 'rank-math-' . $page : 'rank-math-redirections';
		$args = wp_parse_args( $args, array( 'page' => $page ) );

		return add_query_arg( $args, admin_url( 'admin.php' ) );
	}

	/**
	 * Check if plugin is network active
	 *
	 * @codeCoverageIgnore
	 *
	 * @return boolean
	 */
	public static function is_plugin_active_for_network() {
		if ( ! is_multisite() ) {
			return false;
		}

		// Makes sure the plugin is defined before trying to use it.
		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}

		if ( ! is_plugin_active_for_network( plugin_basename( RANK_MATH_REDIRECTIONS_FILE ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Gets post type label.
	 *
	 * @param  string $post_type Post type name.
	 * @param  bool   $singular  Get singular label.
	 * @return string|false
	 */
	public static function get_post_type_label( $post_type, $singular = false ) {
		$object = get_post_type_object( $post_type );
		if ( ! $object ) {
			return false;
		}
		return ! $singular ? $object->labels->name : $object->labels->singular_name;
	}
}

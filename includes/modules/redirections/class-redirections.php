<?php
/**
 * The Redirections Module
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath_Redirections\Redirections
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath_Redirections\Redirections;

use RankMath_Redirections\Helper;
use RankMath_Redirections\Traits\Hooker;
use MyThemeShop\Helpers\Conditional;

/**
 * Redirections class.
 *
 * @codeCoverageIgnore
 */
class Redirections {

	use Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		if ( is_admin() ) {
			$this->admin = new Admin;
		} else {
			$this->action( 'wp', 'do_redirection' );
		}

		if ( is_admin() || Conditional::is_rest() ) {
			new Watcher;
		}

		$this->filter( 'rank_math/admin_bar/items', 'admin_bar_items', 11 );
		$this->filter( 'rank_math/help/tabs', 'help_tabs', 11 );

		// Disable Auto-Redirect.
		if ( get_option( 'permalink_structure' ) && Helper::get_settings( 'general.redirections_post_redirect' ) ) {
			remove_action( 'template_redirect', 'wp_old_slug_redirect' );
		}
	}

	/**
	 * Add help tab into help page.
	 *
	 * @param array $tabs Array of tabs.
	 * @return array
	 */
	public function help_tabs( $tabs ) {
		$tabs['redirections'] = [
			'title' => esc_html__( 'Redirections', 'rank-math' ),
			'view'  => dirname( __FILE__ ) . '/views/help.php',
		];

		return $tabs;
	}

	/**
	 * Do redirection on frontend.
	 */
	public function do_redirection() {
		if ( is_customize_preview() || wp_doing_ajax() || ! isset( $_SERVER['REQUEST_URI'] ) || empty( $_SERVER['REQUEST_URI'] ) || $this->is_script_uri_or_http_x() ) {
			return;
		}

		$redirector = new Redirector;
	}

	/**
	 * Add admin bar item.
	 *
	 * @param array $items Array of admin bar nodes.
	 * @return array
	 */
	public function admin_bar_items( $items ) {

		$items['redirections'] = [
			'id'        => 'rank-math-redirections',
			'title'     => esc_html__( 'Redirections', 'redirections' ),
			'href'      => Helper::get_admin_url( 'redirections' ),
			'parent'    => 'redirections',
			'meta'      => [ 'title' => esc_html__( 'Create and edit redirections', 'redirections' ) ],
			'_priority' => 50,
		];

		$items['redirections-child'] = [
			'id'        => 'rank-math-redirections-child',
			'title'     => esc_html__( 'Manage Redirections', 'redirections' ),
			'href'      => Helper::get_admin_url( 'redirections' ),
			'parent'    => 'rank-math-redirections',
			'meta'      => [ 'title' => esc_html__( 'Create and edit redirections', 'redirections' ) ],
			'_priority' => 51,
		];

		$items['redirections-settings'] = [
			'id'        => 'rank-math-redirections-settings',
			'title'     => esc_html__( 'Redirection Settings', 'redirections' ),
			'href'      => Helper::get_admin_url( 'options-general' ) . '#setting-panel-redirections',
			'parent'    => 'rank-math-redirections',
			'meta'      => [ 'title' => esc_html__( 'Redirection Settings', 'redirections' ) ],
			'_priority' => 52,
		];

		if ( ! is_admin() ) {
			$items['redirections-redirect-me'] = [
				'id'        => 'rank-math-redirections-redirect-me',
				'title'     => esc_html__( '&raquo; Redirect this page', 'redirections' ),
				'href'      => add_query_arg( 'url', urlencode( ltrim( $_SERVER['REQUEST_URI'], '/' ) ), Helper::get_admin_url( 'redirections' ) ),
				'parent'    => 'rank-math-redirections',
				'meta'      => [ 'title' => esc_html__( 'Redirect the current URL', 'redirections' ) ],
				'_priority' => 53,
			];
		}

		return $items;
	}

	/**
	 * Is script uri or http-x request
	 *
	 * @return boolean
	 */
	private function is_script_uri_or_http_x() {
		if ( isset( $_SERVER['SCRIPT_URI'] ) && ! empty( $_SERVER['SCRIPT_URI'] ) && admin_url( 'admin-ajax.php' ) === $_SERVER['SCRIPT_URI'] ) {
			return true;
		}

		if ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && ! empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest' ) {
			return true;
		}

		return false;
	}
}

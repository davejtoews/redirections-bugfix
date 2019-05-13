<?php
/**
 * The option center of the plugin.
 *
 * @since      1.0.9
 * @package    RankMath
 * @subpackage RankMath_Redirections\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath_Redirections\Admin;

use RankMath_Redirections\CMB2;
use RankMath_Redirections\Helper;
use RankMath_Redirections\Runner;
use RankMath_Redirections\Traits\Hooker;
use MyThemeShop\Helpers\Arr;
use MyThemeShop\Helpers\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Option_Center class.
 */
class Option_Center implements Runner {

	use Hooker;

	/**
	 * Register hooks.
	 */
	public function hooks() {
		if ( ! Helper::is_404_monitor_active() ) {
			$this->action( 'init', 'register_general_settings', 125 );
		}
	}

	public function add_redirections( $tabs ) {
		$tabs['redirections'] =  [
			'icon'  => 'dashicons dashicons-no',
			'title' => esc_html__( 'Redirections', 'redirections' ),
			/* translators: 1. Link to kb article 2. Link to redirection setting scree */
			'desc'  => sprintf( esc_html__( 'Enable Redirections to set up custom 301, 302, 307, 410, or 451 redirections. %s.', 'redirections' ), '<a href="https://rankmath.com/kb/general-settings/#redirections" target="_blank">' . esc_html__( 'Learn more', 'redirections' ) . '</a>' ),
		];
		return $tabs;
	}

	/**
	 * General Settings.
	 */
	public function register_general_settings() {
		$tabs = [
			'redirections' => [
				'icon'  => 'dashicons dashicons-no',
				//'title' => esc_html__( 'Redirections', 'redirections' ), */
				/* translators: 1. Link to kb article 2. Link to redirection setting scree */
				'desc'  => sprintf( esc_html__( 'Enable Redirections to set up custom 301, 302, 307, 410, or 451 redirections. %s.', 'redirections' ), '<a href="https://rankmath.com/kb/general-settings/#redirections" target="_blank">' . esc_html__( 'Learn more', 'redirections' ) . '</a>' ),
			],
		];

		/**
		 * Allow developers to add new section into general setting option panel.
		 *
		 * @param array $tabs
		 */
		$tabs = apply_filters( 'rank_math/settings/general', $tabs );

		new Options([
			'key'        => 'rank-math-options-general',
			'title'      => esc_html__( 'Redirections', 'redirections' ),
			'menu_title' => esc_html__( 'General Settings', 'redirections' ),
			'folder'     => 'general',
			'tabs'       => $tabs,
		]);
	}
}

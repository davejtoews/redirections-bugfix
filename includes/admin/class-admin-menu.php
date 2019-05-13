<?php
/**
 * The admin pages of the plugin.
 *
 * @since      1.0.9
 * @package    RankMath
 * @subpackage RankMath_Redirections\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath_Redirections\Admin;

use RankMath_Redirections\Runner;
use RankMath_Redirections\Traits\Hooker;
use MyThemeShop\Admin\Page;
use RankMath_Redirections\Helper as GlobalHelper;

defined( 'ABSPATH' ) || exit;

/**
 * Admin_Menu class.
 *
 * @codeCoverageIgnore
 */
class Admin_Menu implements Runner {

	use Hooker;

	/**
	 * Register hooks.
	 */
	public function hooks() {

		if ( ! GlobalHelper::is_404_monitor_active() ) {
			$this->action( 'init', 'register_pages' );
		}
		$this->action( 'admin_menu', 'fix_first_submenu', 999 );
		$this->action( 'admin_head', 'icon_css' );
	}

	/**
	 * Register admin pages for plugin.
	 */
	public function register_pages() {

		// Dashboard / Welcome / About.
		new Page( 'rank-math-redirection', esc_html__( 'Rank Math', 'redirections' ), array(
			'position'   => 80,
			'capability' => 'manage_options',
			'icon'       => 'dashicons-chart-area',
			'render'     => Admin_Helper::get_view( 'dashboard' ),
			'classes'    => array( 'rank-math-page' ),
			'assets'     => array(
				'styles'  => array( 'rank-math-dashboard' => '' ),
				'scripts' => array( 'rank-math-dashboard' => '' ),
			),
			'is_network' => is_network_admin() && GlobalHelper::is_plugin_active_for_network(),
		));

		// Help & Support.
		new Page( 'rank-math-help', esc_html__( 'Help &amp; Support', 'redirections' ), array(
			'position'   => 99,
			'parent'     => 'rank-math-redirection',
			'capability' => 'level_1',
			'classes'    => array( 'rank-math-page' ),
			'render'     => Admin_Helper::get_view( 'help-manager' ),
			'assets'     => array(
				'styles'  => array( 'rank-math-common' => '', 'rank-math-dashboard' => '' ),
				'scripts' => array( 'rank-math-common' => '' ),
			),
		));
	}

	/**
	 * Fix first submenu name.
	 *
	 * @TODO Why are we unsetting [0] and why we are saving transient.
	 */
	public function fix_first_submenu() {
		global $submenu;
		if ( ! isset( $submenu['rank-math-redirection'] ) ) {
			return;
		}

		if ( current_user_can( 'manage_options' ) && 'Rank Math' === $submenu['rank-math-redirection'][0][0] ) {
			$submenu['rank-math-redirection'][0][0] = esc_html__( 'Dashboard', 'redirections' );
		} else {
			unset( $submenu['rank-math-redirection'][0] );
		}

		if ( empty( $submenu['rank-math-redirection'] ) ) {
			return;
		}

		// Store ID of first_menu item so we can use it in the Admin menu item.
		set_transient( 'rank_math_first_submenu_id', array_values( $submenu['rank-math-redirection'] )[0][2] );
	}

	/**
	 * Print icon CSS for admin menu bar.
	 */
	public function icon_css() {
		?>
		<style>
			#wp-admin-bar-rank-math .rank-math-icon {
				display: inline-block;
				top: 6px;
				position: relative;
				padding-right: 10px;
				max-width: 20px;
			}
			#wp-admin-bar-rank-math .rank-math-icon svg {
				fill-rule: evenodd;
				fill: #dedede;
			}
			#wp-admin-bar-rank-math:hover .rank-math-icon svg {
				fill-rule: evenodd;
				fill: #00b9eb;
			}
		</style>
		<?php
	}
}

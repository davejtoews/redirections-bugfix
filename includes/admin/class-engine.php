<?php
/**
 * The admin engine of the plugin.
 *
 * @since      1.0.9
 * @package    RankMath
 * @subpackage RankMath_Redirections\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath_Redirections\Admin;

use RankMath_Redirections\Helper;
use RankMath_Redirections\Traits\Hooker;
use MyThemeShop\Helpers\Conditional;
use RankMath_Redirections\Search_Console\Search_Console;

defined( 'ABSPATH' ) || exit;

/**
 * Engine class.
 *
 * @codeCoverageIgnore
 */
class Engine {

	use Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {

		rank_math_redirection()->admin        = new Admin;
		rank_math_redirection()->admin_assets = new Assets;

		$runners = array(
			rank_math_redirection()->admin,
			rank_math_redirection()->admin_assets,
			new Admin_Menu,
			new Option_Center,
			new Import_Export,
			new CMB2_Fields,
			new Deactivate_Survey,
			new Watcher,
		);

		foreach ( $runners as $runner ) {
			$runner->hooks();
		}

		/**
		 * Fires when admin is loaded.
		 */
		$this->do_action( 'admin/loaded' );
	}
}

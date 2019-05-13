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

use CMB2_hookup;
use RankMath_Redirections\Helper;
use RankMath_Redirections\Module;
use RankMath_Redirections\Traits\Ajax;
use RankMath_Redirections\Traits\Hooker;
use RankMath_Redirections\Admin\Admin_Helper;
use MyThemeShop\Admin\Page;
use MyThemeShop\Helpers\Arr;
use MyThemeShop\Helpers\Str;
use MyThemeShop\Helpers\Util;
use MyThemeShop\Helpers\WordPress;

/**
 * Admin class.
 */
class Admin extends Module {

	use Ajax, Hooker;

	/**
	 * The Constructor.
	 *
	 * @codeCoverageIgnore
	 */
	public function __construct() {

		$directory = dirname( __FILE__ );
		$this->config([
			'id'             => 'redirect',
			'directory'      => $directory,
			'table'          => 'RankMath_Redirections\Redirections\Table',
			'help'           => [
				'title' => esc_html__( 'Redirections', 'redirections' ),
				'view'  => $directory . '/views/help.php',
			],
			'screen_options' => [
				'id'      => 'rank_math_redirections_per_page',
				'default' => 100,
			],
		]);
		parent::__construct();

		$this->action( 'rank_math/dashboard/widget', 'dashboard_widget', 12 );
		$this->filter( 'rank_math/settings/general', 'add_settings' );

		if ( $this->page->is_current_page() || 'rank_math_save_redirections' === Util::param_post( 'action' ) ) {
			$this->form = new Form;
			$this->form->hooks();
		}

		if ( $this->page->is_current_page() ) {
			new Export;
			$this->action( 'init', 'init' );
			add_action( 'admin_enqueue_scripts', [ 'CMB2_hookup', 'enqueue_cmb_css' ] );
			Helper::add_json( 'maintenanceMode', esc_html__( 'Maintenance Code', 'redirections' ) );
			Helper::add_json( 'emptyError', __( 'This field must not be empty.', 'redirections' ) );
		}

		if ( $this->is_ajax() ) {
			$this->ajax( 'delete', 'handle_ajax' );
			$this->ajax( 'activate', 'handle_ajax' );
			$this->ajax( 'deactivate', 'handle_ajax' );
			$this->ajax( 'trash', 'handle_ajax' );
			$this->ajax( 'restore', 'handle_ajax' );
		}

		add_action( 'rank_math/redirection/clean_trashed', 'RankMath_Redirections\\Redirections\\DB::periodic_clean_trash' );
	}

	/**
	 * Register admin page.
	 */
	public function register_admin_page() {

		$dir = $this->directory . '/views/';
		$uri = untrailingslashit( plugin_dir_url( __FILE__ ) );

		$this->page = new Page( 'rank-math-redirections', esc_html__( 'Redirections', 'redirections' ), [
			'position'   => 12,
			'parent'     => Helper::is_404_monitor_active() ? 'rank-math-monitor' : 'rank-math-redirection',
			'render'     => $dir . 'main.php',
			'classes'    => [ 'rank-math-page' ],
			'help'       => [
				'redirections-overview'       => [
					'title' => esc_html__( 'Overview', 'redirections' ),
					'view'  => $dir . 'help-tab-overview.php',
				],
				'redirections-screen-content' => [
					'title' => esc_html__( 'Screen Content', 'redirections' ),
					'view'  => $dir . 'help-tab-screen-content.php',
				],
				'redirections-actions'        => [
					'title' => esc_html__( 'Available Actions', 'redirections' ),
					'view'  => $dir . 'help-tab-actions.php',
				],
				'redirections-bulk'           => [
					'title' => esc_html__( 'Bulk Actions', 'redirections' ),
					'view'  => $dir . 'help-tab-bulk.php',
				],
			],
			'assets'     => [
				'styles'  => [
					'rank-math-common'       => '',
					'rank-math-cmb2'         => '',
					'rank-math-redirections' => $uri . '/assets/redirections.css',
				],
				'scripts' => [
					'rank-math-common'       => '',
					'rank-math-redirections' => $uri . '/assets/redirections.js',
				],
			],
		]);
	}

	/**
	 * Add module settings into general optional panel.
	 *
	 * @param  array $tabs Array of option panel tabs.
	 * @return array
	 */
	public function add_settings( $tabs ) {

		/**
		 * Allow developers to change number of redirections to process at once.
		 *
		 * @param int $number
		 */
		Helper::add_json( 'redirectionPastedContent', $this->do_filter( 'redirections/pastedContent', 100 ) );

		Arr::insert( $tabs, [
			'redirections' => [
				'icon'  => 'dashicons dashicons-controls-forward',
				'title' => esc_html__( 'Redirections', 'redirections' ),
				/* translators: Link to kb article */
				'desc'  => sprintf( esc_html__( 'Enable Redirections to set up custom 301, 302, 307, 410, or 451 redirections. %s.', 'redirections' ), '<a href="https://rankmath.com/kb/general-settings/#redirections" target="_blank">' . esc_html__( 'Learn more', 'redirections' ) . '</a>' ),
				'file'  => $this->directory . '/views/options.php',
			],
		], 8 );

		return $tabs;
	}

	/**
	 * Add stats into admin dashboard.
	 *
	 * @codeCoverageIgnore
	 */
	public function dashboard_widget() {
		$data = DB::get_stats();
		?>
		<br />
		<h3><?php esc_html_e( 'Redirections Stats', 'redirections' ); ?></h3>
		<ul>
			<li><span><?php esc_html_e( 'Redirections Count', 'redirections' ); ?></span><?php echo Str::human_number( $data->total ); ?></li>
			<li><span><?php esc_html_e( 'Redirections Hits', 'redirections' ); ?></span><?php echo Str::human_number( $data->hits ); ?></li>
		</ul>
		<?php
	}

	/**
	 * Initialize.
	 *
	 * @codeCoverageIgnore
	 */
	public function init() {
		if ( ! empty( $_REQUEST['delete_all'] ) ) {
			check_admin_referer( 'bulk-redirections' );
			DB::clear_trashed();
			return;
		}

		$action = WordPress::get_request_action();
		if ( false === $action || empty( $_REQUEST['redirection'] ) || 'edit' === $action ) {
			return;
		}

		check_admin_referer( 'bulk-redirections' );

		$ids = (array) wp_parse_id_list( $_REQUEST['redirection'] );
		if ( empty( $ids ) ) {
			Helper::add_notification( 'No valid id found.' );
			return;
		}

		$notice = $this->perform_action( $action, $ids );
		if ( $notice ) {
			Helper::add_notification( $notice, [ 'type' => 'success' ] );
			return;
		}

		Helper::add_notification( esc_html__( 'No valid action found.', 'redirections' ) );
	}

	/**
	 * Handle AJAX request.
	 *
	 * @codeCoverageIgnore
	 */
	public function handle_ajax() {
		$action = WordPress::get_request_action();
		if ( false === $action ) {
			return;
		}

		check_ajax_referer( 'redirection_list_action', 'security' );

		$id     = isset( $_REQUEST['redirection'] ) ? absint( $_REQUEST['redirection'] ) : 0;
		$action = str_replace( 'rank_math_redirections_', '', $action );

		if ( ! $id ) {
			$this->error( esc_html__( 'No valid id found.', 'redirections' ) );
		}

		$notice = $this->perform_action( $action, $id );
		if ( $notice ) {
			$this->success( $notice );
		}

		$this->error( esc_html__( 'No valid action found.', 'redirections' ) );
	}

	/**
	 * Perform action on db.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param  string        $action Action to perform.
	 * @param  integer|array $ids    Rows to perform on.
	 * @return string
	 */
	private function perform_action( $action, $ids ) {
		$status  = [
			'activate'   => 'active',
			'deactivate' => 'inactive',
			'trash'      => 'trashed',
			'restore'    => 'active',
		];
		$message = [
			'activate'   => esc_html__( 'Redirection successfully activated.', 'redirections' ),
			'deactivate' => esc_html__( 'Redirection successfully deactivated.', 'redirections' ),
			'trash'      => esc_html__( 'Redirection successfully moved to Trash.', 'redirections' ),
			'restore'    => esc_html__( 'Redirection successfully restored.', 'redirections' ),
		];

		if ( isset( $status[ $action ] ) ) {
			DB::change_status( $ids, $status[ $action ] );
			return $message[ $action ];
		}

		if ( 'delete' === $action ) {
			$count = DB::delete( $ids );
			if ( $count > 0 ) {
				/* translators: delete counter */
				return sprintf( esc_html__( '%d redirection(s) successfully deleted.', 'redirections' ), $count );
			}
		}

		return false;
	}
}

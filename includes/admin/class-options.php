<?php
/**
 * The option page functionality of the plugin.
 *
 * @since      1.0.0
 * @package    RankMath
 * @subpackage RankMath_Redirections\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath_Redirections\Admin;

use WP_Http;
use CMB2_hookup;
use RankMath_Redirections\CMB2;
use RankMath_Redirections\Replace_Vars;
use RankMath_Redirections\Traits\Hooker;
use MyThemeShop\Helpers\Str;
use RankMath_Redirections\Helper as GlobalHelper;

defined( 'ABSPATH' ) || exit;

/**
 * Options class.
 */
class Options {

	use Hooker;

	/**
	 * Page title.
	 *
	 * @var string
	 */
	public $title = 'Settings';

	/**
	 * Menu title.
	 *
	 * @var string
	 */
	public $menu_title = 'Settings';

	/**
	 * Hold tabs for page.
	 *
	 * @var array
	 */
	public $tabs = array();

	/**
	 * Hold folder name for tab files.
	 *
	 * @var string
	 */
	public $folder = '';

	/**
	 * Menu Position.
	 *
	 * @var int
	 */
	public $position = 10;

	/**
	 * The capability required for this menu to be displayed to the user.
	 *
	 * @var string
	 */
	public $capability = 'manage_options';

	/**
	 * CMB2 option page id.
	 *
	 * @var string
	 */
	private $cmb_id = null;

	/**
	 * The Constructor
	 *
	 * @param array $config Array of configuration.
	 */
	public function __construct( $config ) {

		$this->config( $config );
		$this->cmb_id = $this->key . '_options';

		$this->action( 'cmb2_admin_init', 'register_option_page', $this->position );
		$this->action( 'admin_post_' . $this->key, 'reset_options', 2 );

		if ( true === empty( get_option( $this->key ) ) ) {
			$this->action( 'cmb2_init_hookup_' . $this->cmb_id, 'set_defaults', 11 );
		}

		if ( ! $this->is_current_page() ) {
			return;
		}

		$this->action( 'admin_enqueue_scripts', 'enqueue' );
		$this->action( 'admin_body_class', 'body_class' );
		add_action( 'admin_enqueue_scripts', array( 'CMB2_hookup', 'enqueue_cmb_css' ), 25 );
	}

	/**
	 * Create option object and add settings
	 */
	function register_option_page() {

		$cmb = new_cmb2_box( array(
			'id'           => $this->cmb_id,
			'title'        => $this->title,
			'menu_title'   => $this->menu_title,
			'capability'   => $this->capability,
			'object_types' => array( 'options-page' ),
			'option_key'   => $this->key,
			'parent_slug'  => GlobalHelper::is_404_monitor_active() ? 'rank-math-monitor' : 'rank-math-redirection',
			'cmb_styles'   => false,
			'display_cb'   => array( $this, 'display' ),
		) );

		$tabs = $this->get_tabs();
		$cmb->add_field( array(
			'id'   => 'setting-panel-container-' . $this->cmb_id,
			'type' => 'tab_container_open',
			'tabs' => $tabs,
		) );

		foreach ( $tabs as $id => $tab ) {
			$located = $this->locate_file( $id, $tab );
			if ( false === $located ) {
				continue;
			}

			$cmb->add_field( array(
				'name' => esc_html__( 'Panel', 'redirections' ),
				'id'   => 'setting-panel-' . $id,
				'type' => 'tab_open',
			) );

			$cmb->add_field( array(
				'id'      => $id . '_section_title',
				'type'    => 'title',
				'name'    => isset( $tab['page_title'] ) ? $tab['page_title'] : ( isset( $tab['title'] ) ? $tab['title'] : '' ),
				'desc'    => isset( $tab['desc'] ) ? $tab['desc'] : '',
				'after'   => isset( $tab['after'] ) ? $tab['after'] : '',
				'classes' => 'main',
			) );

			include $located;

			$cmb->add_field( array(
				'id'   => 'setting-panel-' . $id . '-close',
				'type' => 'tab_close',
			) );
		}

		$cmb->add_field( array(
			'id'   => 'setting-panel-container-close-' . $this->cmb_id,
			'type' => 'tab_container_close',
		) );

		CMB2::pre_init( $cmb );
	}

	/**
	 * Set the default values if not set
	 *
	 * @param CMB2 $cmb The CMB2 object to hookup.
	 */
	public function set_defaults( $cmb ) {
		foreach ( $cmb->prop( 'fields' ) as $id => $field_args ) {
			$field = $cmb->get_field( $id );
			if ( isset( $field_args['default'] ) || isset( $field_args['default_cb'] ) ) {
				$defaults[ $id ] = $field->get_default();
			}
		}

		// Save Defaults if any.
		if ( ! empty( $defaults ) ) {
			add_option( $this->key, $defaults );
		}
	}

	/**
	 * Reset options
	 */
	public function reset_options() {
		$url = wp_get_referer();
		if ( ! $url ) {
			$url = admin_url();
		}

		if ( isset( $_POST['reset-cmb'], $_POST['action'] ) && $this->key === $_POST['action'] ) {
			delete_option( $this->key );
			wp_safe_redirect( esc_url_raw( $url ), WP_Http::SEE_OTHER );
			exit;
		}
	}

	/**
	 * Enqueue styles and scripts
	 */
	public function enqueue() {
		$screen = get_current_screen();

		if ( ! Str::contains( $this->key, $screen->id ) ) {
			return;
		}

		CMB2_hookup::enqueue_cmb_css();
		wp_enqueue_style( 'font-awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', null, rank_math_redirection()->version );
		wp_enqueue_style( 'rank-math-options', rank_math_redirection()->plugin_url() . 'assets/admin/css/option-panel.css', array( 'rank-math-common', 'rank-math-cmb2' ), rank_math_redirection()->version );
		wp_enqueue_script( 'rank-math-options', rank_math_redirection()->plugin_url() . 'assets/admin/js/option-panel.js', array( 'underscore', 'rank-math-common' ), rank_math_redirection()->version, true );

		// Add thank you.
		GlobalHelper::add_json( 'indexUrl', rank_math_redirection()->plugin_url() . 'assets/admin/js/search-index/' );
		GlobalHelper::add_json( 'optionPage', str_replace( 'rank-math-options-', '', $this->key ) );
	}

	/**
	 * Add classes to <body> of WordPress admin
	 *
	 * @param string $classes Space-separated list of CSS classes.
	 * @return string
	 */
	public function body_class( $classes = '' ) {
		return $classes . ' rank-math-page';
	}

	/**
	 * Display Setting on a page
	 *
	 * @param CMB2_Options $machine CUrrent CMB2 box object.
	 */
	public function display( $machine ) {
		$cmb = $machine->cmb;
		?>
		<div class="wrap rank-math-wrap rank-math-wrap-settings">

			<span class="wp-header-end"></span>

			<h1 class="page-title"><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<form class="cmb-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST" id="<?php echo $cmb->cmb_id; ?>" enctype="multipart/form-data" encoding="multipart/form-data">

				<input type="hidden" name="action" value="<?php echo esc_attr( $machine->option_key ); ?>">
				<?php $machine->options_page_metabox(); ?>

				<footer class="form-footer rank-math-ui settings-footer wp-clearfix">
					<input type="submit" name="reset-cmb" id="rank-math-reset-cmb" value="Reset Options" class="button button-secondary button-xlarge reset-options alignleft">
					<input type="submit" name="submit-cmb" id="submit-cmb" class="button button-primary button-xlarge save-options" value="Save Changes">
				</footer>

			</form>

		</div>

		<?php
	}

	/**
	 * Is the page is currrent page
	 *
	 * @return bool
	 */
	public function is_current_page() {
		$page   = isset( $_REQUEST['page'] ) && ! empty( $_REQUEST['page'] ) ? sanitize_text_field( $_REQUEST['page'] ) : false;
		$action = isset( $_REQUEST['action'] ) && ! empty( $_REQUEST['action'] ) ? sanitize_text_field( $_REQUEST['action'] ) : false;

		return $page === $this->key || $action === $this->key;
	}

	/**
	 * Get setting tabs
	 *
	 * @return array
	 */
	private function get_tabs() {

		$filter = str_replace( '-', '_', str_replace( 'rank-math-', '', $this->key ) );
		/**
		 * Allow developers to add new tabs into option panel.
		 *
		 * The dynamic part of hook is, page name without 'rank-math-' prefix.
		 *
		 * @param array $tabs
		 */
		return $this->do_filter( "admin/options/{$filter}_tabs", $this->tabs );
	}

	/**
	 * Locate tab options file
	 *
	 * @param  string $id  Tab id.
	 * @param  array  $tab Tab options.
	 * @return string|boolean
	 */
	private function locate_file( $id, $tab ) {
		if ( isset( $tab['type'] ) && 'seprator' === $tab['type'] ) {
			return false;
		}

		$file = isset( $tab['file'] ) && ! empty( $tab['file'] ) ? $tab['file'] : rank_math_redirection()->includes_dir() . "settings/{$this->folder}/{$id}.php";

		return file_exists( $file ) ? $file : false;
	}
}

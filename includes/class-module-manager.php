<?php
/**
 * The Module Manager
 *
 * @since      1.0.0
 * @package    RankMath
 * @subpackage RankMath_Redirections\Core
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath_Redirections;

use RankMath_Redirections\Traits\Hooker;
use MyThemeShop\Helpers\Conditional;

defined( 'ABSPATH' ) || exit;

/**
 * Module_Manager class.
 */
class Module_Manager {

	use Hooker;

	/**
	 * Hold modules.
	 *
	 * @var array
	 */
	public $modules = array();

	/**
	 * Hold module object.
	 *
	 * @var array
	 */
	private $controls = array();

	/**
	 * Hold active module ids.
	 *
	 * @var array
	 */
	private $active = array();

	/**
	 * The Constructor.
	 */
	public function __construct() {
		if ( Conditional::is_heartbeat() ) {
			return;
		}

		$this->action( 'plugins_loaded', 'setup_modules' );
		$this->action( 'plugins_loaded', 'load_modules', 11 );
	}

	/**
	 * Include default modules support.
	 */
	public function setup_modules() {
		/**
		 * Filters the array of modules available to be activated.
		 *
		 * @param array $modules Array of available modules.
		 */
		$modules = $this->do_filter( 'modules', array(
			'redirections'   => array(
				'id'            => 'redirections',
				'title'         => esc_html__( 'Redirections', 'redirections' ),
				'desc'          => esc_html__( 'Redirect non-existent content easily with 301 and 302 status code. This can help reduce errors and improve your site ranking.', 'redirections' ),
				'class'         => 'RankMath_Redirections\Redirections\Redirections',
				'icon'          => 'dashicons-randomize',
				'settings_link' => Helper::get_admin_url( 'options-general' ) . '#setting-panel-redirections',
			),

			'404-monitor'    => array(
				'id'            => '404-monitor',
				'title'         => esc_html__( '404 Monitor', 'redirections' ),
				'desc'          => esc_html__( 'Records the URLs on which visitors & search engines run into 404 Errors. You can also turn on Redirections to redirect the error causing URLs to other URLs.', 'redirections' ),
				'icon'          => 'dashicons-dismiss',
			),

			'local-seo'      => array(
				'id'    => 'local-seo',
				'title' => esc_html__( 'Local SEO & Google Knowledge Graph', 'redirections' ),
				'desc'  => esc_html__( 'Dominate the search results for local audience by optimizing your website and posts using this Rank Math module.', 'redirections' ),
				'icon'  => 'dashicons-location-alt',
			),

			'rich-snippet'   => array(
				'id'    => 'rich-snippet',
				'title' => esc_html__( 'Rich Snippets', 'redirections' ),
				'desc'  => esc_html__( 'Enable support for the Rich Snippets, which adds metadata to your website, resulting in rich search results and more traffic.', 'redirections' ),
				'icon'  => 'dashicons-awards',
			),

			'role-manager'   => array(
				'id'    => 'role-manager',
				'title' => esc_html__( 'Role Manager', 'redirections' ),
				'desc'  => esc_html__( 'The Role Manager allows you to use internal WordPress\' roles to control which of your site admins can change Rank Math\'s settings', 'redirections' ),
				'icon'  => 'dashicons-admin-users',
			),

			'search-console' => array(
				'id'    => 'search-console',
				'title' => esc_html__( 'Search Console', 'redirections' ),
				'desc'  => esc_html__( 'Connect Rank Math with Google Search Console to see the most important information from Google directly in your WordPress dashboard.', 'redirections' ),
				'icon'  => 'dashicons-search',
			),

			'seo-analysis'   => array(
				'id'    => 'seo-analysis',
				'title' => esc_html__( 'SEO Analysis', 'redirections' ),
				'desc'  => esc_html__( 'Let Rank Math analyze your website and your website\'s content using 70+ different tests to provide tailor-made SEO Analysis to you.', 'redirections' ),
				'icon'  => 'dashicons-chart-bar',
			),

			'sitemap'        => array(
				'id'    => 'sitemap',
				'title' => esc_html__( 'Sitemap', 'redirections' ),
				'desc'  => esc_html__( 'Enable Rank Math\'s sitemap feature, which helps search engines index your website\'s content effectively.', 'redirections' ),
				'icon'  => 'dashicons-networking',
			),

			'amp'            => array(
				'id'    => 'amp',
				'title' => esc_html__( 'AMP', 'redirections' ),
				'desc'  => esc_html__( 'Install AMP plugin from WordPress.org to make Rank Math work with Accelerated Mobile Pages. It is required because AMP are different than WordPress pages and our plugin doesn\'t work with them out-of-the-box.', 'redirections' ),
				'icon'  => 'dashicons-smartphone',
			),

			'woocommerce'    => array(
				'id'    => 'woocommerce',
				'title' => esc_html__( 'WooCommerce', 'redirections' ),
				'desc'  => esc_html__( 'WooCommerce module to use Rank Math to optimize WooCommerce Product Pages.', 'redirections' ),
				'icon'  => 'dashicons-cart',
			),

			'link-counter'   => array(
				'id'    => 'link-counter',
				'title' => esc_html__( 'Link Counter', 'redirections' ),
				'desc'  => esc_html__( 'Counts the total number of internal, external links, to and from links inside your posts.', 'redirections' ),
				'icon'  => 'dashicons-admin-links',
			),
		) );

		foreach ( $modules as $module ) {
			$this->add_module( $module );
		}
	}

	/**
	 * Load active modules.
	 */
	public function load_modules() {
		$this->active = get_option( 'rank_math_modules', array() );
		$this->controls['redirections'] = new $this->modules['redirections']['class'];
	}

	/**
	 * Display module form to enable/disable them.
	 *
	 * @codeCoverageIgnore
	 */
	public function display_form() {
		if ( ! current_user_can( 'manage_options' ) ) {
			echo 'You cant access this page.';
			return;
		}
		?>
		<div class="rank-math-ui module-listing">

			<div class="two-col">
			<?php
			foreach ( $this->modules as $module_id => $module ) :

				$is_active   = true;
				$label_class = '';
				if ( 'redirections' !== $module_id ) {
					$is_active   = false;
					$label_class = 'rank-math-tooltip';
				}
				?>
				<div class="col">
					<div class="rank-math-box <?php echo $is_active ? 'active' : ''; ?>">

						<span class="dashicons <?php echo isset( $module['icon'] ) ? $module['icon'] : 'dashicons-category'; ?>"></span>

						<header>
							<h3><?php echo $module['title']; ?></h3>
							<p><em><?php echo $module['desc']; ?></em></p>
							<?php if ( isset( $module['settings_link'] ) ) { ?>
								<a class="module-settings" href="<?php echo esc_url( $module['settings_link'] ); ?>"><?php esc_html_e( 'Settings', 'redirections' ); ?></a>
							<?php } ?>
						</header>
						<div class="status wp-clearfix">
							<span class="rank-math-switch">
								<input type="checkbox" class="rank-math-modules" id="module-<?php echo $module_id; ?>" name="modules[]" value="<?php echo $module_id; ?>"<?php checked( $is_active ); ?> disabled="disabled">
								<label for="module-<?php echo $module_id; ?>" class="<?php echo $label_class; ?>"><?php esc_html_e( 'Toggle', 'redirections' ); ?></label>
							</span>
							<label>
								<?php esc_html_e( 'Status:', 'redirections' ); ?>
								<?php if ( $is_active ) { ?>
									<span class="module-status active-text"><?php echo esc_html__( 'Active', 'redirections' ); ?></span>
								<?php } else { ?>
									<span class="module-status inactive-text"><?php echo esc_html__( 'Inactive', 'redirections' ); ?> </span>
								<?php } ?>
							</label>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
			</div>

		</div>
		<?php
	}

	/**
	 * Add module.
	 *
	 * @param array $args Module configuration.
	 */
	public function add_module( $args = array() ) {
		$this->modules[ $args['id'] ] = $args;
	}

	/**
	 * Get module by id.
	 *
	 * @param  string $id ID to get module.
	 * @return object     Module class object.
	 */
	public function get_module( $id ) {
		return isset( $this->controls[ $id ] ) ? $this->controls[ $id ] : false;
	}
}

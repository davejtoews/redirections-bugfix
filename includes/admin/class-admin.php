<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 * @package    RankMath
 * @subpackage RankMath_Redirections\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath_Redirections\Admin;

use RankMath_Redirections\Runner;
use RankMath_Redirections\Helper;
use RankMath_Redirections\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Admin class.
 *
 * @codeCoverageIgnore
 */
class Admin implements Runner {

	use Hooker;

	/**
	 * Register hooks.
	 */
	public function hooks() {
		$this->action( 'wp_dashboard_setup', 'add_dashboard_widgets' );
		$this->action( 'admin_footer', 'rank_math_modal' );
	}

	/**
	 * Register dashboard widget.
	 */
	public function add_dashboard_widgets() {
		wp_add_dashboard_widget( 'rank_math_dashboard_widget', esc_html__( 'Rank Math', 'redirections' ), [ $this, 'render_dashboard_widget' ] );
	}

	/**
	 * Render dashboard widget.
	 */
	public function render_dashboard_widget() {
		?>
		<div id="published-posts" class="activity-block">
			<?php $this->do_action( 'dashboard/widget' ); ?>
		</div>
		<?php
	}

	/**
	 * Display dashabord tabs.
	 */
	public function display_dashboard_nav() {
		$current = isset( $_GET['view'] ) ? filter_input( INPUT_GET, 'view' ) : 'modules';
		?>
		<h2 class="nav-tab-wrapper">
			<?php
			foreach ( $this->get_nav_links() as $id => $link ) :
				if ( isset( $link['cap'] ) && ! current_user_can( $link['cap'] ) ) {
					continue;
				}
				?>
			<a class="nav-tab<?php echo $id === $current ? ' nav-tab-active' : ''; ?>" href="<?php echo esc_url( Helper::get_admin_url( $link['url'], $link['args'] ) ); ?>" title="<?php echo $link['title']; ?>"><?php echo $link['title']; ?></a>
			<?php endforeach; ?>
		</h2>
		<?php
	}

	/**
	 * Get dashbaord navigation links
	 *
	 * @return array
	 */
	private function get_nav_links() {
		$links = [
			'modules'       => [
				'url'   => '',
				'args'  => 'view=modules',
				'cap'   => 'manage_options',
				'title' => esc_html__( 'Modules', 'redirections' ),
			],
			'help'          => [
				'url'   => 'help',
				'args'  => '',
				'cap'   => 'manage_options',
				'title' => esc_html__( 'Help', 'redirections' ),
			],
			'import-export' => [
				'url'   => 'import-export',
				'args'  => '',
				'cap'   => 'manage_options',
				'title' => esc_html__( 'Import &amp; Export', 'redirections' ),
			],
		];

		if ( Helper::is_plugin_active_for_network() ) {
			unset( $links['help'] );
		}

		return $links;
	}

	/**
	 * Activate Rank Math Modal.
	 */
	public function rank_math_modal() {
		$screen = get_current_screen();

		// Early Bail!
		if ( 'toplevel_page_rank-math-redirection'!== $screen->id ) {
			return;
		}

		if ( file_exists( WP_PLUGIN_DIR . '/seo-by-rank-math' ) ) {
			$text = __( 'Activate Now', 'redirections' );
			$path = 'seo-by-rank-math/rank-math.php';
			$link = wp_nonce_url( self_admin_url( 'plugins.php?action=activate&plugin=' . $path ), 'activate-plugin_' . $path );
		} else {
			$text = __( 'Install for Free', 'redirections' );
			$link = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=seo-by-rank-math' ), 'install-plugin_seo-by-rank-math' );
		}

		// Scripts.
		rank_math_redirection()->admin_assets->enqueue_style( 'plugin-modal' );
		rank_math_redirection()->admin_assets->enqueue_script( 'redirections-plugin-modal' );

		?>
		<div class="rank-math-feedback-modal rank-math-ui" id="rank-math-redirections-feedback-form">
			<div class="rank-math-feedback-content">

				<div class="plugin-card plugin-card-seo-by-rank-math">
					<span class="button-close dashicons dashicons-no-alt alignright"></span>
					<div class="plugin-card-top">
						<div class="name column-name">
							<h3>
								<a href="https://rankmath.com/wordpress/plugin/seo-suite/" target="_blank">
								<?php esc_html_e( 'WordPress SEO Plugin – Rank Math', 'redirections' ); ?>
								<img src="https://ps.w.org/seo-by-rank-math/assets/icon.svg" class="plugin-icon" alt="<?php esc_html_e( 'Rank Math SEO', 'redirections' ); ?>">
								</a>
								<span class="vers column-rating">
									<a href="https://wordpress.org/support/plugin/seo-by-rank-math/reviews/" target="_blank">
										<div class="star-rating">
											<div class="star star-full" aria-hidden="true"></div>
											<div class="star star-full" aria-hidden="true"></div>
											<div class="star star-full" aria-hidden="true"></div>
											<div class="star star-full" aria-hidden="true"></div>
											<div class="star star-full" aria-hidden="true"></div>
										</div>
										<span class="num-ratings" aria-hidden="true">(195)</span>
									</a>
								</span>
							</h3>
						</div>

						<div class="desc column-description">
							<p><?php esc_html_e( 'Rank Math is a revolutionary SEO plugin that combines the features of many SEO tools in a single package & helps you multiply your traffic.', 'redirections' ); ?></p>
						</div>
					</div>

					<div class="plugin-card-bottom">
						<div class="column-compatibility">
							<span class="compatibility-compatible"><strong><?php esc_html_e( 'Compatible', 'redirections' ); ?></strong> <?php esc_html_e( 'with your version of WordPress', 'redirections' ); ?></span>
						</div>
						<a href="<?php echo $link; ?>" class="button button-primary install-button"><?php echo $text; ?></a>
					</div>
				</div>

			</div>

		</div>
		<?php
	}
}

<?php
/**
 * Handle the plugin deactivation feedback
 *
 * @since      1.0.3
 * @package    RankMath
 * @subpackage RankMath_Redirections\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath_Redirections\Admin;

use RankMath_Redirections\Runner;
use RankMath_Redirections\Traits\Ajax;
use RankMath_Redirections\Traits\Hooker;
use RankMath_Redirections\Helper as GlobalHelper;

defined( 'ABSPATH' ) || exit;

/**
 * Deactivate_Survey class.
 *
 * @codeCoverageIgnore
 */
class Deactivate_Survey implements Runner {

	use Hooker, Ajax;

	/**
	 * Register hooks.
	 */
	public function hooks() {
		$this->action( 'admin_footer', 'deactivate_scripts' );
		$this->ajax( 'deactivate_feedback', 'deactivate_feedback' );
	}

	/**
	 * Send deactivated feedback to api.
	 */
	public function deactivate_feedback() {

		check_ajax_referer( 'rank_math_redirections_deactivate_feedback_nonce', 'security' );

		$reason_key  = '';
		$reason_text = '';
		if ( ! empty( $_POST['reason_key'] ) ) {
			$reason_key = $_POST['reason_key'];
		}
		if ( ! empty( $_POST[ "reason_{$reason_key}" ] ) ) {
			$reason_text = $_POST[ "reason_{$reason_key}" ];
		}

		wp_safe_remote_post( 'https://rankmath.com/mtsapi/v1/deactivate_feedback', array(
			'timeout'   => 30,
			'blocking'  => false,
			'sslverify' => false,
			'cookies'   => array(),
			'headers'   => array( 'user-agent' => 'RankMath/' . md5( esc_url( home_url( '/' ) ) ) . ';' ),
			'body'      => array(
				'product_name'    => 'redirections',
				'product_version' => rank_math_redirection()->version,
				'site_url'        => esc_url( site_url() ),
				'site_lang'       => get_bloginfo( 'language' ),
				'feedback_key'    => $reason_key,
				'feedback'        => $reason_text,
			),
		));

		wp_send_json_success();
	}

	/**
	 * Print deactivate feedback dialog.
	 */
	public function deactivate_scripts() {
		$screen = get_current_screen();

		// Early Bail!
		if ( ! in_array( $screen->id, array( 'plugins', 'plugins-network' ), true ) ) {
			return;
		}

		// Scripts.
		rank_math_redirection()->admin_assets->enqueue_style( 'plugin-modal' );
		rank_math_redirection()->admin_assets->enqueue_script( 'redirections-plugin-modal' );

		// Form.
		?>
		<div class="rank-math-feedback-modal rank-math-ui" id="rank-math-redirections-feedback-form">
			<div class="rank-math-feedback-content">

				<header>

					<h2>
						<?php echo __( 'Quick Feedback', 'redirections' ); ?>
						<span class="button-close dashicons dashicons-no-alt alignright"></span>
					</h2>

					<p><?php echo __( 'If you have a moment, please share why you are deactivating Rank Math:', 'redirections' ); ?></p>

				</header>

				<form method="post">

					<input type="hidden" name="action" value="rank_math_redirections_deactivate_feedback" />
					<?php wp_nonce_field( 'rank_math_redirections_deactivate_feedback_nonce', 'security' ); ?>

					<?php foreach ( $this->get_uninstall_reasons() as $key => $reason ) : ?>
					<div class="rank-math-feedback-input-wrapper">

						<input id="deactivate-feedback-<?php echo esc_attr( $key ); ?>" type="radio" name="reason_key" value="<?php echo esc_attr( $key ); ?>" />

						<label for="deactivate-feedback-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $reason['title'] ); ?></label>

						<?php if ( ! empty( $reason['placeholder'] ) ) : ?>
							<input class="regular-text" type="text" name="reason_<?php echo esc_attr( $key ); ?>" placeholder="<?php echo esc_attr( $reason['placeholder'] ); ?>" />
						<?php endif; ?>

					</div>
					<?php endforeach; ?>

					<footer>

						<button type="submit" class="button button-primary button-large button-submit"><?php esc_html_e( 'Submit & Deactivate', 'redirections' ); ?></button>

						<button type="button" class="button button-link alignright button-skip"><?php esc_html_e( 'Skip & Deactivate', 'redirections' ); ?></button>

					</footer>

				</form>

			</div>

		</div>
		<?php
	}

	/**
	 * Get uninstall reasons.
	 *
	 * @return array
	 */
	private function get_uninstall_reasons() {
		return array(
			'no_longer_needed'           => array(
				'title'       => esc_html__( 'I no longer need the plugin', 'redirections' ),
				'placeholder' => '',
			),
			'found_a_better_plugin'      => array(
				'title'       => esc_html__( 'I found a better plugin', 'redirections' ),
				'placeholder' => esc_html__( 'Please share which plugin', 'redirections' ),
			),
			'couldnt_get_plugin_to_work' => array(
				'title'       => esc_html__( 'I couldn\'t get the plugin to work', 'redirections' ),
				'placeholder' => '',
			),
			'temporary_deactivation'     => array(
				'title'       => esc_html__( 'It\'s a temporary deactivation', 'redirections' ),
				'placeholder' => '',
			),
			'other'                      => array(
				'title'       => esc_html__( 'Other', 'redirections' ),
				'placeholder' => esc_html__( 'Please share the reason', 'redirections' ),
			),
		);
	}
}

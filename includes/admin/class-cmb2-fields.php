<?php
/**
 * The CMB2 fields for the plugin.
 *
 * @since      1.0.0
 * @package    RankMath
 * @subpackage RankMath_Redirections\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath_Redirections\Admin;

use RankMath_Redirections\Runner;
use RankMath_Redirections\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * CMB2_Fields class.
 *
 * @codeCoverageIgnore
 */
class CMB2_Fields implements Runner {

	use Hooker;

	/**
	 * Register hooks.
	 */
	public function hooks() {
		// CMB2 Custom Fields.
		if ( ! has_action( 'cmb2_render_switch' ) ) {
			$this->action( 'cmb2_render_switch', 'render_switch', 10, 5 );
		}
		if ( ! has_action( 'cmb2_render_notice' ) ) {
			$this->action( 'cmb2_render_notice', 'render_notice' );
		}
	}

	/**
	 * Render switch field.
	 *
	 * @param array  $field             The passed in `CMB2_Field` object.
	 * @param mixed  $escaped_value     The value of this field escaped
	 *                                  It defaults to `sanitize_text_field`.
	 *                                  If you need the unescaped value, you can access it
	 *                                  via `$field->value()`.
	 * @param int    $object_id         The ID of the current object.
	 * @param string $object_type       The type of object you are working with.
	 *                                  Most commonly, `post` (this applies to all post-types),
	 *                                  but could also be `comment`, `user` or `options-page`.
	 * @param object $field_type_object This `CMB2_Types` object.
	 */
	public function render_switch( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {

		if ( empty( $field->args['options'] ) ) {
			$field->args['options'] = array(
				'off' => esc_html( $field->get_string( 'off', __( 'Off', 'redirections' ) ) ),
				'on'  => esc_html( $field->get_string( 'on', __( 'On', 'redirections' ) ) ),
			);
		}
		$field->set_options();

		echo $field_type_object->radio_inline();
	}

	/**
	 * Render notices
	 *
	 * @param array $field The passed in `CMB2_Field` object.
	 */
	public function render_notice( $field ) {
		$hash = array(
			'error'   => 'notice notice-alt notice-error error inline',
			'info'    => 'notice notice-alt notice-info info inline',
			'warning' => 'notice notice-alt notice-warning warning inline',
		);

		echo '<div class="' . $hash[ $field->args( 'what' ) ] . '"><p>' . $field->args( 'content' ) . '</p></div>';
	}
}

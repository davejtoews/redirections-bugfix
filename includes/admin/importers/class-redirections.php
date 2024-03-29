<?php
/**
 * The Redirections Import Class
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath_Redirections\Admin\Importers
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath_Redirections\Admin\Importers;

use RankMath_Redirections\Helper;
use RankMath_Redirections\Admin\Admin_Helper;
use RankMath_Redirections\Redirections\Redirection;

defined( 'ABSPATH' ) || exit;

/**
 * Redirections class.
 */
class Redirections extends Plugin_Importer {

	/**
	 * The plugin name.
	 *
	 * @var string
	 */
	protected $plugin_name = 'Redirections';

	/**
	 * Array of option keys to import and clean
	 *
	 * @var array
	 */
	protected $option_keys = [ 'redirection_options' ];

	/**
	 * Array of choices keys to import
	 *
	 * @var array
	 */
	protected $choices = [ 'redirections' ];

	/**
	 * Import redirections of plugin.
	 *
	 * @return bool
	 */
	protected function redirections() {
		global $wpdb;

		$count = 0;
		$rows  = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}redirection_items" );

		if ( empty( $rows ) ) {
			return false;
		}

		foreach ( (array) $rows as $row ) {
			$item = Redirection::from(
				[
					'sources'     => [
						[
							'pattern'    => $row->url,
							'comparison' => empty( $row->regex ) ? 'exact' : 'regex',
						],
					],
					'url_to'      => $this->get_url_to( $row ),
					'header_code' => $row->action_code,
				]
			);

			if ( false !== $item->save() ) {
				$count++;
			}
		}

		return compact( 'count' );
	}

	/**
	 * Get validated url to value
	 *
	 * @param  object $row Current row we are processing.
	 * @return string
	 */
	private function get_url_to( $row ) {
		if ( is_string( $row->action_data ) ) {
			return $row->action_data;
		}

		$data = maybe_unserialize( $row->action_data );
		if ( is_array( $data ) && isset( $data['url'] ) ) {
			return $data['url'];
		}

		return '/';
	}

	/**
	 * Returns array of choices of action which can be performed for plugin
	 *
	 * @return array
	 */
	public function get_choices() {
		return [
			'redirections' => esc_html__( 'Import Redirections', 'redirections' ) . Admin_Helper::get_tooltip( esc_html__( 'Plugin redirections.', 'redirections' ) ),
		];
	}
}

<?php
/**
 * The Yoast SEO Import Class
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath_Redirections\Admin\Importers
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath_Redirections\Admin\Importers;

use RankMath_Redirections\Helper;
use MyThemeShop\Helpers\DB;
use RankMath_Redirections\Sitemap\Sitemap;
use MyThemeShop\Helpers\WordPress;
use RankMath_Redirections\Redirections\Redirection;

defined( 'ABSPATH' ) || exit;

/**
 * Yoast class.
 */
class Yoast extends Plugin_Importer {

	/**
	 * The plugin name.
	 *
	 * @var string
	 */
	protected $plugin_name = 'Yoast SEO';

	/**
	 * Array of choices keys to import
	 *
	 * @var array
	 */
	protected $choices = [ 'redirections' ];

	/**
	 * Imports redirections data.
	 *
	 * @return array
	 */
	protected function redirections() {
		$redirections = get_option( 'wpseo-premium-redirects-base' );
		if ( ! $redirections ) {
			return false;
		}

		$count = 0;
		foreach ( $redirections as $redirection ) {
			if ( ! isset( $redirection['origin'] ) || empty( $redirection['origin'] ) ) {
				continue;
			}

			$item = Redirection::from([
				'sources'     => [
					[
						'pattern'    => $redirection['origin'],
						'comparison' => isset( $redirection['format'] ) && 'regex' === $redirection['format'] ? 'regex' : 'exact',
					],
				],
				'url_to'      => isset( $redirection['url'] ) ? $redirection['url'] : '',
				'header_code' => isset( $redirection['type'] ) ? $redirection['type'] : '301',
			]);

			if ( false !== $item->save() ) {
				$count++;
			}
		}

		return compact( 'count' );
	}
}

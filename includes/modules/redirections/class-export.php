<?php
/**
 * The Redirections Export.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath_Redirections\Redirections
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath_Redirections\Redirections;

use RankMath_Redirections\Traits\Hooker;

/**
 * Export class.
 *
 * @codeCoverageIgnore
 */
class Export {

	use Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->action( 'init', 'export' );
	}

	/**
	 * Export redirections.
	 */
	public function export() {
		$server = isset( $_GET['export'] ) ? filter_input( INPUT_GET, 'export' ) : false;
		if ( ! $server || ! in_array( $server, [ 'apache', 'nginx' ] ) ) {
			return;
		}

		$filename = "rank-math-redirections-{$server}-" . date_i18n( 'Y-m-d-H-i-s' ) . ( 'apache' === $server ? '.htaccess' : '.conf' );

		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Cache-Control: no-cache, no-store, must-revalidate' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$items = DB::get_redirections([
			'limit'  => 1000,
			'status' => 'active',
		]);

		if ( 0 === $items['count'] ) {
			return;
		}

		$text[] = '# Created by Rank Math';
		$text[] = '# ' . date( 'r' );
		$text[] = '# Rank Math ' . trim( rank_math_redirection()->version ) . ' - https://rankmath.com/';
		$text[] = '';

		$text = array_merge( $text, $this->$server( $items['redirections'] ) );

		$text[] = '';
		$text[] = '# Rank Math Redirections END';

		echo implode( PHP_EOL, $text ) . PHP_EOL;
		exit;
	}

	/**
	 * Apache rewrite rules.
	 *
	 * @param  array $items Array of db items.
	 * @return string
	 */
	private function apache( $items ) {
		$text[] = '<IfModule mod_rewrite.c>';

		foreach ( $items as $item ) {
			$to = sprintf( '%s [R=%d,L]', $this->encode2nd( $item['url_to'] ), $item['header_code'] );

			$sources = maybe_unserialize( $item['sources'] );
			foreach ( $sources as $from ) {
				$url = $from['pattern'];
				if ( 'regex' !== $from['comparison'] && strpos( $url, '?' ) !== false || strpos( $url, '&' ) !== false ) {
					$url_parts = parse_url( $url );
					$url       = $url_parts['path'];
					$text[]    = sprintf( 'RewriteCond %%{QUERY_STRING} ^%s$', preg_quote( $url_parts['query'] ) );
				}

				// Get rewrite string.
				$text[] = sprintf( '%sRewriteRule %s %s', $this->is_valid_regex( $from ), $this->get_comparison( $url, $from ), $to );
			}
		}

		$text[] = '</IfModule>';

		return $text;
	}

	/**
	 * NGINX rewrite rules.
	 *
	 * @param  array $items Array of db items.
	 * @return string
	 */
	private function nginx( $items ) {
		$text[] = 'server {';

		foreach ( $items as $item ) {
			$to   = $this->encode2nd( $item['url_to'] );
			$code = '301' === $item['header_code'] ? 'permanent' : 'redirect';

			$sources = maybe_unserialize( $item['sources'] );
			foreach ( $sources as $from ) {
				if ( '' !== $this->is_valid_regex( $from ) ) {
					continue;
				}

				$text[] = $this->normalize_nginx_redirect( $this->get_comparison( $from['pattern'], $from ), $to, $code );
			}
		}

		$text[] = '}';

		return $text;
	}

	/**
	 * Check if it's a valid pattern.
	 *
	 * So we don't break the site when it's inserted in the .htaccess.
	 *
	 * @param  array $source Source array.
	 * @return string
	 */
	private function is_valid_regex( $source ) {
		if ( 'regex' == $source['comparison'] && @preg_match( $source['pattern'], null ) === false ) { // phpcs:ignore
			return '# ';
		}

		return '';
	}

	/**
	 * Normalize redirect data
	 *
	 * @param string $source Matching pattern.
	 * @param string $target Target where to redirect.
	 * @param string $code   Response header code.
	 * @return string
	 */
	private function normalize_nginx_redirect( $source, $target, $code ) {
		$source = preg_replace( "/[\r\n\t].*?$/s", '', $source );
		$source = preg_replace( '/[^\PC\s]/u', '', $source );
		$target = preg_replace( "/[\r\n\t].*?$/s", '', $target );
		$target = preg_replace( '/[^\PC\s]/u', '', $target );

		return "    rewrite {$source} {$target} {$code};";
	}

	/**
	 * Get comparison pattern
	 *
	 * @param  string $url  Url for comparison.
	 * @param  array  $from Comparison type and url.
	 * @return string
	 */
	private function get_comparison( $url, $from ) {
		$comparison = $from['comparison'];
		if ( 'regex' === $comparison ) {
			return $this->encode_regex( $from['pattern'] );
		}

		$hash = [
			'exact'    => '^{url}$',
			'contains' => '^(.*){url}(.*)$',
			'start'    => '^{url}',
			'end'      => '{url}$',
		];

		$url = preg_quote( $url );
		return isset( $hash[ $comparison ] ) ? str_replace( '{url}', $url, $hash[ $comparison ] ) : $url;
	}

	/**
	 * Encode url
	 *
	 * @param  string $url Url to encode.
	 * @return string
	 */
	private function encode2nd( $url ) {
		$url = urlencode( $url );
		$url = str_replace( '%2F', '/', $url );
		$url = str_replace( '%3F', '?', $url );
		$url = str_replace( '%3A', ':', $url );
		$url = str_replace( '%3D', '=', $url );
		$url = str_replace( '%26', '&', $url );
		$url = str_replace( '%25', '%', $url );
		$url = str_replace( '+', '%20', $url );
		$url = str_replace( '%24', '$', $url );
		return $url;
	}

	/**
	 * Encode regex
	 *
	 * @param  string $url Url to encode.
	 * @return string
	 */
	private function encode_regex( $url ) {
		$url = preg_replace( '/[^a-zA-Z0-9\s](.*)[^a-zA-Z0-9\s][imsxeADSUXJu]*/', '$1', $url ); // Strip delimiters.
		$url = preg_replace( "/[\r\n\t].*?$/s", '', $url ); // Remove newlines.
		$url = preg_replace( '/[^\PC\s]/u', '', $url ); // Remove any invalid characters.
		$url = str_replace( ' ', '%20', $url ); // Make sure spaces are quoted.
		$url = str_replace( '%24', '$', $url );
		$url = ltrim( $url, '/' ); // No leading slash.
		$url = preg_replace( '@^\^/@', '^', $url ); // If pattern has a ^ at the start then ensure we don't have a slash immediately.

		return $url;
	}
}

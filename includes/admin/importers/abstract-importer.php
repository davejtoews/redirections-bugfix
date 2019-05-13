<?php
/**
 * The abstract class for plugins import to inherit from
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath_Redirections\Admin\Import
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath_Redirections\Admin\Importers;

use Exception;
use RankMath_Redirections\Helper;
use RankMath_Redirections\Traits\Ajax;
use RankMath_Redirections\Traits\Hooker;
use RankMath_Redirections\Admin\Admin_Helper;
use MyThemeShop\Helpers\DB;
use MyThemeShop\Helpers\Attachment;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin_Importer class.
 */
abstract class Plugin_Importer {

	use Hooker, Ajax;

	/**
	 * The plugin name
	 *
	 * @var string
	 */
	protected $plugin_name;

	/**
	 * The plugin file
	 *
	 * @var string
	 */
	protected $plugin_file;

	/**
	 * Array of option keys to import and clean
	 *
	 * @var array
	 */
	protected $option_keys;

	/**
	 * Array of table names to drop while cleaning
	 *
	 * @var array
	 */
	protected $table_names;

	/**
	 * Array of choices keys to import
	 *
	 * @var array
	 */
	protected $choices;

	/**
	 * Items to parse for post/term/user meta.
	 *
	 * @var int
	 */
	protected $items_per_page = 100;

	/**
	 * Pagination arguments.
	 *
	 * @var array
	 */
	protected $_pagination_args = array();

	/**
	 * Plugin slug for internal  use.
	 *
	 * @var string
	 */
	protected $plugin_slug = '';

	/**
	 * Class constructor
	 *
	 * @param string $plugin_file Plugins file.
	 */
	public function __construct( $plugin_file ) {
		$this->plugin_slug = \strtolower( get_class( $this ) );
		$this->plugin_slug = \str_replace( 'RankMath_Redirections\\admin\\importers\\', '', $this->plugin_slug );
		$this->plugin_file = $plugin_file;
	}

	/**
	 * Returns the string for the plugin we're importing from
	 *
	 * @return string Plugin name
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Returns the string for the plugin file
	 *
	 * @return string Plugin file
	 */
	public function get_plugin_file() {
		return $this->plugin_file;
	}

	/**
	 * Returns array of choices of action which can be performed for plugin
	 *
	 * @return array
	 */
	public function get_choices() {
		if ( empty( $this->choices ) ) {
			return array();
		}

		$hash = array(
			'redirections' => esc_html__( 'Import Redirections', 'redirections' ) . Admin_Helper::get_tooltip( esc_html__( 'Import all the redirections you have already set up in Yoast.', 'redirections' ) ),
		);

		return \array_intersect_key( $hash, \array_combine( $this->choices, $this->choices ) );
	}

	/**
	 * Detects whether an import for this plugin is needed
	 *
	 * @return bool Indicating whether there is something to import
	 */
	public function run_detect() {
		if ( true === $this->has_options() ) {
			return true;
		}
	}

	/**
	 * Removes the plugin data from the database.
	 *
	 * @return bool
	 */
	public function run_cleanup() {
		if ( ! $this->run_detect() ) {
			return false;
		}

		return $this->cleanup();
	}

	/**
	 * Removes the plugin data from the database.
	 *
	 * @return bool Cleanup status.
	 */
	public function cleanup() {
		global $wpdb;
		$result = false;

		if ( ! empty( $this->option_keys ) ) {
			$table = DB::query_builder( 'options' );
			foreach ( $this->option_keys as $option_key ) {
				$table->orWhere( 'option_name', $option_key );
			}

			$result = $table->delete();
		}

		if ( ! empty( $this->table_names ) ) {
			foreach ( $this->table_names as $table ) {
				$wpdb->query( "DROP TABLE {$wpdb->prefix}{$table}" ); // phpcs:ignore
			}
		}

		return $result;
	}

	/**
	 * Run importer routines
	 *
	 * @throws Exception Throw error if no perform function founds.
	 *
	 * @param string $perform The action to perform when running import action.
	 */
	public function run_import( $perform ) {

		if ( ! method_exists( $this, $perform ) ) {
			throw new Exception( esc_html__( 'Unable to perform action this time.', 'redirections' ) );
		}

		/**
		 * Number of items to import per run.
		 *
		 * @param int $items_per_page Default 100.
		 */
		$this->items_per_page = absint( $this->do_filter( 'importers/items_per_page', 100 ) );

		$hash_ok = array(
			'deactivate'   => esc_html__( 'Plugin deactivated successfully.', 'redirections' ),
			'redirections' => esc_html__( 'Imported %s redirections.', 'redirections' ),
		);

		$hash_failed = array(
			'redirections' => esc_html__( 'There are no redirection to import.', 'redirections' ),
		);

		$result = $this->$perform();
		if ( is_array( $result ) ) {
			$message = $hash_ok[ $perform ];
			$this->success( $result );
		}

		if ( true === $result ) {
			$this->success( $hash_ok[ $perform ] );
		}

		$this->error( $hash_failed[ $perform ] );
	}

	/**
	 * Deactivate plugin action.
	 */
	protected function deactivate() {
		if ( is_plugin_active( $this->get_plugin_file() ) ) {
			deactivate_plugins( $this->get_plugin_file() );
		}

		return true;
	}

	/**
	 * Replce settings based on key/value hash.
	 *
	 * @param array $hash        Array of hash for search and replace.
	 * @param array $source      Array for source where to search.
	 * @param array $destination Array for destination where to save.
	 * @param bool  $convert     (Optional) Conversion type. Default: false.
	 */
	protected function replace( $hash, $source, &$destination, $convert = false ) {
		foreach ( $hash as $search => $replace ) {
			if ( ! isset( $source[ $search ] ) ) {
				continue;
			}

			$destination[ $replace ] = false === $convert ? $source[ $search ] : $this->$convert( $source[ $search ] );
		}
	}

	/**
	 * Has options.
	 *
	 * @return bool
	 */
	private function has_options() {
		if ( empty( $this->option_keys ) ) {
			return false;
		}

		$table = DB::query_builder( 'options' )->selectCount( '*', 'count' );
		foreach ( $this->option_keys as $option_key ) {
			$table->orWhere( 'option_name', $option_key );
		}

		return ( absint( $table->getVar() ) > 0 ) ? true : false;
	}
}

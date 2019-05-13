<?php
/**
 * An interface for registering hooks with WordPress.
 *
 * @since      1.0.0
 * @package    RankMath
 * @subpackage RankMath_Redirections\Core
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath_Redirections;

defined( 'ABSPATH' ) || exit;

/**
 * Runner.
 */
interface Runner {

	/**
	 * Register all hooks to WordPress
	 *
	 * @return void
	 */
	public function hooks();
}

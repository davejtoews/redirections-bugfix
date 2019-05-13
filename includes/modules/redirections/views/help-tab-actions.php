<?php
/**
 * On-Screen help tab.
 *
 * @package    RankMath
 * @subpackage RankMath_Redirections\Redirections
 */

?>
<p>
	<?php esc_html_e( 'Hovering over a row in the list will display action links that allow you to manage the item. You can perform the following actions:', 'redirections' ); ?>
</p>
<ul>
	<li><?php echo wp_kses_post( __( '<strong>Edit</strong> redirection details: from/to URLs and the redirection type.', 'redirections' ) ); ?></li>
	<li><?php echo wp_kses_post( __( '<strong>Activate/Deactivate</strong> redirections. Deactivated redirections do not take effect on your site.', 'redirections' ) ); ?></li>
	<li><?php echo wp_kses_post( __( '<strong>Delete</strong> permanently removes the redirection.', 'redirections' ) ); ?></li>
</ul>

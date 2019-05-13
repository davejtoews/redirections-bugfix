<?php
/**
 * Help page template.
 *
 * @package    RankMath
 * @subpackage RankMath_Redirections\Admin
 */

$tabs = apply_filters( 'rank_math/help/tabs', array() );
?>
<div class="wrap rank-math-wrap limit-wrap">

	<span class="wp-header-end"></span>

	<h1 class="page-title"><?php esc_html_e( 'Help &amp; Support', 'redirections' ); ?></h1>
	<br>

	<div id="rank-math-help-wrapper" class="rank-math-tabs">

		<div class="rank-math-tabs-content">
			<?php foreach ( $tabs as $id => $tab ) : ?>
			<div id="help-panel-<?php echo $id; ?>" class="rank-math-tab">
				<?php include $tab['view']; ?>
			</div>
			<?php endforeach; ?>
		</div>

	</div>

</div>

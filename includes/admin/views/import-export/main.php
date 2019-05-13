<?php
/**
 * Import/Export page template.
 *
 * @package    RankMath
 * @subpackage RankMath_Redirections\Admin
 */

?>
<div class="wrap rank-math-wrap">

	<span class="wp-header-end"></span>

	<h1 class="page-title"><?php esc_html_e( 'Import &amp; Export', 'redirections' ); ?></h1>

	<p style="font-size: 1rem;">
		<?php
		/* translators: Link to learn about import export panel KB article */
		printf( esc_html__( 'Import your previous backed up setting. Or, Export your Rank Math settings and meta data for backup or for reuse on (another) blog. %s', 'redirections' ), '<a href="https://rankmath.com/kb/import-export-settings/" target="_blank">' . esc_html__( 'Learn more about the Import/Export option.', 'redirections' ) . '</a>' );
		?>
	</p>


	<div class="two-col rank-math-ui">

		<div class="col">

			<?php include_once 'export-panel.php'; ?>

		</div>

		<div class="col">

			<?php include_once 'plugins-panel.php'; ?>

		</div>

		<div class="col">

			<?php include_once 'import-panel.php'; ?>

		</div>

	</div>

</div>

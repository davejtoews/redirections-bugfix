<?php
/**
 * Redirections general settings.
 *
 * @package    RankMath
 * @subpackage RankMath_Redirections\Redirections
 */

use RankMath_Redirections\Helper;

$cmb->add_field( array(
	'id'      => 'redirections_debug',
	'type'    => 'switch',
	'name'    => esc_html__( 'Debug Redirections', 'redirections' ),
	'desc'    => esc_html__( 'Display the Debug Console instead of being redirected. Administrators only.', 'redirections' ),
	'default' => 'off',
) );

$cmb->add_field( array(
	'id'      => 'redirections_fallback',
	'type'    => 'radio',
	'name'    => esc_html__( 'Fallback Behavior', 'redirections' ),
	'desc'    => esc_html__( 'If nothing similar is found, this behavior will be applied.', 'redirections' ),
	'options' => array(
		'default'  => esc_html__( 'Default 404', 'redirections' ),
		'homepage' => esc_html__( 'Redirect to Homepage', 'redirections' ),
		'custom'   => esc_html__( 'Custom Redirection', 'redirections' ),
	),
	'default' => 'default',
) );

$cmb->add_field( array(
	'id'   => 'redirections_custom_url',
	'type' => 'text',
	'name' => esc_html__( 'Custom Url ', 'redirections' ),
	'dep'  => array( array( 'redirections_fallback', 'custom' ) ),
) );

$cmb->add_field( array(
	'id'      => 'redirections_header_code',
	'type'    => 'select',
	'name'    => esc_html__( 'Redirection Type', 'redirections' ),
	'options' => Helper::choices_redirection_types(),
	'default' => '301',
) );

$cmb->add_field( array(
	'id'      => 'redirections_post_redirect',
	'type'    => 'switch',
	'name'    => esc_html__( 'Auto Post Redirect', 'redirections' ),
	'desc'    => esc_html__( 'Extend the functionality of WordPress by creating redirects in our plugin when you change the slug of a post, page, category or a CPT. You can modify the redirection further according to your needs.', 'redirections' ),
	'default' => 'off',
) );

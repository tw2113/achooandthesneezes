<?php
/*
 * Plugin Name: Creative Writing Resources
 * Plugin URI: Plugin URI
 * Description: Sets up metaboxes for related data for writing pieces via CMB2
 * Version: 1.0.0
 * Author: Michael Beckwith
 * Author URI: http://michaelbox.net
 * License: GPLv2
 * Text Domain: Text domain to use
 */

namespace tw2113;

function metaboxes() {

	$prefix = '_cwr_';

	$cwr = new_cmb2_box( [
		'id'           => 'creative-writing-resources',
		'title'        => 'External resources for this piece',
		'object_types' => [ 'stories' ],
		'context'      => 'normal',
		'priority'     => 'high',
	] );

	$cwr->add_field( [
		'name' => 'External Source Link Text',
		'id'   => $prefix . 'external_published_link_text',
		'type' => 'text',
	] );

	$cwr->add_field( [
		'name' => 'URL published elsewhere',
		'id'   => $prefix . 'external_published_url',
		'type' => 'text',
	] );

	$cwr->add_field( [
		'name'        => 'Original Publish Date',
		'id'          => $prefix . 'external_published_date',
		'type'        => 'text_date',
		'date_format' => get_option( 'date_format' ),
	] );

	$docstatus = new_cmb2_box( [
		'id'           => 'document-status',
		'title'        => 'Document Status',
		'object_types' => [ 'stories' ],
		'context'      => 'side',
		'priority'     => 'high',
	] );

	$docstatus->add_field( [
		'name'             => 'Document state',
		'id'               => $prefix . 'docstate',
		'type'             => 'select',
		'show_option_none' => true,
		'options'          => [
			'draft'         => 'Draft',
			'editor-review' => 'Editor Review',
			'final'         => 'Final',
		],
	] );
}
add_action( 'cmb2_admin_init', __NAMESPACE__ . '\metaboxes' );

function cwr_get_external_data( $post_id ) {
	$data = [];

	$data['link_text']   = get_post_meta( $post_id, '_cwr_external_published_link_text', true );
	$data['link_url']    = get_post_meta( $post_id, '_cwr_external_published_url', true );
	$data['posted_date'] = get_post_meta( $post_id, '_cwr_external_published_date', true );

	return $data;
}

function cwr_external_data_formatted( $post_id = 0 ) {
	if ( empty( $post_id ) ) {
		$post_id = get_the_ID();
	}

	$data = cwr_get_external_data( $post_id );

	if ( empty( $data['link_url'] ) ) {
		return '';
	}

	$link = sprintf(
		'<a href="%s">%s</a>',
		$data['link_url'],
		$data['link_text']
	);

	$overall_pattern = '<blockquote>Also posted at %s on %s</blockquote>';

	return sprintf(
		$overall_pattern,
		$link,
		$data['posted_date']
	);
}

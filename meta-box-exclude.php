<?php
$prefix = '_es_';

global $meta_boxes;

$meta_boxes = array();


$meta_boxes[] = array(
	// Meta box id, UNIQUE per meta box
	'id' => 'venue_template',

	// Meta box title - Will appear at the drag and drop handle bar
	'title' => 'Template',

	// Post types, accept custom post types as well - DEFAULT is array('post'); (optional)
	'pages' => array( 'venues' ),

	// Where the meta box appear: normal (default), advanced, side; optional
	'context' => 'normal',

	// Order of meta box: high (default), low; optional
	'priority' => 'high',

	// List of meta fields
	'fields' => array(
	    array(
	        'name'    => 'Template to use:',
	        'id'      => $prefix . "venue_template",
	        'type'    => 'taxonomy',
	        'options' => array(
	        	// Taxonomy name
	        	'taxonomy' => 'venue-categories',
	        	// How to show taxonomy: 'checkbox_list' (default) or 'checkbox_tree', 'select_tree' or 'select'. Optional
	        	'type' => 'select_tree',
	        	// Additional arguments for get_terms() function. Optional
	        	'args' => array(
	        		'hide_empty' => 0
	        	)
	        )
		)

	),
	// 'only_on'    => array(
	// 	// 'id'       => array( 10 ),
	// 	// 'slug'  => array( 'news', 'blog' ),
	// 	// 'template' => array( 'fullwidth.php', 'simple.php' ),
	// 	'parent'   => array( 0 )
	// ),
	'not_on' => array(
		// 'id'       => array( 10 ),
		// 'slug'  => array( 'news', 'blog' ),
		// 'template' => array( 'fullwidth.php', 'simple.php' ),
		'parent'   => array( 0 )
	)
);



/**
 * Register meta boxes
 *
 * @return void
 */
function rw_register_meta_boxes()
{
	global $meta_boxes;

	// Make sure there's no errors when the plugin is deactivated or during upgrade
	if ( class_exists( 'RW_Meta_Box' ) ) {
		foreach ( $meta_boxes as $meta_box ) {
			if( isset( $meta_box[ 'not_on' ] ) && !rw_maybe_include( $meta_box[ 'not_on' ], 0 ) ) {
				continue;
			}
			if( isset( $meta_box['only_on'] ) && !rw_maybe_include( $meta_box['only_on'], 1 ) ) {
				continue;
			}

			new RW_Meta_Box( $meta_box );
		}
	}
}

add_action( 'admin_init', 'rw_register_meta_boxes' );

/**
 * Check if meta boxes is included
 *
 * @return bool
 */
function rw_maybe_include( $conditions, $bool = -1 ) {
	// Include in back-end only
	if ( ! defined( 'WP_ADMIN' ) || ! WP_ADMIN ) {
		return false;
	}

	// Always include for ajax
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return true;
	}

	if ( isset( $_GET['post'] ) ) {
		$post_id = $_GET['post'];
	}
	elseif ( isset( $_POST['post_ID'] ) ) {
		$post_id = $_POST['post_ID'];
	}
	else {
		$post_id = false;
	}

	$post_id = (int) $post_id;
	$post    = get_post( $post_id );
	switch( $bool ) {
		// if we're including (only_on)
		case 1:
			foreach ( $conditions as $cond => $v ) {
				// Catch non-arrays too
				if ( ! is_array( $v ) ) {
					$v = array( $v );
				}

				switch ( $cond ) {
					case 'id':
						if ( in_array( $post_id, $v ) ) {
							return true;
						}
					break;
					case 'parent':
						$post_parent = $post->post_parent;
						if ( in_array( $post_parent, $v ) ) {
							return true;
						}
					break;
					case 'slug':
						$post_slug = $post->post_name;
						if ( in_array( $post_slug, $v ) ) {
							return true;
						}
					break;
					case 'template':
						$template = get_post_meta( $post_id, '_wp_page_template', true );
						if ( in_array( $template, $v ) ) {
							return true;
						}
					break;
				}
			}
			break;
		// when we're excluding (not_on)
		case 0:
			foreach ( $conditions as $cond => $v ) {
				// Catch non-arrays too
				if ( ! is_array( $v ) ) {
					$v = array( $v );
				}

				switch ( $cond ) {
					case 'id':
						if ( !in_array( $post_id, $v ) ) {
							return true;
						}
					break;
					case 'parent':
						$post_parent = $post->post_parent;
						if ( !in_array( $post_parent, $v ) ) {
							return true;
						}
					break;
					case 'slug':
						$post_slug = $post->post_name;
						if ( !in_array( $post_slug, $v ) ) {
							return true;
						}
					break;
					case 'template':
						$template = get_post_meta( $post_id, '_wp_page_template', true );
						if ( !in_array( $template, $v ) ) {
							return true;
						}
					break;
				}
			}
			break;
		default:
			return true;
	}


	// If no condition matched
	return false;
} ?>
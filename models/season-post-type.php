<?php

// let's create the function for the custom type
function season_post_type() { 
  global $vb_wpv_basedir;
	// creating (registering) the custom type 
	register_post_type( 'season_type', /* (http://codex.wordpress.org/Function_Reference/register_post_type) */
		// let's now add all the options for this post type
		array( 'labels' => array(
			'name' => __( 'Seasons', 'wpvacancy' ), /* This is the Title of the Group */
			'singular_name' => __( 'Season', 'wpvacancy' ), /* This is the individual type */
			'all_items' => __( 'All seasons', 'wpvacancy' ), /* the all items menu item */
			'add_new' => __( 'Add season', 'wpvacancy' ), /* The add new menu item */
			'add_new_item' => __( 'Add a new season', 'wpvacancy' ), /* Add New Display Title */
			'edit' => __( 'Edit', 'wpvacancy' ), /* Edit Dialog */
			'edit_item' => __( 'Edit season', 'wpvacancy' ), /* Edit Display Title */
			'new_item' => __( 'New season', 'wpvacancy' ), /* New Display Title */
			'view_item' => __( 'Show season', 'wpvacancy' ), /* View Display Title */
			'search_items' => __( 'Search seasons', 'wpvacancy' ), /* Search Custom Type Title */ 
			'not_found' =>  __( 'No seasons found', 'wpvacancy' ), /* This displays if there are no entries yet */ 
			'not_found_in_trash' => __( 'No seasons in trash', 'wpvacancy' ), /* This displays if there is nothing in the trash */
			'parent_item_colon' => ''
			), /* end of arrays */
			'description' => __( 'Seasons in your resort or hotel', 'wpvacancy' ), /* Custom Type Description */
			'public' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'show_ui' => true,
			'query_var' => true,
			'menu_position' => 82, /* this is what order you want it to appear in on the left hand side menu */ 
			'menu_icon' => 'dashicons-chart-line', /* the icon for the custom post type menu */
			'rewrite'	=> array( 'slug' => 'season_type', 'with_front' => true ), /* you can specify its url slug */
			'has_archive' => false, /* you can rename the slug here */
			'capability_type' => 'post',
			'hierarchical' => false,
			/* the next one is important, it tells what's enabled in the post editor */
			'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'sticky', 'page-attributes')
		) /* end of options */
	); /* end of register post type */
		
}

	// adding the function to the Wordpress init
	add_action( 'init', 'season_post_type');
	
	
/**
 * Define the metabox and field configurations.
 */
function season_custom_fields() {

  global $vb_wpv_custom_fields_prefix ;
    // Start with an underscore to hide fields from custom fields list
    $prefix = $vb_wpv_custom_fields_prefix;

    /**
     * Initiate the metabox
     */
    $cmb = new_cmb2_box( array(
        'id'            => 'season_rules',
        'title'         => __( 'Season rules', 'wpvacancy' ),
        'object_types'  => array( 'season_type', ), // Post type
        'context'       => 'normal',
        'priority'      => 'high',
        'show_names'    => true, // Show field names on the left
        // 'cmb_styles' => false, // false to disable the CMB stylesheet
        'closed'     => false, // Keep the metabox closed by default
    ) );

   
  $cmb->add_field( array(
      'name'       => __( 'Periods', 'wpvancancy' ),
      'desc'       => __( 'The periods that belong to this season', 'wpvacancy' ),
      'id'         => $prefix . 'season_periods',
  		'type'       => 'custom_attached_posts',
      'options' => array(
        'show_thumbnails' => true, // Show thumbnails on the left
        'filter_boxes'    => true, // Show a text box for filtering the results
        'query_args'      => array(
          'posts_per_page' => 20,
          'post_type'      => 'period_type',
        ), // override the get_posts args
      ),
      'attributes' => array(
          'data-validation' => 'required',
      ),
      // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
      // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
      // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
      'on_front'        => false, // Optionally designate a field to wp-admin only
      // 'repeatable'      => true,
  ));
}

add_action( 'cmb2_admin_init', 'season_custom_fields' );


?>

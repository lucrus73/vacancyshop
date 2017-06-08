<?php

// let's create the function for the custom type
function season_post_type() { 
	// creating (registering) the custom type 
	register_post_type( 'season_type', /* (http://codex.wordpress.org/Function_Reference/register_post_type) */
		// let's now add all the options for this post type
		array( 'labels' => array(
			'name' => __( 'Seasons', 'wpvacance' ), /* This is the Title of the Group */
			'singular_name' => __( 'Season', 'wpvacance' ), /* This is the individual type */
			'all_items' => __( 'All seasons', 'wpvacance' ), /* the all items menu item */
			'add_new' => __( 'Add season', 'wpvacance' ), /* The add new menu item */
			'add_new_item' => __( 'Add a new season', 'wpvacance' ), /* Add New Display Title */
			'edit' => __( 'Edit', 'wpvacance' ), /* Edit Dialog */
			'edit_item' => __( 'Edit season', 'wpvacance' ), /* Edit Display Title */
			'new_item' => __( 'New season', 'wpvacance' ), /* New Display Title */
			'view_item' => __( 'Show season', 'wpvacance' ), /* View Display Title */
			'search_items' => __( 'Search seasons', 'wpvacance' ), /* Search Custom Type Title */ 
			'not_found' =>  __( 'No seasons found', 'wpvacance' ), /* This displays if there are no entries yet */ 
			'not_found_in_trash' => __( 'No seasons in trash', 'wpvacance' ), /* This displays if there is nothing in the trash */
			'parent_item_colon' => ''
			), /* end of arrays */
			'description' => __( 'Seasons in your resort or hotel', 'wpvacance' ), /* Custom Type Description */
			'public' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'show_ui' => true,
			'query_var' => true,
			'menu_position' => 9, /* this is what order you want it to appear in on the left hand side menu */ 
			'menu_icon' => plugin_dir_path( __FILE__ ) . 'images/season_type-icon.png', /* the icon for the custom post type menu */
			'rewrite'	=> array( 'slug' => 'season_type', 'with_front' => true ), /* you can specify its url slug */
			'has_archive' => false, /* you can rename the slug here */
			'capability_type' => 'post',
			'hierarchical' => false,
			/* the next one is important, it tells what's enabled in the post editor */
			'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'sticky', 'page-attributes')
		) /* end of options */
	); /* end of register post type */
	
	// now let's add custom categories (these act like categories)
	
  register_taxonomy( 'season_cat', 
    array('season_type'), /* if you change the name of register_post_type( 'custom_type', then you have to change this */
    array('hierarchical' => true,     /* if this is true, it acts like categories */
      'labels' => array(
        'name' => __( 'Season categories', 'wpvacance' ), /* name of the custom taxonomy */
        'singular_name' => __( 'Season category', 'wpvacance' ), /* single taxonomy name */
        'search_items' =>  __( 'Search season categories', 'wpvacance' ), /* search title for taxomony */
        'all_items' => __( 'All season categories', 'wpvacance' ), /* all title for taxonomies */
        'parent_item' => __( 'Parent category', 'wpvacance' ), /* parent title for taxonomy */
        'parent_item_colon' => __( 'Parent category:', 'wpvacance' ), /* parent taxonomy title */
        'edit_item' => __( 'Edit season category', 'wpvacance' ), /* edit custom taxonomy title */
        'update_item' => __( 'Update season category', 'wpvacance' ), /* update title for taxonomy */
        'add_new_item' => __( 'Add season category', 'wpvacance' ), /* add new title for taxonomy */
        'new_item_name' => __( 'New season category', 'wpvacance' ) /* name title for taxonomy */
      ),
      'show_admin_column' => true, 
      'show_ui' => true,
      'query_var' => true,
      'rewrite' => array( 'slug' => 'season-category' ),
    )
  );

  // now let's add custom tags (these act like categories)
  register_taxonomy( 'season_tag', 
    array('season_type'), /* if you change the name of register_post_type( 'custom_type', then you have to change this */
    array('hierarchical' => false,    /* if this is false, it acts like tags */
      'labels' => array(
        'name' => __( 'Season Tags', 'wpvacance' ), /* name of the custom taxonomy */
        'singular_name' => __( 'Season Tag', 'wpvacance' ), /* single taxonomy name */
        'search_items' =>  __( 'Search season tags', 'wpvacance' ), /* search title for taxomony */
        'all_items' => __( 'All season tags', 'wpvacance' ), /* all title for taxonomies */
        'parent_item' => __( 'Parent tag', 'wpvacance' ), /* parent title for taxonomy */
        'parent_item_colon' => __( 'Parent tag:', 'wpvacance' ), /* parent taxonomy title */
        'edit_item' => __( 'Edit tag', 'wpvacance' ), /* edit custom taxonomy title */
        'update_item' => __( 'Update tag', 'wpvacance' ), /* update title for taxonomy */
        'add_new_item' => __( 'Add season tag', 'wpvacance' ), /* add new title for taxonomy */
        'new_item_name' => __( 'New season tag name', 'wpvacance' ) /* name title for taxonomy */
      ),
      'show_admin_column' => true,
      'show_ui' => true,
      'query_var' => true,
    )
  );

	/* this adds your post categories to your custom post type */
	register_taxonomy_for_object_type( 'category', 'season_type' );
	/* this adds your post tags to your custom post type */
	register_taxonomy_for_object_type( 'post_tag', 'season_type' );
	
}

	// adding the function to the Wordpress init
	add_action( 'init', 'season_post_type');
	
	
/**
 * Define the metabox and field configurations.
 */
function season_custom_fields() {

  global $vb_wpv_custom_fields_prefix ;
  global $vb_wpv_weekdays;
    // Start with an underscore to hide fields from custom fields list
    $prefix = $vb_wpv_custom_fields_prefix;

    /**
     * Initiate the metabox
     */
    $cmb = new_cmb2_box( array(
        'id'            => 'season_rules',
        'title'         => __( 'Season rules', 'wpvancance' ),
        'object_types'  => array( 'season_type', ), // Post type
        'context'       => 'normal',
        'priority'      => 'high',
        'show_names'    => true, // Show field names on the left
        // 'cmb_styles' => false, // false to disable the CMB stylesheet
        'closed'     => false, // Keep the metabox closed by default
    ) );

   
    $cmb->add_field( array(
        'name'       => __( 'Bookings start on', 'wpvancance' ),
        'desc'       => __( 'Choose days bookings periods must start on. Check all or none if any day will do.', 'wpvancance' ),
        'id'         => $prefix . 'start_on_weekday',
        'type'       => 'multicheck',
        'options'    => $vb_wpv_weekdays,
        // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
        // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
        // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
        'on_front'        => false, // Optionally designate a field to wp-admin only
        // 'repeatable'      => true,
    ) );

    $cmb->add_field( array(
        'name'       => __( 'Min nights for booking', 'wpvancance' ),
        'desc'       => __( 'Defaults to one night. If set, bookings must last a multiple of this number of nights.', 'wpvancance' ),
        'id'         => $prefix . 'min_nights_for_bookings',
        'type'       => 'text',
        'options'    => $vb_wpv_weekdays,
        // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
        // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
        // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
        'on_front'        => false, // Optionally designate a field to wp-admin only
        // 'repeatable'      => true,
    ) );

}

add_action( 'cmb2_admin_init', 'season_custom_fields' );


?>

<?php

// let's create the function for the custom type
function accommodation_post_type() { 
	// creating (registering) the custom type 
	register_post_type( 'accommodation_type', /* (http://codex.wordpress.org/Function_Reference/register_post_type) */
		// let's now add all the options for this post type
		array( 'labels' => array(
			'name' => __( 'Accommodations', 'wpvacance' ), /* This is the Title of the Group */
			'singular_name' => __( 'Accommodation', 'wpvacance' ), /* This is the individual type */
			'all_items' => __( 'All accommodations', 'wpvacance' ), /* the all items menu item */
			'add_new' => __( 'Add accommodation', 'wpvacance' ), /* The add new menu item */
			'add_new_item' => __( 'Add a new accommodation', 'wpvacance' ), /* Add New Display Title */
			'edit' => __( 'Edit', 'wpvacance' ), /* Edit Dialog */
			'edit_item' => __( 'Edit accommodation', 'wpvacance' ), /* Edit Display Title */
			'new_item' => __( 'New accommodation', 'wpvacance' ), /* New Display Title */
			'view_item' => __( 'Show accommodation', 'wpvacance' ), /* View Display Title */
			'search_items' => __( 'Search accommodations', 'wpvacance' ), /* Search Custom Type Title */ 
			'not_found' =>  __( 'No accommodations found', 'wpvacance' ), /* This displays if there are no entries yet */ 
			'not_found_in_trash' => __( 'No accommodations in trash', 'wpvacance' ), /* This displays if there is nothing in the trash */
			'parent_item_colon' => ''
			), /* end of arrays */
			'description' => __( 'Accommodations in your resort or rooms in your hotel', 'wpvacance' ), /* Custom Type Description */
			'public' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'show_ui' => true,
			'query_var' => true,
			'menu_position' => 9, /* this is what order you want it to appear in on the left hand side menu */ 
			'menu_icon' => plugin_dir_path( __FILE__ ) . 'images/accommodation_type-icon.png', /* the icon for the custom post type menu */
			'rewrite'	=> array( 'slug' => 'accommodation_type', 'with_front' => true ), /* you can specify its url slug */
			'has_archive' => false, /* you can rename the slug here */
			'capability_type' => 'post',
			'hierarchical' => false,
			/* the next one is important, it tells what's enabled in the post editor */
			'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'sticky', 'page-attributes')
		) /* end of options */
	); /* end of register post type */
	
	// now let's add custom categories (these act like categories)
	
  register_taxonomy( 'accommodation_cat', 
    array('accommodation_type'), /* if you change the name of register_post_type( 'custom_type', then you have to change this */
    array('hierarchical' => true,     /* if this is true, it acts like categories */
      'labels' => array(
        'name' => __( 'Accommodation categories', 'wpvacance' ), /* name of the custom taxonomy */
        'singular_name' => __( 'Accommodation category', 'wpvacance' ), /* single taxonomy name */
        'search_items' =>  __( 'Search accommodation categories', 'wpvacance' ), /* search title for taxomony */
        'all_items' => __( 'All accommodation categories', 'wpvacance' ), /* all title for taxonomies */
        'parent_item' => __( 'Parent category', 'wpvacance' ), /* parent title for taxonomy */
        'parent_item_colon' => __( 'Parent category:', 'wpvacance' ), /* parent taxonomy title */
        'edit_item' => __( 'Edit accommodation category', 'wpvacance' ), /* edit custom taxonomy title */
        'update_item' => __( 'Update accommodation category', 'wpvacance' ), /* update title for taxonomy */
        'add_new_item' => __( 'Add accommodation category', 'wpvacance' ), /* add new title for taxonomy */
        'new_item_name' => __( 'New accommodation category', 'wpvacance' ) /* name title for taxonomy */
      ),
      'show_admin_column' => true, 
      'show_ui' => true,
      'query_var' => true,
      'rewrite' => array( 'slug' => 'accommodation-category' ),
    )
  );

  // now let's add custom tags (these act like categories)
  register_taxonomy( 'accommodation_tag', 
    array('accommodation_type'), /* if you change the name of register_post_type( 'custom_type', then you have to change this */
    array('hierarchical' => false,    /* if this is false, it acts like tags */
      'labels' => array(
        'name' => __( 'Accommodation Tags', 'wpvacance' ), /* name of the custom taxonomy */
        'singular_name' => __( 'Accommodation Tag', 'wpvacance' ), /* single taxonomy name */
        'search_items' =>  __( 'Search accommodation tags', 'wpvacance' ), /* search title for taxomony */
        'all_items' => __( 'All accommodation tags', 'wpvacance' ), /* all title for taxonomies */
        'parent_item' => __( 'Parent tag', 'wpvacance' ), /* parent title for taxonomy */
        'parent_item_colon' => __( 'Parent tag:', 'wpvacance' ), /* parent taxonomy title */
        'edit_item' => __( 'Edit tag', 'wpvacance' ), /* edit custom taxonomy title */
        'update_item' => __( 'Update tag', 'wpvacance' ), /* update title for taxonomy */
        'add_new_item' => __( 'Add accommodation tag', 'wpvacance' ), /* add new title for taxonomy */
        'new_item_name' => __( 'New accommodation tag name', 'wpvacance' ) /* name title for taxonomy */
      ),
      'show_admin_column' => true,
      'show_ui' => true,
      'query_var' => true,
    )
  );

	/* this adds your post categories to your custom post type */
	register_taxonomy_for_object_type( 'category', 'accommodation_type' );
	/* this adds your post tags to your custom post type */
	register_taxonomy_for_object_type( 'post_tag', 'accommodation_type' );
	
}

	// adding the function to the Wordpress init
	add_action( 'init', 'accommodation_post_type');
	
	
/**
 * Define the metabox and field configurations.
 */
function vb_wpv_accommodation_custom_fields() {

  global $vb_wpv_custom_fields_prefix ;
  // Start with an underscore to hide fields from custom fields list
  $prefix = $vb_wpv_custom_fields_prefix;

  /**
   * Initiate the metabox
   */
  $cmb = new_cmb2_box( array(
      'id'            => 'accommodation_meta',
      'title'         => __( 'Accomodation data', 'wpvancance' ),
      'object_types'  => array( 'accommodation_type', ), // Post type
      'context'       => 'normal',
      'priority'      => 'high',
      'show_names'    => true, // Show field names on the left
      // 'cmb_styles' => false, // false to disable the CMB stylesheet
      'closed'     => false, // Keep the metabox closed by default
  ) );

   
  $cmb->add_field( array(
      'name'       => __( 'Map ID', 'wpvancance' ),
      'desc'       => __( 'The ID of the map where this accommodation unit is placed', 'wpvancance' ),
      'id'         => $prefix . 'acc_map_id',
      'type'       => 'text',
      // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
      // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
      // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
      'on_front'        => false, // Optionally designate a field to wp-admin only
      // 'repeatable'      => true,
  ) );

  $cmb->add_field( array(
      'name'       => __( 'Unit left position', 'wpvancance' ),
      'desc'       => __( 'The X coordinate of the upper-left corner of the unit box (in percentage of the map width)', 'wpvancance' ),
      'id'         => $prefix . 'acc_unit_box_x',
      'type'       => 'text',
      // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
      // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
      // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
      'on_front'        => false, // Optionally designate a field to wp-admin only
      // 'repeatable'      => true,
  ) );

  $cmb->add_field( array(
      'name'       => __( 'Unit top position', 'wpvancance' ),
      'desc'       => __( 'The Y coordinate of the upper-left corner of the unit box (in percentage of the map height)', 'wpvancance' ),
      'id'         => $prefix . 'acc_unit_box_y',
      'type'       => 'text',
      // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
      // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
      // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
      'on_front'        => false, // Optionally designate a field to wp-admin only
      // 'repeatable'      => true,
  ) );
  
  $cmb->add_field( array(
      'name'       => __( 'Unit width', 'wpvancance' ),
      'desc'       => __( 'The width of the unit box (in percentage of the map width)', 'wpvancance' ),
      'id'         => $prefix . 'acc_unit_box_w',
      'type'       => 'text',
      // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
      // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
      // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
      'on_front'        => false, // Optionally designate a field to wp-admin only
      // 'repeatable'      => true,
  ) );

  $cmb->add_field( array(
      'name'       => __( 'Unit height', 'wpvancance' ),
      'desc'       => __( 'The height of the unit box (in percentage of the map height)', 'wpvancance' ),
      'id'         => $prefix . 'acc_unit_box_h',
      'type'       => 'text',
      // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
      // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
      // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
      'on_front'        => false, // Optionally designate a field to wp-admin only
      // 'repeatable'      => true,
  ) );

  $cmb->add_field( array(
      'name'       => __( 'Name', 'wpvancance' ),
      'desc'       => __( 'The name of this accommodation unit', 'wpvancance' ),
      'id'         => $prefix . 'name',
      'type'       => 'text',
      // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
      // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
      // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
      'on_front'        => false, // Optionally designate a field to wp-admin only
      // 'repeatable'      => true,
  ) );

  $cmb->add_field( array(
      'name'       => __( 'Extra CSS class', 'wpvancance' ),
      'desc'       => __( 'Optional extra CSS class to apply', 'wpvancance' ),
      'id'         => $prefix . 'css_class',
      'type'       => 'text',
      // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
      // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
      // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
      'on_front'        => false, // Optionally designate a field to wp-admin only
      // 'repeatable'      => true,
  ) );

  $cmb->add_field( array(
      'name'       => __( 'Pax', 'wpvancance' ),
      'desc'       => __( 'How many people can fit into this unit', 'wpvancance' ),
      'id'         => $prefix . 'pax',
      'type'       => 'text',
      // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
      // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
      // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
      'on_front'        => false, // Optionally designate a field to wp-admin only
      // 'repeatable'      => true,
  ) );

}

add_action( 'cmb2_admin_init', 'vb_wpv_accommodation_custom_fields' );

  


?>

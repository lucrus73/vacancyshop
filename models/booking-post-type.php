<?php

// let's create the function for the custom type
function booking_post_type() { 
	// creating (registering) the custom type 
	register_post_type( 'booking_type', /* (http://codex.wordpress.org/Function_Reference/register_post_type) */
		// let's now add all the options for this post type
		array( 'labels' => array(
			'name' => __( 'Bookings', 'wpvacance' ), /* This is the Title of the Group */
			'singular_name' => __( 'Booking', 'wpvacance' ), /* This is the individual type */
			'all_items' => __( 'All bookings', 'wpvacance' ), /* the all items menu item */
			'add_new' => __( 'Add booking', 'wpvacance' ), /* The add new menu item */
			'add_new_item' => __( 'Add a new booking', 'wpvacance' ), /* Add New Display Title */
			'edit' => __( 'Edit', 'wpvacance' ), /* Edit Dialog */
			'edit_item' => __( 'Edit booking', 'wpvacance' ), /* Edit Display Title */
			'new_item' => __( 'New booking', 'wpvacance' ), /* New Display Title */
			'view_item' => __( 'Show booking', 'wpvacance' ), /* View Display Title */
			'search_items' => __( 'Search bookings', 'wpvacance' ), /* Search Custom Type Title */ 
			'not_found' =>  __( 'No bookings found', 'wpvacance' ), /* This displays if there are no entries yet */ 
			'not_found_in_trash' => __( 'No bookings in trash', 'wpvacance' ), /* This displays if there is nothing in the trash */
			'parent_item_colon' => ''
			), /* end of arrays */
			'description' => __( 'Bookings already placed', 'wpvacance' ), /* Custom Type Description */
			'public' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'show_ui' => true,
			'query_var' => true,
			'menu_position' => 9, /* this is what order you want it to appear in on the left hand side menu */ 
			'menu_icon' => plugin_dir_path( __FILE__ ) . 'images/booking_type-icon.png', /* the icon for the custom post type menu */
			'rewrite'	=> array( 'slug' => 'booking_type', 'with_front' => true ), /* you can specify its url slug */
			'has_archive' => false, /* you can rename the slug here */
			'capability_type' => 'post',
			'hierarchical' => false,
			/* the next one is important, it tells what's enabled in the post editor */
			'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'sticky', 'page-attributes')
		) /* end of options */
	); /* end of register post type */
	
	// now let's add custom categories (these act like categories)
	
  register_taxonomy( 'booking_cat', 
    array('booking_type'), /* if you change the name of register_post_type( 'custom_type', then you have to change this */
    array('hierarchical' => true,     /* if this is true, it acts like categories */
      'labels' => array(
        'name' => __( 'Booking categories', 'wpvacance' ), /* name of the custom taxonomy */
        'singular_name' => __( 'Booking category', 'wpvacance' ), /* single taxonomy name */
        'search_items' =>  __( 'Search booking categories', 'wpvacance' ), /* search title for taxomony */
        'all_items' => __( 'All booking categories', 'wpvacance' ), /* all title for taxonomies */
        'parent_item' => __( 'Parent category', 'wpvacance' ), /* parent title for taxonomy */
        'parent_item_colon' => __( 'Parent category:', 'wpvacance' ), /* parent taxonomy title */
        'edit_item' => __( 'Edit booking category', 'wpvacance' ), /* edit custom taxonomy title */
        'update_item' => __( 'Update booking category', 'wpvacance' ), /* update title for taxonomy */
        'add_new_item' => __( 'Add booking category', 'wpvacance' ), /* add new title for taxonomy */
        'new_item_name' => __( 'New booking category', 'wpvacance' ) /* name title for taxonomy */
      ),
      'show_admin_column' => true, 
      'show_ui' => true,
      'query_var' => true,
      'rewrite' => array( 'slug' => 'booking-category' ),
    )
  );

  // now let's add custom tags (these act like categories)
  register_taxonomy( 'booking_tag', 
    array('booking_type'), /* if you change the name of register_post_type( 'custom_type', then you have to change this */
    array('hierarchical' => false,    /* if this is false, it acts like tags */
      'labels' => array(
        'name' => __( 'Booking Tags', 'wpvacance' ), /* name of the custom taxonomy */
        'singular_name' => __( 'Booking Tag', 'wpvacance' ), /* single taxonomy name */
        'search_items' =>  __( 'Search booking tags', 'wpvacance' ), /* search title for taxomony */
        'all_items' => __( 'All booking tags', 'wpvacance' ), /* all title for taxonomies */
        'parent_item' => __( 'Parent tag', 'wpvacance' ), /* parent title for taxonomy */
        'parent_item_colon' => __( 'Parent tag:', 'wpvacance' ), /* parent taxonomy title */
        'edit_item' => __( 'Edit tag', 'wpvacance' ), /* edit custom taxonomy title */
        'update_item' => __( 'Update tag', 'wpvacance' ), /* update title for taxonomy */
        'add_new_item' => __( 'Add booking tag', 'wpvacance' ), /* add new title for taxonomy */
        'new_item_name' => __( 'New booking tag name', 'wpvacance' ) /* name title for taxonomy */
      ),
      'show_admin_column' => true,
      'show_ui' => true,
      'query_var' => true,
    )
  );

	/* this adds your post categories to your custom post type */
	register_taxonomy_for_object_type( 'category', 'booking_type' );
	/* this adds your post tags to your custom post type */
	register_taxonomy_for_object_type( 'post_tag', 'booking_type' );
	
}

	// adding the function to the Wordpress init
	add_action( 'init', 'booking_post_type');
	
	

?>

<?php

// let's create the function for the custom type
function booking_post_type() { 
  global $vb_wpv_basedir;
	// creating (registering) the custom type 
	register_post_type( 'booking_type', /* (http://codex.wordpress.org/Function_Reference/register_post_type) */
		// let's now add all the options for this post type
		array( 'labels' => array(
			'name' => __( 'Bookings', 'wpvacancy' ), /* This is the Title of the Group */
			'singular_name' => __( 'Booking', 'wpvacancy' ), /* This is the individual type */
			'all_items' => __( 'All bookings', 'wpvacancy' ), /* the all items menu item */
			'add_new' => __( 'Add booking', 'wpvacancy' ), /* The add new menu item */
			'add_new_item' => __( 'Add a new booking', 'wpvacancy' ), /* Add New Display Title */
			'edit' => __( 'Edit', 'wpvacancy' ), /* Edit Dialog */
			'edit_item' => __( 'Edit booking', 'wpvacancy' ), /* Edit Display Title */
			'new_item' => __( 'New booking', 'wpvacancy' ), /* New Display Title */
			'view_item' => __( 'Show booking', 'wpvacancy' ), /* View Display Title */
			'search_items' => __( 'Search bookings', 'wpvacancy' ), /* Search Custom Type Title */ 
			'not_found' =>  __( 'No bookings found', 'wpvacancy' ), /* This displays if there are no entries yet */ 
			'not_found_in_trash' => __( 'No bookings in trash', 'wpvacancy' ), /* This displays if there is nothing in the trash */
			'parent_item_colon' => ''
			), /* end of arrays */
			'description' => __( 'Bookings already placed', 'wpvacancy' ), /* Custom Type Description */
			'public' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'show_ui' => true,
			'query_var' => true,
			'menu_position' => 90, /* this is what order you want it to appear in on the left hand side menu */ 
			'menu_icon' => 'dashicons-tickets-alt', /* the icon for the custom post type menu */
			'rewrite'	=> array( 'slug' => 'booking_type', 'with_front' => true ), /* you can specify its url slug */
			'has_archive' => false, /* you can rename the slug here */
			'capability_type' => 'post',
			'hierarchical' => false,
			/* the next one is important, it tells what's enabled in the post editor */
			'supports' => array( 'title', 'editor', 'custom-fields')
		) /* end of options */
	); /* end of register post type */
	
	// now let's add custom categories (these act like categories)
	
  register_taxonomy( 'booking_cat', 
    array('booking_type'), /* if you change the name of register_post_type( 'custom_type', then you have to change this */
    array('hierarchical' => true,     /* if this is true, it acts like categories */
      'labels' => array(
        'name' => __( 'Booking categories', 'wpvacancy' ), /* name of the custom taxonomy */
        'singular_name' => __( 'Booking category', 'wpvacancy' ), /* single taxonomy name */
        'search_items' =>  __( 'Search booking categories', 'wpvacancy' ), /* search title for taxomony */
        'all_items' => __( 'All booking categories', 'wpvacancy' ), /* all title for taxonomies */
        'parent_item' => __( 'Parent category', 'wpvacancy' ), /* parent title for taxonomy */
        'parent_item_colon' => __( 'Parent category:', 'wpvacancy' ), /* parent taxonomy title */
        'edit_item' => __( 'Edit booking category', 'wpvacancy' ), /* edit custom taxonomy title */
        'update_item' => __( 'Update booking category', 'wpvacancy' ), /* update title for taxonomy */
        'add_new_item' => __( 'Add booking category', 'wpvacancy' ), /* add new title for taxonomy */
        'new_item_name' => __( 'New booking category', 'wpvacancy' ) /* name title for taxonomy */
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
        'name' => __( 'Booking Tags', 'wpvacancy' ), /* name of the custom taxonomy */
        'singular_name' => __( 'Booking Tag', 'wpvacancy' ), /* single taxonomy name */
        'search_items' =>  __( 'Search booking tags', 'wpvacancy' ), /* search title for taxomony */
        'all_items' => __( 'All booking tags', 'wpvacancy' ), /* all title for taxonomies */
        'parent_item' => __( 'Parent tag', 'wpvacancy' ), /* parent title for taxonomy */
        'parent_item_colon' => __( 'Parent tag:', 'wpvacancy' ), /* parent taxonomy title */
        'edit_item' => __( 'Edit tag', 'wpvacancy' ), /* edit custom taxonomy title */
        'update_item' => __( 'Update tag', 'wpvacancy' ), /* update title for taxonomy */
        'add_new_item' => __( 'Add booking tag', 'wpvacancy' ), /* add new title for taxonomy */
        'new_item_name' => __( 'New booking tag name', 'wpvacancy' ) /* name title for taxonomy */
      ),
      'show_admin_column' => true,
      'show_ui' => true,
      'query_var' => true,
    )
  );

	/* this adds your post categories to your custom post type */
	register_taxonomy_for_object_type( 'booking_cat', 'booking_type' );
	/* this adds your post tags to your custom post type */
	register_taxonomy_for_object_type( 'booking_tag', 'booking_type' );
	
}

	// adding the function to the Wordpress init
	add_action( 'init', 'booking_post_type');
	
/**
 * Define the metabox and field configurations.
 */
function vb_wpv_booking_custom_fields() {

  global $vb_wpv_custom_fields_prefix ;
  // Start with an underscore to hide fields from custom fields list
  $prefix = $vb_wpv_custom_fields_prefix;

  /**
   * Initiate the metabox
   */
  $cmb = new_cmb2_box( array(
      'id'            => 'booking_meta',
      'title'         => __( 'Booking data', 'wpvancancy' ),
      'object_types'  => array( 'booking_type', ), // Post type
      'context'       => 'normal',
      'priority'      => 'high',
      'show_names'    => true, // Show field names on the left
      // 'cmb_styles' => false, // false to disable the CMB stylesheet
      'closed'     => false, // Keep the metabox closed by default
  ) );

  $cmb->add_field( array(
		'name'    => __( 'User', 'wpvacancy' ),
		'desc'    => __( 'The user this booking belongs to', 'wpvacancy' ),
		'id'      => $prefix . 'booking_user_id',
		'type'    => 'custom_attached_posts',
		'options' => array(
			'show_thumbnails' => true, // Show thumbnails on the left
			'filter_boxes'    => true, // Show a text box for filtering the results
      'query_users'     => true
		                  ),
      'attributes' => array(
          'data-validation' => 'required',
      ),
      'on_front'        => false, // Optionally designate a field to wp-admin only
	));
   
  $cmb->add_field( array(
      'name'       => __( 'Accommodation', 'wpvancancy' ),
      'desc'       => __( 'The accommodation unit this booking refers to', 'wpvacancy' ),
      'id'         => $prefix . 'booking_acc_unit_id',
  		'type'       => 'custom_attached_posts',
      'options' => array(
        'show_thumbnails' => true, // Show thumbnails on the left
        'filter_boxes'    => true, // Show a text box for filtering the results
        'query_args'      => array(
          'posts_per_page' => 10,
          'post_type'      => 'accommodation_type',
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

  $cmb->add_field( array(
      'name'       => __( 'Booking price', 'wpvacancy' ),
      'desc'       => __( 'The price, tax included, to pay for this booking', 'wpvacancy' ),
      'id'         => $prefix . 'booking_tax_incl',
      'type'       => 'text',
      // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
      // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
      // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
      'on_front'        => false, // Optionally designate a field to wp-admin only
      'attributes' => array(
        'type' => 'number',
        'min'  => '0',
        'data-validation' => 'required',
      ),        
      // 'repeatable'      => true,
  ) );
  
  $cmb->add_field( array(
      'name'       => __( 'Booking start date', 'wpvacancy' ),
      'desc'       => __( 'The date this booking starts at', 'wpvacancy' ),
      'id'         => $prefix . 'booking_start_date',
      'type'       => 'text_date',
      // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
      // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
      // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
      'on_front'        => false, // Optionally designate a field to wp-admin only
      'date_format' => 'Y-m-d',
      'attributes' => array(
          'data-validation' => 'required',
      ),        
      // 'repeatable'      => true,
  ) );
  $cmb->add_field( array(
      'name'       => __( 'Booking start time', 'wpvacancy' ),
      'desc'       => __( 'The time this booking starts at', 'wpvacancy' ),
      'id'         => $prefix . 'booking_start_time',
      'type'       => 'text_time',
      // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
      // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
      // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
      'on_front'        => false, // Optionally designate a field to wp-admin only
      // 'repeatable'      => true,
  ) );
  $cmb->add_field( array(
      'name'       => __( 'Booking end date', 'wpvacancy' ),
      'desc'       => __( 'The date this booking starts at', 'wpvacancy' ),
      'id'         => $prefix . 'booking_end_date',
      'type'       => 'text_date',
      // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
      // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
      // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
      'date_format' => 'Y-m-d',
      'on_front'        => false, // Optionally designate a field to wp-admin only
      // 'repeatable'      => true,
  ) );
  $cmb->add_field( array(
      'name'       => __( 'Booking end time', 'wpvacancy' ),
      'desc'       => __( 'The time this booking starts at', 'wpvacancy' ),
      'id'         => $prefix . 'booking_end_time',
      'type'       => 'text_time',
      // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
      // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
      // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
      'on_front'        => false, // Optionally designate a field to wp-admin only
      // 'repeatable'      => true,
  ) );
  $cmb->add_field( array(
      'name'       => __( 'Booking order time', 'wpvacancy' ),
      'desc'       => __( 'The date/time this booking was placed', 'wpvacancy' ),
      'id'         => $prefix . 'booking_order_time',
      'type'       => 'text_datetime_timestamp',
      // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
      // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
      // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
      'on_front'        => false, // Optionally designate a field to wp-admin only
      // 'repeatable'      => true,
  ) );
  $cmb->add_field( array(
      'name'       => __( 'Booking order expiration time', 'wpvacancy' ),
      'desc'       => __( 'The date/time this booking expires', 'wpvacancy' ),
      'id'         => $prefix . 'booking_expiration_time',
      'type'       => 'text_datetime_timestamp',
      // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
      // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
      // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
      'on_front'        => false, // Optionally designate a field to wp-admin only
      // 'repeatable'      => true,
  ) );
}

add_action( 'cmb2_admin_init', 'vb_wpv_booking_custom_fields' );
	

?>

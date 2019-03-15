<?php

// let's create the function for the custom type
function price_post_type() { 
  global $vb_wpv_basedir;
	// creating (registering) the custom type 
	register_post_type( 'price_type', /* (http://codex.wordpress.org/Function_Reference/register_post_type) */
		// let's now add all the options for this post type
		array( 'labels' => array(
			'name' => __( 'Prices', 'wpvacancy' ), /* This is the Title of the Group */
			'singular_name' => __( 'Price', 'wpvacancy' ), /* This is the individual type */
			'all_items' => __( 'All prices', 'wpvacancy' ), /* the all items menu item */
			'add_new' => __( 'Add price', 'wpvacancy' ), /* The add new menu item */
			'add_new_item' => __( 'Add a new price', 'wpvacancy' ), /* Add New Display Title */
			'edit' => __( 'Edit', 'wpvacancy' ), /* Edit Dialog */
			'edit_item' => __( 'Edit price', 'wpvacancy' ), /* Edit Display Title */
			'new_item' => __( 'New price', 'wpvacancy' ), /* New Display Title */
			'view_item' => __( 'Show price', 'wpvacancy' ), /* View Display Title */
			'search_items' => __( 'Search prices', 'wpvacancy' ), /* Search Custom Type Title */ 
			'not_found' =>  __( 'No prices found', 'wpvacancy' ), /* This displays if there are no entries yet */ 
			'not_found_in_trash' => __( 'No prices in trash', 'wpvacancy' ), /* This displays if there is nothing in the trash */
			'parent_item_colon' => ''
			), /* end of arrays */
			'description' => __( 'Accommodations Prices', 'wpvacancy' ), /* Custom Type Description */
			'public' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'show_ui' => true,
			'query_var' => true,
			'menu_position' => 84, /* this is what order you want it to appear in on the left hand side menu */ 
			'menu_icon' => 'dashicons-list-view', /* the icon for the custom post type menu */
			'rewrite'	=> array( 'slug' => 'price_type', 'with_front' => true ), /* you can specify its url slug */
			'has_archive' => false, /* you can rename the slug here */
			'capability_type' => 'post',
			'hierarchical' => false,
			/* the next one is important, it tells what's enabled in the post editor */
			'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'sticky', 'page-attributes')
		) /* end of options */
	); /* end of register post type */
	
	// now let's add custom categories (these act like categories)
	
  register_taxonomy( 'price_cat', 
    array('price_type'), /* if you change the name of register_post_type( 'custom_type', then you have to change this */
    array('hierarchical' => true,     /* if this is true, it acts like categories */
      'labels' => array(
        'name' => __( 'Price categories', 'wpvacancy' ), /* name of the custom taxonomy */
        'singular_name' => __( 'Price category', 'wpvacancy' ), /* single taxonomy name */
        'search_items' =>  __( 'Search price categories', 'wpvacancy' ), /* search title for taxomony */
        'all_items' => __( 'All price categories', 'wpvacancy' ), /* all title for taxonomies */
        'parent_item' => __( 'Parent category', 'wpvacancy' ), /* parent title for taxonomy */
        'parent_item_colon' => __( 'Parent category:', 'wpvacancy' ), /* parent taxonomy title */
        'edit_item' => __( 'Edit price category', 'wpvacancy' ), /* edit custom taxonomy title */
        'update_item' => __( 'Update price category', 'wpvacancy' ), /* update title for taxonomy */
        'add_new_item' => __( 'Add price category', 'wpvacancy' ), /* add new title for taxonomy */
        'new_item_name' => __( 'New price category', 'wpvacancy' ) /* name title for taxonomy */
      ),
      'show_admin_column' => true, 
      'show_ui' => true,
      'query_var' => true,
      'rewrite' => array( 'slug' => 'price-category' ),
    )
  );

  // now let's add custom tags (these act like categories)
  register_taxonomy( 'price_tag', 
    array('price_type'), /* if you change the name of register_post_type( 'custom_type', then you have to change this */
    array('hierarchical' => false,    /* if this is false, it acts like tags */
      'labels' => array(
        'name' => __( 'Price Tags', 'wpvacancy' ), /* name of the custom taxonomy */
        'singular_name' => __( 'Price Tag', 'wpvacancy' ), /* single taxonomy name */
        'search_items' =>  __( 'Search price tags', 'wpvacancy' ), /* search title for taxomony */
        'all_items' => __( 'All price tags', 'wpvacancy' ), /* all title for taxonomies */
        'parent_item' => __( 'Parent tag', 'wpvacancy' ), /* parent title for taxonomy */
        'parent_item_colon' => __( 'Parent tag:', 'wpvacancy' ), /* parent taxonomy title */
        'edit_item' => __( 'Edit tag', 'wpvacancy' ), /* edit custom taxonomy title */
        'update_item' => __( 'Update tag', 'wpvacancy' ), /* update title for taxonomy */
        'add_new_item' => __( 'Add price tag', 'wpvacancy' ), /* add new title for taxonomy */
        'new_item_name' => __( 'New price tag name', 'wpvacancy' ) /* name title for taxonomy */
      ),
      'show_admin_column' => true,
      'show_ui' => true,
      'query_var' => true,
    )
  );

	/* this adds your post categories to your custom post type */
	register_taxonomy_for_object_type( 'price_cat', 'price_type' );
	/* this adds your post tags to your custom post type */
	register_taxonomy_for_object_type( 'price_tag', 'price_type' );
	
}

	// adding the function to the Wordpress init
	add_action( 'init', 'price_post_type');
	
/**
 * Define the metabox and field configurations.
 */
function vb_wpv_price_custom_fields() {

  global $vb_wpv_custom_fields_prefix ;
  // Start with an underscore to hide fields from custom fields list
  $prefix = $vb_wpv_custom_fields_prefix;

  /**
   * Initiate the metabox
   */
  $cmb = new_cmb2_box( array(
      'id'            => 'price_meta',
      'title'         => __( 'Price data', 'wpvancancy' ),
      'object_types'  => array( 'price_type', ), // Post type
      'context'       => 'normal',
      'priority'      => 'high',
      'show_names'    => true, // Show field names on the left
      // 'cmb_styles' => false, // false to disable the CMB stylesheet
      'closed'     => false, // Keep the metabox closed by default
  ) );

  $cmb->add_field( array(
		'name'    => __( 'Season', 'wpvacancy' ),
		'desc'    => __( 'The season this price refers to', 'wpvacancy' ),
		'id'      => $prefix . 'price_season_id',
		'type'    => 'custom_attached_posts',
		'options' => array(
			'show_thumbnails' => true, // Show thumbnails on the left
			'filter_boxes'    => true, // Show a text box for filtering the results
			'query_args'      => array(
				'posts_per_page' => 10,
				'post_type'      => 'season_type',
			), // override the get_posts args
		),
      'attributes' => array(
          'data-validation' => 'required',
      ),
      'on_front'        => false, // Optionally designate a field to wp-admin only
	));
   
  $cmb->add_field( array(
      'name'       => __( 'Accommodation', 'wpvancancy' ),
      'desc'       => __( 'The accommodation unit this price refers to', 'wpvacancy' ),
      'id'         => $prefix . 'price_acc_unit_id',
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
      'name'       => __( 'Apply only to this accommodation', 'wpvancancy' ),
      'desc'       => __( 'Should this price apply only to this accommodation only or should it apply to others of the same category as well? Default unchecked, which means it applies to others as well.', 'wpvacancy' ),
      'id'         => $prefix . 'price_acc_cat_slug',
  		'type'       => 'checkbox',
      // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
      // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
      // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
      'on_front'        => false, // Optionally designate a field to wp-admin only
      // 'repeatable'      => true,
  ) );
  
  $cmb->add_field( array(
      'name'       => __( 'Price', 'wpvacancy' ),
      'desc'       => __( 'The price, tax included, per minimum time slot as defined in the referenced season', 'wpvacancy' ),
      'id'         => $prefix . 'price_tax_incl',
      'type'       => 'text',
      // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
      // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
      // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
      'on_front'        => false, // Optionally designate a field to wp-admin only
      // 'repeatable'      => true,
  ) );

  $cmb->add_field( array(
      'name'       => __( 'Allowed free reservation time', 'wpvacancy' ),
      'desc'       => __( 'The time in seconds the accommodation is reserved to registered users waiting for payment', 'wpvacancy' ),
      'id'         => $prefix . 'booking_expiration_time',
      'type'       => 'text',
      'default'   => get_option(Wpvacancy_Admin::$defaultBookingExpirationTime, 7200),
      // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
      // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
      // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
      'on_front'        => false, // Optionally designate a field to wp-admin only
      // 'repeatable'      => true,
  ) );
  
}

add_action( 'cmb2_admin_init', 'vb_wpv_price_custom_fields' );


?>

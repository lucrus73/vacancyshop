<?php

class VS_AccommodationMapMetaKeys
{
  public static $singleDaySelection;
  public static $defaultDuration;
  public static $mapDrivenBooking;
  public static $bookingsStartOn;
  public static $startTime;
  public static $endTime;
  public static $timeslotDuration;
  public static $minimumTimeslots;
  public static $maximumTimeslots;
  public static $minimumDays;
  public static $maximumDays;
  
  function __construct()
  {
    global $vb_wpv_custom_fields_prefix;
    $prefix = $vb_wpv_custom_fields_prefix."accm_map_";
    self::$singleDaySelection = $prefix . 'singledayselection';
    self::$defaultDuration = $prefix . 'defaultduration';
    self::$mapDrivenBooking = $prefix . 'mapdriven';
    self::$startTime = $prefix . 'start_time';
    self::$endTime = $prefix . 'end_time';
    self::$bookingsStartOn = $prefix . 'bookings_start_on';
    self::$timeslotDuration = $prefix . 'timeslot_duration';
    self::$minimumTimeslots= $prefix . 'min_timeslots_for_bookings';
    self::$maximumTimeslots = $prefix . 'max_timeslots_for_bookings';
    self::$minimumDays = $prefix . 'min_days_for_bookings';
    self::$maximumDays = $prefix . 'max_days_for_bookings';
  }  
}

new VS_AccommodationMapMetaKeys();

// let's create the function for the custom type
function accommodationmap_post_type() {
  global $vb_wpv_basedir;
	// creating (registering) the custom type 
	register_post_type( 'accm_map_type', /* (http://codex.wordpress.org/Function_Reference/register_post_type) */
		// let's now add all the options for this post type
		array( 'labels' => array(
			'name' => __( 'Accm. Maps', 'wpvacancy' ), /* This is the Title of the Group */
			'singular_name' => __( 'Map', 'wpvacancy' ), /* This is the individual type */
			'all_items' => __( 'All maps', 'wpvacancy' ), /* the all items menu item */
			'add_new' => __( 'Add map', 'wpvacancy' ), /* The add new menu item */
			'add_new_item' => __( 'Add a new map', 'wpvacancy' ), /* Add New Display Title */
			'edit' => __( 'Edit', 'wpvacancy' ), /* Edit Dialog */
			'edit_item' => __( 'Edit map', 'wpvacancy' ), /* Edit Display Title */
			'new_item' => __( 'New map', 'wpvacancy' ), /* New Display Title */
			'view_item' => __( 'Show maps', 'wpvacancy' ), /* View Display Title */
			'search_items' => __( 'Search maps', 'wpvacancy' ), /* Search Custom Type Title */ 
			'not_found' =>  __( 'No maps found', 'wpvacancy' ), /* This displays if there are no entries yet */ 
			'not_found_in_trash' => __( 'No maps in trash', 'wpvacancy' ), /* This displays if there is nothing in the trash */
			'parent_item_colon' => ''
			), /* end of arrays */
			'description' => __( 'Maps or plans images to show bookable accommodations', 'wpvacancy' ), /* Custom Type Description */
			'public' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => true,
			'show_ui' => true,
			'query_var' => true,
			'menu_position' => 80, /* this is what order you want it to appear in on the left hand side menu */ 
			'menu_icon' => 'dashicons-layout', /* the icon for the custom post type menu */
			'rewrite'	=> array( 'slug' => 'accm_map_type', 'with_front' => true ), /* you can specify its url slug */
			'has_archive' => false, /* you can rename the slug here */
			'capability_type' => 'post',
			'hierarchical' => true,
			/* the next one is important, it tells what's enabled in the post editor */
			'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'sticky', 'page-attributes')
		) /* end of options */
	); /* end of register post type */
	
	// now let's add custom categories (these act like categories)
	
  register_taxonomy( 'accmmap_cat', 
    array('accm_map_type'), /* if you change the name of register_post_type( 'custom_type', then you have to change this */
    array('hierarchical' => true,     /* if this is true, it acts like categories */
      'labels' => array(
        'name' => __( 'Map categories', 'wpvacancy' ), /* name of the custom taxonomy */
        'singular_name' => __( 'Map category', 'wpvacancy' ), /* single taxonomy name */
        'search_items' =>  __( 'Search maps categories', 'wpvacancy' ), /* search title for taxomony */
        'all_items' => __( 'All maps categories', 'wpvacancy' ), /* all title for taxonomies */
        'parent_item' => __( 'Parent category', 'wpvacancy' ), /* parent title for taxonomy */
        'parent_item_colon' => __( 'Parent category:', 'wpvacancy' ), /* parent taxonomy title */
        'edit_item' => __( 'Edit map category', 'wpvacancy' ), /* edit custom taxonomy title */
        'update_item' => __( 'Update map category', 'wpvacancy' ), /* update title for taxonomy */
        'add_new_item' => __( 'Add map category', 'wpvacancy' ), /* add new title for taxonomy */
        'new_item_name' => __( 'New map category', 'wpvacancy' ) /* name title for taxonomy */
      ),
      'show_admin_column' => true, 
      'show_ui' => true,
      'query_var' => true,
      'rewrite' => array( 'slug' => 'accommodationmap-category' ),
    )
  );

  // now let's add custom tags (these act like categories)
  register_taxonomy( 'accmmap_tag', 
    array('accm_map_type'), /* if you change the name of register_post_type( 'custom_type', then you have to change this */
    array('hierarchical' => false,    /* if this is false, it acts like tags */
      'labels' => array(
        'name' => __( 'Map Tags', 'wpvacancy' ), /* name of the custom taxonomy */
        'singular_name' => __( 'Map Tag', 'wpvacancy' ), /* single taxonomy name */
        'search_items' =>  __( 'Search map tags', 'wpvacancy' ), /* search title for taxomony */
        'all_items' => __( 'All map tags', 'wpvacancy' ), /* all title for taxonomies */
        'parent_item' => __( 'Parent tag', 'wpvacancy' ), /* parent title for taxonomy */
        'parent_item_colon' => __( 'Parent tag:', 'wpvacancy' ), /* parent taxonomy title */
        'edit_item' => __( 'Edit tag', 'wpvacancy' ), /* edit custom taxonomy title */
        'update_item' => __( 'Update tag', 'wpvacancy' ), /* update title for taxonomy */
        'add_new_item' => __( 'Add map tag', 'wpvacancy' ), /* add new title for taxonomy */
        'new_item_name' => __( 'New map tag name', 'wpvacancy' ) /* name title for taxonomy */
      ),
      'show_admin_column' => true,
      'show_ui' => true,
      'query_var' => true,
    )
  );

	/* this adds your post categories to your custom post type */
	register_taxonomy_for_object_type( 'accmmap_cat', 'accm_map_type' );
	/* this adds your post tags to your custom post type */
	register_taxonomy_for_object_type( 'accmmap_tag', 'accm_map_type' );
	
}

	// adding the function to the Wordpress init
	add_action( 'init', 'accommodationmap_post_type');

  
  
add_filter( 'default_content', 'wpv_accommodationmap_editor_content', 10, 2 );

function wpv_accommodationmap_editor_content( $content, $post ) 
{
  if ( $post->post_type == 'accm_map_type') 
  {
    $content = __('Please use the featured image of this post to set the map image, then add a standard WordPress gallery to this content if you want images to show up in the carousel.', 'wpvacancy');
  }

  return $content;
}  


function vb_wpv_accmap_custom_fields() 
{
  global $vb_wpv_weekdays;
  /**
   * Initiate the metabox
   */
  $cmb = new_cmb2_box( array(
      'id'            => 'accm_map_type_meta',
      'title'         => __( 'Accomodation Map options', 'wpvacancy' ),
      'object_types'  => array( 'accm_map_type', ), // Post type
      'context'       => 'normal',
      'priority'      => 'high',
      'show_names'    => true, // Show field names on the left
      // 'cmb_styles' => false, // false to disable the CMB stylesheet
      'closed'     => false, // Keep the metabox closed by default
  ) );

  $cmb->add_field( array(
		'name'    => __( 'Book by the hour', 'wpvacancy' ),
		'desc'    => __( 'Shows a timepicker to let users book by the hour instead of whole days', 'wpvacancy' ),
		'id'      => VS_AccommodationMapMetaKeys::$singleDaySelection,
		'type'    => 'checkbox',
    'default' => false, 
    'on_front'        => false, // Optionally designate a field to wp-admin only
	));

  $cmb->add_field( array(
		'name'    => __( 'Initial duration', 'wpvacancy' ),
		'desc'    => __( 'The default duration of the booking (days or minutes, depending on timepicker presence)', 'wpvacancy' ),
		'id'      => VS_AccommodationMapMetaKeys::$defaultDuration,
		'type'    => 'text',
    'default' => 15, 
    'attributes' => array(
        'data-validation' => 'required',
        'type' => 'number',
        'min'  => '1',
    ),
    'on_front'        => false, // Optionally designate a field to wp-admin only
	));

  $cmb->add_field( array(
		'name'    => __( 'Map driven booking', 'wpvacancy' ),
		'desc'    => __( 'Is the map above the calendar? It also enables calendar and timepicker constraints per service on the map', 'wpvacancy' ),
		'id'      => VS_AccommodationMapMetaKeys::$mapDrivenBooking,
		'type'    => 'checkbox',
    'default' => false, 
    'on_front'        => false, // Optionally designate a field to wp-admin only
	));
    $cmb->add_field( array(
        'name'       => __( 'Start time', 'wpvacancy' ),
        'desc'       => __( 'The time bookings in this period start in the first booked day', 'wpvacancy' ),
        'id'         => VS_PeriodMetaKeys::$startTime,
        'type'       => 'text_time',
        'on_front'        => false, // Optionally designate a field to wp-admin only
    ) );

    $cmb->add_field( array(
        'name'       => __( 'End time', 'wpvacancy' ),
        'desc'       => __( 'The time bookings in this period end in the last booked day', 'wpvacancy' ),
        'id'         => VS_PeriodMetaKeys::$endTime,
        'type'       => 'text_time',
        'on_front'        => false, // Optionally designate a field to wp-admin only
    ) );
    
    $cmb->add_field( array(
        'name'       => __( 'Bookings start on', 'wpvacancy' ),
        'desc'       => __( 'Choose days bookings periods must start on. Check all or none if any day will do.', 'wpvacancy' ),
        'id'         => VS_PeriodMetaKeys::$bookingsStartOn,
        'type'       => 'multicheck',
        'options'    => $vb_wpv_weekdays,
        // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
        // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
        // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
        'on_front'        => false, // Optionally designate a field to wp-admin only
        // 'repeatable'      => true,
    ) );

    $cmb->add_field( array(
        'name'       => __( 'Timeslot duration in  minutes', 'wpvacancy' ),
        'desc'       => __( 'Bookings must last a multiple of this number of minutes. It affects the timepicker display accordingly.', 'wpvacancy' ),
        'id'         => VS_PeriodMetaKeys::$timeslotDuration,
        'type'       => 'radio_inline',
        'default'    => "30",
        'options'    => array(
            "15" => "15",
            "20" => "20",
            "30" => "30",
            "60" => "60",
        ),
        // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
        // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
        // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
        'on_front'        => false, // Optionally designate a field to wp-admin only
        // 'repeatable'      => true,
    ) );

    $cmb->add_field( array(
        'name'       => __( 'Minimum timeslots for booking', 'wpvacancy' ),
        'desc'       => __( 'Bookings must last a multiple of this number timeslots. Defaults to 1.', 'wpvacancy' ),
        'id'         => VS_PeriodMetaKeys::$minimumTimeslots,
        'type'       => 'text',
        'default'    => 1,
        'attributes' => array(
          'type' => 'number',
          'min'  => 1,
        ),        
        // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
        // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
        // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
        'on_front'        => false, // Optionally designate a field to wp-admin only
        // 'repeatable'      => true,
    ) );

    /* If this is set to a positive value >= 1, then prices are calculated on a per day basis. 
     * If this is set to zero, then the prices are calculated on a per-(min-minutes) time slot basis.
     * For example, setting this to 0 and setting minimum minutes to 30, then you must enter
     * the price for a 30 minutes time slot.
     * Setting this to 1 and minimum minutes to 0 (not enabled), then you must enter
     * the price for 1 day or night.
     * Setting this to 7 (one week), then you must enter the price for the whole week.
     */
    $cmb->add_field( array(
        'name'       => __( 'Min days for booking', 'wpvacancy' ),
        'desc'       => __( 'Defaults to one day/night. If set, bookings must last a multiple of this number of days/nights.', 'wpvacancy' ),
        'id'         => VS_PeriodMetaKeys::$minimumDays,
        'type'       => 'text',
        'default'    => 1,
        'attributes' => array(
          'type' => 'number',
          'min'  => 1,
        ),        
        // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
        // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
        // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
        'on_front'        => false, // Optionally designate a field to wp-admin only
        // 'repeatable'      => true,
    ) );

    $cmb->add_field( array(
        'name'       => __( 'Maximum timeslots per booking', 'wpvacancy' ),
        'desc'       => __( 'Defaults to zero, which means there\'s no maximum set.', 'wpvacancy' ),
        'id'         => VS_PeriodMetaKeys::$maximumTimeslots,
        'type'       => 'text',
        'default'    => 0,
        'attributes' => array(
          'type' => 'number',
          'min'  => 0,
        ),        
        // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
        // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
        // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
        'on_front'        => false, // Optionally designate a field to wp-admin only
        // 'repeatable'      => true,
    ) );

    /* 
     */
    $cmb->add_field( array(
        'name'       => __( 'Max days for booking', 'wpvacancy' ),
        'desc'       => __( 'Defaults to zero, that means no maximum.', 'wpvacancy' ),
        'id'         => VS_PeriodMetaKeys::$maximumDays,
        'type'       => 'text',
        'default'    => 0,
        'attributes' => array(
          'type' => 'number',
          'min'  => 0,
        ),        
        // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
        // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
        // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
        'on_front'        => false, // Optionally designate a field to wp-admin only
        // 'repeatable'      => true,
    ) );
}

add_action( 'cmb2_admin_init', 'vb_wpv_accmap_custom_fields' );

  

?>

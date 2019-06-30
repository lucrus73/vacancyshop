<?php

class VS_PeriodMetaKeys
{
  public static $startDate;
  public static $startTime;
  public static $endDate;
  public static $endTime;
  public static $bookingsStartOn;
  public static $minimumMinutes;
  public static $minimumDays;
  public static $maximumMinutes;
  public static $maximumDays;
  
  function __construct()
  {
    global $vb_wpv_custom_fields_prefix;
    $prefix = $vb_wpv_custom_fields_prefix.'period_';
    self::$singleDaySelection = $prefix . 'start_date';
    self::$endDate = $prefix . 'end_date';
    self::$startTime = $prefix . 'start_time';
    self::$endTime = $prefix . 'end_time';
    self::$bookingsStartOn = $prefix . 'bookings_start_on';
    self::$minimumMinutes = $prefix . 'min_minutes_for_bookings';
    self::$maximumMinutes = $prefix . 'max_minutes_for_bookings';
    self::$minimumDays = $prefix . 'min_days_for_bookings';
    self::$maximumDays = $prefix . 'max_days_for_bookings';
  }    
}

// let's create the function for the custom type
function period_post_type() { 
  global $vb_wpv_basedir;
	// creating (registering) the custom type 
	register_post_type( 'period_type', /* (http://codex.wordpress.org/Function_Reference/register_post_type) */
		// let's now add all the options for this post type
		array( 'labels' => array(
			'name' => __( 'Periods', 'wpvacancy' ), /* This is the Title of the Group */
			'singular_name' => __( 'Period', 'wpvacancy' ), /* This is the individual type */
			'all_items' => __( 'All periods', 'wpvacancy' ), /* the all items menu item */
			'add_new' => __( 'Add period', 'wpvacancy' ), /* The add new menu item */
			'add_new_item' => __( 'Add a new period', 'wpvacancy' ), /* Add New Display Title */
			'edit' => __( 'Edit', 'wpvacancy' ), /* Edit Dialog */
			'edit_item' => __( 'Edit period', 'wpvacancy' ), /* Edit Display Title */
			'new_item' => __( 'New period', 'wpvacancy' ), /* New Display Title */
			'view_item' => __( 'Show period', 'wpvacancy' ), /* View Display Title */
			'search_items' => __( 'Search periods', 'wpvacancy' ), /* Search Custom Type Title */ 
			'not_found' =>  __( 'No periods found', 'wpvacancy' ), /* This displays if there are no entries yet */ 
			'not_found_in_trash' => __( 'No periods in trash', 'wpvacancy' ), /* This displays if there is nothing in the trash */
			'parent_item_colon' => ''
			), /* end of arrays */
			'description' => __( 'Periods of opening when the accommodations are available for booking', 'wpvacancy' ), /* Custom Type Description */
			'public' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'show_ui' => true,
			'query_var' => true,
			'menu_position' => 83, /* this is what order you want it to appear in on the left hand side menu */ 
			'menu_icon' => 'dashicons-calendar-alt', /* the icon for the custom post type menu */
			'rewrite'	=> array( 'slug' => 'period_type', 'with_front' => true ), /* you can specify its url slug */
			'has_archive' => false, /* you can rename the slug here */
			'capability_type' => 'post',
			'hierarchical' => false,
			/* the next one is important, it tells what's enabled in the post editor */
			'supports' => array( 'title', 'editor', 'custom-fields')
		) /* end of options */
	); /* end of register post type */
		
}

	// adding the function to the Wordpress init
	add_action( 'init', 'period_post_type');
	
	
/**
 * Define the metabox and field configurations.
 */
function period_custom_fields() {

  global $vb_wpv_custom_fields_prefix ;
  global $vb_wpv_weekdays;
    // Start with an underscore to hide fields from custom fields list
    $prefix = $vb_wpv_custom_fields_prefix;

    /**
     * Initiate the metabox
     */
    $cmb = new_cmb2_box( array(
        'id'            => 'period_rules',
        'title'         => __( 'Period details', 'wpvacancy' ),
        'object_types'  => array( 'period_type', ), // Post type
        'context'       => 'normal',
        'priority'      => 'high',
        'show_names'    => true, // Show field names on the left
        // 'cmb_styles' => false, // false to disable the CMB stylesheet
        'closed'     => false, // Keep the metabox closed by default
    ) );

   
    $cmb->add_field( array(
        'name'       => __( 'Start date', 'wpvacancy' ),
        'desc'       => __( 'The date this period starts on.', 'wpvacancy' ),
        'id'         => VS_PeriodMetaKeys::$startDate,
        'type'       => 'text_date',
        // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
        // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
        // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
        'date_format' => 'Y-m-d',
        'on_front'        => false, // Optionally designate a field to wp-admin only
        // 'repeatable'      => true,
    ) );

    $cmb->add_field( array(
        'name'       => __( 'End date', 'wpvacancy' ),
        'desc'       => __( 'The date this period ends on (inclusive).', 'wpvacancy' ),
        'id'         => VS_PeriodMetaKeys::$endDate,
        'type'       => 'text_date',
        // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
        // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
        // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
        'date_format' => 'Y-m-d',
        'on_front'        => false, // Optionally designate a field to wp-admin only
        // 'repeatable'      => true,
    ) );

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
        'name'       => __( 'Minimum minutes for booking', 'wpvacancy' ),
        'desc'       => __( 'Defaults to zero, which means there\'s no minimum set. If set, bookings must last a multiple of this number of minutes.', 'wpvacancy' ),
        'id'         => VS_PeriodMetaKeys::$minimumMinutes,
        'type'       => 'text',
        'default'    => 30,
        'attributes' => array(
          'type' => 'number',
          'min'  => '0',
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
          'min'  => '0',
        ),        
        // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
        // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
        // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
        'on_front'        => false, // Optionally designate a field to wp-admin only
        // 'repeatable'      => true,
    ) );

    $cmb->add_field( array(
        'name'       => __( 'Maximum minutes for booking', 'wpvacancy' ),
        'desc'       => __( 'Defaults to zero, which means there\'s no maximum set.', 'wpvacancy' ),
        'id'         => VS_PeriodMetaKeys::$maximumMinutes,
        'type'       => 'text',
        'default'    => 0,
        'attributes' => array(
          'type' => 'number',
          'min'  => '0',
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
          'min'  => '0',
        ),        
        // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
        // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
        // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
        'on_front'        => false, // Optionally designate a field to wp-admin only
        // 'repeatable'      => true,
    ) );
}

add_action( 'cmb2_admin_init', 'period_custom_fields' );


?>

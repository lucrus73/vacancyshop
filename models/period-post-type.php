<?php

class VS_PeriodMetaKeys
{
  public static $startDate;
  public static $endDate;
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
    $prefix = $vb_wpv_custom_fields_prefix.'period_';
    self::$startDate = $prefix . 'start_date';
    self::$endDate = $prefix . 'end_date';
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

new VS_PeriodMetaKeys();

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
}

add_action( 'cmb2_admin_init', 'period_custom_fields' );


?>

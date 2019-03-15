<?php

class WPV_BookingMetaKeys {
  public static $cart;
  public static $accommodation;
  public static $amount;
  public static $startDate;
  public static $startTime;
  public static $endDate;
  public static $endTime;
  public static $orderTime;
  public static $expirationTime;
  public static $DoSPreventionTime;
  public static $deleted;
  
  function __construct()
  {
    global $vb_wpv_custom_fields_prefix;
    $prefix = $vb_wpv_custom_fields_prefix;
    self::$cart = $prefix.'booking_cart_id';
    self::$accommodation = $prefix.'booking_acc_unit_id';
    self::$amount = $prefix.'booking_amount_tax_incl';
    self::$startDate = $prefix.'booking_start_date';
    self::$startTime = $prefix.'booking_start_time';
    self::$endDate = $prefix.'booking_end_date';
    self::$endTime = $prefix.'booking_end_time';
    self::$orderTime = $prefix.'booking_order_time';
    self::$expirationTime = $prefix.'booking_expiration_time';
    self::$DoSPreventionTime = $prefix.'booking_dosprevention_time';
    self::$deleted = $prefix.'booking_deleted';
  }
}

global $wpv_booking_meta_keys_ensure_at_least_one_instance;
$wpv_booking_meta_keys_ensure_at_least_one_instance = new WPV_BookingMetaKeys();

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
		'desc'    => __( 'The cart this booking belongs to', 'wpvacancy' ),
		'id'      => WPV_BookingMetaKeys::$cart,
		'type'    => 'custom_attached_posts',
      'options' => array(
        'show_thumbnails' => true, // Show thumbnails on the left
        'filter_boxes'    => true, // Show a text box for filtering the results
        'query_args'      => array(
          'posts_per_page' => 10,
          'post_type'      => 'cart_type',
        ), // override the get_posts args
      ),
      'attributes' => array(
          'data-validation' => 'required',
      ),
      'on_front'        => false, // Optionally designate a field to wp-admin only
	));
   
  $cmb->add_field( array(
      'name'       => __( 'Accommodation', 'wpvancancy' ),
      'desc'       => __( 'The accommodation unit this booking refers to', 'wpvacancy' ),
      'id'         => WPV_BookingMetaKeys::$accommodation,
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
      'id'         => WPV_BookingMetaKeys::$amount,
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
      'id'         => WPV_BookingMetaKeys::$startDate,
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
      'id'         => WPV_BookingMetaKeys::$startTime,
      'type'       => 'text_time',
      // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
      // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
      // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
      'on_front'        => false, // Optionally designate a field to wp-admin only
      // 'repeatable'      => true,
  ) );
  $cmb->add_field( array(
      'name'       => __( 'Booking end date', 'wpvacancy' ),
      'desc'       => __( 'The date this booking ends at', 'wpvacancy' ),
      'id'         => WPV_BookingMetaKeys::$endDate,
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
      'desc'       => __( 'The time this booking ends at', 'wpvacancy' ),
      'id'         => WPV_BookingMetaKeys::$endTime,
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
      'id'         => WPV_BookingMetaKeys::$orderTime,
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
      'id'         => WPV_BookingMetaKeys::$expirationTime,
      'type'       => 'text_datetime_timestamp',
      // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
      // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
      // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
      'on_front'        => false, // Optionally designate a field to wp-admin only
      // 'repeatable'      => true,
  ) );
  $cmb->add_field( array(
      'name'       => __( 'DoS prevention time', 'wpvacancy' ),
      'desc'       => __( 'The date/time the user can place a new booking for this accommodation', 'wpvacancy' ),
      'id'         => WPV_BookingMetaKeys::$DoSPreventionTime,
      'type'       => 'text_datetime_timestamp',
      // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
      // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
      // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
      'on_front'        => false, // Optionally designate a field to wp-admin only
      // 'repeatable'      => true,
  ) );
  $cmb->add_field( array(
      'name'       => __( 'Deleted', 'wpvacancy' ),
      'desc'       => __( 'Indicates if the user has removed the booking from the cart', 'wpvacancy' ),
      'id'         => WPV_BookingMetaKeys::$deleted,
      'type'       => 'checkbox',
      // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
      // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
      // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
      'on_front'        => false, // Optionally designate a field to wp-admin only
      // 'repeatable'      => true,
  ) );
}

add_action( 'cmb2_admin_init', 'vb_wpv_booking_custom_fields' );

/**
 * Rerturns the $expiration parameter if specified, or the configured booking 
 * expiration time for the specified user, if any. Otherwise it returns a default 
 * value (currently 7200 which is 2 hours).
 * @param $userid the user to retrieve the meta key from
 * @param $expiration if specified it overrides the meta key and the function returns exactly the specified value
 * @return int
 */
function vb_wpv_get_configured_booking_expiration($userid = false, $expiration = false)
{
  $default_expiration = 7200;
  if (empty($expiration))
  {
    if (!empty($userid))
    {
      $umk = $vb_wpv_custom_fields_prefix.'pending_booking_expiration';
      $expiration = get_user_meta($userid, $umk, true);
      if (empty($expiration))
      {
        $expiration = $default_expiration;
        add_user_meta($userid, $umk, $expiration);
      }
    }
    else
      $expiration = $default_expiration;
  }
  return $expiration;
}

function vb_wpv_create_booking($userid, $accommodation_id, $startDayId, $endDayId, $startTime, $endTime, $expiration = false)
{
  global $vb_wpv_custom_fields_prefix;

  $expiration = vb_wpv_get_configured_booking_expiration($userid, $expiration);
  
  $now = time();
  $cart = vb_wpv_get_cart($userid, true);
  $items = vb_wpv_get_cart_items($cart->ID);
  
  foreach ($items as $preexistingbooking)
  {
    $pe_dostime = get_post_meta($preexistingbooking->ID, WPV_BookingMetaKeys::$DoSPreventionTime, true);
    $pe_expiration = get_post_meta($preexistingbooking->ID, WPV_BookingMetaKeys::$expirationTime, true);
    $pe_accid = get_post_meta($preexistingbooking->ID, WPV_BookingMetaKeys::$accommodation, true);
    
    /* Anti DoS measure:
     * -----------------
     * 
     * A registered user can book any accommodation, and that accommodation is reserved for $expiration seconds
     * until we receive the payment. After $expiration seconds, if the user hasn't paid for the booking,
     * the accommodation becomes available again to other users.
     * However a user can script a bot that calls our rest API and books the accommodation again after $expiration seconds.
     * This way the user keeps the accommodation to himself for a indefinite time, causing a DoS to other users.
     * DosPreventionTime is there to avoid that situation. A user that adds a booking to his cart, must pay
     * within $expiration seconds. If he doesn't pay, he can't add the same accommodation again to his cart before
     * $DosPreventionTime. Other users then get a chance to book the accommodation.
     * $DosPreventionTime is hardcoded as twice the $expiration time.
     * Please note that the user CAN add the accommodation to his cart more than once, if he does so within
     * the $expiration time of the first time he added it. This enables users to book the same accommodation for
     * different periods of time within the same cart.
     * 
     */
    if ($now >= $pe_expiration && $now < $pe_dostime && $pe_accid == $accommodation_id)
      return false;
  }
  
  $booking = array(
        'post_status'           => 'publish', 
        'post_type'             => 'booking_type',
        'post_author'           => $user
      );
  
  $startDate = date("Y-m-d", $startDayId * 86400);
  $endDate = date("Y-m-d", $endDayId * 86400);
  
  $booking_id = wp_insert_post($booking);
  update_post_meta($booking_id, WPV_BookingMetaKeys::$cart, $cart_id);
  update_post_meta($booking_id, WPV_BookingMetaKeys::$accommodation, $accommodation_id);
  update_post_meta($booking_id, WPV_BookingMetaKeys::$startDate, $startDate);
  update_post_meta($booking_id, WPV_BookingMetaKeys::$endDate, $endDate);
  update_post_meta($booking_id, WPV_BookingMetaKeys::$startTime, $startTime);
  update_post_meta($booking_id, WPV_BookingMetaKeys::$endTime, $endTime);
  vb_wpv_set_booking_expiration_time($booking_id, $now, $expiration);
  return $booking_id;
}

function vb_wpv_set_booking_expiration_time($booking_id, $time = false, $expiration = false, $userid = false)
{
  if (empty($time))
  {
    $time = time();
  }
  if (empty($expiration))
  {
    $expiration = vb_wpv_get_configured_booking_expiration($userid);
  }
  update_post_meta($booking_id, WPV_BookingMetaKeys::$orderTime, $time);
  update_post_meta($booking_id, WPV_BookingMetaKeys::$expirationTime, $time + $expiration);
  update_post_meta($booking_id, WPV_BookingMetaKeys::$DoSPreventionTime, $time + $expiration * 3); // twice the $expiration, 3 is correct, do your math if you don't believe it
}

function vb_wpv_get_booking_accommodation_id($booking)
{
  $id = $booking;
  if (!is_integer($id))
  {
    $id = $booking->ID;
  }
  $result = get_post_meta($id, WPV_BookingMetaKeys::$accommodation, true);
  if (is_array($result))
  {
    $result = $result[0];
  }
  if (empty($result))
  {
    $result = null;
  }
  return $result;
}

function vb_wpv_get_booking_datetime($booking, $datekey, $timekey)
{
  $id = $booking;
  if (!is_integer($id))
  {
    $id = $booking->ID;
  }
  $resultDate = get_post_meta($id, $datekey, true);
  $resultTime = get_post_meta($id, $timekey, true);
  if (!empty($resultTime))
  {
    $resultDate .= ' ' . $resultTime;
  }
  return $resultDate;
}

function vb_wpv_get_booking_start($booking)
{
  return vb_wpv_get_booking_datetime($booking, WPV_BookingMetaKeys::$startDate, WPV_BookingMetaKeys::$startTime);
}

function vb_wpv_get_booking_end($booking)
{
  return vb_wpv_get_booking_datetime($booking, WPV_BookingMetaKeys::$endDate, WPV_BookingMetaKeys::$endTime);
}

function vb_wpv_get_booking_datetime_as_uxts($booking_datetime)
{
  $dtarr = explode(' ', $booking_datetime);
  if (count($dtarr) > 1)
  {
    $dt_obj = DateTime::createFromFormat("Y-m-d H:i:s", $booking_datetime);
  }
  else
  {
    $dt_obj = DateTime::createFromFormat("Y-m-d", $booking_datetime);
  }
  return $dt_obj->getTimestamp();
}

function vb_wpv_get_booking_start_as_uxts($booking)
{
  return vb_wpv_get_booking_datetime_as_uxts(
          vb_wpv_get_booking_datetime($booking, WPV_BookingMetaKeys::$startDate, WPV_BookingMetaKeys::$startTime));
}

function vb_wpv_get_booking_end_as_uxts($booking)
{
  return vb_wpv_get_booking_datetime_as_uxts(
          vb_wpv_get_booking_datetime($booking, WPV_BookingMetaKeys::$endDate, WPV_BookingMetaKeys::$endTime));
}

function vb_wpv_get_booking_expiration($booking_id)
{
  return get_post_meta($booking_id, WPV_BookingMetaKeys::$expirationTime, true);
}

function vb_wpv_get_booking_dos_prevention($booking_id)
{
  return get_post_meta($booking_id, WPV_BookingMetaKeys::$DoSPreventionTime, true);
}

?>

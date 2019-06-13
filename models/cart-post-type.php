<?php
use Jaybizzle\CrawlerDetect\CrawlerDetect;

global $vb_wpv_session_id;
global $vb_wpv_session_lifetime;
$vb_wpv_session_lifetime = 2592000; // 30 days lifetime


// let's create the function for the custom type
function cart_post_type() { 
  global $vb_wpv_basedir;
	// creating (registering) the custom type 
	register_post_type( 'cart_type', /* (http://codex.wordpress.org/Function_Reference/register_post_type) */
		// let's now add all the options for this post type
		array( 'labels' => array(
			'name' => __( 'Carts', 'wpvacancy' ), /* This is the Title of the Group */
			'singular_name' => __( 'Cart', 'wpvacancy' ), /* This is the individual type */
			'all_items' => __( 'All carts', 'wpvacancy' ), /* the all items menu item */
			'add_new' => __( 'Add cart', 'wpvacancy' ), /* The add new menu item */
			'add_new_item' => __( 'Add a new cart', 'wpvacancy' ), /* Add New Display Title */
			'edit' => __( 'Edit', 'wpvacancy' ), /* Edit Dialog */
			'edit_item' => __( 'Edit cart', 'wpvacancy' ), /* Edit Display Title */
			'new_item' => __( 'New cart', 'wpvacancy' ), /* New Display Title */
			'view_item' => __( 'Show cart', 'wpvacancy' ), /* View Display Title */
			'search_items' => __( 'Search carts', 'wpvacancy' ), /* Search Custom Type Title */ 
			'not_found' =>  __( 'No carts found', 'wpvacancy' ), /* This displays if there are no entries yet */ 
			'not_found_in_trash' => __( 'No carts in trash', 'wpvacancy' ), /* This displays if there is nothing in the trash */
			'parent_item_colon' => ''
			), /* end of arrays */
			'description' => __( 'Carts', 'wpvacancy' ), /* Custom Type Description */
			'public' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'show_ui' => true,
			'query_var' => true,
			'menu_position' => 89, /* this is what order you want it to appear in on the left hand side menu */ 
			'menu_icon' => 'dashicons-cart', /* the icon for the custom post type menu */
			'rewrite'	=> array( 'slug' => 'cart_type', 'with_front' => true ), /* you can specify its url slug */
			'has_archive' => false, /* you can rename the slug here */
			'capability_type' => 'post',
			'hierarchical' => false,
			/* the next one is important, it tells what's enabled in the post editor */
			'supports' => array( 'title', 'editor', 'custom-fields')
		) /* end of options */
	); /* end of register post type */
	
}

	// adding the function to the Wordpress init
	add_action( 'init', 'cart_post_type');
	
/**
 * Define the metabox and field configurations.
 */
function vb_wpv_cart_custom_fields() {

  global $vb_wpv_custom_fields_prefix ;
  // Start with an underscore to hide fields from custom fields list
  $prefix = $vb_wpv_custom_fields_prefix;

  /**
   * Initiate the metabox
   */
  $cmb = new_cmb2_box( array(
      'id'            => 'cart_meta',
      'title'         => __( 'Cart data', 'wpvancancy' ),
      'object_types'  => array( 'cart_type', ), // Post type
      'context'       => 'normal',
      'priority'      => 'high',
      'show_names'    => true, // Show field names on the left
      // 'cmb_styles' => false, // false to disable the CMB stylesheet
      'closed'     => false, // Keep the metabox closed by default
  ) );

  $cmb->add_field( array(
		'name'    => __( 'User', 'wpvacancy' ),
		'desc'    => __( 'The user this cart belongs to', 'wpvacancy' ),
		'id'      => $prefix . 'cart_user_id',
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
      'name'       => __( 'Cart price', 'wpvacancy' ),
      'desc'       => __( 'The price, tax included, to pay for this cart', 'wpvacancy' ),
      'id'         => $prefix . 'cart_tax_incl',
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
      'name'       => __( 'Cart checkout time', 'wpvacancy' ),
      'desc'       => __( 'The date/time this cart was checked out', 'wpvacancy' ),
      'id'         => $prefix . 'cart_checkout_time',
      'type'       => 'text_datetime_timestamp',
      // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
      // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
      // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
      'on_front'        => false, // Optionally designate a field to wp-admin only
      // 'repeatable'      => true,
  ) );
}

add_action( 'cmb2_admin_init', 'vb_wpv_cart_custom_fields' );

function vb_wpv_get_cart_items($cart_id)
{
  global $vb_wpv_custom_fields_prefix;
 
  if (empty($cart_id))
    $cart_id = vb_wpv_get_cart();
  
  $bookings = get_posts( 
          array('post_type' => 'booking_type',
              'numberposts' => '999999', 
              'post_status' => 'publish', 
              'order' => 'ASC', 
              'orderby' => 'date',
              'meta_query' => array(
                                      'key'     => $vb_wpv_custom_fields_prefix.'booking_cart_id',
                                      'value'   => $cart_id,
                                      'compare' => '='
                                    )));
  return $bookings;
}

function vb_wpv_get_cart_total_amount($cart_id)
{
  global $vb_wpv_custom_fields_prefix;
  $bookings = vb_wpv_get_cart_items($cart_id);
  $total = 0;
  foreach ($bookings as $b)
  {
    $price = get_post_meta($b->ID, $vb_wpv_custom_fields_prefix.'booking_amount_tax_incl', true);
    $total += $price;
  }
  return $total;
}

function vb_wpv_get_cart_expiry($cart_id)
{
  global $vb_wpv_custom_fields_prefix;
  $bookings = vb_wpv_get_cart_items($cart_id);
  $expiry = PHP_INT_MAX;
  foreach ($bookings as $b)
  {
    $exp = get_post_meta($b->ID, $vb_wpv_custom_fields_prefix.'booking_expiration_time', true);
    if ($exp < $expiry)
      $expiry = $exp;
  }
  return $expiry;
}

function vb_wpv_get_cart_checkout_time($cart_id)
{
  global $vb_wpv_custom_fields_prefix;
  $cotime = get_post_meta($cart_id, $vb_wpv_custom_fields_prefix.'cart_checkout_time', true);
  return $cotime;
}

function vb_wpv_create_cart()
{
  global $vb_wpv_custom_fields_prefix, $vb_wpv_session_lifetime;
  
  $user = get_current_user_id();
  
  $post_author = $user > 0 ? $user : get_option(Wpvacancy_Admin::$anonymousCartsUser, 1); // KNOWN BUG, the "1" default is ok only if user with ID 1 exists
                                                                                        // Here we are assuming either:
                                                                                        // - the plugin has been configured in the backend so we don't need this default
                                                                                        // or
                                                                                        // - the user with ID 1 does exist.

  $cart = array(
        'post_status'           => 'publish', 
        'post_type'             => 'cart_type',
        'post_author'           => $post_author
      );
  $cartid = wp_insert_post($cart);
  if ($cartid > 0)
  {
    update_post_meta($cartid, $vb_wpv_custom_fields_prefix.'cart_user_id', $user);
    if ($user == 0)
    {
      $sessionid = vb_wpv_get_session_id();
      update_post_meta($cartid, $vb_wpv_custom_fields_prefix.'cart_session_id', $sessionid);
      update_post_meta($cartid, $vb_wpv_custom_fields_prefix.'cart_session_expire', time() + $vb_wpv_session_lifetime);
    }
  }
  else
  {
    $cartid = false;  
  }
  
  return $cartid;
}

function vb_wpv_get_cart_by_userid($userid)
{
  global $vb_wpv_custom_fields_prefix;
  return get_posts( 
            array('post_type' => 'cart_type',
                'numberposts' => '1', 
                'post_status' => 'publish', 
                'order' => 'DESC', 
                'orderby' => 'date',
                'meta_query' => array(
                                      'key'     => $vb_wpv_custom_fields_prefix.'cart_user_id',
                                      'value'   => $userid
                                     )
                ));
}
	
function vb_wpv_get_cart_by_sessionid()
{
  global $vb_wpv_custom_fields_prefix;
  $sessionid = vb_wpv_get_session_id();
  if (!empty($sessionid))
  {
    return get_posts( 
              array('post_type' => 'cart_type',
                  'numberposts' => '1', 
                  'post_status' => 'publish', 
                  'order' => 'DESC', 
                  'orderby' => 'date',
                  'meta_query' => array(
                                        'key'     => $vb_wpv_custom_fields_prefix.'cart_session_id',
                                        'value'   => $sessionid
                                       )
                  ));
  }
  return false;
}

function vb_wpv_get_cart($userid = 0, $create_it_if_missing = false)
{
  global $vb_wpv_custom_fields_prefix;
  
  $cart = $userid > 0 ? vb_wpv_get_cart_by_userid($userid) : vb_wpv_get_cart_by_sessionid();
  if ((is_array($cart) && count($cart) > 0) &&
      (empty(vb_wpv_get_cart_checkout_time($cart[0]->ID))))
    {
      // TODO KNOWN BUG: we should check if there's a session cart too and, in that case, merge the carts
      return $cart[0];
    }
  // no pending cart, but there might be a session cart even for logged in users,
  // because any user can create a cart before loggin in.
  if ($userid > 0)
  {
    $cart = vb_wpv_get_cart_by_sessionid();
    if ((is_array($cart) && count($cart) > 0) &&
        (empty(vb_wpv_get_cart_checkout_time($cart[0]->ID))))
    {
      // let's assign the session cart we found to the current logged in user
      update_post_meta($cart[0]->ID, $vb_wpv_custom_fields_prefix . 'cart_user_id', $userid);
      // remove the sessionid, so that it's not a session cart anymore
      delete_post_meta($cart[0]->ID, $vb_wpv_custom_fields_prefix . 'cart_session_id');
      // and we need to set the bookings expiration times too
      $bookings = vb_wpv_get_cart_items($cart[0]->ID);
      $now = time();
      foreach ($bookings as $b)
      {
        vb_wpv_set_booking_expiration_time($b->ID, $now, vb_wpv_get_configured_booking_expiration($userid));
      }
      // finally we return the cart
      return $cart[0];
    }
  }
  // no pending cart anywhere, we create one if requested
  if ($create_it_if_missing)
  {
    return vb_wpv_create_cart();
  }
  // nothing else to try, let's give up
  return false;
}

function vb_wpv_get_session_id()
{
  global $vb_wpv_session_id;
  if (empty($vb_wpv_session_id))
  {
    $CrawlerDetect = new CrawlerDetect;
    if (empty($CrawlerDetect->isCrawler())) // if it is NOT Google Bot et al.
    {    
      $vb_wpv_session_id = filter_input(INPUT_COOKIE, "vb-wpv-sessionid");
    }
  }
  return $vb_wpv_session_id;
}

function vb_wpv_set_session_id()
{
  global $vb_wpv_session_id, $vb_wpv_session_lifetime;
  vb_wpv_get_session_id();
  if (empty($vb_wpv_session_id))
  {
    $CrawlerDetect = new CrawlerDetect;
    if (empty($CrawlerDetect->isCrawler())) // if it is NOT Google Bot et al.
    {
      session_set_cookie_params($vb_wpv_session_lifetime); 
      session_start();
      $vb_wpv_session_id = filter_input(INPUT_COOKIE, "vb-wpv-sessionid");
      if (empty($vb_wpv_session_id))
      {
        $vb_wpv_session_id = random_bytes(250);
        setcookie("vb-wpv-sessionid", $vb_wpv_session_id);
      }	
    }
  }
}

vb_wpv_set_session_id();


?>

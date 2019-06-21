<?php

if (function_exists("strptime") === false)
{

  function strptime($date, $format)
  {
    $masks = array(
        '%d' => '(?P<d>[0-9]{2})',
        '%m' => '(?P<m>[0-9]{2})',
        '%Y' => '(?P<Y>[0-9]{4})',
        '%H' => '(?P<H>[0-9]{2})',
        '%M' => '(?P<M>[0-9]{2})',
        '%S' => '(?P<S>[0-9]{2})',
            // usw.. 
    );

    $rexep = "#" . strtr(preg_quote($format), $masks) . "#";
    if (!preg_match($rexep, $date, $out))
      return false;

    $ret = array(
        "tm_sec" => (int) $out['S'],
        "tm_min" => (int) $out['M'],
        "tm_hour" => (int) $out['H'],
        "tm_mday" => (int) $out['d'],
        "tm_mon" => $out['m'] ? $out['m'] - 1 : 0,
        "tm_year" => $out['Y'] > 1900 ? $out['Y'] - 1900 : 0,
    );
    return $ret;
  }
}

class WPV_BookingForm
{
  private $html;
  private $cal;
  private $maps;
  public static $namespace = 'wpvacancy/v1';
  private static $accommodations = null;
  private static $accunitDetailClass = 'wpv-booking-details-row-accommodation';
  private static $accunitTypeClass = 'wpv-booking-details-row-accommodationtype';
  private static $startDateClass = 'wpv-booking-details-row-startdate';
  private static $endDateClass = 'wpv-booking-details-row-enddate';
  private static $totalPriceClass = 'wpv-booking-details-row-totalprice';
  private static $notesClass = 'wpv-booking-details-row-notes';
  public static $bookingformcontainerclass = 'wpv-booking-form-container';
  public static $bookingformmapiddatatag = 'mapid';
  public static $singleAccmAvailable = 'wpv-calendar-daytag-single-accommodation-ok';
  public static $singleAccmUnavailable = 'wpv-calendar-daytag-single-accommodation-ko';
  public static $addToCartButtonClass = 'wpv-booking-addtocart-button';
  private static $periodsCache;
  private static $periodsCacheStartsFrom;
  private static $periodsCacheSpansTo;
  private static $userid;

  function __construct()
  {
    /* WARNING: these two MUST be called BEFORE constructing the calendar and  
     * the map, so that the JS code calls the corresponding API functions and 
     * initializes JS variables beforehand.
     */
    Wpvacancy::$instance->registerScriptParamsCallback(array($this, "setupDefaults"));
    Wpvacancy::$instance->registerScriptParamsCallback(array($this, "loadMapParams"));
    
    
    global $vb_wpv_basedir;
    require_once $vb_wpv_basedir.'includes/WPV_Calendar.php';
    require_once $vb_wpv_basedir.'includes/WPV_AccommodationsMap.php';
    require_once $vb_wpv_basedir.'includes/WPV_Timepicker.php';
    add_action( 'rest_api_init', array($this, 'registerRoutes'), 999, 0); 
    $this->cal = new WPV_Calendar();
    $this->maps = new WPV_AccommodationsMap();
    Wpvacancy::$instance->registerScriptParamsCallback(array($this, "registerAddToCart"));
  }
  
  private function toHtml($atts = null, $content = '')
  {
    if (is_array($atts))
      extract($atts, EXTR_OVERWRITE);    
    
    $map_specified = !empty($map_id);
    
    if (!$map_specified)
      return '<div class="wpv-error">You must specify a map with the map_id attribute</div>';
    
    $res = '<div class="'.self::$bookingformcontainerclass.'" data-'.self::$bookingformmapiddatatag.'="'.$map_id.'">';
      $res .= $this->cal->getCalendar($map_id);

      $res .= $this->maps->map([$map_id]);
      $res .= $this->recap();
      $res .= $this->addToCartButton();
    $res .= "</div>";
    
    return $res;
  }
  
  public function getHtml($atts = null, $content = '')
  {
    if (is_preview() || !is_singular())
      return '';
    
    if (empty($this->html))
      $this->html = $this->toHtml($atts, $content);
    
    return $this->html;
  }
  
  
  public static function getAllBookings($dayid)
  {
    global $vb_wpv_custom_fields_prefix;
    
    $seconds = $dayid * 86400;
    $thedate = date("Y-m-d", $seconds);
    
    $bookings = get_posts( 
            array('post_type' => 'booking_type',
                'numberposts' => '999999', 
                'post_status' => 'publish', 
                'order' => 'DESC', 
                'orderby' => 'date',
                'meta_query' => array(
                                  'relation' => 'OR',
                                  array(
                                     'relation' => 'AND',
                                      array(
                                        'key'     => $vb_wpv_custom_fields_prefix.'booking_start_date',
                                        'value'   => $thedate,
                                        'compare' => '<=',
                                        'type' => 'DATE' 
                                         ), 
                                      array(
                                         'key'     => $vb_wpv_custom_fields_prefix.'booking_end_date',
                                         'value'   => $thedate,
                                         'compare' => '>=',
                                         'type' => 'DATE' 
                                          ),                                      
                                     ),
                                      array(
                                             'key'     => $vb_wpv_custom_fields_prefix.'booking_start_date',
                                             'value'   => $thedate,
                                             'compare' => '=',
                                             'type' => 'DATE' 
                                          )
                                      )
                ));
    return $bookings;
  }
  
  public static function getAllPeriods($dayid)
  {
    global $vb_wpv_custom_fields_prefix;
    
    $seconds = $dayid * 86400;
    $thedate = date("Y-m-d", $seconds);
    
    $periods = get_posts( 
            array('post_type' => 'period_type',
                'numberposts' => '1', 
                'post_status' => 'publish', 
                'order' => 'DESC', 
                'orderby' => 'date',
                'meta_query' => array(
                                  'relation' => 'OR',
                                  array(
                                     'relation' => 'AND',
                                      array(
                                        'key'     => $vb_wpv_custom_fields_prefix.'period_start_date',
                                        'value'   => $thedate,
                                        'compare' => '<=',
                                        'type' => 'DATE' 
                                         ), 
                                      array(
                                         'key'     => $vb_wpv_custom_fields_prefix.'period_end_date',
                                         'value'   => $thedate,
                                         'compare' => '>=',
                                         'type' => 'DATE' 
                                          ),                                      
                                     ),
                                      array(
                                             'key'     => $vb_wpv_custom_fields_prefix.'period_start_date',
                                             'value'   => $thedate,
                                             'compare' => '=',
                                             'type' => 'DATE' 
                                          )
                                      )
                ));
    return $periods;
  }

  public static function getAllPeriodsInRange($startdayid, $enddayid)
  {
    global $vb_wpv_custom_fields_prefix;
    
    // Since end_date is optional, and, if missing, we have to consider it == start_date, and since
    // I don't know how to check for empty(end_date) using the meta query, here are the conditions 
    // to retrieve a period:
    // start_date == startdayid || 
    //    (start_date > startdayid && start_date <= enddayid) ||
    //    (start_date < startdayid && end_date > startdayid)
    //
    // In ascii art, where sd = start_date and ed = end_date, using a timeline:
    //
    // < ------------------------------ timeline ------------------------------>
    //
    // 1st condition, sd == startdayid and we don't know/care about ed:
    // 
    //                       startdayid ------------------ enddayid
    //                       sd ___ ... ?ed
    //                       
    // 2nd condition, sd > startdayid && sd <= enddayid, careless about ed
    //                       startdayid ------------------ enddayid
    //                                   sd _____ ... ?ed
    //                       
    // 3rd condition, sd < startdayid && ed > startdayid
    //                       startdayid ------------------ enddayid
    //                sd ________________ ed
    //              sd ___________________________________ ed
    //                   sd ________________________________________ ed
    //                       
    
    $s_seconds = $startdayid * 86400;
    $s_thedate = date("Y-m-d", $s_seconds);
    
    $e_seconds = $enddayid * 86400;
    $e_thedate = date("Y-m-d", $e_seconds);
    
    $periods = get_posts( 
            array('post_type' => 'period_type',
                'numberposts' => '1', 
                'post_status' => 'publish', 
                'order' => 'DESC', 
                'orderby' => 'date',
                'meta_query' => array(
                                  'relation' => 'OR',
                                  array( /* start_date == startdayid */
                                      'key'     => $vb_wpv_custom_fields_prefix.'period_start_date',
                                      'value'   => $s_thedate,
                                      'compare' => '=',
                                      'type' => 'DATE' ),
                                  array( /* start_date > startdayid && start_date <= enddayid */
                                     'relation' => 'AND',
                                      array( /* start_date > startdayid */
                                         'key'     => $vb_wpv_custom_fields_prefix.'period_start_date',
                                         'value'   => $s_thedate,
                                         'compare' => '>',
                                         'type' => 'DATE' 
                                          ),                                      
                                      array( /* start_date <= enddayid */
                                         'key'     => $vb_wpv_custom_fields_prefix.'period_start_date',
                                         'value'   => $e_thedate,
                                         'compare' => '<=',
                                         'type' => 'DATE' 
                                          ),                                      
                                     ),                    
                                  array( /* start_date < startdayid && end_date > startdayid */
                                      'relation' => 'AND',
                                      array( /* start_date < startdayid */
                                           'key'     => $vb_wpv_custom_fields_prefix.'period_start_date',
                                           'value'   => $s_thedate,
                                           'compare' => '<',
                                           'type' => 'DATE' 
                                        ),
                                      array( /* end_date > startdayid */
                                           'key'     => $vb_wpv_custom_fields_prefix.'period_end_date',
                                           'value'   => $s_thedate,
                                           'compare' => '>',
                                           'type' => 'DATE' 
                                        )
                                      )
                                    )));
    return $periods;
  }
  
  
  public static function getAllAccommodations()
  {
    if (self::$accommodations == null)
      self::$accommodations = get_posts(
             array('post_type' => 'accommodation_type',
                'posts_per_page' => '999999', 
                'post_status' => 'publish'));
    return self::$accommodations;
  }
  
  public static function getBookableAccommodations($dayid, $accommodationid = null)
  {
    global $vb_wpv_custom_fields_prefix;
    $bookable = array();
    if (self::isBookableDay(!$dayid))
      return $bookable;
    
    if ($accommodationid === null)
      $accommodations = self::getAllAccommodations();
    else
      $accommodations = [$accommodationid];
    
    $bookings = self::getAllBookings($dayid);
    // let's remove the alreay booked accommodations
    foreach ($accommodations as $acc)
    {
      $availableForBooking = get_post_meta($acc->ID, $vb_wpv_custom_fields_prefix."acc_available_for_booking", true);
      if (empty($availableForBooking))
        continue;
      
      $booked = false;
      foreach ($bookings as $b)
      {
        $bookedAcc = get_post_meta($b->ID, $vb_wpv_custom_fields_prefix.'booking_acc_unit_id', true);
        if ($acc->ID == $bookedAcc)
        {
          $booked = true;
          break;
        }
      }
      if ($booked === false)
        array_push($bookable, $acc);
    }
    return $bookable;
  }

  public static function getBookableDays($accm_id, $fromdayid = null, $interval_lenght = 230)
  {
    global $vb_wpv_custom_fields_prefix;
      
    if (empty($fromdayid))
      $fromdayid = WPV_Calendar::dayid(time()); // today
    $bookableDays = array();
    
    $availableForBooking = get_post_meta($accm_id, $vb_wpv_custom_fields_prefix."acc_available_for_booking", true);
    if (empty($availableForBooking) && !Wpvacancy::is_admin() && !Wpvacancy::is_vacancy_admin())
    {
      return $bookableDays;
    }

    self::initPeriodsCache($fromdayid - $interval_lenght, $fromdayid + $interval_lenght);
    for ($dayid = $fromdayid; $dayid < $fromdayid + $interval_lenght; $dayid++)
    {
      $bookable = self::isBookableDayFromCache($dayid);
      if ($bookable === true)
      {
        $bookings = self::getAllBookings($dayid);
        foreach ($bookings as $b)
        {
          // Deleted bookings do not keep the accommodation booked, so others can book it
          $deleted = get_post_meta($b->ID, WPV_BookingMetaKeys::$deleted, true);
          if (!empty($deleted))
            continue;
          $booked_acc_id = get_post_meta($b->ID, $vb_wpv_custom_fields_prefix.'booking_acc_unit_id', true);
          if (!empty($booked_acc_id) && is_array($booked_acc_id) && in_array($accm_id, $booked_acc_id))
          {
            $bookable = false;
            break;
          }
        }

        if ($bookable === true)
          array_push ($bookableDays, $dayid);
      }
    }
    
    return $bookableDays;
  }
  
  public static function isBookableDay($dayid)
  {
    self::initPeriodsCache($dayid);
    return self::isBookableDayFromCache($dayid);
  }
  
  private static function dateparse($strdate)
  {
    $a = strptime($strdate, "%Y-%m-%d");
    $timestamp = mktime(0, 0, 0, $a['tm_mon']+1, $a['tm_mday'], $a['tm_year']+1900);      
    return $timestamp;
  }
  
  private static function initPeriodsCache($startdayid, $enddayid = null)
  {
    global $vb_wpv_custom_fields_prefix;
        
    
    if (empty(self::$periodsCache) || 
            empty(self::$periodsCacheStartsFrom) || $startdayid < self::$periodsCacheStartsFrom ||
            empty(self::$periodsCacheSpansTo) ||(!empty($enddayid) && $enddayid > self::$periodsCacheSpansTo))
    {
      if (!is_array(self::$periodsCache))
      {
        self::$periodsCache = array();
      }
      if (!empty($enddayid))
      {
        $periods = self::getAllPeriodsInRange($startdayid, $enddayid);
      }
      else
      {
        $periods = self::getAllPeriods($startdayid);
      }
      foreach ($periods as $p)
      {
        $pstart = WPV_Calendar::dayid(self::dateparse(get_post_meta($p->ID, $vb_wpv_custom_fields_prefix.'period_start_date', true)));
        $pend = WPV_Calendar::dayid(self::dateparse(get_post_meta($p->ID, $vb_wpv_custom_fields_prefix.'period_end_date', true)));
        array_push(self::$periodsCache, array($pstart, $pend, $p));
        if (empty($enddayid) || $pend > $enddayid)
        {
          $enddayid = $pend;
        }
      }
    }
    
    if (empty(self::$periodsCacheStartsFrom) || $startdayid < self::$periodsCacheStartsFrom)
    {
      self::$periodsCacheStartsFrom = $startdayid;
    }
    
    if (empty($enddayid))
    {
      $enddayid = $startdayid;
    }

    if (empty(self::$periodsCacheSpansTo) || $enddayid > self::$periodsCacheSpansTo)
    {
      self::$periodsCacheSpansTo = $enddayid;
    }
  }
  
  private static function isBookableDayFromCache($dayid, $returntype = 'boolean')
  {
    self::initPeriodsCache($dayid);
    foreach (self::$periodsCache as $period)
    {
      if ($dayid >= $period[0] && $dayid <= $period[1])
      {
        if ($returntype == 'boolean')
        {
          return true;
        }
        else
        {
          return $period[2];
        }
      }
    }

    return false;
  }
  
  public function recap()
  {
    $res = '<div class="wpv-booking-option-title wpv-booking-recap-title">'.__('Here are your booking details', 'wpvacancy').'</div>';
    $res .= '<div class="wpv-booking-details">';
      $res .= '<div class="wpv-booking-details-row '.self::$accunitDetailClass.'">';
        $res .= '<div class="wpv-booking-details-label">';
        $res .= __('You are booking the accommodation', 'wpvacancy');
        $res .= '</div>';
        $res .= '<div class="wpv-booking-details-value">';
        $res .= '---';
        $res .= '</div>';
      $res .= '</div>';
 
      $res .= '<div class="wpv-booking-details-row '.self::$accunitTypeClass.'">';
        $res .= '<div class="wpv-booking-details-label">';
        $res .= __('which is a', 'wpvacancy');
        $res .= '</div>';
        $res .= '<div class="wpv-booking-details-value">';
        $res .= '---';
        $res .= '</div>';
      $res .= '</div>';

      $res .= '<div class="wpv-booking-details-row '.self::$startDateClass.'">';
        $res .= '<div class="wpv-booking-details-label">';
        $res .= __('Your booking starts on', 'wpvacancy');
        $res .= '</div>';
        $res .= '<div class="wpv-booking-details-value">';
        $res .= '---';
        $res .= '</div>';
      $res .= '</div>';

      $res .= '<div class="wpv-booking-details-row '.self::$endDateClass.'">';
        $res .= '<div class="wpv-booking-details-label">';
        $res .= __('and ends on', 'wpvacancy');
        $res .= '</div>';
        $res .= '<div class="wpv-booking-details-value">';
        $res .= '---';
        $res .= '</div>';
      $res .= '</div>';

      $res .= '<div class="wpv-booking-details-row '.self::$totalPriceClass.'">';
        $res .= '<div class="wpv-booking-details-label">';
        $res .= __('Total price', 'wpvacancy');
        $res .= '</div>';
        $res .= '<div class="wpv-booking-details-value">';
        $res .= '---';
        $res .= '</div>';
      $res .= '</div>';

      $res .= '<div class="wpv-booking-details-row '.self::$notesClass.'">';
        $res .= '<div class="wpv-booking-details-label">';
        $res .= __('Notes', 'wpvacancy');
        $res .= '</div>';
        $res .= '<div class="wpv-booking-details-value">';
        $res .= '---';
        $res .= '</div>';
      $res .= '</div>';

    $res .= '</div>';
    
    return $res;
  }
  
  public function addToCartButton()
  {
    $res = '<div class="wpv-booking-addtocart">';
      $res .= '<div class="'.self::$addToCartButtonClass.'">';
        $res .= '<div class="wpv-booking-addtocart-text">';
          $res .= __('Add to cart', 'wpvacancy');
        $res .= '</div>';
        $res .= '<div class="wpv-booking-addtocart-icon">';
          $res .= '<i class="fa fa-cart-arrow-down" aria-hidden="true"></i>';
        $res .= '</div>';
      $res .= '</div>';
    $res .= '</div>';
    
    return $res;
  }
  
  public function setupDefaults(array $params)
  {  
    return array('load', 
                 'setupDefaults', 
                  array(self::$accunitDetailClass,
                        self::$accunitTypeClass,
                        self::$startDateClass,
                        self::$endDateClass,
                        self::$totalPriceClass,
                        self::$notesClass,
                        'wpv-booking-details-value',
                        get_rest_url(),
                        Wpvacancy::$namespace,
                        self::$singleAccmAvailable,
                        self::$singleAccmUnavailable,
                        WPV_AccommodationsMap::$accommodation_ok_class,
                        WPV_AccommodationsMap::$accommodation_ko_class,
                        WPV_AccommodationsMap::$accommodation_class
                        ));    
  }
  
  public function loadMapParams(array $params)
  {  
    return array('load', 
                 'loadMapParams', 
                  array(self::$bookingformcontainerclass,
                        self::$bookingformmapiddatatag
                        ));    
  }
  
  public function registerRoutes()
  {
    self::$userid = get_current_user_id();
    register_rest_route(Wpvacancy::$namespace, '/getRecapInfo', array(
    'methods'  => WP_REST_Server::READABLE,
    'callback' => array($this, 'getRecapInfo'),
      ) );
    register_rest_route(Wpvacancy::$namespace, '/addToCart', array(
    'methods'  => WP_REST_Server::READABLE,
    'callback' => array($this, 'addToCart'),
      ) );
    register_rest_route(Wpvacancy::$namespace, '/getMapParams', array(
    'methods'  => WP_REST_Server::READABLE,
    'callback' => array($this, 'getMapParams'),
      ) );
  }
  
  public static function getBookingPrice($booking)
  {
    $bk = vb_wpv_get_booking_accommodation_id($booking);
    $sd = vb_wpv_get_booking_start_as_uxts($booking);
    $ed = vb_wpv_get_booking_end_as_uxts($booking);
    $dayid_sd = WPV_Calendar::dayid($sd);
    $dayid_ed = WPV_Calendar::dayid($ed);
    $starttime = WPV_Calendar::timeofday($sd);
    $endtime = WPV_Calendar::timeofday($ed);
    return self::getTotalPrice($bk, $dayid_sd, $dayid_ed, $starttime, $endtime);
  }

  public static function getTotalPrice($accid, $startdayid, $enddayid, $starttime = 0, $endtime = 0)
  {
    $total = 0;
    $price_per_day_for_debug = 150;
    self::initPeriodsCache($startdayid, $enddayid);
    for ($day = $startdayid; $day < $enddayid; $day++)
    {
      if (!self::isBookableDayFromCache($day))
        return -1;
      $available = self::getBookableAccommodations($day);
    }
    
    // DEBUG ONLY
    return $price_per_day_for_debug * ($enddayid - $startdayid);
  }


  public function getRecapInfo(WP_REST_Request $request)
  {
    global $vb_wpv_custom_fields_prefix;
    $key = $request->get_param("key");
    $result = ["value" => ''];

    switch ($key)
    {
      case 'dateFromDayId':
        $dayid = $request->get_param("dayid");
        $locale = get_locale();
        $timestamp = $dayid * 86400;
        $result["value"] = strftime("%x", $timestamp);
        break;
      case 'getNotesForAccommodation':
        $accid = $request->get_param("accid");
        $accunit = get_post($accid);
        $pax = get_post_meta($accid, $vb_wpv_custom_fields_prefix."acc_unit_pax", true);
        if (!empty($pax))
          $pax .= ' '.__('Pax', 'wpvacancy');
        $notes = apply_filters('the_content', get_post_meta($accunit, $vb_wpv_custom_fields_prefix."acc_unit_notes", true));
        if (!empty($pax))
        {
          if (empty($notes))
            $notes = $pax;
          else
            $notes = $pax . ' - ' .$notes;
        }
        
        $ucats = wp_get_object_terms($accid, 'accommodation_cat', array('fields' => 'id=>slug'));
        foreach ($ucats as $unitcategoryslug)
        {
          $fullcat = get_term_by('slug', $unitcategoryslug, 'accommodation_cat');
          if (!empty($fullcat->description))
          {
            if (!empty($notes))
              $notes .= ' - '.apply_filters('the_content', $fullcat->description);
            else
              $notes = apply_filters('the_content', $fullcat->description);
          }
        }
        $result["value"] = $notes;
        break;
      case 'getPriceForBooking':
        $accid = $request->get_param("accid");
        $startdayid = $request->get_param("startdayid");
        $enddayid = $request->get_param("enddayid");
        $starttime = $request->get_param("starttime");
        $endtime = $request->get_param("endtime");
        if (empty($starttime) || empty($endtime))
          $total = self::getTotalPrice($accid, $startdayid, $enddayid);
        else
          $total = self::getTotalPrice($accid, $startdayid, $enddayid, $starttime, $endtime);
        if ($total > 0)
          $result["value"] = "€ ".$total.' '.__("tax included", 'wpvacancy');
        else
          $result["value"] =  __("Your choice is not available, try choosing different dates and/or accommodation", "wpvacancy");
        
        break;
    }
    return $result;
  }
  
  public function addToCart(WP_REST_Request $request)
  {
    $booking = null;
    $accommodation = $request->get_param("accid");
    
    $startDate = $request->get_param("startDate");
    $endDate = $request->get_param("endDate");
    $startTime = $request->get_param("startTime");
    $endTime = $request->get_param("endTime");
    
    $cart_id = vb_wpv_get_cart(get_current_user_id(), true);

    // step 1: try to edit this booking if it is already in the cart
    // step 2: check real availability at this exact time
    // step 3: add to cart
    
    // step 1
    $editedBooking = self::editExistingBooking(self::$userid, $accommodation, $startDate, $endDate, $startTime, $endTime);
    if (!empty($editedBooking))
    {
      $booking = $editedBooking;
      $bmessage = __('Booking modified as requested', 'wpvacancy');
    }
    else
    { // step 2 (TODO: add startTime/endTime support)
      $bookable = self::getBookableDays($accommodation, $startDate, $endDate - $startDate);
      if (count($bookable) == $endDate - $startDate)
      {
        $alreadyBooked = $this->findInCart(self::$userid, $accommodation, $startDate, $endDate, $startTime, $endTime);
        if (!empty($alreadyBooked))
          $result = ["value" => 'error', "message" => __('You can\'t change this booking you already added to your cart', 'wpvacancy')];
        else
        {
          $booking = vb_wpv_create_booking(self::$userid, $accommodation, $startDate, $endDate, $startTime, $endTime);
          $bmessage = __('Booking added to your cart', 'wpvacancy');
        }
        if (!empty($booking))
        {
          $nitems = count(vb_wpv_get_cart_items($cart_id));
          $result = ["value" => 'booked', 
                      "itemwrapperclass" => 'wpv-booking-details', 
                      "cartwrapperclass" => WPV_Cart::$cartbuttonwrapperclass, 
                      "nitemsclass" => WPV_Cart::$numberofitemsclass,
                      "nitems" => $nitems,
                      "message" => $bmessage,
                      "animationelement" => '<div class="wpv-addtocart-animated-number">'.$nitems.'</div>',
                      "animationelementclass" => "wpv-addtocart-animated-number-stop1"
                    ];
        }
      }
    }
    if (empty($booking) && empty($result))
      $result = ["value" => 'error', "message" => __('Booking not available (anymore)', 'wpvacancy'), "action" => 'reload'];

    return $result;
  }
  
  public function getMapParams(WP_REST_Request $request)
  {
    global $vb_wpv_custom_fields_prefix;
    $mapid = $request->get_param("mapid");
    
    $allowSingleDayBooking = (boolean)get_post_meta($mapid, $vb_wpv_custom_fields_prefix."accm_map_singledayselection", true);
    $defaultSliderDuration = (int)get_post_meta($mapid, $vb_wpv_custom_fields_prefix."accm_map_defaultsliderduration", true);
    
    return ["value" => "params", 
             "allowSingleDayBooking" => $allowSingleDayBooking,
             "defaultSliderDuration" => $defaultSliderDuration];
  }

  public function registerAddToCart()
  {
    $addtocartparams = WPV_Cart::getShowCartParams();
    array_unshift($addtocartparams, self::$addToCartButtonClass);

    return array('click', 
                  'addToCart', 
                  $addtocartparams);
    
  }

  public function findInCart($userid, $accommodation, $startDate, $endDate, $startTime, $endTime)
  {
    // first of all, I need to check other bookings in the cart.
    $cart = vb_wpv_get_cart($userid);
    if (!empty($cart))
    {
      $existingbookings = vb_wpv_get_cart_items($cart->ID);
    }
    if (!empty($existingbookings))
    {
      $b = $this->findBookingInArray($existingbookings, $accommodation, $startDate, $endDate, $startTime, $endTime);
      if (!empty($b))
        return $b;
    }
    return false;
  }
  
  private function findBookingInArray($bookingsArray, $accommodation, $startDate, $endDate, $startTime, $endTime, $exact = true)
  {
    foreach ($bookingsArray as $b)
    {
      $b_acc = get_post_meta($b->ID, WPV_BookingMetaKeys::$accommodation);
      if ($b_acc != $accommodation)
        continue;
      $b_startd = get_post_meta($b->ID, WPV_BookingMetaKeys::$startDate);
      $b_endd = get_post_meta($b->ID, WPV_BookingMetaKeys::$endDate);
      $b_startt = get_post_meta($b->ID, WPV_BookingMetaKeys::$startTime);
      $b_endt = get_post_meta($b->ID, WPV_BookingMetaKeys::$endTime);
      $b_start = intval($b_startd) * 86400 + $b_startt;
      $b_end = intval($b_endd) * 86400 + $b_endt;
      $paramStart = intval($startDate) * 86400 + $startTime;
      $paramEnd = intval($endDate) * 86400 + $endTime;
      if ($exact)
      {
        // find only a booking that matches exactly the parameters
        if ($b_start === $paramStart && $b_end === $paramEnd)
          return $b;
      }
      else
      {
        // find first booking that overlaps the parameters
        if (($b_start <= $paramStart && $b_end >= $paramStart) ||
             ($b_start <= $paramEnd && $b_end >= $paramEnd))
          return $b;
      }
    }
    return false;
  }
  
  private function editExistingBooking($userid, $accommodation, $startDate, $endDate, $startTime, $endTime)
  {
    $cart = vb_wpv_get_cart($userid);
    if (!empty($cart))
    {
      $existingbookings = vb_wpv_get_cart_items($cart->ID);
    }
    if (!empty($existingbookings))
    {
      $b = $this->findBookingInArray($existingbookings, $accommodation, $startDate, $endDate, $startTime, $endTime, false);
      if (!empty($b))
      {
        // TODO: I need to check if the new date/time limits are bookable
      }
    }
  }
  
  public function enqueueScripts()
  {
    $this->maps->enqueueScripts();
  }
}

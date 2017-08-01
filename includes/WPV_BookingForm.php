<?php


class WPV_BookingForm
{
  private $html;
  private $cal;
  private $range;
  private $maps;
  public static $namespace = 'wpvacancy/v1';
  private static $accommodations = null;
  private static $loadingClass = 'wpv-loading';
  private static $accunitDetailClass = 'wpv-booking-details-row-accommodation';
  private static $accunitTypeClass = 'wpv-booking-details-row-accommodationtype';
  private static $startDateClass = 'wpv-booking-details-row-startdate';
  private static $endDateClass = 'wpv-booking-details-row-enddate';
  private static $totalPriceClass = 'wpv-booking-details-row-totalprice';
  private static $notesClass = 'wpv-booking-details-row-notes';
  public static $singleAccmAvailable = 'wpv-calendar-daytag-single-accommodation-ok';
  public static $singleAccmUnavailable = 'wpv-calendar-daytag-single-accommodation-ko';
  private static $periodsCache;

  function __construct()
  {
    Wpvacancy::$instance->registerScriptParamsCallback(array($this, "setupDefaults"));
    global $vb_wpv_basedir;
    require_once $vb_wpv_basedir.'includes/WPV_Calendar.php';
    require_once $vb_wpv_basedir.'includes/WPV_RangeSlider.php';
    require_once $vb_wpv_basedir.'includes/WPV_AccommodationsMap.php';
    add_action( 'rest_api_init', array($this, 'registerRoutes'), 999, 0); 
    $this->cal = new WPV_Calendar();
    $this->range = new WPV_RangeSlider();
    $this->maps = new WPV_AccommodationsMap();
  }
  
  private function toHtml($atts = null, $content = '')
  {
    if (is_array($atts))
      extract($atts, EXTR_OVERWRITE);    
    
    $res = $this->cal->getCalendar();
    
    $res .= $this->range->range(31, 'startfrom1');
    
    $res .= $this->maps->map();
    
    $res .= $this->recap();
    
    return $res;
  }
  
  public function getHtml($atts = null, $content = '')
  {
    if (empty($this->html))
      $this->html = $this->toHtml($atts, $content);
    
    return $this->html;
  }
  
  public static function loading()
  {
    $html = '<div class="'.self::$loadingClass.'">';
    $html .= '</div>';
    return $html;
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
  
  public static function getAllAccommodations()
  {
    if (self::$accommodations == null)
      self::$accommodations = get_posts(
             array('post_type' => 'accommodation_type',
                'posts_per_page' => '999999', 
                'post_status' => 'publish'));
    return self::$accommodations;
  }
  
  public static function getBookableAccommodations($dayid)
  {
    global $vb_wpv_custom_fields_prefix;
    $bookable = array();
    if (self::isBookableDay(!$dayid))
      return $bookable;
    
    $accommodations = self::getAllAccommodations();
    $bookings = self::getAllBookings($dayid);
    // let's remove the alreay booked accommodations
    foreach ($accommodations as $acc)
    {
      $booked = false;
      foreach ($bookings as $b)
      {
        if ($booked == true)
          break;
        $bookedAccms = get_post_meta($b->ID, $vb_wpv_custom_fields_prefix.'booking_acc_unit_id', true);
        foreach ($bookedAccms as $accid)
        {
          if ($acc->ID == $accid)
          {
            $booked = true;
            break;
          }
        }
      }
      if ($booked === false)
        array_push($bookable, $acc);
    }
    return $bookable;
  }

  public static function getBookableDays($accm_id, $fromdayid = null, $interval_lenght = 459)
  {
    global $vb_wpv_custom_fields_prefix;
    if (empty($fromdayid))
      $fromdayid = WPV_Calendar::dayid(time()); // today
    $bookableDays = array();
    for ($dayid = $fromdayid; $dayid < $fromdayid + $interval_lenght; $dayid++)
    {
      $bookable = self::isBookableDay($dayid);
      if ($bookable === true)
      {
        $bookings = self::getAllBookings($dayid);
        foreach ($bookings as $b)
        {
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
    if (empty(self::$periodsCache))
    {
      $periods = self::getAllPeriods($dayid);
      self::buildPeriodsCache($periods);
    }
    return self::isBookableDayFromCache($dayid);
    // return count($periods) > 0 ? true : false;
  }
  
  private static function dateparse($strdate)
  {
    $a = strptime($strdate, "%Y-%m-%d");
    $timestamp = mktime(0, 0, 0, $a['tm_mon']+1, $a['tm_mday'], $a['tm_year']+1900);      
    return $timestamp;
  }
  
  private static function buildPeriodsCache($periods)
  {
    global $vb_wpv_custom_fields_prefix;
    if (!is_array(self::$periodsCache))
      self::$periodsCache = array();
    foreach ($periods as $p)
    {
      $pstart = WPV_Calendar::dayid(self::dateparse(get_post_meta($p->ID, $vb_wpv_custom_fields_prefix.'period_start_date', true)));
      $pend = WPV_Calendar::dayid(self::dateparse(get_post_meta($p->ID, $vb_wpv_custom_fields_prefix.'period_end_date', true)));
      array_push(self::$periodsCache, array($pstart, $pend));
    }
  }
  
  private static function isBookableDayFromCache($dayid)
  {
    foreach (self::$periodsCache as $period)
    {
      if ($dayid >= $period[0] && $dayid <= $period[1])
      {
        return true;
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
        $res .= __('Please note', 'wpvacancy');
        $res .= '</div>';
        $res .= '<div class="wpv-booking-details-value">';
        $res .= '---';
        $res .= '</div>';
      $res .= '</div>';

    $res .= '</div>';
    
    return $res;
  }
  
  public function setupDefaults(array $params)
  {  
    $postid = $params[0];
    $target = $params[1];
    $highlight = $params[2];
    return array('load', 
                 'setupDefaults', 
                  array(self::$loadingClass,
                        self::$accunitDetailClass,
                        self::$accunitTypeClass,
                        self::$startDateClass,
                        self::$endDateClass,
                        self::$totalPriceClass,
                        self::$notesClass,
                        'wpv-booking-details-value',
                        get_rest_url(),
                        self::$namespace,
                        self::$singleAccmAvailable,
                        self::$singleAccmUnavailable
                        ));    
  }
  
  public function registerRoutes()
  {    
    register_rest_route(WPV_BookingForm::$namespace, '/getRecapInfo', array(
    'methods'  => WP_REST_Server::READABLE,
    'callback' => array($this, 'getRecapInfo'),
      ) );
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
        $notes = get_post_meta($accunit, $vb_wpv_custom_fields_prefix."acc_unit_notes", true);
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
              $notes .= ' - '.$fullcat->description;
            else
              $notes = $fullcat->description;
          }
        }
        $result["value"] = $notes;
        break;
      case 'getPriceForBooking':
        $accid = $request->get_param("accid");
        $startdayid = $request->get_param("startdayid");
        $enddayid = $request->get_param("enddayid");
        $result["value"] = self::getTotalPrice($accid, $startdayid, $enddayid);
        break;
    }
    return $result;
  }


}

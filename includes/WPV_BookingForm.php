<?php


class WPV_BookingForm
{
  private $html;
  private $cal;
  private $range;
  private $maps;
  public static $namespace = 'wpvacancy/v1';
  private static $accommodations = null;

  function __construct()
  {
    global $vb_wpv_basedir;
    require_once $vb_wpv_basedir.'includes/WPV_Calendar.php';
    require_once $vb_wpv_basedir.'includes/WPV_RangeSlider.php';
    require_once $vb_wpv_basedir.'includes/WPV_AccommodationsMap.php';
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
    
    return $res;
  }
  
  public function getHtml($atts = null, $content = '')
  {
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
                'posts_per_page' => '999999', 
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
    $accommodations = self::getAllAccommodations();
    $bookings = self::getAllBookings($dayid);
    // let's remove the alreay booked accommodations
    $bookable = array();
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
}

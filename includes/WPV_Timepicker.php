<?php

class WPV_Timepicker
{
  public static $timeAvailabilityEndpoint = "getTimeAvailability";
  public static $timepickerMarkupEndpoint = "getTimepickerMarkup";
  public static $wrapperclass = "vs-timepicker-wrapper";
  public static $layoutclass = "vs-timepicker-layout";
  public static $containerclass = "vs-timepicker-container";
  public static $scrollclass = "vs-timepicker-scroll";
  public static $previousbuttonclass = "vs-timepicker-previous-button";
  public static $nextbuttonclass = "vs-timepicker-next-button";
  public static $navbuttonclass = "vs-timepicker-nav-button";
  public static $itemclass = "vs-timepicker-item";
  public static $clickabletimeclass = "vs-timepicker-clickable-time";
  public static $firstselectedclass = "vs-timepicker-first-selected-time";
  public static $selectedclass = "vs-timepicker-selected-time";
  public static $lastselectedclass = "vs-timepicker-last-selected-time";

  function __construct()
  {
    add_action( 'rest_api_init', array($this, 'registerRoutes'), 999, 0); 
    Wpvacancy::$instance->registerScriptParamsCallback(array($this, "bookingData"));
    Wpvacancy::$instance->registerScriptParamsCallback(array($this, "load"));
    Wpvacancy::$instance->registerScriptParamsCallback(array($this, "timeSelection"));
  }

  public function registerRoutes()
  {    
    register_rest_route(Wpvacancy::$namespace, '/'.self::$timeAvailabilityEndpoint, array(
    'methods'  => WP_REST_Server::READABLE,
    'callback' => array($this, 'get_time_availability'
      )));
    register_rest_route(Wpvacancy::$namespace, '/'.self::$timepickerMarkupEndpoint, array(
    'methods'  => WP_REST_Server::READABLE,
    'callback' => array($this, 'get_timepicker_markup'
      )));
  }
  
  private function items($mapid)
  {
    $res = '';
    $stepping = 30;
    $decimalstep = $stepping / 60;
    for ($t = 0; $t <= 24; $t += $decimalstep)
    {
      $minutesinday = intval($t * 60);
      $hours = sprintf("%02d", intval($t));
      $minutes = sprintf("%02d", intval(($t - intval($t)) * 60));
      $res .= '<div class="'.self::$itemclass.'" data-timeid="'.$minutesinday.'">'.$hours.':'.$minutes.'</div>';
    }
    return $res;
  }
  
  public function clock($mapid)
  {
    $res = '<div class="'.self::$wrapperclass.'">';
    $res .= '<div class="'.self::$layoutclass.'">';
    $res .= $this->previousTimes();
    $res .= '<div class="'.self::$containerclass.'">';
    $res .= '<div class="'.self::$scrollclass.'">';
    
    $res .= $this->items($mapid);
    
    $res .= '</div>';
    $res .= '</div>';
    $res .= $this->nextTimes();
    $res .= '</div>';
    $res .= '</div>';
    return $res;
  }
  
  public function previousTimes()
  {
    $res = '<div class="'.self::$navbuttonclass.' '.self::$previousbuttonclass.'">';
    $res .= '</div>';
    return $res;
  }
  
  public function nextTimes()
  {
    $res = '<div class="'.self::$navbuttonclass.' '.self::$nextbuttonclass.'">';
    $res .= '</div>';
    return $res;    
  }
  
  public function get_timepicker_markup(WP_REST_Request $request)
  {
    $tags = $request->get_param("includeavailabilitytags");
    $mapid = $request->get_param("mapid");
    $result = ["markup" => $this->items($mapid), 
               "container" => self::$scrollclass,
               "events" => $this->timeSelectionParams()];
    return $result;
  }


  public function bookingData()
  {
    return array('click', 
                  'updateBookingAvailabilityFromTimepickerClick', 
                  array(self::$itemclass));
    
  }

  // TODO, per ora da qui in giù è tutto copiato da Calendar, idem in JS (oppure là manca proprio)
  public function load()
  {
    return array('load', 
                  'loadTimepicker', 
                  array('', // dummy value, used only when loading on button click, while here is onLoad 
                        self::$wrapperclass, 
                        self::$layoutclass, 
                        self::$containerclass, 
                        self::$scrollclass,
                        self::$previousbuttonclass,
                        self::$nextbuttonclass,
                        'wpv-calendar-timetag-availability',
                        self::timeSelectionParams()
                      ));    
  }

  public function timeSelection()
  {
    return array('click', 
                  'timeSelection', 
                  self::timeSelectionParams());
    
  }
  
  private static function timeSelectionParams()
  {
    return  array(self::$clickabletimeclass,
                  self::$firstselectedclass,
                  self::$selectedclass,
                  self::$lastselectedclass);
  }
  
}
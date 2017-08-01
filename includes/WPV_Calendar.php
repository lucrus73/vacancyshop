<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of WPV_Calendario
 *
 * @author lucio
 */
class WPV_Calendar
{
  private static $endpoint = 'getCalendarMarkup';
  private static $wrapperclass = 'wpv-calendar-wrapper';
  private static $previousMonthButton = 'wpv-calendar-previous-month-button';
  private static $nextMonthButton = 'wpv-calendar-next-month-button';
  private static $defaultoffset = 0;
  private static $defaultspan = 2;
  private static $nonworkingday = 1;
  private static $ratio_low_availability = 0.3;
  private static $ratio_norm_availability = 0.7;
  private static $always_show_availability_for_admin_users = false;
  
  function __construct()
  {
    Wpvacancy::$instance->registerScriptParamsCallback(array($this, "bookingData"));
    Wpvacancy::$instance->registerScriptParamsCallback(array($this, "load"));
    Wpvacancy::$instance->registerScriptParamsCallback(array($this, "daySelection"));
    $this->controlpanel();
    add_action( 'rest_api_init', array($this, 'registerRoutes'), 999, 0); 
  }

  public function registerRoutes()
  {    
    register_rest_route(WPV_BookingForm::$namespace, '/'.self::$endpoint, array(
    'methods'  => WP_REST_Server::READABLE,
    'callback' => array($this, 'get_calendar_markup'),
      ) );
  }
  
  public function get_calendar_markup(WP_REST_Request $request)
  {
    $offset = $request->get_param("offset");
    $span = $request->get_param("span");

    $result["markup"] = $this->months($offset, $span);
    
    return $result;
  }
  
  public function getCalendar()
  {
    $html = '<div class="wpv-booking-option-title wpv-booking-startdate-title">';
    $html .= __('When does your holiday start?', 'wpvacancy');
    $html .= $this->controlpanel();
    $html .= '</div>';
    $html .= '<div class="'.self::$wrapperclass.'">';
    $html .= WPV_BookingForm::loading();
    $html .= '</div>';
    return $html;
  }
  
  public static function dayid($unixtime)
  {
    return (int)($unixtime / 86400);
  }
  
  public function months($offset = null, $span = null)
  {
    if (empty($offset) || empty($span) || intval($span) >= 4 || intval($span) < 1)
    {
      $offset = self::$defaultoffset;
      $span = self::$defaultspan;
    }

    $ut_now = time(null);
    $m = date("m", $ut_now) + $offset;
    $utnow_day = self::dayid($ut_now);
    $html = '';
    $meseiniziale = $m;
    $mesefinale = $m + ($span - 1);
    for ($meseincostruzione = $meseiniziale; $meseincostruzione <= $mesefinale; $meseincostruzione++)
    {
      $d = date("d");
      $y = date("Y");
      $nd = date('t', mktime(0, 0, 0, $meseincostruzione, 1, $y));
      $mn = date('n', mktime(0, 0, 0, $meseincostruzione, 1, $y));
      $yn = date('Y', mktime(0, 0, 0, $meseincostruzione, 1, $y));
      $j = date('w', mktime(0, 0, 0, $meseincostruzione, 1, $y)) + 1;
      if ($j == "7")
      {
        $j = "0";
      }
      $MONTHS = array(1 =>  __('Jan', 'wpvacancy'), 
                            __('Feb', 'wpvacancy'), 
                            __('Mar', 'wpvacancy'), 
                            __('Apr', 'wpvacancy'),
                            __('May', 'wpvacancy'),
                            __('Jun', 'wpvacancy'),
                            __('Jul', 'wpvacancy'),
                            __('Aug', 'wpvacancy'),
                            __('Sep', 'wpvacancy'),
                            __('Oct', 'wpvacancy'),
                            __('Nov', 'wpvacancy'),
                            __('Dec', 'wpvacancy'));

      $html .= '<div class="wpv-calendar-month">';
      $html .= '<table cellspacing="0" cellpadding="5" align="center" width="100" border="1">';
      $html .= '<thead>';
      $html .= '<tr>';
      $html .= '<th>';
      
      if ($meseincostruzione == $meseiniziale)
        $html .= '<i class="fa fa-arrow-left '.self::$previousMonthButton.'" data-wpvoffset="'.($offset - 1).'" data-wpvspan="'.$span.'" aria-hidden="true"></i>';
      
      $html .= '</th>';
      $html .= '<th colspan="5">';
      $html .= $MONTHS[$mn] . " " . $yn;
      $html .= '</th>';
      $html .= '<th align="center">';
      
      if ($meseincostruzione == $mesefinale)
        $html .= '<i class="fa fa-arrow-right '.self::$nextMonthButton.'" data-wpvoffset="'.($offset + 1).'" data-wpvspan="'.$span.'" aria-hidden="true"></i>';
      
      $html .= '</th>';
      $html .= '</tr>';
      $html .= '<tr>';
      $days = [$this->fc(__('Saturday', 'wpvacancy')), 
               $this->fc(__('Sunday', 'wpvacancy')),
               $this->fc(__('Monday', 'wpvacancy')),
               $this->fc(__('Tuesday', 'wpvacancy')),
               $this->fc(__('Wednesday', 'wpvacancy')),
               $this->fc(__('Thursday', 'wpvacancy')),
               $this->fc(__('Friday', 'wpvacancy'))
          ];
      for ($dayoffset = 0; $dayoffset < 7; $dayoffset++)
      {
        $wday = ($j + $dayoffset) % 7;
        $html .= '<th class="wpv-calendar-day">'.$this->allTheTagsForADay(null, $wday).$days[$wday].'</th>';
      }
      $html .= '</tr>';
      $html .= '</thead>';
      $html .= '<tbody>';
      $html .= '<tr>';
      $column = 0;
      for ($giornodelmese = 1; $giornodelmese <= $nd; $giornodelmese++)
      {
        $weekday = ($column + $j) % 7;
        $ut_dayofmonth = self::dayid(mktime(0, 0, 0, $meseincostruzione, $giornodelmese, $y));

        $showavailability = WPV_BookingForm::isBookableDay($ut_dayofmonth);
        
        $timeline_class = "wpv-calendar-day-today";
        if ($ut_dayofmonth < $utnow_day)
        {
          $timeline_class = "wpv-calendar-day-inthepast";
          $showavailability = self::$always_show_availability_for_admin_users && Wpvacancy::is_admin();
        }
        else
        if ($ut_dayofmonth > $utnow_day)
          $timeline_class = "wpv-calendar-day-inthefuture";
        
        $clickable = $showavailability ? "wpv-calendar-clickable-day" : "";
        
        $html .= '<td class="wpv-calendar-day '.$clickable.'" data-wpvdayid="'.$ut_dayofmonth.'">'; 
        $html .= $this->allTheTagsForADay($ut_dayofmonth, $weekday, $showavailability);
        $html .= '<div class="wpv-calendar-daynumber '.$timeline_class.'">';
        $html .= $giornodelmese;
        $html .= '</div>';
        $html .= '</td>';
        $column++;
        if ($column == 7)
        {
          $html .= '</tr><tr>';
          $column = 0;
        }
      }
      $html .= '</tr>';
      $html .= '</tbody>';
      $html .= '</table>';
      $html .= '</div>';
    }

    $html .= WPV_BookingForm::loading();
    return $html; 
  }
  
  private function allTheTagsForADay($dayid, $weekday, $showavailability = true)
  {
    $res = $this->nonWorkingDayTag($dayid, $weekday);
    $res .= $this->nonWorkingDayEveTag($dayid, $weekday);
    if (!empty($dayid) && $showavailability === true)
      $res .= $this->accommodationAvailability($dayid);
    $res .= '<div class="wpv-calendar-daytag '.WPV_BookingForm::$singleAccmAvailable.'"></div>';
    $res .= '<div class="wpv-calendar-daytag '.WPV_BookingForm::$singleAccmUnavailable.'"></div>';
    return $res;
  }
  
  /**
   * Returns a <div></div> or empty string. If the $weekday
   * is holiday, it returns a <div> with appropriate CSS classes. Otherwise 
   * it returns a empty string.
   * It SHOULD also consider holidays other than Sundays (or other configured weekday)
   * but, as of this writing, that's not implemented yet. That means the $dayid argument
   * is not being used ATM.
   * @param type $dayid
   * @param type $weekday
   */
  private function nonWorkingDayTag($dayid, $weekday)
  {
    if ($weekday == self::$nonworkingday)
      return '<div class="wpv-calendar-daytag wpv-calendar-daytag-daytype wpv-calendar-daytag-nonworking"></div>';
    return '';
  }

  private function nonWorkingDayEveTag($dayid, $weekday)
  {
    if (!empty($this->nonWorkingDayTag($dayid + 1, ($weekday + 1) % 7)))
      return '<div class="wpv-calendar-daytag wpv-calendar-daytag-daytype wpv-calendar-daytag-nonworkingeve"></div>';
    return '';
  }
  
  private function accommodationAvailability($dayid)
  {
    $all = WPV_BookingForm::getAllAccommodations();
    $bookable = WPV_BookingForm::getBookableAccommodations($dayid);
    $ratio = count($bookable) / count($all);
    $availImage = 'empty';
    if ($ratio > 0 && $ratio <= self::$ratio_low_availability)
      $availImage = 'low';
    else
      if ($ratio > self::$ratio_low_availability && $ratio <= self::$ratio_norm_availability)
        $availImage = 'normal';
      else
        if ($ratio > self::$ratio_norm_availability)
          $availImage = 'full';
    return '<div class="wpv-calendar-daytag wpv-calendar-daytag-availability wpv-calendar-daytag-availability-'.$availImage.'"></div>';
  }
  
  private function fc($string) // First Character
  {
    return strtoupper(substr($string, 0, 1));
  }
  
  private function controlpanel()
  {
    $res = '<i class="fa fa-cog icon-cog wpv-calendar-options-gear" aria-hidden="true">';
    $res .= '</i>';
    $res .= '<div class="wpv-calendar-options">';
    $res .= '<div class="wpv-calendar-controlpanel">';
    
      $res .= '<div class="wpv-calendar-option wpv-calendar-show-festivities">';
      $res .= '<input type="checkbox" class="wpv-calendar-show-festivities-check">'.__('Show festivities', 'wpvacancy').'</input>';

      $res .= '<div class="wpv-calendar-legend">';
        $res .= '<div class="wpv-calendar-legend-entry wpv-calendar-legend-eve">';
          $res .= '<div class="wpv-calendar-legend-icon wpv-calendar-legendicon-nonworkingeve"></div>';
          $res .= '<div class="wpv-calendar-legend-text">'.__('Holiday Eves', 'wpvacancy').'</div>';
        $res .= '</div>';
        $res .= '<div class="wpv-calendar-legend-entry wpv-calendar-legend-holiday">';
          $res .= '<div class="wpv-calendar-legend-icon wpv-calendar-legendicon-nonworking"></div>';
          $res .= '<div class="wpv-calendar-legend-text">'.__('Holidays', 'wpvacancy').'</div>';
        $res .= '</div>';
      $res .= '</div>';

      $res .= '</div>';
    
      $res .= '<div class="wpv-calendar-option wpv-calendar-show-availability">';
      $res .= '<input type="checkbox" class="wpv-calendar-show-availability-check">'.__('Show availability', 'wpvacancy').'</input>';

      $res .= '<div class="wpv-calendar-legend">';
        $res .= '<div class="wpv-calendar-legend-entry wpv-calendar-legend-full">';
          $res .= '<div class="wpv-calendar-legend-icon wpv-calendar-legendicon-availability-full"></div>';
          $res .= '<div class="wpv-calendar-legend-text">'.__('Wide choice', 'wpvacancy').'</div>';
        $res .= '</div>';
        $res .= '<div class="wpv-calendar-legend-entry wpv-calendar-legend-normal">';
          $res .= '<div class="wpv-calendar-legend-icon wpv-calendar-legendicon-availability-normal"></div>';
          $res .= '<div class="wpv-calendar-legend-text">'.__('Some choice', 'wpvacancy').'</div>';
        $res .= '</div>';
        $res .= '<div class="wpv-calendar-legend-entry wpv-calendar-legend-low">';
          $res .= '<div class="wpv-calendar-legend-icon wpv-calendar-legendicon-availability-low"></div>';
          $res .= '<div class="wpv-calendar-legend-text">'.__('Nearly sold out', 'wpvacancy').'</div>';
        $res .= '</div>';
        $res .= '<div class="wpv-calendar-legend-entry wpv-calendar-legend-empty">';
          $res .= '<div class="wpv-calendar-legend-icon wpv-calendar-legendicon-availability-empty"></div>';
          $res .= '<div class="wpv-calendar-legend-text">'.__('Sold out', 'wpvacancy').'</div>';
        $res .= '</div>';
        $res .= '<div class="wpv-calendar-legend-entry wpv-calendar-legend-choice-available">';
          $res .= '<div class="wpv-calendar-legend-icon wpv-calendar-daytag-single-accommodation-legendicon-ok"></div>';
          $res .= '<div class="wpv-calendar-legend-text">'.__('Your choice is available', 'wpvacancy').'</div>';
        $res .= '</div>';
        $res .= '<div class="wpv-calendar-legend-entry wpv-calendar-legend-choice-unavailable">';
          $res .= '<div class="wpv-calendar-legend-icon wpv-calendar-daytag-single-accommodation-legendicon-ko"></div>';
          $res .= '<div class="wpv-calendar-legend-text">'.__('Your choice is NOT available', 'wpvacancy').'</div>';
        $res .= '</div>';
      $res .= '</div>';

      $res .= '</div>';
    
    $res .= '</div>';
    $res .= '</div>';
    Wpvacancy::$instance->registerScriptParamsCallback(array($this, "toggleFestivities"));
    Wpvacancy::$instance->registerScriptParamsCallback(array($this, "toggleAvailability"));
    Wpvacancy::$instance->registerScriptParamsCallback(array($this, "toggleOptions"));
    return $res;
  }
  
  public function bookingData()
  {
    return array('click', 
                  'updateBookingAvailabilityFromCalendarClick', 
                  array('wpv-calendar-day'));
    
  }
  
  public function load()
  {
    return array('load', 
                  'loadCalendar', 
                  array('', // dummy value, used only when loading on button click, while here is onLoad 
                        self::$wrapperclass,
                        self::$defaultoffset, // offset 0 = current month
                        self::$defaultspan,  // span N months (2 -> current and next one)
                        self::$previousMonthButton,
                        self::$nextMonthButton,
                        'wpv-calendar-daytag-daytype',
                        'wpv-calendar-daytag-availability',
                        array('wpv-calendar-clickable-day', 
                              'wpv-calendar-first-selected-day',
                              'wpv-calendar-selected-day',
                              'wpv-calendar-last-selected-day')
                      ));    
  }
  
  public function toggleFestivities()
  {
    return array('click', 
                  'toggleFestivities', 
                  array('wpv-calendar-show-festivities-check',
                        'wpv-calendar-daytag-daytype',
                        'wpv-calendar-options-gear'));
    
  }

  public function toggleAvailability()
  {
    return array('click', 
                  'toggleAvailability', 
                  array('wpv-calendar-show-availability-check',
                        'wpv-calendar-daytag-availability'));
    
  }

  public function toggleOptions()
  {
    return array('click', 
                  'toggleOptions', 
                  array('wpv-calendar-options-gear',
                        'wpv-calendar-options'));
    
  }

  public function daySelection()
  {
    return array('click', 
                  'daySelection', 
                  array('wpv-calendar-clickable-day',
                        'wpv-calendar-first-selected-day',
                        'wpv-calendar-selected-day',
                        'wpv-calendar-last-selected-day'));
    
  }

}

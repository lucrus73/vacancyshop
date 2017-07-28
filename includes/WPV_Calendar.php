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
  private static $defaultspan = 3;
  
  
  
  function __construct()
  {
    Wpvacancy::$instance->registerScriptParamsCallback(array($this, "bookingData"));
    Wpvacancy::$instance->registerScriptParamsCallback(array($this, "load"));
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
    $html = '<div class="wpv-booking-option-title wpv-booking-startdate-title">'.__('When does your holiday start?', 'wpvacancy').'</div>';
    $html .= '<div class="'.self::$wrapperclass.'">';
    $html .= '</div>';
    return $html;
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
    $utnow_day = (int)($ut_now / 86400);
    $html = '';
    $meseiniziale = $m;
    $mesefinale = $m + ($span - 1);
    for ($meseincostruzione = $meseiniziale; $meseincostruzione <= $mesefinale; $meseincostruzione++)
    {
      $adj = "";
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
      for ($k = 1; $k <= $j; $k++)
      {
        $adj .= '<td class="wpv-calendar-day wpv-calendar-day-disabled"> </td>';
      }

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
      $html .= '<th class="wpv-calendar-day">'.$this->fc(__('Saturday', 'wpvacancy')).'</th>';
      $html .= '<th class="wpv-calendar-day">'.$this->fc(__('Sunday', 'wpvacancy')).'</th>';
      $html .= '<th class="wpv-calendar-day">'.$this->fc(__('Monday', 'wpvacancy')).'</th>';
      $html .= '<th class="wpv-calendar-day">'.$this->fc(__('Tuesday', 'wpvacancy')).'</th>';
      $html .= '<th class="wpv-calendar-day">'.$this->fc(__('Wednesday', 'wpvacancy')).'</th>';
      $html .= '<th class="wpv-calendar-day">'.$this->fc(__('Thursday', 'wpvacancy')).'</th>';
      $html .= '<th class="wpv-calendar-day">'.$this->fc(__('Friday', 'wpvacancy')).'</th>';
      $html .= '</tr>';
      $html .= '</thead>';
      $html .= '<tbody>';
      $html .= '<tr>';
      for ($giornodelmese = 1; $giornodelmese <= $nd; $giornodelmese++)
      {
        $ut_dayofmonth = (int)(mktime(0, 0, 0, $meseincostruzione, $giornodelmese, $y) / 86400);
        $timeline_class = "wpv-calendar-day-today";
        if ($ut_dayofmonth < $utnow_day)
          $timeline_class = "wpv-calendar-day-inthepast";
        else
        if ($ut_dayofmonth > $utnow_day)
          $timeline_class = "wpv-calendar-day-inthefuture";
          
        $html .= $adj . '<td valign="top" data-wpvdayid="'.$ut_dayofmonth.'" class="wpv-calendar-day '.$timeline_class.'">' . $giornodelmese . '</td>';
        $adj = '';
        $j++;
        if ($j == 7)
        {
          $html .= '</tr><tr>';
          $j = 0;
        }
      }
      $html .= '</tr>';
      $html .= '</tbody>';
      $html .= '</table>';
      $html .= '</div>';
    }

    return $html; //ob_get_flush();
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
                        get_rest_url(),
                        WPV_BookingForm::$namespace, 
                        self::$wrapperclass,
                        self::$defaultoffset, // offset 0 = current month
                        self::$defaultspan,  // span N months (2 -> current and next one)
                        self::$previousMonthButton,
                        self::$nextMonthButton
                      ));    
  }
  
  private function fc($string) // First Character
  {
    return strtoupper(substr($string, 0, 1));
  }

}

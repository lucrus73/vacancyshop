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
  function __construct()
  {
    Wpvacance::$instance->registerScriptParamsCallback(array($this, "bookingData"));
    
  }

  public function months($atts, $content = '')
  {
    
    if (isset($_GET['prm']))
    {
      $m = strip_tags($_GET['prm']) + strip_tags($_GET['chm']);
    }
    else
    {
      $m = date("m");
    }

    $html = '<div class="wpv-booking-option-title wpv-booking-startdate-title">'.__('When does your holiday start?', 'wpvacance').'</div>';
    $html .= '<div class="wpv-calendar-wrapper">';
    
    $meseiniziale = $m;
    $mesefinale = $m + 1;
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
      $MONTHS = array(1 =>  __('Jan', 'wpvacance'), 
                            __('Feb', 'wpvacance'), 
                            __('Mar', 'wpvacance'), 
                            __('Apr', 'wpvacance'),
                            __('May', 'wpvacance'),
                            __('Jun', 'wpvacance'),
                            __('Jul', 'wpvacance'),
                            __('Aug', 'wpvacance'),
                            __('Sep', 'wpvacance'),
                            __('Oct', 'wpvacance'),
                            __('Nov', 'wpvacance'),
                            __('Dec', 'wpvacance'));
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
        $html .= '<a href="?prm=' . $m . '&chm=-1"><i class="fa fa-arrow-left" aria-hidden="true"></i></a>';
      
      $html .= '</th>';
      $html .= '<th colspan="5">';
      $html .= $MONTHS[$mn] . " " . $yn;
      $html .= '</th>';
      $html .= '<th align="center">';
      
      if ($meseincostruzione == $mesefinale)
        $html .= '<a href="?prm=' . $m . '&chm=1"><i class="fa fa-arrow-right" aria-hidden="true"></i></a>';
      
      $html .= '</th>';
      $html .= '</tr>';
      $html .= '<tr>';
      $html .= '<th class="wpv-calendar-giorno">'.$this->fl(__('Saturday', 'wpvacance')).'</th>';
      $html .= '<th class="wpv-calendar-giorno">'.$this->fl(__('Sunday', 'wpvacance')).'</th>';
      $html .= '<th class="wpv-calendar-giorno">'.$this->fl(__('Monday', 'wpvacance')).'</th>';
      $html .= '<th class="wpv-calendar-giorno">'.$this->fl(__('Tuesday', 'wpvacance')).'</th>';
      $html .= '<th class="wpv-calendar-giorno">'.$this->fl(__('Wednesday', 'wpvacance')).'</th>';
      $html .= '<th class="wpv-calendar-giorno">'.$this->fl(__('Thursday', 'wpvacance')).'</th>';
      $html .= '<th class="wpv-calendar-giorno">'.$this->fl(__('Friday', 'wpvacance')).'</th>';
      $html .= '</tr>';
      $html .= '</thead>';
      $html .= '<tbody>';
      $html .= '<tr>';
      for ($giornodelmese = 1; $giornodelmese <= $nd; $giornodelmese++)
      {
        $html .= $adj . '<td valign="top" class="wpv-calendar-giorno">' . $giornodelmese . '</td>';
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
    $html .= '</div>';

    return $html; //ob_get_flush();
  }
  
  public function bookingData()
  {
    return array('click', 
                  'updateBookingAvailabilityFromCalendarClick', 
                  array('wpv-calendar-giorno'));
    
  }
  
  private function fl($string)
  {
    return strtoupper(substr($string, 0, 1));
  }

}

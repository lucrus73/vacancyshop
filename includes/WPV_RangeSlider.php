<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of WPV_SliderDurata
 *
 * @author lucio
 */
class WPV_RangeSlider
{
  function __construct()
  {
    Wpvacance::$instance->registerScriptParamsCallback(array($this, "load"));    
  }

  public function range($ticks = false, $labels = false, $balloon = false, $snaptoticks = false)
  {
    $res = '<div class="wpv-booking-duration-slider">';
    $res .= '<div id="" class="wpv-booking-duration-slider-custom-handle ui-slider-handle"></div>';
    $res .= '</div>';
    
    return $res;
  }
  
  public function deprecated_range($ticks = false, $labels = false, $balloon = false, $snaptoticks = false)
  {
    $res = '<div class="wpv-booking-rangeslider">';
      $res .= '<div class="wpv-booking-range-wrapper">';
        $res .= '<div class="wpv-booking-range-ruler"><hr/></div>';
        Wpvacance::$instance->registerScriptParamsCallback(array($this, "rulerClick"));    

        if (!empty($ticks))
        {
          $numticks = 10;
          if (is_numeric($ticks))
            $numticks = $ticks;
          else
            if (is_array($ticks))
              $numticks = count($ticks);


          for ($i = 0; $i < $numticks; $i++)
          {
            $leftpercent = (100.0 / ($numticks - 1)) * $i;
            $res .= '<div class="wpv-booking-range-tick-wrapper" style="left:'.$leftpercent.'%;">';

            $res .= '<div class="wpv-booking-range-tick">';
            if (is_array($ticks))
              $res .= $ticks[$i];
            else
              $res .= '<div class="wpv-booking-range-tick-image"></div>';
            $res .= '</div>';

            if (!empty($labels))
            {
              $res .= '<div class="wpv-booking-range-tick-label">';
              $labeltext = $i;
              if (is_array($labels))
                $labeltext .= $labels[$i];
              else
                if (is_string($labels))
                  if ("startfrom1" == strtolower($labels))
                    $labeltext = $i + 1;
              $res .= '<div class="wpv-booking-range-tick-label-text">'.$labeltext.'</div>';

              $res .= '</div>';
            }
            $res .= '</div>'; // wpv-booking-range-tick
          }

        }
        $res .= '<div class="wpv-booking-range-thumb"><i draggable="true" class="wpv-booking-range-thumb-icon fa fa-sun-o"></i></div>'; // thumb
        Wpvacance::$instance->registerScriptParamsCallback(array($this, "thumbDrag"));    
        Wpvacance::$instance->registerScriptParamsCallback(array($this, "thumbDrop"));    
      $res .= '</div>'; // wrapper
    $res .= '</div>'; // range
    return $res;
  }
  
  public function load()
  {
    return array('load', 
                  'showDurationSlider', 
                  array('wpv-booking-duration-slider', 'wpv-booking-duration-slider-custom-handle'));
    
  }

  public function thumbDrag()
  {
    return array('drag', 
                  'updateBookingAvailabilityFromDurationDrag', 
                  array('wpv-booking-range-thumb-icon', 'wpv-booking-range-thumb'));
    
  }

  public function thumbDrop()
  {
    return array('drop', 
                  'updateBookingAvailabilityFromDurationDrop', 
                  array('wpv-booking-range-thumb-icon', 'wpv-booking-range-thumb'));
    
  }
}

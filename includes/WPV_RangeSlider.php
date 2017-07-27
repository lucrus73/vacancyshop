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
    $res = '<div class="wpv-booking-option-title wpv-booking-duration-title">'.__('How long does your holiday last?', 'wpvacance').'</div>';
    $res .= '<div class="wpv-booking-duration-slider">';
    $res .= '<div class="wpv-booking-duration-slider-custom-handle ui-slider-handle" style="z-index:0;"></div>';
    $res .= '</div>';
    
    return $res;
  }
    
  public function load()
  {
    return array('load', 
                  'showDurationSlider', 
                  array('wpv-booking-duration-slider', 
                      'wpv-booking-duration-slider-custom-handle', 
                      'wpv-booking-duration-slider-custom-handle-baloon',
                      __('night', 'wpvacance'),
                      __('nights', 'wpvacance'),
                      14, 1, 60, 1
                      ));
    
  }

}

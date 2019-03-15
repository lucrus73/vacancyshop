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
    Wpvacancy::$instance->registerScriptParamsCallback(array($this, "load"));    
    Wpvacancy::$instance->registerScriptParamsCallback(array($this, "rangeClickPlus"));    
    Wpvacancy::$instance->registerScriptParamsCallback(array($this, "rangeClickMinus"));    
  }

  public function range($ticks = false, $labels = false, $balloon = false, $snaptoticks = false)
  {
    $res = '<div class="wpv-booking-option-title wpv-booking-duration-title">'.__('How long does your holiday last?', 'wpvacancy').'</div>';
    $res .= '<div class="wpv-booking-duration-slider">';
    $res .= '<div class="wpv-booking-duration-slider-custom-handle ui-slider-handle" style="z-index:0;"></div>';
    $res .= '</div>';
    
    $res .= '<div class="wpv-booking-duration-slider-buttons">';
    $res .= '<div class="wpv-booking-duration-button wpv-booking-duration-minus"><i class="fa fa-minus-circle"></i></i></div>';
    $res .= '<div class="wpv-booking-duration-button wpv-booking-duration-plus"><i class="fa fa-plus-circle"></i></div>';
    $res .= '</div>';
    
    
    return $res;
  }
    
  public function load()
  {
    $minDurationDays = get_option(Wpvacancy_Admin::$allowSingleDaySelection) ? 0 : 1;
    $defaultDurationDays = intval(get_option(Wpvacancy_Admin::$defaultBookingDurationDays));
    return array('load', 
                  'showDurationSlider', 
                  array('wpv-booking-duration-slider', 
                      'wpv-booking-duration-slider-custom-handle', 
                      'wpv-booking-duration-slider-custom-handle-baloon',
                      __('night', 'wpvacancy'),
                      __('nights', 'wpvacancy'),
                      $defaultDurationDays, $minDurationDays, 60, 1
                      ));
    
  }
  
  public function rangeClickPlus()
  {
    return array('click', 
                  'rangeClickPlus', 
                  array('wpv-booking-duration-plus'
                      ));
  }

  public function rangeClickMinus()
  {
    return array('click', 
                  'rangeClickMinus', 
                  array('wpv-booking-duration-minus'
                      ));
  }

}

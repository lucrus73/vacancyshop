<?php

class WPVacancyShortcodes
{

  public static function bookingform($atts, $content = '')
  {
    return Wpvacancy::$instance->bookingform->getHtml($atts, $content);
  }
  
  public static function cart($atts, $content = '')
  {
    return Wpvacancy::$instance->bookingform->getHtml($atts, $content);
  }
  
}

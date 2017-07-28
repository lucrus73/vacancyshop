<?php

class WPVacancyShortcodes
{

  public static function bookingform($atts, $content = '')
  {
    return Wpvacancy::$instance->bookingform->getHtml($atts, $content);
  }
  
}

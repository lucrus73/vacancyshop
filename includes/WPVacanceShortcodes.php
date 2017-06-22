<?php

class WPVacanceShortcodes
{

  public static function bookingform($atts, $content = '')
  {
    return Wpvacance::$instance->bookingform->getHtml();
  }
  
}

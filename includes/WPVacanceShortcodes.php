<?php

class WPVacanceShortcodes
{

  public static function bookingform($atts, $content = '')
  {
    require_once plugin_dir_path(dirname(__FILE__)) . 'includes/WPV_BookingForm.php';

    $form = new WPV_BookingForm();
    return $form->toHtml($atts, $content);
  }

  public static function calendario_tremesi($atts, $content = '')
  {
    require_once plugin_dir_path(dirname(__FILE__)) . 'includes/WPV_Calendario.php';

    $form = new WPV_Calendario();
    return $form->tremesi($atts, $content);
  }
  
}

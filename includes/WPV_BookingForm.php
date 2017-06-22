<?php


class WPV_BookingForm
{
  private $html;
  
  function __construct()
  {
    $this->html = $this->toHtml();
  }

  public function toHtml($atts, $content = '')
  {
    if (is_array($atts))
      extract($atts, EXTR_OVERWRITE);    
    
    require_once plugin_dir_path(dirname(__FILE__)) . 'includes/WPV_Calendar.php';
    $cal = new WPV_Calendar();
    $res = $cal->months($atts, $content);
    
    require_once plugin_dir_path(dirname(__FILE__)) . 'includes/WPV_RangeSlider.php';
    $range = new WPV_RangeSlider();
    $res .= $range->range(31, 'startfrom1');
    
    require_once plugin_dir_path(dirname(__FILE__)) . 'includes/WPV_AccommodationsMap.php';
    $maps = new WPV_AccomodationsMap();
    $res .= $maps->map();
    
    return $res;
  }
  
  public function getHtml()
  {
    return $this->html;
  }
}

<?php


class WPV_BookingForm
{
  private $html;
  private $cal;
  private $range;
  private $maps;
  public static $namespace = 'wpvacance/v1';

  
  function __construct()
  {
    require_once plugin_dir_path(dirname(__FILE__)) . 'includes/WPV_Calendar.php';
    require_once plugin_dir_path(dirname(__FILE__)) . 'includes/WPV_RangeSlider.php';
    require_once plugin_dir_path(dirname(__FILE__)) . 'includes/WPV_AccommodationsMap.php';
    $this->cal = new WPV_Calendar();
    $this->range = new WPV_RangeSlider();
    $this->maps = new WPV_AccommodationsMap();
  }
  
  private function toHtml($atts = null, $content = '')
  {
    if (is_array($atts))
      extract($atts, EXTR_OVERWRITE);    
    
    $res = $this->cal->months($atts, $content);
    
    $res .= $this->range->range(31, 'startfrom1');
    
    $res .= $this->maps->map();
    
    return $res;
  }
  
  public function getHtml($atts = null, $content = '')
  {
    if (empty($this->html))
      $this->html = $this->toHtml($atts, $content);
    
    return $this->html;
  }
  
}

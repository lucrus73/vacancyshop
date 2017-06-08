<?php


class WPV_BookingForm
{
  public function toHtml($atts, $content = '')
  {
    if (is_array($atts))
      extract($atts, EXTR_OVERWRITE);    
        
  }
}

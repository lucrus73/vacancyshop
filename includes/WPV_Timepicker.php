<?php

class WPV_Timepicker
{
  public function clock($stepping = 30, // 30 minutes slots
                        $start = 8, // 8 AM
                        $end = 20) // 8 PM
  {
    $res = '<div class="vs-timepicker-wrapper">';
    $res .= '<div class="vs-timepicker-container">';
    
    $decimalstep = $stepping / 60;
    for ($t = $start; $t <= $end; $t += $decimalstep)
    {
      $minutesinday = intval($t * 60);
      $hours = sprintf("%02d", intval($t));
      $minutes = sprintf("%02d", intval(($t - intval($t)) * 60));
      $res .= '<div class="vs-timepicker-item" data-timeid="'.$minutesinday.'">'.$hours.':'.$minutes.'</div>';
    }
    
    $res .= '</div>';
    $res .= '</div>';
    return $res;
  }
}
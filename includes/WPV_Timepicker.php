<?php

class WPV_Timepicker
{
  
  public static $wrapperclass = "vs-timepicker-wrapper";
  public static $layoutclass = "vs-timepicker-layout";
  public static $containerclass = "vs-timepicker-container";
  public static $scrollclass = "vs-timepicker-scroll";
  public static $previousbuttonclass = "vs-timepicker-previous-button";
  public static $nextbuttonclass = "vs-timepicker-next-button";
  public static $navbuttonclass = "vs-timepicker-nav-button";
  
  public function clock($mapid,
                        $stepping = 30, // 30 minutes slots
                        $start = 8, // 8 AM
                        $end = 20) // 8 PM
  {
    $res = '<div class="'.self::$wrapperclass.'">';
    $res .= '<div class="'.self::$layoutclass.'">';
    $res .= $this->previousTimes($mapid, $stepping, $start);
    $res .= '<div class="'.self::$containerclass.'">';
    $res .= '<div class="'.self::$scrollclass.'">';
    
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
    $res .= $this->nextTimes($mapid, $stepping, $end);
    $res .= '</div>';
    $res .= '</div>';
    return $res;
  }
  
  public function previousTimes($mapid, $step, $start)
  {
    $res = '<div class="'.self::$navbuttonclass.' '.self::$previousbuttonclass.'">';
    $res .= '</div>';
    return $res;
  }
  
  public function nextTimes($mapid, $step, $end)
  {
    $res = '<div class="'.self::$navbuttonclass.' '.self::$nextbuttonclass.'">';
    $res .= '</div>';
    return $res;    
  }
}
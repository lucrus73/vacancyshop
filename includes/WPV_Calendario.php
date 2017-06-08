<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of WPV_Calendario
 *
 * @author lucio
 */
class WPV_Calendario
{
  function __construct()
  {
    Wpvacance::$instance->registerScriptParamsCallback(array($this, "bookingData"));
    
  }

  public function tremesi($atts, $content = '')
  {
    if (isset($_GET['prm']))
    {
      $m = strip_tags($_GET['prm']) + strip_tags($_GET['chm']);
    }
    else
    {
      $m = date("m");
    }

    $html = '<div class="ds-calendario-tremesi">';

    $meseiniziale = $m - 1;
    $mesefinale = $m + 1;
    for ($meseincostruzione = $meseiniziale; $meseincostruzione <= $mesefinale; $meseincostruzione++)
    {
      $adj = "";
      $d = date("d");
      $y = date("Y");
      $nd = date('t', mktime(0, 0, 0, $meseincostruzione, 1, $y));
      $mn = date('n', mktime(0, 0, 0, $meseincostruzione, 1, $y));
      $yn = date('Y', mktime(0, 0, 0, $meseincostruzione, 1, $y));
      $j = date('w', mktime(0, 0, 0, $meseincostruzione, 1, $y)) + 1;
      if ($j == "7")
      {
        $j = "0";
      }
      $MONTHS = array(1 => __('Gennaio', 'wpvacance'), 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre');
      for ($k = 1; $k <= $j; $k++)
      {
        $adj .= '<td class="ds-calendario-giorno"> </td>';
      }

      $html .= '<div class="ds-contenitore-calendario">';
      $html .= '<table cellspacing="0" cellpadding="5" align="center" width="100" border="1">';
      $html .= '<tr>';
      $html .= '<td align="center" bgcolor="#ffff00">';
      $html .= '<a href="?prm=' . $m . '&chm=-1">&lt;</a>';
      $html .= '</td>';
      $html .= '<td colspan="5" align="center" bgcolor="#ffff00">';
      $html .= $MONTHS[$mn] . " " . $yn;
      $html .= '</td>';
      $html .= '<td align="center" bgcolor="#ffff00">';
      $html .= '<a href="?prm=' . $m . '&chm=1">&gt;</a>';
      $html .= '</td>';
      $html .= '</tr>';
      $html .= '<tr>';
      $html .= '<td class="ds-calendario-giorno"><strong>S</strong></td>';
      $html .= '<td class="ds-calendario-giorno"><strong>D</strong></td>';
      $html .= '<td class="ds-calendario-giorno"><strong>L</strong></td>';
      $html .= '<td class="ds-calendario-giorno"><strong>M</strong></td>';
      $html .= '<td class="ds-calendario-giorno"><strong>M</strong></td>';
      $html .= '<td class="ds-calendario-giorno"><strong>G</strong></td>';
      $html .= '<td class="ds-calendario-giorno"><strong>V</strong></td>';
      $html .= '</tr>';
      $html .= '<tr>';
      for ($giornodelmese = 1; $giornodelmese <= $nd; $giornodelmese++)
      {
        $html .= $adj . '<td valign="top" class="ds-calendario-giorno">' . $giornodelmese . '</td>';
        $adj = '';
        $j++;
        if ($j == 7)
        {
          $html .= '</tr><tr>';
          $j = 0;
        }
      }
      $html .= '</tr>';
      $html .= '</table>';
      $html .= '</div>';
    }
    $html .= '</div>';

    return $html; //ob_get_flush();
  }
  
  public function bookingData()
  {
    return array('click', 
                  'updateBookingAvailabilityFromCalendarClick', 
                  array('ds-calendario-giorno'));
    
  }

}

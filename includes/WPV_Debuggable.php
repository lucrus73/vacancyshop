<?php

class WPV_Debuggable
{
  public static $enableDebug;
  
  public static function infolog($output = null, $logfile = null)
  {
    if (empty(self::$enableDebug))
      return;
    
    date_default_timezone_set('Europe/Rome');
    // $btrac = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2)[1]['function'];
    $btrac = "";
    $remoteaddr = 'localhost';
    if (!empty($_SERVER['REMOTE_ADDR']))
      $remoteaddr = $_SERVER['REMOTE_ADDR'];
    $date = $remoteaddr." ".$btrac." ".date('Y/m/d h:i:s a', time());

    if (empty($output))
    {
      $output = "\n".$date." POST: ".var_export($_POST, true);
      $httpuseragent = 'wget/curl/...';
      if (!empty($_SERVER['HTTP_USER_AGENT']))
        $httpuseragent = $_SERVER['HTTP_USER_AGENT'];
      $output = $date." ".$httpuseragent."\n".$date." GET: ".var_export($_GET, true).$output;
    }
    else
    {
      if (is_object($output))
        $output = (array)$output;
      if (is_array($output))
      {
        if (!empty($output['ivp_authkey']))
        {
          $output_copy = $output;
          $output = $output_copy;
          $output['ivp_authkey'] = "*****";
        }
        $output = '['.implode(', ', array_map(
            function ($v, $k) { return sprintf("%s='%s'", $k, $v); },
            $output,
            array_keys($output)
        )).']';      
            
      }
      $output = "\n".$date." > ".$output;
    }
    $logdir = $_SERVER['DOCUMENT_ROOT']."/logs";
    if (file_exists($logdir) === false)
      mkdir($logdir);

    if (empty($logfile))
      $logfile = "info.log";
    
    $outfile = $logdir."/".$logfile;

    $fh = fopen($outfile, "a+");
    if (!empty($fh))
    {
      fwrite($fh, $output."\n");
      fclose($fh);
    }
  }
}

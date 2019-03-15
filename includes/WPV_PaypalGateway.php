<?php

require_once (__DIR__ .'/WPV_Debuggable.php');


class WPV_PaypalGateway extends WPV_Debuggable
{
  public static $payment_meta_prefix = "wpv_paypal_payment_";
  public static $max_pending_time_in_hours = 4;
  public static $ipn_slug = "wpvacancyPaypalIpnSlug";
  
  public function ipn($query)
  {
    global $vb_wpv_custom_fields_prefix;
    if (!empty($_POST['payment_status'])) // se c'è payment status è il primo ipn da verificare
    {
      $idtentativo = $_POST['custom'];
      $tentativo = get_post_meta_by_id($idtentativo);
      if (!empty($tentativo))
      {
        $currency = $tentativo['currency'];
        $paypalBusiness = $tentativo['paypalBusiness'];
        $cart_id = $tentativo['cart_id'];
        $meta_key = $tentativo['meta_key'];
        
        $fp = fopen("php://input", "r");
        $input = stream_get_contents($fp);
        fclose($fp);

        self::infolog($input);

        $data = 'cmd=_notify-validate&'.$input;// go through each of the posted vars and add them to the postback variable

        self::infolog("data: ".$data);

        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => $data
            ),
            'ssl' => array(
                'verify_peer' => false, 
                'verify_peer_name' => false)
        );
        $context = stream_context_create($options);
        $url = 'https://www.paypal.com/cgi-bin/webscr';
        if (!empty($_POST['test_ipn']))
          $url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        $result = file_get_contents($url, false, $context);
        self::infolog($result);
        $safetychecks = empty($_POST['test_ipn']);
        self::infolog('$safetychecks '.$safetychecks);
        $safetychecks = $safetychecks && ('VERIFIED' == $result);
        self::infolog('$safetychecks '.$safetychecks);
        $safetychecks = $safetychecks && ('Completed' == $_POST['payment_status']);
        self::infolog('$safetychecks '.$safetychecks);
        $safetychecks = $safetychecks && ($paypalBusiness == $_POST['receiver_email']);
        self::infolog('$safetychecks '.$safetychecks);
        $safetychecks = $safetychecks && ($currency == $_POST['mc_currency']);
        self::infolog('$safetychecks '.$safetychecks);
        $expiry = vb_wpv_get_cart_expiry($cart_id);
        $now = time(NULL);
        $safetychecks = $safetychecks && ($now <= $expiry);
        self::infolog('$safetychecks '.$safetychecks);
        if ($safetychecks === true)
        {
          self::infolog('tentativo id '.$idtentativo);
          $tentativo['esito'] = $input;
          update_post_meta($booking_id, $meta_key, $tentativo);
          
          $totale = vb_wpv_get_cart_total_amount($cart_id);
          
          $totalepagato = $_POST['mc_gross'] * 100;
          self::infolog('totale '.$totale.' / pagato '.$totalepagato);
          
          if (intval($totalepagato) == intval($totale))
          {
            $ok = true;
            $nowstring = date('Y-m-d H:i:s', $now);
            update_post_meta($cart_id, $vb_wpv_custom_fields_prefix.'cart_checkout_time', $nowstring);
            
            $bookings = vb_wpv_get_cart_items($cart_id);
            foreach ($bookings as $bk)
            {
              self::infolog('$bk='.$bk->ID);
              update_post_meta($bk->ID, $vb_wpv_custom_fields_prefix.'booking_order_time', $nowstring);
            }
            $txn_id = Wpvacancy::getHttpQueryParam("txn_id");
            update_post_meta($bk->ID, $vb_wpv_custom_fields_prefix.'paypal_txn_id', $txn_id);
            update_post_meta($bk->ID, $vb_wpv_custom_fields_prefix.'booking_expiration_time', 99999999999);
            // emailer->schedulaNotificaEmail($ordine, $txn_id);
          }
        }
      }
      
      if ($ok === true)
        http_response_code(200);
      else
        http_response_code(409);
    }
  }
  
  public static function createPaymentAttempt($cart_id, $amount, $currency = 'EUR') 
  {
    $pb = get_option(Wpvacancy_Admin::$paypalBusiness);
    $meta_key = self::$payment_meta_prefix.uniqid();
    $tentativopagamento = ['cart_id' => $cart_id, 'meta_key' => $meta_key, 'timestamp' => date('Y-m-d H:i:s'), "amount" => $amount, "currency" => $currency, 'paypalBusiness' => $pb];
    $paymentid = update_post_meta($cart_id, $meta_key, $tentativopagamento);
    
    $item_name = get_post($cart_id)->post_title;
    
    $totale = trim(sprintf("%7.2f", $amount));
    $formid = str_replace(".", "", uniqid('ppf', true));
    $formcode = '<form id="'.$formid.'" name="_xclick" action="https://www.paypal.com/cgi-bin/webscr" method="post">'.
                '<input type="hidden" name="cmd" value="_xclick">'.
                '<input type="hidden" name="charset" value="utf-8">'.
                '<input type="hidden" name="notify_url" value="'.site_url().'/'.self::$ipn_slug.'">'.
                '<input type="hidden" name="business" value="'.$pb.'">'.
                '<input type="hidden" name="currency_code" value="'.$currency.'">'.
                '<input type="hidden" name="no_shipping" value="1">'.
                '<input type="hidden" name="custom" value="'.$paymentid.'">'.
                '<input type="hidden" name="amount" value="'.$amount.'">'.
                '<input type="hidden" name="item_name" value="'.$item_name.'">'.
                '</form>';
    
    return array('status' => 'ok', 'action' => 'formsubmit', 'formid' => $formid, 'attachformto' => $dom_selector, 'formcode' => $formcode);
    
  }
}

<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CircuitoPagamentoPaypal
 *
 * @author lucio
 */
class WPV_PaypalGateway 
{
  public function ipn()
  {
    if (!empty($_POST['payment_status'])) // se c'è payment status è il primo ipn da verificare
    {
      $fp = fopen("php://input", "r");
      $input = stream_get_contents($fp);
      fclose($fp);
      
      CircuitoPagamentoBroker::infolog($input);
      
      $data = 'cmd=_notify-validate&'.$input;// go through each of the posted vars and add them to the postback variable

      CircuitoPagamentoBroker::infolog("data: ".$data);
      
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
      CircuitoPagamentoBroker::infolog($result);
      $safetychecks = empty($_POST['test_ipn']);
      CircuitoPagamentoBroker::infolog('$safetychecks '.$safetychecks);
      $safetychecks = $safetychecks && ('VERIFIED' == $result);
      CircuitoPagamentoBroker::infolog('$safetychecks '.$safetychecks);
      $safetychecks = $safetychecks && ('Completed' == $_POST['payment_status']);
      CircuitoPagamentoBroker::infolog('$safetychecks '.$safetychecks);
      $safetychecks = $safetychecks && (get_option(Dogmaweb_Admin::$option_paypal_business) == $_POST['receiver_email']);
      CircuitoPagamentoBroker::infolog('$safetychecks '.$safetychecks);
      $safetychecks = $safetychecks && ('EUR' == $_POST['mc_currency']);
      CircuitoPagamentoBroker::infolog('$safetychecks '.$safetychecks);
      if ($safetychecks === true)
      {
        $idtentativo = $_POST['custom'];
        $tentativo = TentativoPagamento::find_by_id($idtentativo);
        if (!empty($tentativo))
        {
          CircuitoPagamentoBroker::infolog('tentativo id '.$tentativo->id);
          $tentativo->esito = $input;
          $tentativo->save();
          
          $ordine = Ordine::find_by_id($tentativo->idordine);
          $totale = $ordine->totaledapagare;
          
          $totalepagato = $_POST['mc_gross'] * 100;
          CircuitoPagamentoBroker::infolog('totale '.$totale.' / pagato '.$totalepagato);
          
          if (intval($totalepagato) == intval($totale))
          {
            $ok = true;
            $ordine->datapagamento = new DateTime();
            $ordine->save();
            $iscrizioni = Iscrizione::find_all_by_idordine($values, $options);
            foreach ($iscrizioni as $i)
            {
              CircuitoPagamentoBroker::infolog('$idiscrizione='.$i->id);
              $i->pagata = 'Y';
              $i->save();
            }
            $txn_id = BookingWizard::getHttpQueryParam("txn_id");
            $tentativo->trackid = $txn_id;
            $tentativo->idpayment = $txn_id;
            $tentativo->save();
            Dogmaweb::$instance->emailer->schedulaNotificaEmail($ordine, $txn_id);
          }
        }
      }
      
      if ($ok === true)
        http_response_code(200);
      else
        http_response_code(409);
    }
  }

  public function pay($dom_selector) 
  {
    $booking = 
    $bookingpost = wp_insert_post($booking);
    
    $tentativopagamento = new TentativoPagamento();
    $tentativopagamento->idordine = $ordine->id;
    $tentativopagamento->datatentativo = date('Y-m-d H:i:s');
    $tentativopagamento->save();

    $tentativopagamento->trackid = $tentativopagamento->id;
    $tentativopagamento->save();

    $totale = trim(sprintf("%7.2f", $ordine->totaledapagare / 100));
    $formid = str_replace(".", "", uniqid('ppf', true));
    $nomeevento = htmlentities(BookingWizard::stringaEvento($evento));
    $formcode = '<form id="'.$formid.'" name="_xclick" action="https://www.paypal.com/cgi-bin/webscr" method="post">'.
                '<input type="hidden" name="cmd" value="_xclick">'.
                '<input type="hidden" name="charset" value="utf-8">'.
                '<input type="hidden" name="notify_url" value="'.Dogmaweb::$instance->circuitoPagamentoReceiptLink.'">'.
                '<input type="hidden" name="business" value="'.get_option(Dogmaweb_Admin::$option_paypal_business).'">'.
                '<input type="hidden" name="currency_code" value="EUR">'.
                '<input type="hidden" name="no_shipping" value="1">'.
                '<input type="hidden" name="custom" value="'.$tentativopagamento->id.'">'.
                '<input type="hidden" name="amount" value="'.$totale.'">'.
                '<input type="hidden" name="item_name" value="'.$nomeevento.'">'.
                '</form>';
    
    return array('status' => 'ok', 'action' => 'formsubmit', 'formid' => $formid, 'attachformto' => $dom_selector, 'formcode' => $formcode);
    
  }
}

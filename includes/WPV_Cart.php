<?php

class WPV_Cart
{
  private static $endpoint = 'getCartMarkup';
  private $html;
  public static $cartwrapperclass = "wpv-cart-wrapper";
  public static $cartbuttonwrapperclass = "wpv-cart-button-wrapper";
  public static $numberofitemsclass = "wpv-cart-nitems";
  public static $cartbookingwrapper = "wpv-cart-item-wrapper";

  public static $cartbookingimage = "wpv-cart-item-image";
  public static $cartbookingdata = "wpv-cart-item-data";
  public static $cartbookingcountdown = "wpv-cart-item-countdown";
  public static $cartbookingprice = "wpv-cart-item-price";
  public static $cartbookingactions = "wpv-cart-item-actions";

  public static $cartbookingwhen = "wpv-cart-item-when";

  public static $cartbookingremove = "wpv-cart-item-remove";

  function __construct()
  {
    add_action( 'rest_api_init', array($this, 'registerRoutes'), 999, 0); 
    Wpvacancy::$instance->registerScriptParamsCallback(array($this, "registerCartButton"));
    Wpvacancy::$instance->registerScriptParamsCallback(array($this, "registerClickToHideButton"));
  }

  public function registerRoutes()
  {    
    register_rest_route(Wpvacancy::$namespace, '/'.self::$endpoint, array(
    'methods'  => WP_REST_Server::READABLE,
    'callback' => array($this, 'get_cart_markup'),
      ) );
  }
  
  private function toHtml($atts = null, $content = '')
  {
    return $this->buttonHtml($atts, $content).$this->cartHtml($atts, $content);
  }
  
  private function buttonHtml($atts = null, $content = '')
  {
    if (is_array($atts))
      extract($atts, EXTR_OVERWRITE);    
    
    $cart = vb_wpv_get_cart(get_current_user_id());

    $res = '<div class="'.self::$cartbuttonwrapperclass.'">';
      $res .= '<div>';
        $res .= '<i class="fa fa-shopping-cart" aria-hidden="true"></i>';        
      $res .= '</div>';
      
      $res .= '<div class="'.self::$numberofitemsclass.'">';
      $cartitems = [];
      if (!empty($cart))
      {
        $cartitems = vb_wpv_get_cart_items($cart);
        $res .= count($cartitems);
      }
      else
        $res .= "0";
      $res .= '</div>';
      
    $res .= '</div>';
    return $res;
  }
    
  private function cartHtml($atts = null, $content = '')
  {
    $wrapper = true;
    
    if (is_array($atts))
      extract($atts, EXTR_OVERWRITE);    

    $res = "";
    if (!empty($wrapper))
      $res = '<div class="'.self::$cartwrapperclass.'">';
    
    $cart = vb_wpv_get_cart(get_current_user_id());
    $cartitems = [];
    if (!empty($cart))
      $cartitems = vb_wpv_get_cart_items($cart);
    
    foreach ($cartitems as $booking)
    {
      $res .= '<div class="'.self::$cartbookingwrapper.'">';
        $res .= $this->itemHtml($booking);
      $res .= '</div>';
    }
    
    if (!empty($wrapper))
      $res .= '</div>';
    
    return $res;
  }
  
  private function itemHtml($booking)
  {
    $res = '<div class="'.self::$cartbookingimage.'">';
      $res .= $this->itemImage($booking);
    $res .= '</div>';
    $res .= '<div class="'.self::$cartbookingdata.'">';
      $res .= $this->itemData($booking);
    $res .= '</div>';
    $res .= '<div class="'.self::$cartbookingcountdown.'">';
      $res .= $this->itemCountdown($booking);
    $res .= '</div>';
    $res .= '<div class="'.self::$cartbookingprice.'">';
      $res .= $this->itemPrice($booking);
    $res .= '</div>';
    $res .= '<div class="'.self::$cartbookingactions.'">';
      $res .= $this->itemActions($booking);
    $res .= '</div>';
    return $res;
  }
  
  private function itemImage($booking)
  {
    $accm_id = vb_wpv_get_booking_accommodation_id($booking);
    $res = WPV_AccommodationsMap::featuredImageInADiv($accm_id, "small", null, self::$cartbookingimage."-featured");
    $res .= '</div>';
    $res .= '</div>';
    return $res;
  }
  
  private function itemData($booking)
  {
    $res = $this->itemName($booking);
    $res .= $this->itemPeriod($booking);
    return $res;
  }
  
  private function itemName($booking)
  {
    $res = '<div class="todo">';
    $accm_id = vb_wpv_get_booking_accommodation_id($booking);
    $res .= vb_wpv_get_accommodation_name($accm_id);
    $res .= '</div>';
    return $res;
  }
  
  private function itemPeriod($booking)
  {
    $res = '<div class="todo">';
    $start = vb_wpv_get_booking_start($booking);
    $res .= $start.' - '.vb_wpv_get_booking_end($booking);
    $res .= '</div>';
    return $res;
  }
  
  private function itemPrice($booking)
  {
    $res = '<div class="todo">';
    $res .= '€ '.WPV_BookingForm::getBookingPrice($booking);
    $res .= '</div>';
    return $res;
  }
  
  private function itemCountdown($booking)
  {
    $exp = vb_wpv_get_booking_expiration($booking);
    $dosprev = vb_wpv_get_booking_dos_prevention($booking);
    $res = '<div class="todo" data-booking-expiration="'.$exp.'" data-booking-dosprevention="'.$dosprev.'">';
    $res .= '00:00:05';
    $res .= '</div>';
    return $res;
  }
  
  private function itemActions($booking)
  {
    $res = '<div class="'.self::$cartbookingremove.'" data-remove="'.$booking->ID.'"><i class="fa fa-times" aria-hidden="true"></i>';
    $res .= '</div>';
    return $res;
  }
  
  public function getHtml($atts = null, $content = '')
  {
    if (empty($this->html))
      $this->html = $this->toHtml($atts, $content);
    
    return $this->html;
  }
    
  public function registerCartButton()
  {
    return array('click', 
                  'showCart', 
                  array(self::$cartbuttonwrapperclass,
                        self::$cartwrapperclass,
                        self::$numberofitemsclass));
    
  }

  public function registerRemoveButton()
  {
    return array('click', 
                  'removeFromCart', 
                  array(self::$cartbookingremove,
                        "remove"));
    
  }

  public function registerClickToHideButton()
  {
    return array('click', 
                  'hideCart', 
                  array(self::$cartwrapperclass));
    
  }

  public function get_cart_markup(WP_REST_Request $request)
  {
    $update = $request->get_param("update");
    if ($update == "true")
      $this->html = false;
    $result = ["status" => 'ok', "wrapperclass" => self::$cartwrapperclass, "markup" => $this->cartHtml(['wrapper' => 0])];
    return $result;
  }


}

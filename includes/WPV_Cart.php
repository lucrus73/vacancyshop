<?php

class WPV_Cart
{
  private $html;
  private $itemscount;
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
  public static $cartbookingiddataname = "cartitemid";

  public static $confirmdeletedialogclass = "vs-cart-item-remove-dialog";
  public static $confirmdeleteitembuttonclass = "vs-cart-item-confirm-remove-button";
  public static $canceldeleteitembuttonclass = "vs-cart-item-cancel-remove-button";

  function __construct()
  {
    add_action( 'rest_api_init', array($this, 'registerRoutes'), 999, 0); 
    Wpvacancy::$instance->registerScriptParamsCallback(array($this, "registerCartButton"));
    Wpvacancy::$instance->registerScriptParamsCallback(array($this, "registerRemoveButton"));
    Wpvacancy::$instance->registerScriptParamsCallback(array($this, "registerConfirmRemoveButton"));
    Wpvacancy::$instance->registerScriptParamsCallback(array($this, "registerCancelRemoveButton"));
    Wpvacancy::$instance->registerScriptParamsCallback(array($this, "registerClickToHideButton"));
  }

  public function registerRoutes()
  {    
    register_rest_route(Wpvacancy::$namespace, '/getCartMarkup', array(
    'methods'  => WP_REST_Server::READABLE,
    'callback' => array($this, 'getCartMarkup'),
      ) );
    register_rest_route(Wpvacancy::$namespace, '/removeFromCart', array(
    'methods'  => WP_REST_Server::READABLE,
    'callback' => array($this, 'removeFromCart'),
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
    
    $this->itemscount = count($cartitems);
    
    $confirmdeletedlgs = '';
    
    foreach ($cartitems as $booking)
    {
      $id = $booking;
      if (!is_integer($id))
      {
        $id = $booking->ID;
      }
      $res .= '<div class="'.self::$cartbookingwrapper.'" data-'.self::$cartbookingiddataname.'="'.$id.'">';
        $res .= $this->itemHtml($booking);
      $res .= '</div>';
      
      $confirmdeletedlgs .= $this->confirmDeleteItemDialog($booking);
    }
    
    if (empty($confirmdeletedlgs))
      $res .= '<div class="vs-emptycart">'.__('Your cart is empty!', 'wpvacancy').'</div>';
    else
      $res .= $confirmdeletedlgs;
    
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
    $res .= $this->bookingName($booking);
    $res .= '</div>';
    return $res;
  }
  
  private function bookingName($booking)
  {
    $accm_id = vb_wpv_get_booking_accommodation_id($booking);
    return vb_wpv_get_accommodation_name($accm_id);
  }
  
  private function itemPeriod($booking)
  {
    $res = '<div class="vs-cartitem-period">';
    $start = vb_wpv_get_booking_start($booking);
    $res .= $start.' - '.vb_wpv_get_booking_end($booking);
    $res .= '</div>';
    return $res;
  }
  
  private function itemPrice($booking)
  {
    $res = '<div class="vs-cartitem-price">';
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
    $res = '<div class="'.self::$cartbookingremove.'" data-'.self::$cartbookingiddataname.'="'.$booking->ID.'">';
      $res .= '<i class="fa fa-trash" aria-hidden="true"></i>';
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
                        self::$cartbookingiddataname,
                        self::$confirmdeletedialogclass));
    
  }

  public function registerConfirmRemoveButton()
  {
    return array('click', 
                  'confirmRemoveFromCart', 
                  array(self::$confirmdeleteitembuttonclass,
                        self::$confirmdeletedialogclass,
                        self::$cartwrapperclass,
                        self::$numberofitemsclass,
                        self::$cartbookingiddataname));
    
  }
  
  public function registerCancelRemoveButton()
  {
    return array('click', 
                  'cancelRemoveFromCart', 
                  array(self::$canceldeleteitembuttonclass,
                        self::$confirmdeletedialogclass,
                        self::$cartbookingiddataname));
    
  }
  
  public function registerClickToHideButton()
  {
    return array('click', 
                  'hideCart', 
                  array(self::$cartwrapperclass));
    
  }

  public function getCartMarkup(WP_REST_Request $request)
  {
    $update = $request->get_param("update");
    if ($update == "true")
      $this->html = false;
    $markup = $this->cartHtml(['wrapper' => 0]);
    $result = ["status" => 'ok', 
               "wrapperclass" => self::$cartwrapperclass, 
               "markup" => $markup, 
               "itemscount" => $this->itemscount,
               "events" => $this->getJsHandlerDescriptiorsForRemoveFromCart()];
    return $result;
  }

  public function removeFromCart(WP_REST_Request $request)
  {
    $cartitemid = $request->get_param("cartitemid");
    wp_delete_post($cartitemid);
    $this->html = false;
    $result = ["status" => 'removed', "nitems" => count(vb_wpv_get_cart_items(vb_wpv_get_cart()))];
    return $result;
  }

  private function confirmDeleteItemDialog($booking)
  {
    $res = '<div class="'.self::$confirmdeletedialogclass.'" data-cartitemid="'.$booking->ID.'">';
      $res .= '<div class="'.self::$confirmdeletedialogclass.'-msg">';
        $res .= sprintf(__('Really cancel %s booking?', 'wpvacancy'), $this->bookingName($booking));
      $res .= '</div>';
      $res .= '<div class="'.self::$confirmdeletedialogclass.'-buttons">';
        $res .= '<div class="'.self::$confirmdeleteitembuttonclass.'" data-cartitemid="'.$booking->ID.'">';
          $res .= __('Yes', 'wpvacancy');
        $res .= '</div>';
        $res .= '<div class="'.self::$canceldeleteitembuttonclass.'" data-cartitemid="'.$booking->ID.'">';
          $res .= __('No', 'wpvacancy');
        $res .= '</div>';
      $res .= '</div>';
    $res .= '</div>';
    return $res;
  }

  private function getJsHandlerDescriptiorsForRemoveFromCart()
  {
    return array($this->eventHandlerDescriptor($this->registerRemoveButton()),
                 $this->eventHandlerDescriptor($this->registerConfirmRemoveButton()),
                 $this->eventHandlerDescriptor($this->registerCancelRemoveButton()));
  }
  
  private function eventHandlerDescriptor(array $a)
  {
    array_unshift($a, 'public'); 
    return $a;
  }
}

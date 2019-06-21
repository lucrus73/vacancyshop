<?php

class WPV_AccommodationsMap
{
  private $lightbox_template_postid = "lightbox-show-accommodation";
  private $postid;
  private static $lightboxPermalinkEndpoint = 'getLightboxPermalink';
  private static $accommodationImagesEndpoint = 'getAccommodationImages';
  private static $accommodationBoxClass = 'wpv-accommodation-box';
  private static $accommodationMapClass = 'wpv-booking-accommodations-map';
  private static $accommodationCarouselClass = 'wpv-accommodation-carousel';
  private static $carouselLightboxClass = 'wpv-booking-accomodations-maps-lightbox';
  public static $accommodation_ok_class = 'wpv-accommodation-tag-ok';
  public static $accommodation_ko_class = 'wpv-accommodation-tag-ko';
  public static $accommodation_class = 'wpv-booking-accommodation-unit';
  public static $selected_accommodation_class = 'wpv-booking-accommodation-selected-unit';
  
  function __construct()
  {
    add_action( 'rest_api_init', array($this, 'registerRoutes'), 999, 0); 
    $this->registerCallbacks(); // ensures the registerScriptParamsCallback calls are executed early
  }
  
  public function lightboxTags()
  {
    global $vb_wpv_baseurl;
    $res = '<div class="'.self::$carouselLightboxClass.'" style="z-index:999999;">';
      $res .= '<div class="'.self::$carouselLightboxClass.'-frame"></div>';
      $res .= '<div class="'.self::$carouselLightboxClass.'-close" style="background-image:url('. Wpvacancy::skinfileUrl('images/button-close.png').')"></div>';
    $res .= '</div>';
    echo $res;
  }

  private function mapPost($map)
  {
    if (!is_object($map))
    {
      if (is_numeric($map))
        $m = get_post($map);
      else
        if (is_string($map))
        {
          $args = array('name'        => $map,
                        'post_type'   => 'accm_map_type',
                        'post_status' => 'publish',
                        'numberposts' => 1
          );
          $posts = get_posts($args);
          if( !empty($posts))
            $m = $my_posts[0]->ID;
        }
    }
    else 
      $m = $map;
    return $m;
  }
  
  private function getAllMaps()
  {
    $args = array('post_type'   => 'accm_map_type',
                  'post_status' => 'publish',
                  'numberposts' => 5
    );
    return get_posts($args);
  }
  
  public function map(array $maps = null)
  {    
    if (empty($maps))
      $maps = $this->getAllMaps();
    
    if (!is_array($maps))
      $maps = array($maps);
    
    $res = '<div class="wpv-booking-option-title wpv-booking-accommodation-title">'.__('Which accommodation do you want?', 'wpvacancy').'</div>';
    $res .= '<div class="wpv-booking-accomodations-maps">';
    foreach ($maps as $map)
    {
      $m = $this->mapPost($map);
      if (empty($m))
        continue;
      $mapOpenDiv = self::featuredImageInADiv($m);
      if (empty($mapOpenDiv))
        continue;
      
      $res .= $mapOpenDiv;
      $res .= $this->accommodationUnits($m->ID);
      $res .= '</div>'; // see comment in featuredImageInADiv
      
      $res .= $this->carousel($m);
      
      $res .= '</div>'; // see comment in featuredImageInADiv
    }
    $res .= '</div>';
    $res .= $this->lightboxTags();
    return $res;
  }
  
  private function carousel($post)
  {
    $res = '<div class="'.self::$accommodationCarouselClass.'-side-wrapper '.self::$accommodationCarouselClass.'-side-wrapper-'.$post->post_name.'">';
      $res .= '<div class="'.self::$accommodationCarouselClass.' '.self::$accommodationCarouselClass.'-'.$post->post_name.'">';
        $res .= $this->getPostMedia($post->ID)['markup'];
      $res .= '</div>';
      $res .= '<div class="'.self::$accommodationCarouselClass.'-button '.self::$accommodationCarouselClass.'-button-prev"></div>'; 
      $res .= '<div class="'.self::$accommodationCarouselClass.'-button '.self::$accommodationCarouselClass.'-button-next"></div>'; 
    $res .= '</div>';
    return $res;
  }
  
  private function registerCallbacks()
  {   
    Wpvacancy::$instance->registerScriptParamsCallback(array($this, "chooseAccommodation"));    
    Wpvacancy::$instance->registerScriptParamsCallback(array($this, "clearAccommodation"));    
    Wpvacancy::$instance->registerScriptParamsCallback(array($this, "loadCarousel"));    
    Wpvacancy::$instance->registerScriptParamsCallback(array($this, "updateCarousel"));    
    Wpvacancy::$instance->registerScriptParamsCallback(array($this, "previousImageInCarousel"));    
    Wpvacancy::$instance->registerScriptParamsCallback(array($this, "nextImageInCarousel"));    
    Wpvacancy::$instance->registerScriptParamsCallback(array($this, "showCarouselPictureInLightbox"));    

  }
  
  private function getPostMedia($postid)
  {
    $divImageUrl = function ($url)
    {
      $postid = attachment_url_to_postid($url);
      $thumburl = wp_get_attachment_image_src($postid, 'thumbnail', true);
      return '<div class="'.self::$accommodationCarouselClass.'-image" style="background-image: url('.$thumburl[0].');" data-fullsize="'.$url.'"></div>';
    };
    
    $result['markup'] = '';
    $gallery = get_post_gallery_images($postid);

    foreach( $gallery as $image_url ) 
    {
      $result['markup'] .= $divImageUrl($image_url);
    }

    $attachments = get_attached_media( 'image', $postid);
    foreach($attachments as $id => $imagepost)
    {
      $full_img_url = wp_get_attachment_url($imagepost->ID);
      $result['markup'] .= $divImageUrl($full_img_url);
    }    
   
    return $result;
  }
  
  public function getAccommodationImages(WP_REST_Request $request)
  {
    $postid = $request->get_param("postid");
    $result = $this->getPostMedia($postid);
    $result['events'] = $this->getJsHandlerDescriptiorForCarouselLightbox();
    return $result;
  }
  
  private function getJsHandlerDescriptiorForCarouselLightbox()
  {
    return array(array('public', 'click', 
                      'showCarouselPictureInLightbox', 
                      array(self::$accommodationCarouselClass."-image",
                            self::$carouselLightboxClass,
                            self::$carouselLightboxClass."-frame",
                            "fullsize",
                            self::$carouselLightboxClass."-close")));         
  }
    
  private function accommodationUnits($map)
  {
    global $vb_wpv_custom_fields_prefix, $vb_wpv_baseurl;
    
    $args = array('post_type'   => 'accommodation_type',
                  'post_status' => 'publish',
                  'numberposts' => 5000
    );
    $units = get_posts($args);
    $result = '';
    foreach ($units as $u)
    {
      // each unit can belong to different maps
      // this meta lists all the maps this unit belogs to in an array
      $maps = get_post_meta($u->ID, $vb_wpv_custom_fields_prefix.'acc_map_id', true);
      if (!in_array($map, $maps)) // if this unit does not belong to the requested map
        continue;
      
      $left = get_post_meta($u->ID, $vb_wpv_custom_fields_prefix."acc_unit_box_x", true);
      $top = get_post_meta($u->ID, $vb_wpv_custom_fields_prefix."acc_unit_box_y", true);
      $width = get_post_meta($u->ID, $vb_wpv_custom_fields_prefix."acc_unit_box_w", true);
      $height = get_post_meta($u->ID, $vb_wpv_custom_fields_prefix."acc_unit_box_h", true);
      $viewmorebuttonclass = 'wpv-accommodation-hint-image-button-viewmore-'.$u->ID;
      $choosethisbuttonclass = 'wpv-accommodation-hint-image-button-choosethis-'.$u->ID;
      $extra_class = get_post_meta($u->ID, $vb_wpv_custom_fields_prefix.'css_class', true);
      if (empty($extra_class))
        $extra_class = '';
      
      $result .= '<div id="'.self::$accommodationBoxClass.'-id-'.$u->ID.'" class="'.self::$accommodationBoxClass.
                    ' '.self::$accommodation_class;
      $ucatnames = "";
      if (taxonomy_exists('accommodation_cat')) // it happens it does not exist when called from the constructor, since the taxonomy is not registered yet
      {
        $ucats = wp_get_object_terms($u->ID, 'accommodation_cat', array('fields' => 'id=>slug'));
        foreach ($ucats as $unitcategoryslug)
        {
          $result .= ' '.self::$accommodation_class.'-cat-'.$unitcategoryslug;
          $fullcat = get_term_by('slug', $unitcategoryslug, 'accommodation_cat');
          if (!empty($ucatnames))
            $ucatnames .= ', ';
          $ucatnames .= $fullcat->name;
        }
      }
      /**
       * Skin creators can use the generated class name to set whatever CSS property
       * they want for the specific accommodation icon:
       */
      $result .= ' '.self::$accommodation_class.'-name-'.$u->post_name.
                    ' '.$extra_class.
                    '" style="left:'.$left.
                    '%; top:'.$top.
                    '%; width:'.$width.
                    '%; height:'.$height.
                    '%;';
      /**
       * ...but, easier, they can also place a images/accm-unit-{slug}.png file 
       * into their skin images/ folder and it will be used as icon for the 
       * specific accommodation.
       */
      $unit_icon = Wpvacancy::skinfileUrl("images/accm-unit-".$u->post_name.".png");
      if ($unit_icon !== false)
        $result .= " background-image: url(".$unit_icon.");";
      
      $result .= '" data-accunitid="'.$u->ID.'" ';
      $result .= ' data-accunitname="'.$u->post_title.'" ';
      $result .= ' data-accunitcat="'.$ucatnames.'"';
      $result .= ' data-bookable="'.implode(",", WPV_BookingForm::getBookableDays($u->ID)).'" ';
      $result .= '>';
      $result .= '<div class="wpv-accommodation-tag '.self::$accommodation_ok_class.'"></div>';
      $result .= '<div class="wpv-accommodation-tag '.self::$accommodation_ko_class.'"></div>';
      $result .= '</div>';
    }
    return $result;
  }
  
  public function updateCarousel(array $params)
  {
    return array('click', 
                  'updateCarousel', 
                  array(self::$accommodationBoxClass, self::$accommodationCarouselClass));
    
  }
  
  public function loadCarousel(array $params)
  {
    return array('load', 
                  'loadCarousel', 
                  array(WPV_BookingForm::$bookingformcontainerclass,
                      WPV_BookingForm::$bookingformmapiddatatag, 
                      self::$accommodationCarouselClass));
    
  }
  
  public function chooseAccommodation(array $params)
  {
    return array('click', 
                  'chooseAccommodation', 
                  array(self::$accommodationBoxClass, self::$selected_accommodation_class));
    
  }
  
  public function clearAccommodation(array $params)
  {
    return array('click', 
                  'clearAccommodation', 
                  array(self::$accommodationMapClass."-image", 
                      self::$accommodationBoxClass,
                      self::$selected_accommodation_class,
                      WPV_BookingForm::$bookingformcontainerclass,
                      WPV_BookingForm::$bookingformmapiddatatag, 
                      self::$accommodationCarouselClass));
    
  }
  
  public function previousImageInCarousel(array $params)
  {
    return array('click', 
                  'previousImageInCarousel', 
                  array(self::$accommodationCarouselClass.'-button-prev', 
                        self::$accommodationCarouselClass,
                        self::$accommodationCarouselClass."-image",
                        $this->getJsHandlerDescriptiorForCarouselLightbox()));
    
  }

  public function nextImageInCarousel(array $params)
  {
    return array('click', 
                  'nextImageInCarousel', 
                  array(self::$accommodationCarouselClass.'-button-next', 
                      self::$accommodationCarouselClass,
                      self::$accommodationCarouselClass."-image",
                      $this->getJsHandlerDescriptiorForCarouselLightbox()));
    
    
  }
   
  public static function getImageInADivParams($imgfile_or_url)
  {
    $size = getimagesize($imgfile_or_url);

    if (empty($size))
      $size = array(100, 100);
    
    $wpx = $size[0];
    $hpx = $size[1];

    $ratio = $hpx / $wpx;

    $w = 100;
    $h = $ratio * 100;
   
    return array("max-width" => $wpx."px", "width" => $w."%", "padding-bottom" => $h."%");
  }
  
  /**
   * This function returns a string containing a HTML fragment. It opens one or two <div> tags
   * and it DOES NOT close them, by design.
   * The caller is supposed to add whatever inside the <div> tag(s) and then close it (both).
   * The $wrapper param controls if it opens only one <div> (false) or two (true, default, suitable for flex items)
   * 
   * @global type $vb_wpv_baseurl
   * @param type $post
   * @param type $tsize
   * @param string $default_img_url
   * @param type $class_prefix
   * @param $wrapper if true (default) it adds a wrapper <div>
   * @return string
   */  
  public static function featuredImageInADiv($post, $tsize = "large", $default_img_url = null, $class_prefix = null, $wrapper = true)
  {
    if (!is_object($post))
      $post = get_post($post);
    
    $thumb = get_post_thumbnail_id($post->ID);
    $bgimage_file = get_attached_file($thumb);

    if (!empty($bgimage_file))
    {
      $bgimage_url = wp_get_attachment_image_url($thumb, $tsize);
    }
    else
    {  
      $gallery = get_post_gallery_images($post->ID);
      if (count($gallery) > 0)
        $bgimage_url = $gallery[0];
      
      if (empty($bgimage_url))
      {
        if (empty($default_img_url))
        {
          $default_img_url = Wpvacancy::skinfileUrl('images/no-image.png');        
        }
        $bgimage_url = $default_img_url;  
      }
    }

    $iparams = self::getImageInADivParams($bgimage_url);
    
    if ($class_prefix === null)
      $class_prefix = self::$accommodationMapClass;
    
    $imgclass = $class_prefix."-image";
    
    $res = '';
    if ($wrapper === true)
      $res = '<div class="'.$class_prefix.'-wrapper" style="max-width: '.$iparams['max-width'].'; min-width: 0;" >';

    $res .= '<div class="'.$class_prefix.'-container '.
            $class_prefix.'-container-'.$post->post_name.'" style="position:relative;max-width:'.$iparams['max-width'].
            ';min-width:0;width:'.$iparams["width"].';">';
      $res .= '<img class="'.$imgclass.'" src="'.$bgimage_url.'" style="width: 100%; display:block;">';
    return $res;
  }
  
  public function registerRoutes()
  {    
    register_rest_route(Wpvacancy::$namespace, '/'.self::$accommodationImagesEndpoint, array(
    'methods'  => WP_REST_Server::READABLE,
    'callback' => array($this, 'getAccommodationImages'),
      ) );
  }
  
  public function enqueueScripts()
  {
  }
  
  public static function wantsTimepicker($mapid)
  {
    $tp = get_post_meta($mapid, VS_AccommodationMapMetaKeys::$singleDaySelection, true);
    return strcmp("on", $tp) === 0;
  }
  
  
}

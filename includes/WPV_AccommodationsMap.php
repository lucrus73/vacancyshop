<?php

class WPV_AccommodationsMap
{
  private $lightbox_template_postid = "lightbox-show-accommodation";
  private $postid;
  private static $endpoint = 'getLightboxPermalink';
  private static $accommodationBoxClass = 'wpv-accommodation-box';
  private static $accommodationHintClass = 'wpv-accommodation-hint';
  public static $accommodation_ok_class = 'wpv-accommodation-tag-ok';
  public static $accommodation_ko_class = 'wpv-accommodation-tag-ko';
  public static $accommodation_class = 'wpv-booking-accommodation-unit';
  
  public static $defaultScaleFactor = 5;

  function __construct()
  {
    $this->postid = filter_input(INPUT_GET, $this->lightbox_template_postid, FILTER_SANITIZE_NUMBER_INT);
    add_action( 'rest_api_init', array($this, 'registerRoutes'), 999, 0); 
    add_filter( 'single_template', array($this, 'lightbox_template'));
    remove_action( 'wp_footer', array($this, 'lightboxTags'), PHP_INT_MAX); // avoid adding more than once
    add_action( 'wp_footer', array($this, 'lightboxTags'), PHP_INT_MAX);
    $this->registerCallbacks(); // ensures the registerScriptParamsCallback calls are executed early
  }
  
  public function lightbox_template($single_template)
  {
    global $post;
    if ($post->post_type == 'accommodation_type' && $this->postid > 0)
    {
      $single_template = dirname( __FILE__ ) . '/show-post-in-lightbox.php';
      Wpvacancy::$instance->plugin_public->addStyle('wpvacancy-single-post-lightbox.css');
    }
    return $single_template;
  }    
  
  public function lightboxTags()
  {
    global $vb_wpv_baseurl;
    $res = '<div class="wpv-booking-accomodations-maps-lightbox" style="z-index:999999;">';
      $res .= '<iframe id="wpv-booking-accomodations-maps-lightbox-frame" src="#"></iframe>';
      $res .= '<div class="wpv-booking-accomodations-maps-lightbox-close" style="background-image:url('. Wpvacancy::skinfileUrl('images/button-close.png').')"></div>';
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
      $res .= '</div></div>'; // see comment in featuredImageInADiv
    }
    $res .= '</div>';
    return $res;
  }
  
  private function registerCallbacks()
  {
    $args = array('post_type'   => 'accommodation_type',
                  'post_status' => 'publish',
                  'numberposts' => 5000);
    
    Wpvacancy::$instance->registerScriptParamsCallback(array($this, "selectionDialog"));    

    // $units = get_posts($args);
    // TODO: can't call get_posts before init or so as of WP5. We need to make this 
    // binding later.
    $units = []; // for the time being
    foreach ($units as $u)
    {
      $viewmorebuttonclass = 'wpv-accommodation-hint-image-button-viewmore-'.$u->ID;
      $choosethisbuttonclass = 'wpv-accommodation-hint-image-button-choosethis-'.$u->ID;

      Wpvacancy::$instance->registerScriptParamsCallback(array($this, "viewMoreOfAccommodationItem"), array($u->ID, $viewmorebuttonclass)); 
      Wpvacancy::$instance->registerScriptParamsCallback(array($this, "chooseThisAccommodationItem"), array($u->ID, $choosethisbuttonclass, $elementclass)); 
    }
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
    $scalefactor = get_post_meta($map, $vb_wpv_custom_fields_prefix.'accm_map_previewscalefactor', true);
    if (empty($scalefactor))
      $scalefactor = self::$defaultScaleFactor;
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
      if (taxonomy_exists('accommodation_cat')) // it happens it does not when called from the constructor, since the taxonomy is not registered yet
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
      $result .= ' data-accunitname="'.apply_filters('the_content', $u->post_title).'" ';
      $result .= ' data-accunitcat="'.$ucatnames.'"';
      $result .= ' data-bookable="'.implode(",", WPV_BookingForm::getBookableDays($u->ID)).'" ';
      $result .= '>';
      $result .= '<div class="wpv-accommodation-tag '.self::$accommodation_ok_class.'"></div>';
      $result .= '<div class="wpv-accommodation-tag '.self::$accommodation_ko_class.'"></div>';
        $result .= '<div class="'.self::$accommodationHintClass.'" id="'.self::$accommodationHintClass.'-id-'.$u->ID.'" style="position: absolute;">';
          $result .= self::featuredImageInADiv($u, "medium", null, "wpv-accommodation-hint-image", $scalefactor, false);
            $result .= '<div class="wpv-accommodation-hint-image-buttons">';
              $result .= '<div class="wpv-accommodation-hint-image-button wpv-accommodation-hint-image-button-viewmore '.$viewmorebuttonclass.'">';
                $result .= '<span>';
                $result .= __("View more", 'wpvacancy');
                $result .= '</span>';
              $result .= '</div>';
              $result .= '<div class="wpv-accommodation-hint-image-button wpv-accommodation-hint-image-button-choosethis '.$choosethisbuttonclass.'" data-accunitid="'.$u->ID.'">';
                $result .= '<span>';
                $result .= __('Choose this', 'wpvacancy');
                $result .= '</span>';
              $result .= '</div>';
            $result .= '</div>'; // see comment in featuredImageInADiv

            // I add 4 transparent divs around the hint and above all the rest to grab any clicks around.
            // This way the hint is like a modal or a lightbox but with static positioning (e.g. relative to the clicked icon)
            $result .= '<div class="wpv-accommodation-hint-inputgrab wpv-accommodation-hint-inputgrab-right"></div>';
            $result .= '<div class="wpv-accommodation-hint-inputgrab wpv-accommodation-hint-inputgrab-left"></div>';
            $result .= '<div class="wpv-accommodation-hint-inputgrab wpv-accommodation-hint-inputgrab-top"></div>';
            $result .= '<div class="wpv-accommodation-hint-inputgrab wpv-accommodation-hint-inputgrab-bottom"></div>';
                        
          $result .= '</div>';      

        $result .= '</div>';
      $result .= '</div>';
    }
    return $result;
  }
  
  public function selectionDialog(array $params)
  {
    return array('click', 
                  'showSelectionDialog', 
                  array(self::$accommodationBoxClass, self::$accommodationHintClass));
    
  }
  
  public function viewMoreOfAccommodationItem(array $params)
  {  
    $postid = $params[0];
    $postlink = get_permalink($postid, false);
    strpos($postlink,'?') !== false ? $postlink .= '&' : $postlink .= '?';
    $postlink .= $this->lightbox_template_postid.'='.$postid; 
    $target = $params[1];
    return array('click', 
                 'viewMoreOfAccommodationItem', 
                  array($target, 
                        'wpv-booking-accomodations-maps-lightbox', 
                        'fast', 
                        'wpv-booking-accomodations-maps-lightbox-frame',
                        $postlink,
                        'wpv-booking-accomodations-maps-lightbox-close'
                        ));    
  }

  public function chooseThisAccommodationItem(array $params)
  {  
    $postid = $params[0];
    $target = $params[1];
    $highlight = $params[2];
    return array('click', 
                 'chooseThisAccommodationItem', 
                  array($target,
                        $postid,
                        'wpv-booking-accommodation-selected-unit',
                        $highlight
                        ));    
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
   * @param type $scale
   * @param $wrapper if true (default) it adds a wrapper <div>
   * @return string
   */  
  public static function featuredImageInADiv($post, $tsize = "large", $default_img_url = null, $class_prefix = "wpv-booking-accommodations-map", $scale = 0.8, $wrapper = true)
  {
    global $vb_wpv_baseurl;
    if (!is_object($post))
      $post = get_post($post);
    $thumb = get_post_thumbnail_id($post->ID);
    $bgimage_file = get_attached_file($thumb);

    if (!empty($bgimage_file))
    {
      $bgimage_url = wp_get_attachment_image_url($thumb, $tsize);
      $size = getimagesize($bgimage_file);

    }
    else
    {      
      if (empty($default_img_url))
      {
        $default_img_url = Wpvacancy::skinfileUrl('images/no-image.png');        
      }
      $bgimage_url = $default_img_url;  
      $size = getimagesize($default_img_url);
    }

    if (empty($size))
      $size = array(100, 100);
    
    $wpx = $size[0];
    $hpx = $size[1];

    $ratio = $hpx / $wpx;
    $widthpercent = $scale;
    $heightpercent = $widthpercent * $ratio;

    $w = $widthpercent * 100;
    $h = $heightpercent * 100;

    $res = '';
    if ($wrapper === true)
      $res = '<div class="'.$class_prefix.'-wrapper" style="max-width: '.$wpx.'px; min-width: 0;" >';
    $res .= '<div class="'.$class_prefix.' '.$class_prefix.'-'.$post->post_name.
              '" style="background-image: url('.$bgimage_url.'); max-width: '.$wpx.'px; min-width: 0; width: '.$w.'%; padding-bottom: '.$h.'%; margin-left: auto; margin-right: auto;">';
    return $res;
  }
  
  public function registerRoutes()
  {    
    register_rest_route(Wpvacancy::$namespace, '/'.self::$endpoint, array(
    'methods'  => WP_REST_Server::READABLE,
    'callback' => array($this, 'get_lightbox_permalink'),
      ) );
  }
  
  public function get_lightbox_permalink(WP_REST_Request $request)
  {
    $postid = $request->get_param("postid");
    $result = ["url" => '404'];

    if (!empty($postid))
    {
      $postlink = get_permalink($postid, false);
      strpos($postlink,'?') !== false ? $postlink .= '&' : $postlink .= '?';
      $postlink .= $this->lightbox_template_postid.'='.$postid; 
      $result["url"] = $postlink;
    }
    return $result;
  }
  
  
}

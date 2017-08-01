<?php

class WPV_AccommodationsMap
{
  private $lightbox_template_postid = "lightbox-show-accommodation";
  private $postid;
  private static $endpoint = 'getLightboxPermalink';
  
  public static $defaultScaleFactor = 5;

  function __construct()
  {
    $this->postid = filter_input(INPUT_GET, $this->lightbox_template_postid, FILTER_SANITIZE_NUMBER_INT);
    add_action( 'rest_api_init', array($this, 'registerRoutes'), 999, 0); 
    add_filter( 'single_template', array($this, 'lightbox_template'));
    remove_action( 'wp_footer', array($this, 'lightboxTags'), PHP_INT_MAX); // avoid adding more than once
    add_action( 'wp_footer', array($this, 'lightboxTags'), PHP_INT_MAX);
    $this->map(); // ensures the registerScriptParamsCallback calls are executed early
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
      $res .= '<div class="wpv-booking-accomodations-maps-lightbox-close" style="background-image:url('.$vb_wpv_baseurl. 'images/button-close.png)"></div>';
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
      $mapOpenDiv = $this->featuredImageInADiv($m);
      if (empty($mapOpenDiv))
        continue;
      
      $res .= $mapOpenDiv;
      $res .= $this->accommodationUnits($m->ID);
      $res .= '</div>';  
    }
    $res .= '</div>';
    return $res;
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
      if (!in_array($map, $maps)) // it this unit does not belong to the requested map
        continue;
      
      $left = get_post_meta($u->ID, $vb_wpv_custom_fields_prefix."acc_unit_box_x", true);
      $top = get_post_meta($u->ID, $vb_wpv_custom_fields_prefix."acc_unit_box_y", true);
      $width = get_post_meta($u->ID, $vb_wpv_custom_fields_prefix."acc_unit_box_w", true);
      $height = get_post_meta($u->ID, $vb_wpv_custom_fields_prefix."acc_unit_box_h", true);
      $elementclass = 'wpv-accommodation-box-id-'.$u->ID;
      $viewmorebuttonclass = 'wpv-accommodation-hint-image-button-viewmore-'.$u->ID;
      $choosethisbuttonclass = 'wpv-accommodation-hint-image-button-choosethis-'.$u->ID;
      $extra_class = get_post_meta($u->ID, $vb_wpv_custom_fields_prefix.'css_class', true);
      if (empty($extra_class))
        $extra_class = '';
      
      $result .= '<div class="'.$elementclass.
                    ' wpv-booking-accommodation-unit';
      $ucatnames = "";
      if (taxonomy_exists('accommodation_cat')) // it happens it does not when called from the constructor, since the taxonomy is not registered yet
      {
        $ucats = wp_get_object_terms($u->ID, 'accommodation_cat', array('fields' => 'id=>slug'));
        foreach ($ucats as $unitcategoryslug)
        {
          $result .= ' wpv-booking-accommodation-unit-cat-'.$unitcategoryslug;
          $fullcat = get_term_by('slug', $unitcategoryslug, 'accommodation_cat');
          if (!empty($ucatnames))
            $ucatnames .= ', ';
          $ucatnames .= $fullcat->name;
        }
      }
      $result .= ' wpv-booking-accommodation-unit-name-'.$u->post_name.
                    ' '.$extra_class.
                    '" style="left:'.$left.
                    '%; top:'.$top.
                    '%; width:'.$width.
                    '%; height:'.$height.
                    '%;" data-accunitid="'.$u->ID.'" ';
      $result.= ' data-accunitname="'.$u->post_title.'" ';
      $result.= ' data-accunitcat="'.$ucatnames.'"';
      $result.= ' data-bookable="'.implode(",", WPV_BookingForm::getBookableDays($u->ID)).'" ';
      $result.= '>';
        $result .= '<div class="wpv-accommodation-hint wpv-accommodation-hint-id-'.$u->ID.'" style="position: absolute;">';
          $result .= $this->featuredImageInADiv($u, "medium", null, "wpv-accommodation-hint-image", 12);
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
            $result .= '</div>';

            // I add 4 transparent divs around the hint and above all the rest to grab any clicks around.
            // This way the hint is like a modal or a lightbox but with static positioning (e.g. relative to the clicked icon)
            $result .= '<div class="wpv-accommodation-hint-inputgrab wpv-accommodation-hint-inputgrab-right"></div>';
            $result .= '<div class="wpv-accommodation-hint-inputgrab wpv-accommodation-hint-inputgrab-left"></div>';
            $result .= '<div class="wpv-accommodation-hint-inputgrab wpv-accommodation-hint-inputgrab-top"></div>';
            $result .= '<div class="wpv-accommodation-hint-inputgrab wpv-accommodation-hint-inputgrab-bottom"></div>';
                        
          $result .= '</div>';      

        $result .= '</div>';
      $result .= '</div>';
      Wpvacancy::$instance->registerScriptParamsCallback(array($this, "selectionDialog"), array($elementclass, 'wpv-accommodation-hint-id-'.$u->ID));    
      Wpvacancy::$instance->registerScriptParamsCallback(array($this, "viewMoreOfAccommodationItem"), array($u->ID, $viewmorebuttonclass)); 
      Wpvacancy::$instance->registerScriptParamsCallback(array($this, "chooseThisAccommodationItem"), array($u->ID, $choosethisbuttonclass, $elementclass)); 
    }
    return $result;
  }
  
  public function selectionDialog(array $params)
  {
    $elementclass = $params[0];
    $dialogclass = $params[1];
    return array('click', 
                  'showSelectionDialog', 
                  array($elementclass, $dialogclass));
    
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
                        'wpv-booking-accommodation-unit',
                        'wpv-booking-accommodation-selected-unit',
                        $highlight
                        ));    
  }
    
  public function featuredImageInADiv($post, $tsize = "large", $default_img_url = null, $class_prefix = "wpv-booking-accommodations-map", $scale = 0.8)
  {
    global $vb_wpv_baseurl;
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
        $default_img_url = $vb_wpv_baseurl.'images/no-image.png';        
      }
      $bgimage_url = $default_img_url;  
      $size = getimagesize($default_img_url);
    }

    if (empty($size))
      $size = array(100, 100);
    
    $w = $size[0];
    $h = $size[1];

    $ratio = $h / $w;
    $widthpercent = $scale;
    $heightpercent = $widthpercent * $ratio;

    $w = $widthpercent * 100;
    $h = $heightpercent * 100;

    $mapname = $post->post_name;

    $res = '<div class="'.$class_prefix.' '.$class_prefix.'-'.$mapname.
              '" style="background-image: url('.$bgimage_url.'); width: '.$w.'%; padding-bottom: '.$h.'%;">';
    return $res;
  }
  
  public function registerRoutes()
  {    
    register_rest_route(WPV_BookingForm::$namespace, '/'.self::$endpoint, array(
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

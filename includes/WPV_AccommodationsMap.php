<?php

class WPV_AccommodationsMap
{
  private $lightbox_template_postid = "lightbox-show-accommodation";
  private $postid;
  private static $endpoint = 'getLightboxPermalink';

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
      Wpvacance::$instance->plugin_public->addStyle('wpvacance-single-post-lightbox.css');
    }
    return $single_template;
  }    
  
  public function lightboxTags()
  {
    $res = '<div class="wpv-booking-accomodations-maps-lightbox" style="z-index:999999;">';
      $res .= '<iframe id="wpv-booking-accomodations-maps-lightbox-frame" src="#"></iframe>';
      $res .= '<div class="wpv-booking-accomodations-maps-lightbox-close" style="background-image:url('.plugin_dir_url( __FILE__ ).'res/button-close.png)"></div>';
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
    
    $res = '<div class="wpv-booking-option-title wpv-booking-accommodation-title">'.__('Which accommodation do you want?', 'wpvacance').'</div>';
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
    global $vb_wpv_custom_fields_prefix;
    
    $args = array('post_type'   => 'accommodation_type',
                  'post_status' => 'publish',
                  'numberposts' => 500,
        'meta_query' => array(
                          array(
                              'key' => $vb_wpv_custom_fields_prefix.'acc_map_id',
                              'value' => $map
                                )
                              )
    );
    $units = get_posts($args);
    $result = '';
    foreach ($units as $u)
    {
      $left = get_post_meta($u->ID, $vb_wpv_custom_fields_prefix."acc_unit_box_x", true);
      $top = get_post_meta($u->ID, $vb_wpv_custom_fields_prefix."acc_unit_box_y", true);
      $width = get_post_meta($u->ID, $vb_wpv_custom_fields_prefix."acc_unit_box_w", true);
      $height = get_post_meta($u->ID, $vb_wpv_custom_fields_prefix."acc_unit_box_h", true);
      $elementclass = 'wpv-accommodation-box-id-'.$u->ID;
      $viewmorebuttonclass = 'wpv-accommodation-hint-image-button-viewmore-'.$u->ID;
      $extra_class = get_post_meta($u->ID, $vb_wpv_custom_fields_prefix.'css_class', true);
      if (empty($extra_class))
        $extra_class = '';
      $result .= '<div class="'.$elementclass.' wpv-booking-accommodation-unit wpv-booking-accommodation-unit-'.$u->post_name.
                    ' '.$extra_class.
                    '" style="left:'.$left.
                    '%; top:'.$top.
                    '%; width:'.$width.
                    '%; height:'.$height.'%; position: absolute; background-image: url('.plugin_dir_url( __FILE__ ) . 'res/accm-unit.png);">';
      $result .= '<div class="wpv-accommodation-hint wpv-accommodation-hint-id-'.$u->ID.'" style="position: absolute;">';
      $result .= $this->featuredImageInADiv($u, "medium", null, "wpv-accommodation-hint-image", 4);
      $result .= '<div class="wpv-accommodation-hint-image-buttons"><div class="wpv-accommodation-hint-image-button wpv-accommodation-hint-image-button-viewmore '.$viewmorebuttonclass.'"><span>'.__("View more", 'wpvacance').'</span></div><div class="wpv-accommodation-hint-image-button wpv-accommodation-hint-image-button-choosethis"><span>'.__('Choose this', 'wpvacance').'</span></div></div>';
      $result .= '</div>';      
      
      $result .= '</div>';
      $result .= '</div>';
      Wpvacance::$instance->registerScriptParamsCallback(array($this, "selectionDialog"), array($elementclass, 'wpv-accommodation-hint-id-'.$u->ID));    
      Wpvacance::$instance->registerScriptParamsCallback(array($this, "viewMoreOfAccommodationItem"), array($u->ID, $viewmorebuttonclass)); 
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
    
  public function featuredImageInADiv($post, $tsize = "large", $default_img_url = null, $class_prefix = "wpv-booking-accommodations-map", $scale = 0.8)
  {
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
        $default_img_url = plugin_dir_url( __FILE__ ) . 'res/no-image.png';        
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

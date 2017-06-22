<?php

class WPV_AccomodationsMap
{
  function __construct()
  {
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
    
    $res = '<div class="wpv-booking-accomodations-maps">';
    foreach ($maps as $map)
    {
      $m = $this->mapPost($map);
      if (empty($m))
        continue;
      
      $thumb = get_post_thumbnail_id($m->ID);
      // TODO: if $thumb is empty, take a default image from the plugin configuration.
      $bgimage_file = get_attached_file($thumb);

      if (empty($bgimage_file))
        continue;
      
      $size = getimagesize($bgimage_file);
      
      if (empty($size))
        continue;
      
      $w = $size[0];
      $h = $size[1];
      
      $ratio = $h / $w;
      $widthpercent = 0.8;
      $heightpercent = $widthpercent * $ratio;
      
      $w = $widthpercent * 100;
      $h = $heightpercent * 100;
      $mapname = $m->post_name;
      
      $bgimage_url = wp_get_attachment_image_url($thumb, 'large');

      $res .= '<div class="wpv-booking-accommodations-map wpv-booking-accommodations-map-'.$mapname.
                '" style="background-image: url('.$bgimage_url.'); width: '.$w.'%; padding-bottom: '.$h.'%;">';
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
      $extra_class = get_post_meta($u->ID, $vb_wpv_custom_fields_prefix.'css_class', true);
      if (empty($extra_class))
        $extra_class = '';
      $result .= '<div class="'.$elementclass.' wpv-booking-accommodation-unit wpv-booking-accommodation-unit-'.$u->post_name.
                    ' '.$extra_class.
                    '" style="left:'.$left.
                    '%; top:'.$top.
                    '%; width:'.$width.
                    '%; height:'.$height.'%">';
      $result .= '</div>';
      Wpvacance::$instance->registerScriptParamsCallback(array($this, "bookingData"), array($elementclass, $u->ID));    
    }
    return $result;
  }
  
  public function bookingData(array $params)
  {
    $elementclass = params[0];
    $postid = params[1];
    return array('click', 
                  'updateBookingAvailabilityFromAccomodationMap', 
                  array($elementid, $postid));
    
  }
  
}

<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.virtualbit.it/
 * @since      1.0.0
 *
 * @package    Wpvacancy
 * @subpackage Wpvacancy/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wpvacancy
 * @subpackage Wpvacancy/admin
 * @author     Lucio Crusca <lucio@sulweb.org>
 */
class Wpvacancy_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

  private static $options_group;
  private static $plugin_options_slug;

  public static $activeSkin;
  public static $paypalBusiness;
  public static $cartMenu;
  public static $anonymousCartsUser;
  public static $defaultBookingExpirationTime;
  public static $defaultBookingDurationDays;


  private $jsCallbackManager;
  
  
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
    $this->jsCallbackManager = new JsCallbackManager("admin");
    $this->registerCallbacks();

	  self::$options_group = $this->plugin_name . '_wpvacancy_options';
    self::$activeSkin = self::$options_group . '_active_skin';
    self::$paypalBusiness = self::$options_group . '_paypal_business';
    self::$cartMenu = self::$options_group . '_cart_menu';
    self::$anonymousCartsUser = self::$options_group . '_anonymous_carts_user';
    self::$defaultBookingExpirationTime = self::$options_group . '_default_booking_expiration_time';
    self::$defaultBookingDurationDays = self::$options_group . '_default_booking_duration_days';
    
    self::$plugin_options_slug = $this->plugin_name . '-admin-options';
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() 
  {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wpvacancy-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() 
  {
    $this->jsCallbackManager->enqueueWPApi("wpv-node-wpapi-adm");

    $jsfileurl = plugin_dir_url( __FILE__ ) . 'js/wpvacancy-admin.js';
    wp_register_script($this->jsCallbackManager->getScriptHandle(), $jsfileurl);
    
    $this->jsCallbackManager->callRegisteredCallbacks();
  }

  public function register_settings() 
  {
    register_setting(self::$plugin_options_slug, self::$activeSkin);
    register_setting(self::$plugin_options_slug, self::$paypalBusiness);
    register_setting(self::$plugin_options_slug, self::$cartMenu);
    register_setting(self::$plugin_options_slug, self::$anonymousCartsUser);
    register_setting(self::$plugin_options_slug, self::$defaultBookingExpirationTime);
    register_setting(self::$plugin_options_slug, self::$defaultBookingDurationDays);
    add_settings_section(self::$plugin_options_slug, "Settings", array($this, "settings_section_title"), self::$plugin_options_slug);
    add_settings_field(self::$activeSkin, "Active skin", array($this, 'show_available_skins_select'), self::$plugin_options_slug, self::$plugin_options_slug);
    add_settings_field(self::$paypalBusiness, "Paypal account", array($this, 'show_paypal_input'), self::$plugin_options_slug, self::$plugin_options_slug);
    add_settings_field(self::$paypalBusiness, "Cart menu", array($this, 'show_cartmenu_select'), self::$plugin_options_slug, self::$plugin_options_slug);
    add_settings_field(self::$anonymousCartsUser, "Owner of anonymous carts", array($this, 'show_cartowner_select'), self::$plugin_options_slug, self::$plugin_options_slug);
    add_settings_field(self::$defaultBookingExpirationTime, "Default bookings expiration time", array($this, 'show_expiration_input'), self::$plugin_options_slug, self::$plugin_options_slug);
    add_settings_field(self::$defaultBookingDurationDays, "Initial bookings duration in days", array($this, 'show_initial_booking_duration_days'), self::$plugin_options_slug, self::$plugin_options_slug);
  }
  
  public function menu() 
  {
    add_options_page(self::$plugin_options_slug, 'WPVacancy', 'manage_options', self::$plugin_options_slug, array($this, 'show_options'));
  }
  
  public function settings_section_title() 
  {
    echo "<h3>".__("WPVacancy settings", "wpvacancy")."</h3>";
  }

  public function show_options() {
    ?>
    <div class="wrap">
      <h2>WPVacancy</h2>
      <form method="post" action="options.php">
    <?php
    settings_fields(self::$plugin_options_slug);
    do_settings_sections(self::$plugin_options_slug);
    submit_button();
    echo "</form></div>";
  }
  
  public function option($option_name, $default, $allowZero = false)
  {
    $val = get_option($option_name, false);
    if (empty($val) && (($allowZero === false && trim($val) === "0") || trim($val) === ""))
    {
      update_option($option_name, $default);
      return $default;
    }
    return $val;
  }
  
  public function show_input_check($option_name, $default = false)
  {
    $v = $this->option($option_name, $default);
    if (empty($v))
      $v = '';
    else
      $v = 'checked="checked"';
    ?>
    <input type="checkbox" id="<?php echo $option_name; ?>" name="<?php echo $option_name; ?>" <?php echo $v; ?> />
    <?php
  }

  public function show_input_text($option_name, $default = "", $note = "", $allowZero = false)
  {
    ?>
    <input type="text" id="<?php echo $option_name; ?>" name="<?php echo $option_name; ?>" size="20" value="<?php echo $this->option($option_name, $default, $allowZero); ?>"/>
    <?php if (!empty($note)) echo '<span class="wpv-admin-options-note wpv-admin-options-note-'.$option_name.'">'.$note.'</span>';
  }

  public function show_select_option($option, $key, $value = NULL)
  {
    echo '<option value="'.$key.'"';
    $dbtext = get_option( $option, '');
    if ($key == $dbtext)
            echo " selected";
    if (is_null($value))
      echo '>'.$key.'</option>';
    else
      echo '>'.$value.'</option>';
  }

  public function show_available_skins_select() 
  {
    ?>
    <select id="<?php echo self::$activeSkin; ?>" name="<?php echo self::$activeSkin; ?>">
      <?php 
          $skins = $this->skins();
          foreach ($skins as $s) 
            $this->show_select_option(self::$activeSkin, $s);
      ?>
    </select>
    <?php
  }
  
  public function show_paypal_input()
  {
    $this->show_input_text(self::$paypalBusiness);    
  }
  
  public function show_expiration_input()
  {
    $this->show_input_text(self::$defaultBookingExpirationTime, 7200);    
  }
  
  public function show_cartmenu_select()
  {
    ?>
    <select id="<?php echo self::$cartMenu; ?>" name="<?php echo self::$cartMenu; ?>">
      <?php 
        $menus = get_registered_nav_menus();
        foreach ($menus as $location => $description ) 
        {
          $this->show_select_option(self::$cartMenu, $location, $description." (".$location.")");
        }
      ?>
    </select>
    <?php
  }
  
  public function show_cartowner_select()
  {
    ?>
    <select id="<?php echo self::$anonymousCartsUser; ?>" name="<?php echo self::$anonymousCartsUser; ?>">
      <?php 
        $args = array(
          'who'          => 'authors',
         ); 
        $notsubscribers = get_users( $args );      
        foreach ($notsubscribers as $u) 
        {
          $roles = implode($u->roles, ",");
          $this->show_select_option(self::$anonymousCartsUser, $u->ID, $u->first_name." ".$u->last_name." (".$roles.")");
        }
      ?>
    </select>
    <?php
  }
    
  public function show_initial_booking_duration_days()
  {
    $this->show_input_text(self::$defaultBookingDurationDays, 1, "", true);
  }

  private function skins()
  {
    global $vb_wpv_basedir;
    $skins_base = $vb_wpv_basedir . 'public/skins/';
    $dir = scandir($skins_base);
    $list = array();
    foreach ($dir as $f)
    {
      if (is_dir($skins_base.$f) && $f[0] != '.')
      {
        array_push($list, $f);
      }
    }
    $list = apply_filters("vb_wpv_list_available_skins", $list);
    return $list;
  }
  
  public function getJsCallbackManager()
  {
    return $this->jsCallbackManager;
  }

  private function registerCallbacks() 
  {   
    global $vb_wpv_custom_fields_prefix;
    $prefix = $vb_wpv_custom_fields_prefix;
    $posts = get_posts( [ 'post_type' => 'accm_map_type' ] );
    foreach ( $posts as $post ) 
    {
      $act = get_post_meta(get_the_ID(), $prefix.'acc_map_id', true);
      $imagesrc = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'large')[0];
      $this->jsCallbackManager->registerScriptParamsCallback('onLoadRegisterAccommodationMapClick',
                                        array("postid" => $post->ID, "imagesrc" => $imagesrc));
      $this->jsCallbackManager->registerScriptParamsCallback('onLoadShowAccommodatioMapsImage',
                                        array("postid" => $post->ID, "imagesrc" => $imagesrc, "act" => $act));
    }
  }

}

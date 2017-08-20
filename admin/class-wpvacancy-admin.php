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

	  self::$options_group = $this->plugin_name . '_wpvacancy_options';
    self::$activeSkin = $this->options_group . '_active_skin';
    
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
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wpvacancy-admin.js', array( 'jquery' ), $this->version, false );
	}

  public function register_settings() 
  {
    register_setting(self::$plugin_options_slug, self::$activeSkin);
    add_settings_section(self::$plugin_options_slug, "Settings", array($this, "settings_section_title"), self::$plugin_options_slug);
    add_settings_field(self::$activeSkin, "Active skin", array($this, 'show_available_skins_select'), self::$plugin_options_slug, self::$plugin_options_slug);
  }
  
  public function menu() 
  {
    add_options_page($this->plugin_options_slug, 'WPVacancy', 'manage_options', self::$plugin_options_slug, array($this, 'show_options'));
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
  
  public function option($option_name, $default)
  {
    $val = get_option($option_name);
    if (empty($val))
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

  public function show_input_text($option_name, $default = "", $note = "")
  {
    ?>
    <input type="text" id="<?php echo $option_name; ?>" name="<?php echo $option_name; ?>" size="20" value="<?php echo $this->option($option_name, $default); ?>"/>
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
    return $list;
  }
}

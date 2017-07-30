<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.virtualbit.it/
 * @since      1.0.0
 *
 * @package    Wpvacancy
 * @subpackage Wpvacancy/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wpvacancy
 * @subpackage Wpvacancy/includes
 * @author     Lucio Crusca <lucio@sulweb.org>
 */
class Wpvacancy {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wpvacancy_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

  /**
   *
   * @var $instance Wpvacancy
   */
  public static $instance = null; // singleton
  
  /**
   *
   * @var $plugin_public Wpvacancy_Public
   */
  public $plugin_public;

  private $script_handle;
  /** 
   * @var $script_params_callbacks array
   */
  private $script_params_callbacks;
  
  
  /**
   *
   * @var $bookingform WPV_BookingForm
   */
  public $bookingform;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct($vb_wpv_basedir) {

    if (empty(self::$instance))
      self::$instance = &$this;

		$this->plugin_name = 'wpvacancy';
		$this->version = '1.0.0';
    $this->script_params_callbacks = array();
    $this->script_handle = 'wpv_formsubmitter';

		$this->load_dependencies($vb_wpv_basedir);
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wpvacancy_Loader. Orchestrates the hooks of the plugin.
	 * - Wpvacancy_i18n. Defines internationalization functionality.
	 * - Wpvacancy_Admin. Defines all hooks for the admin area.
	 * - Wpvacancy_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies($vb_wpv_basedir) {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once $vb_wpv_basedir.'includes/class-wpvacancy-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once $vb_wpv_basedir.'includes/class-wpvacancy-i18n.php';
		require_once $vb_wpv_basedir.'includes/WPVacancyShortcodes.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once $vb_wpv_basedir.'admin/class-wpvacancy-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once $vb_wpv_basedir.'public/class-wpvacancy-public.php';

    require_once $vb_wpv_basedir.'models/accommodationmap-post-type.php';
    require_once $vb_wpv_basedir.'models/accommodation-post-type.php';
    require_once $vb_wpv_basedir.'models/season-post-type.php';
    require_once $vb_wpv_basedir.'models/booking-post-type.php';
    require_once $vb_wpv_basedir.'models/price-post-type.php';

    require_once $vb_wpv_basedir.'includes/WPV_BookingForm.php';

    $this->loader = new Wpvacancy_Loader();
    $this->bookingform = new WPV_BookingForm();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wpvacancy_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Wpvacancy_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Wpvacancy_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

    add_action( 'admin_enqueue_scripts', array(&$this, 'enqueue_scripts') );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$this->plugin_public = new Wpvacancy_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $this->plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $this->plugin_public, 'enqueue_scripts' );
    add_action( 'wp_enqueue_scripts', array($this, 'enqueue_scripts'));
		$this->loader->add_action( 'wp_enqueue_scripts', $this->plugin_public, 'enqueue_extra_styles', PHP_INT_MAX);

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() 
  {
    $this->loader->run();

    $scclasses = array('WPVacancyShortcodes' => 'vb_wpv_');
    foreach ($scclasses as $cname => $prefix)
    {
      $class = new ReflectionClass($cname);
      $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);        
      foreach ($methods as $m)
      {
        $shortcodename = $prefix.$m->name; 
        add_shortcode($shortcodename, array($m->class, $m->name));
      }
    }    
  }
   

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Wpvacancy_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

  public function enqueue_scripts()
  {    
    $wpapifileurl = plugin_dir_url( __FILE__ ) . '../public/js/wpapi.min.js';
    $wpapihandle = "wpv-node-wpapi";
    wp_register_script($wpapihandle, $wpapifileurl);
    wp_enqueue_script($wpapihandle);

    wp_enqueue_script( 'jquery-ui-slider');

    $jsfileurl = plugin_dir_url( __FILE__ ) . '../public/js/wpvacancy-public.js';
    wp_register_script($this->script_handle, $jsfileurl );
    
    $hooks_data_events = array();

    foreach ($this->script_params_callbacks as $callback_pack)
    {
      $callback = $callback_pack['call'];
      $params = $callback_pack['params'];
      $cbarr = $callback($params);
      $hooks_data_events[count($hooks_data_events)] = $cbarr;
    }

    $hooks_data = array('events' => $hooks_data_events);
    
    wp_localize_script($this->script_handle, 'jshooks_params', $hooks_data);
    wp_enqueue_script($this->script_handle);
	}

  public function registerScriptParamsCallback($callable_arg, array $params = array())
  {
    if (is_callable($callable_arg))
      array_push($this->script_params_callbacks, array('call' => $callable_arg, 'params' => $params));
    return is_callable($callable_arg);
  }

  public static function is_admin()
  {
    return current_user_can('manage_options');
  }
 
}

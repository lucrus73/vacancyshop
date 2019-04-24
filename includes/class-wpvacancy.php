<?php
require  plugin_dir_path( dirname( __FILE__ ) ) . "vendor/autoload.php";

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
class Wpvacancy
{

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
   *
   * @var $cart WPV_Cart
   */
  public $cart;
  
  public static $namespace = "wpvacancy/v1";

  /**
   * Define the core functionality of the plugin.
   *
   * Set the plugin name and the plugin version that can be used throughout the plugin.
   * Load the dependencies, define the locale, and set the hooks for the admin area and
   * the public-facing side of the site.
   *
   * @since    1.0.0
   */
  public function __construct($vb_wpv_basedir)
  {

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
  private function load_dependencies($vb_wpv_basedir)
  {

    /**
     * The class responsible for orchestrating the actions and filters of the
     * core plugin.
     */
    require_once $vb_wpv_basedir . 'includes/class-wpvacancy-loader.php';

    /**
     * The class responsible for defining internationalization functionality
     * of the plugin.
     */
    require_once $vb_wpv_basedir . 'includes/class-wpvacancy-i18n.php';
    require_once $vb_wpv_basedir . 'includes/WPVacancyShortcodes.php';
    require_once $vb_wpv_basedir . 'includes/WPV_PaypalGateway.php';

    /**
     * The class responsible for defining all actions that occur in the admin area.
     */
    require_once $vb_wpv_basedir . 'admin/class-wpvacancy-admin.php';

    /**
     * The class responsible for defining all actions that occur in the public-facing
     * side of the site.
     */
    require_once $vb_wpv_basedir . 'public/class-wpvacancy-public.php';

    require_once $vb_wpv_basedir . 'models/accommodationmap-post-type.php';
    require_once $vb_wpv_basedir . 'models/accommodation-post-type.php';
    require_once $vb_wpv_basedir . 'models/period-post-type.php';
    require_once $vb_wpv_basedir . 'models/season-post-type.php';
    require_once $vb_wpv_basedir . 'models/cart-post-type.php';
    require_once $vb_wpv_basedir . 'models/booking-post-type.php';
    require_once $vb_wpv_basedir . 'models/price-post-type.php';
    require_once $vb_wpv_basedir . 'models/Maps_percents.php';
    require_once $vb_wpv_basedir . 'includes/WPV_BookingForm.php';
    require_once $vb_wpv_basedir . 'includes/WPV_Cart.php';

    $this->loader = new Wpvacancy_Loader();
    $this->bookingform = new WPV_BookingForm();
    $this->cart = new WPV_Cart();
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
  private function set_locale()
  {

    $plugin_i18n = new Wpvacancy_i18n();

    $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
  }

  /**
   * Register all of the hooks related to the admin area functionality
   * of the plugin.
   *
   * @since    1.0.0
   * @access   private
   */
  private function define_admin_hooks()
  {

    $plugin_admin = new Wpvacancy_Admin($this->get_plugin_name(), $this->get_version());

    $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
    $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
    $this->loader->add_action('admin_init', $plugin_admin, 'register_settings');
    $this->loader->add_action('admin_menu', $plugin_admin, 'menu');

    add_action('admin_enqueue_scripts', array(&$this, 'enqueue_scripts'));
    add_action( 'pre_get_posts', array(&$this, 'paymentResult'));
  }

  /**
   * Register all of the hooks related to the public-facing functionality
   * of the plugin.
   *
   * @since    1.0.0
   * @access   private
   */
  private function define_public_hooks()
  {

    $this->plugin_public = new Wpvacancy_Public($this->get_plugin_name(), $this->get_version());

    $this->loader->add_action('wp_enqueue_scripts', $this->plugin_public, 'enqueue_styles');
    $this->loader->add_action('wp_enqueue_scripts', $this->plugin_public, 'enqueue_scripts');
    add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    $this->loader->add_action('wp_enqueue_scripts', $this->plugin_public, 'enqueue_extra_styles', PHP_INT_MAX);
    $this->loader->add_action('init', $this->plugin_public, 'add_cart_to_menu', PHP_INT_MAX);
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
        $shortcodename = $prefix . $m->name;
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
  public function get_plugin_name()
  {
    return $this->plugin_name;
  }

  /**
   * The reference to the class that orchestrates the hooks with the plugin.
   *
   * @since     1.0.0
   * @return    Wpvacancy_Loader    Orchestrates the hooks of the plugin.
   */
  public function get_loader()
  {
    return $this->loader;
  }

  /**
   * Retrieve the version number of the plugin.
   *
   * @since     1.0.0
   * @return    string    The version number of the plugin.
   */
  public function get_version()
  {
    return $this->version;
  }
  
  public static function skinfileUrl($file, $skin = null)
  {
    global $vb_wpv_basedir;
    $autodefault = false;
    if ($skin === null)
    {
      $skin = get_option(Wpvacancy_Admin::$activeSkin, 'default');
      $autodefault = true;
    }
    
    $thirdPartySkinSlug = str_replace(' ', '', apply_filters("vb_wpv_get_skin_slug", $skin));
    $thirdPartySkinFileUrl = apply_filters("vb_wpv_get_skinfile_url_".$thirdPartySkinSlug, $file);
    if (!empty($thirdPartySkinFileUrl) && $file != $thirdPartySkinFileUrl)
      return $thirdPartySkinFileUrl;
    
    $skinfilepath = $vb_wpv_basedir . 'public/skins/'.$skin.'/'.$file;
    if (!file_exists($skinfilepath))
      if ($autodefault === true)
        return self::skinfileUrl($file, 'default');
      else
        return false;
    return plugin_dir_url(__FILE__) . '../public/skins/'.$skin.'/'.$file;      
  }
  
  public function enqueue_scripts()
  {
    $wpapifileurl = plugin_dir_url(__FILE__) . '../public/js/wpapi.min.js';
    $wpapihandle = "wpv-node-wpapi";
    wp_register_script($wpapihandle, $wpapifileurl);
    wp_enqueue_script($wpapihandle);

    wp_enqueue_script('jquery-ui-slider');

    $jsfileurl = plugin_dir_url(__FILE__) . '../public/js/wpvacancy-public.js';
    wp_register_script($this->script_handle, $jsfileurl);

    $this->callRegisteredCallbacks();
    
    $skinjsfileurl = self::skinfileUrl('js/skin.js');
    if ($skinjsfileurl !== false)
    {
      wp_register_script($this->script_handle.'-skin', $skinjsfileurl);
      wp_enqueue_script($this->script_handle.'-skin');
    }
  }
  
  
  /**
   * This function calls the registered callbacks in the same order they were
   * registered. See registerScriptParamsCallback in this class.
   * This function is executed during the wp_enqueue_scripts hook.
   * This function calls each callback, it provides it with the registered
   * params and it expects a array as a result of calling it.
   * The resulting array must contain a JS event name (such as 'load', 'click'
   * and so on) and other parameters that the $(document).ready JS function will
   * receive, parse and execute accordingly. 
   * See wpvacancy-public.js for details.
   * jshooks_params is the vector that makes the communication possible: it
   * is the resulting array of arrays: actually it is just a pair whose key is
   * always 'events' and whose value is always the array of the callbacks results.
   * @param array $hooks_data_events
   */
  private function callRegisteredCallbacks()
  {
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

  /**
   * This function saves the callable so that the wp_enqueue_scripts event will
   * call it afterwards (see callRegisteredCallbacks function in this class). 
   * This registerScriptParamsCallback function is intened to be used in 
   * constructors or other functions that are executed before said event takes
   * place and that need to hook that event, but in a guaranteed call order.
   * The callRegisteredCallbacks  function guaratees that the registered 
   * callbacks will be called in the same order they were registered (FIFO order).
   * Maybe WP code that handles hooks does just the same, but, since, even if it
   * did, that's not documented behavior, we cannot rely upon what it 
   * incidentally does as of today.
   * @param Callable $callable_arg The callback
   * @param array $params The params the callback will receive
   * @return boolean is_callable($callable_arg)
   */
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

  public static function is_vacancy_admin()
  {
    return current_user_can('manage_wpvacancy_options');
  }

  function paymentResult($query)
  {
    global $wp;

    if (!is_admin() && $query->is_main_query()) 
    {
      if ($wp->request == WPV_PaypalGateway::$ipn_slug)
      {
        $pg = new WPV_PaypalGateway();
        return $pg->ipn($query);
      }
    }
  }

}

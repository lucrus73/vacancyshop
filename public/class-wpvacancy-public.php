<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.virtualbit.it/
 * @since      1.0.0
 *
 * @package    Wpvacancy
 * @subpackage Wpvacancy/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wpvacancy
 * @subpackage Wpvacancy/public
 * @author     Lucio Crusca <lucio@sulweb.org>
 */
class Wpvacancy_Public {

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

  
  private $extrastyles;
  
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
    $this->extrastyles = array();
	}
  
  public function enqueue_skin($tag, $file)
  {
    wp_enqueue_style( $this->plugin_name . $tag, Wpvacancy::skinfileUrl($file), array(), $this->version, 'all' );
  }

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wpvacancy_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wpvacancy_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		$this->enqueue_skin('', 'css/wpvacancy-public.css');
		$this->enqueue_skin('rangeslider', 'css/wpvacancy-public-rangeslider.css');
		$this->enqueue_skin('calendar', 'css/wpvacancy-public-calendar.css');
		$this->enqueue_skin('maps', 'css/wpvacancy-public-maps.css');
    wp_enqueue_style('font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
    $wp_scripts = wp_scripts();
    wp_enqueue_style(
      'jquery-ui-theme-smoothness',
      sprintf(
        'https://ajax.googleapis.com/ajax/libs/jqueryui/%s/themes/smoothness/jquery-ui.css',
        $wp_scripts->registered['jquery-ui-core']->ver
      )
    );
  }
  
  public function enqueue_extra_styles()
  {
    $index = 0;
    foreach ($this->extrastyles as $st)
    {
      $index++;
      $this->enqueue_skin( $this->plugin_name.'extrast-'.$index, 'css/'.$st);
    }
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wpvacancy_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wpvacancy_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wpvacancy-public.js', array( 'jquery' ), $this->version, false );

	}
  
  public function addStyle($style)
  {
    array_push($this->extrastyles, $style);
  }

}

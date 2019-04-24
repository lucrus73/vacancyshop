<?php
    wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . '../admin/css/Maps_percents.css', array(), $this->version, 'all' );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . '../admin/js/Maps_percents.js', array( 'jquery' ), $this->version, false );

   
function wpdocs_register_my_custom_menu_page(){
    add_menu_page( 
        __( 'Maps percents', 'textdomain' ),
        'Maps percents',
        'manage_options',
        'custompage',
        'my_custom_menu_page',      
        'dashicons-building',
       90
    ); 
}
add_action( 'admin_menu', 'wpdocs_register_my_custom_menu_page' );
 
/**
 * Display a custom menu page
 */
function my_custom_menu_page(){
     //esc_html_e( 'Admin Page Test', 'textdomain' );  

    ?>
    <div id="wrap">
      <div id="hotel">

  </div>
  <div id="ui">

    <div id="savebox">
      <div id="name">
        <input id="accmname" type="text" value="Room 1">
      </div>
      <div id="buttons">
        <input id="clear" class="finish" type="button" value="Clear Selection">
        <input id="save" class="finish" type="button" value="Save Accommodation">
      </div>
      <ul id="accmlist">
              
      </ul>
    </div>
  </div>
</div>

<h4>
  Just drag and draw rectangles above the picture and save
  them as available rooms.
</h4>
    <?php
}


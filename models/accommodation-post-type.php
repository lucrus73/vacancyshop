<?php
wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . '../admin/js/Maps_percents.js', array('jquery'), rand(111, 9999), false);
wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . '../admin/css/Maps_percents.css');

	function remove_post_custom_fields() {
	  remove_meta_box( 'postcustom' , 'accommodation_type' , 'normal' ); 
    //remove_meta_box( 'linktargetdiv', 'link', 'normal' );
		//remove_meta_box( 'linkxfndiv', 'link', 'normal' );
		//remove_meta_box( 'linkadvanceddiv', 'link', 'normal' );
		remove_meta_box( 'postexcerpt', 'accommodation_type', 'normal' );
		remove_meta_box( 'trackbacksdiv', 'accommodation_type', 'normal' );
		remove_meta_box( 'commentstatusdiv', 'accommodation_type', 'normal' );
		remove_meta_box( 'commentsdiv', 'accommodation_type', 'normal' );
		//remove_meta_box( 'revisionsdiv', 'accommodation_type', 'normal' );
		//remove_meta_box( 'authordiv', 'accommodation_type', 'normal' );
		//remove_meta_box( 'sqpt-meta-tags', 'accommodation_type', 'normal' );
}
add_action( 'admin_menu' , 'remove_post_custom_fields' );


// let's create the function for the custom type
function accommodation_post_type() { 
  global $vb_wpv_basedir;
	// creating (registering) the custom type 
	register_post_type( 'accommodation_type', /* (http://codex.wordpress.org/Function_Reference/register_post_type) */
		// let's now add all the options for this post type
		array( 'labels' => array(
			'name' => __( 'Accommodations', 'wpvacancy' ), /* This is the Title of the Group */
			'singular_name' => __( 'Accommodation', 'wpvacancy' ), /* This is the individual type */
			'all_items' => __( 'All accommodations', 'wpvacancy' ), /* the all items menu item */
			'add_new' => __( 'Add accommodation', 'wpvacancy' ), /* The add new menu item */
			'add_new_item' => __( 'Add a new accommodation', 'wpvacancy' ), /* Add New Display Title */
			'edit' => __( 'Edit', 'wpvacancy' ), /* Edit Dialog */
			'edit_item' => __( 'Edit accommodation', 'wpvacancy' ), /* Edit Display Title */
			'new_item' => __( 'New accommodation', 'wpvacancy' ), /* New Display Title */
			'view_item' => __( 'Show accommodation', 'wpvacancy' ), /* View Display Title */
			'search_items' => __( 'Search accommodations', 'wpvacancy' ), /* Search Custom Type Title */ 
			'not_found' =>  __( 'No accommodations found', 'wpvacancy' ), /* This displays if there are no entries yet */ 
			'not_found_in_trash' => __( 'No accommodations in trash', 'wpvacancy' ), /* This displays if there is nothing in the trash */
			'parent_item_colon' => ''
			), /* end of arrays */
			'description' => __( 'Accommodations in your resort or rooms in your hotel', 'wpvacancy' ), /* Custom Type Description */
			'public' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'show_ui' => true,
			'query_var' => true,
			'menu_position' => 81, /* this is what order you want it to appear in on the left hand side menu */ 
			'menu_icon' => 'dashicons-admin-multisite', /* the icon for the custom post type menu */
			'rewrite'	=> array( 'slug' => 'accommodation_type', 'with_front' => true ), /* you can specify its url slug */
			'has_archive' => false, /* you can rename the slug here */
			'capability_type' => 'post',
			'hierarchical' => false,
			/* the next one is important, it tells what's enabled in the post editor */
			'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'sticky', 'page-attributes')
		) /* end of options */
	); /* end of register post type */
	
	// now let's add custom categories (these act like categories)
	
  register_taxonomy( 'accommodation_cat', 
    array('accommodation_type'), /* if you change the name of register_post_type( 'custom_type', then you have to change this */
    array('hierarchical' => true,     /* if this is true, it acts like categories */
      'labels' => array(
        'name' => __( 'Accommodation category', 'wpvacancy' ), /* name of the custom taxonomy */
        'singular_name' => __( 'Accommodation category', 'wpvacancy' ), /* single taxonomy name */
        'search_items' =>  __( 'Search accommodation categories', 'wpvacancy' ), /* search title for taxomony */
        'all_items' => __( 'All accommodation categories', 'wpvacancy' ), /* all title for taxonomies */
        'parent_item' => __( 'Parent category', 'wpvacancy' ), /* parent title for taxonomy */
        'parent_item_colon' => __( 'Parent category:', 'wpvacancy' ), /* parent taxonomy title */
        'edit_item' => __( 'Edit accommodation category', 'wpvacancy' ), /* edit custom taxonomy title */
        'update_item' => __( 'Update accommodation category', 'wpvacancy' ), /* update title for taxonomy */
        'add_new_item' => __( 'Add accommodation category', 'wpvacancy' ), /* add new title for taxonomy */
        'new_item_name' => __( 'New accommodation category', 'wpvacancy' ) /* name title for taxonomy */
      ),
      'show_admin_column' => true, 
      'show_ui' => true,
      'query_var' => true,
      'rewrite' => array( 'slug' => 'accommodation-category' ),
    )
  );

  // now let's add custom tags (these act like categories)
  register_taxonomy( 'accm_features', 
    array('accommodation_type'), /* if you change the name of register_post_type( 'custom_type', then you have to change this */
    array('hierarchical' => false,    /* if this is false, it acts like tags */
      'labels' => array(
        'name' => __( 'Accommodation Features', 'wpvacancy' ), /* name of the custom taxonomy */
        'singular_name' => __( 'Accommodation Feature', 'wpvacancy' ), /* single taxonomy name */
        'search_items' =>  __( 'Search features', 'wpvacancy' ), /* search title for taxomony */
        'all_items' => __( 'All features', 'wpvacancy' ), /* all title for taxonomies */
        'parent_item' => __( 'Parent feature', 'wpvacancy' ), /* parent title for taxonomy */
        'parent_item_colon' => __( 'Parent feature:', 'wpvacancy' ), /* parent taxonomy title */
        'edit_item' => __( 'Edit feature', 'wpvacancy' ), /* edit custom taxonomy title */
        'update_item' => __( 'Update feature', 'wpvacancy' ), /* update title for taxonomy */
        'add_new_item' => __( 'Add feature', 'wpvacancy' ), /* add new title for taxonomy */
        'new_item_name' => __( 'New feature name', 'wpvacancy' ) /* name title for taxonomy */
      ),
      'show_admin_column' => true,
      'show_ui' => true,
      'query_var' => true,
    )
  );

	/* this adds your post categories to your custom post type */
	register_taxonomy_for_object_type( 'accommodation_cat', 'accommodation_type' );
	/* this adds your post tags to your custom post type */
	register_taxonomy_for_object_type( 'accm_features', 'accommodation_type' );
	
}

// adding the function to the Wordpress init
	add_action( 'init', 'accommodation_post_type');	
  
  
function vb_wpv_accommodation_custom_fields() {

  global $vb_wpv_custom_fields_prefix ;
  // Start with an underscore to hide fields from custom fields list
  $prefix = $vb_wpv_custom_fields_prefix;

  /**
   * Initiate the metabox
   */
  $cmb = new_cmb2_box( array(
      'id'            => 'accommodation_meta',
      'title'         => __( 'Accommodation data', 'wpvacancy' ),
      'object_types'  => array( 'accommodation_type', ), // Post type
      'context'       => 'normal',
      'priority'      => 'high',
      'show_names'    => true, // Show field names on the left
      //'cmb_styles' => false, // false to disable the CMB stylesheet
      'closed'     => false, // Keep the metabox closed by default
  ) );

  $cmb->add_field( array(
		'name'    => __( 'Map', 'wpvacancy' ),
		'desc'    => __( 'The map where this accommodation unit is placed', 'wpvacancy' ),
		'id'      => $prefix . 'acc_map_id',
		'type'    => 'custom_attached_posts',
    'options' => array(
			'show_thumbnails' => true, // Show thumbnails on the left
			'filter_boxes'    => true, // Show a text box for filtering the results
        'query_args'      => array(
				'posts_per_page' => 10,
				'post_type'      => 'accm_map_type',
			), // override the get_posts args
		),
      'attributes' => array(
          'data-validation' => 'required',
      ),
      'on_front'        => false, // Optionally designate a field to wp-admin only
	));
  
  $cmb->add_field( array(
	'name' =>  __( 'Map_percents', 'wpvacancy' ),
	'desc' => __( 'The percents map where this accommodation unit is placed', 'wpvacancy' ),
	'id'   => $prefix . 'acc_map_percents_id',
	'type' => 'text',
	'render_row_cb' => 'cmb_test_render_row_cb',
  'on_front'=> false,
  
) ); 
  
  
  $cmb->add_field( array(
      'name'       => __( 'Unit left position', 'wpvacancy' ),
      'desc'       => __( 'The X coordinate of the upper-left corner of the unit box (in percentage of the map width)', 'wpvacancy' ),
      'id'         => $prefix . 'acc_unit_box_x',
      'type'       => 'text',
      // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
      // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
      // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
      'on_front'        => false, // Optionally designate a field to wp-admin only
      // 'repeatable'      => true,
  ) );

  $cmb->add_field( array(
      'name'       => __( 'Unit top position', 'wpvacancy' ),
      'desc'       => __( 'The Y coordinate of the upper-left corner of the unit box (in percentage of the map height)', 'wpvacancy' ),
      'id'         => $prefix . 'acc_unit_box_y',
      'type'       => 'text',
      // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
      // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
      // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
      'on_front'        => false, // Optionally designate a field to wp-admin only
      // 'repeatable'      => true,
  ) );
  
  $cmb->add_field( array(
      'name'       => __( 'Unit width', 'wpvacancy' ),
      'desc'       => __( 'The width of the unit box (in percentage of the map width)', 'wpvacancy' ),
      'id'         => $prefix . 'acc_unit_box_w',
      'type'       => 'text',
      // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
      // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
      // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
      'on_front'        => false, // Optionally designate a field to wp-admin only
      // 'repeatable'      => true,
  ) );

  $cmb->add_field( array(
      'name'       => __( 'Unit height', 'wpvacancy' ),
      'desc'       => __( 'The height of the unit box (in percentage of the map height)', 'wpvacancy' ),
      'id'         => $prefix . 'acc_unit_box_h',
      'type'       => 'text',
      // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
      // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
      // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
      'on_front'        => false, // Optionally designate a field to wp-admin only
      // 'repeatable'      => true,
  ) );

  $cmb->add_field( array(
      'name'       => __( 'Name', 'wpvacancy' ),
      'desc'       => __( 'The name of this accommodation unit', 'wpvacancy' ),
      'id'         => $prefix . 'acc_unit_name',
      'type'       => 'text',
      // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
      // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
      // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
      'on_front'        => false, // Optionally designate a field to wp-admin only
      // 'repeatable'      => true,
  ) );

  $cmb->add_field( array(
      'name'       => __( 'Extra CSS class', 'wpvacancy' ),
      'desc'       => __( 'Optional extra CSS class to apply', 'wpvacancy' ),
      'id'         => $prefix . 'acc_unit_css_class',
      'type'       => 'text',
      // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
      // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
      // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
      'on_front'        => false, // Optionally designate a field to wp-admin only
      // 'repeatable'      => true,
  ) );

  $cmb->add_field( array(
      'name'       => __( 'Pax', 'wpvacancy' ),
      'desc'       => __( 'How many people can fit into this unit', 'wpvacancy' ),
      'id'         => $prefix . 'acc_unit_pax',
      'type'       => 'text',
      // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
      // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
      // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
      'on_front'        => false, // Optionally designate a field to wp-admin only
      // 'repeatable'      => true,
  ) );
  $cmb->add_field( array(
      'name'       => __( 'Notes', 'wpvacancy' ),
      'desc'       => __( 'Additional notes to be shown in the booking recap', 'wpvacancy' ),
      'id'         => $prefix . 'acc_unit_notes',
      'type'       => 'text',
      // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
      // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
      // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
      'on_front'        => false, // Optionally designate a field to wp-admin only
      // 'repeatable'      => true,
  ) );

  $cmb->add_field( array(
      'name'       => __( 'Available for booking', 'wpvacancy' ),
      'desc'       => __( 'REMEMBER to check this one if you want your accommodation to effectively be available for booking!', 'wpvacancy' ),
      'id'         => $prefix . 'acc_available_for_booking',
      'type'       => 'checkbox',
      // 'show_on_cb' => 'cmb2_hide_if_no_cats', // function should return a bool value
      // 'sanitization_cb' => 'my_custom_sanitization', // custom sanitization callback parameter
      // 'escape_cb'       => 'my_custom_escaping',  // custom escaping callback parameter
      'on_front'        => false, // Optionally designate a field to wp-admin only
      // 'repeatable'      => true,
  ) ); 
 
 }
 
add_action( 'cmb2_admin_init', 'vb_wpv_accommodation_custom_fields' );


function get_my_custom_posts() {
   $posts = get_posts( [ 'post_type' => 'accm_map_type' ] );
   $options = [];
   foreach ( $posts as $post ) {
      $options[ $post->ID ] = $post->post_title;
      
   }

   return $options;
}

function vb_wpv_get_accommodation_name($accm_id)
{
  global $vb_wpv_custom_fields_prefix;
  $prefix = $vb_wpv_custom_fields_prefix;
  $name = get_post_meta($accm_id, $vb_wpv_custom_fields_prefix.'acc_unit_name');
  if (empty($name))
  {
    $apost = get_post($accm_id);
    if (!empty($apost))
      $name = $apost->post_title;
  }
  return $name;
}

 function cmb_test_render_row_cb( $field_args, $field ) {   
                  global $vb_wpv_custom_fields_prefix;
                  $prefix = $vb_wpv_custom_fields_prefix;
                  $posts = get_posts( [ 'post_type' => 'accm_map_type' ] );
                  $options = [];
                  foreach ( $posts as $post ) {
                         $options[ $post->ID ] = $post->ID ;
                         $act = get_post_meta(get_the_ID(),'vb_wpvac_cf_acc_map_id');
                         ?>
                  <script type="text/javascript">   
                  immagine({'id':<?php echo $options[ $post->ID ]; ?>,'url':'<?php echo wp_get_attachment_image_src(get_post_thumbnail_id( $options[ $post->ID ]),$large)[0]; ?>'});
                  immstart({'id':<?php echo $options[ $post->ID ]; ?>,'url':'<?php echo wp_get_attachment_image_src(get_post_thumbnail_id( $options[ $post->ID ]),$large)[0]; ?>'},'<?php echo get_post_meta(get_the_ID(),'vb_wpvac_cf_acc_map_id')[0][0]; ?>');
                  </script>
                  <?php
                  }
 ?>

<h1>Map_percents</h1>
  
	 <div id="<?php echo $prefix.'wrap';?>">
     <div id="<?php echo $prefix.'hotel';?>">
      
    </div>
   <div id="<?php echo $prefix.'ui';?>"> 

      <div id="<?php echo $prefix.'savebox';?>">
        <div id="<?php echo $prefix.'name';?>">
          
        </div>
        <div id="<?php echo $prefix.'buttons';?>">
          <input id="<?php echo $prefix.'clear';?>" class="finish" type="button" value="Clear Selection">
          <input id="<?php echo $prefix.'save';?>" class="finish" type="button" value="Acquire Selection">
        </div>
        <ul id="<?php echo $prefix.'accmlist';?>">
          
        </ul> 
        </div> 
    
   </div>
   </div>
<?php
 }


<?php

// let's create the function for the custom type
function accommodationmap_post_type() { 
	// creating (registering) the custom type 
	register_post_type( 'accm_map_type', /* (http://codex.wordpress.org/Function_Reference/register_post_type) */
		// let's now add all the options for this post type
		array( 'labels' => array(
			'name' => __( 'Accm. Maps', 'wpvacancy' ), /* This is the Title of the Group */
			'singular_name' => __( 'Map', 'wpvacancy' ), /* This is the individual type */
			'all_items' => __( 'All maps', 'wpvacancy' ), /* the all items menu item */
			'add_new' => __( 'Add map', 'wpvacancy' ), /* The add new menu item */
			'add_new_item' => __( 'Add a new map', 'wpvacancy' ), /* Add New Display Title */
			'edit' => __( 'Edit', 'wpvacancy' ), /* Edit Dialog */
			'edit_item' => __( 'Edit map', 'wpvacancy' ), /* Edit Display Title */
			'new_item' => __( 'New map', 'wpvacancy' ), /* New Display Title */
			'view_item' => __( 'Show maps', 'wpvacancy' ), /* View Display Title */
			'search_items' => __( 'Search maps', 'wpvacancy' ), /* Search Custom Type Title */ 
			'not_found' =>  __( 'No maps found', 'wpvacancy' ), /* This displays if there are no entries yet */ 
			'not_found_in_trash' => __( 'No maps in trash', 'wpvacancy' ), /* This displays if there is nothing in the trash */
			'parent_item_colon' => ''
			), /* end of arrays */
			'description' => __( 'Maps or plans images to show bookable accommodations', 'wpvacancy' ), /* Custom Type Description */
			'public' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => true,
			'show_ui' => true,
			'query_var' => true,
			'menu_position' => 10, /* this is what order you want it to appear in on the left hand side menu */ 
			'menu_icon' => plugin_dir_path( __FILE__ ) . 'images/accm_map_type-icon.png', /* the icon for the custom post type menu */
			'rewrite'	=> array( 'slug' => 'accm_map_type', 'with_front' => true ), /* you can specify its url slug */
			'has_archive' => false, /* you can rename the slug here */
			'capability_type' => 'post',
			'hierarchical' => true,
			/* the next one is important, it tells what's enabled in the post editor */
			'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'sticky', 'page-attributes')
		) /* end of options */
	); /* end of register post type */
	
	// now let's add custom categories (these act like categories)
	
  register_taxonomy( 'accmmap_cat', 
    array('accm_map_type'), /* if you change the name of register_post_type( 'custom_type', then you have to change this */
    array('hierarchical' => true,     /* if this is true, it acts like categories */
      'labels' => array(
        'name' => __( 'Map categories', 'wpvacancy' ), /* name of the custom taxonomy */
        'singular_name' => __( 'Map category', 'wpvacancy' ), /* single taxonomy name */
        'search_items' =>  __( 'Search maps categories', 'wpvacancy' ), /* search title for taxomony */
        'all_items' => __( 'All maps categories', 'wpvacancy' ), /* all title for taxonomies */
        'parent_item' => __( 'Parent category', 'wpvacancy' ), /* parent title for taxonomy */
        'parent_item_colon' => __( 'Parent category:', 'wpvacancy' ), /* parent taxonomy title */
        'edit_item' => __( 'Edit map category', 'wpvacancy' ), /* edit custom taxonomy title */
        'update_item' => __( 'Update map category', 'wpvacancy' ), /* update title for taxonomy */
        'add_new_item' => __( 'Add map category', 'wpvacancy' ), /* add new title for taxonomy */
        'new_item_name' => __( 'New map category', 'wpvacancy' ) /* name title for taxonomy */
      ),
      'show_admin_column' => true, 
      'show_ui' => true,
      'query_var' => true,
      'rewrite' => array( 'slug' => 'accommodationmap-category' ),
    )
  );

  // now let's add custom tags (these act like categories)
  register_taxonomy( 'accmmap_tag', 
    array('accm_map_type'), /* if you change the name of register_post_type( 'custom_type', then you have to change this */
    array('hierarchical' => false,    /* if this is false, it acts like tags */
      'labels' => array(
        'name' => __( 'Map Tags', 'wpvacancy' ), /* name of the custom taxonomy */
        'singular_name' => __( 'Map Tag', 'wpvacancy' ), /* single taxonomy name */
        'search_items' =>  __( 'Search map tags', 'wpvacancy' ), /* search title for taxomony */
        'all_items' => __( 'All map tags', 'wpvacancy' ), /* all title for taxonomies */
        'parent_item' => __( 'Parent tag', 'wpvacancy' ), /* parent title for taxonomy */
        'parent_item_colon' => __( 'Parent tag:', 'wpvacancy' ), /* parent taxonomy title */
        'edit_item' => __( 'Edit tag', 'wpvacancy' ), /* edit custom taxonomy title */
        'update_item' => __( 'Update tag', 'wpvacancy' ), /* update title for taxonomy */
        'add_new_item' => __( 'Add map tag', 'wpvacancy' ), /* add new title for taxonomy */
        'new_item_name' => __( 'New map tag name', 'wpvacancy' ) /* name title for taxonomy */
      ),
      'show_admin_column' => true,
      'show_ui' => true,
      'query_var' => true,
    )
  );

	/* this adds your post categories to your custom post type */
	register_taxonomy_for_object_type( 'accmmap_cat', 'accm_map_type' );
	/* this adds your post tags to your custom post type */
	register_taxonomy_for_object_type( 'accmmap_tag', 'accm_map_type' );
	
}

	// adding the function to the Wordpress init
	add_action( 'init', 'accommodationmap_post_type');

  
  
add_filter( 'default_content', 'wpv_accommodationmap_editor_content', 10, 2 );

function wpv_accommodationmap_editor_content( $content, $post ) 
{
  if ( $post->post_type == 'accm_map_type') 
  {
    $content = __('Please use the featured image of this post to set the map image', 'wpvacancy');
  }

  return $content;
}  
?>

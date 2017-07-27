<?php
/*
 Template Name: show-post-in-lightbox
 *
*/

get_header(); 
?>

<div id="primary" class="content-area">
  <main id="main" class="site-main" role="main">
    <?php
    global $post;
    // Start the loop.
    while ( have_posts() ) : the_post();

      $content = $post->post_content;
      $content = apply_filters('the_content', $content);
      $content = str_replace(']]>', ']]&gt;', $content);
      echo $content;

      // If comments are open or we have at least one comment, load up the comment template.
      /*
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
       * 
       */

			// End of the loop.
		endwhile;
		?>

	</main><!-- .site-main -->

	<?php 
    //get_sidebar( 'content-bottom' ); 
  ?>

</div><!-- .content-area -->

<?php 
  // get_sidebar(); 
?>
<?php 
 //  get_footer(); 
?>


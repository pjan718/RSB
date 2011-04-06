<?php get_header(); ?>


<div id="box">
    <div class="rotatorArea">
    
        <?php if (function_exists('vSlider')) { vSlider(); }?>
        
  
	 </div>
            <div class="logoArea"></div>
            <div class="bannerArea"><h3>Tel - 718.463.2313  |  133-45 Roosevelt Ave.  |  Flushing N.Y.</h3></div>

              
<div class="mainContent">
	 
	<div class="newsHeader"><h1><strong>RSB News</strong></h1></div>


    <?php if(have_posts()) : ?><?php while(have_posts()) : the_post(); ?>
    <div class="post" id="post-<?php the_ID(); ?>">
    <h2><a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a></h2>
    <div class="entry">
        <?php the_content('<p class="moreText">Read More></p>'); ?>
          <div class="postmetadata">
               <?php _e('Filed under&#58;'); ?> <?php the_category(', ') ?> <?php _e('by'); ?> <?php  the_author(); ?><br />
               <?php comments_popup_link('No Comments &#187;', '1 Comment &#187;', '% Comments &#187;'); ?> <?php edit_post_link('Edit', ' &#124; ', ''); ?>
               
               <?php
               /* If we are on a page, then hide comments */
            if (is_page('contact','about')) {
            /* do nothing */
            } else {
            comments_popup_link('No Comments È', '1 Comment È', '% Comments È');
            comments_template();
            }
            ?>
          </div>
    </div>
</div>
    <?php endwhile; ?>
     <div class="navigation">
	    <?php posts_nav_link(); ?>
	</div>
    <?php endif; ?>
    
    <div class="photoSection">
         <h1><strong>RSB Flickr</strong></h1>
    </div>
      <div class="flickrStream">
        <?php if ( !function_exists('dynamic_sidebar')
        || !dynamic_sidebar('sidebar2') ) : ?>
        <?php endif; ?>
    </div>
    </div>
 <?php get_sidebar(); ?>
</div>


<?php get_footer(); ?>
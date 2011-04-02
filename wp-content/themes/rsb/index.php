<?php get_header(); ?>


<div id="box">
    <div class="rotatorArea">
    <div class="rotatorHead"></div>
        <?php if(function_exists('wp_content_slider')) { wp_content_slider(); } ?>
            <?php if (function_exists('rotating_header_draw')) {
            rotating_header_draw();
            } else { ?>
         <div id="splash"></div>
        <?php } ?>
        
  
	 </div>
            <div class="logoArea"></div>
            <div class="barInfo"></div>

            <div class="socialMedia">
                <h3>Follow Us On</h3>
                <ul>
                    <li><a href="http://www.facebook.com/pages/Roosevelt-Sports-Bar/275226830136">
                    <img src="<?php bloginfo('template_directory'); ?>/images/social_media_fb.gif" alt="Flushing Sports Bar" ></img></a></li>
                    <li><img src="<?php bloginfo('template_directory'); ?>/images/social_media_twitter.gif" alt="Roosevelt Twitter" ></img></li>
                    <li><a href="http://www.flickr.com/photos/38506077@N04" rel="external"><img src="<?php bloginfo('template_directory'); ?>/images/social_media_flickr.gif"
                    alt="Roosevelt Flickr" ></img></a></li>
                </ul>
            </div>

<div class="mainContent">
	 
	<div class="newsHeader"></div>


    <?php if(have_posts()) : ?><?php while(have_posts()) : the_post(); ?>
    <div class="post" id="post-<?php the_ID(); ?>">
    <h2><a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a></h2>
    <div class="entry">
        <?php the_content('<span class="moreText">Read More></span>'); ?>
 
         
            <div class="postmetadata">
            <?php _e('Filed under&#58;'); ?> <?php the_category(', ') ?> <?php _e('by'); ?> <?php  the_author(); ?><br />
            <?php comments_popup_link('No Comments &#187;', '1 Comment &#187;', '% Comments &#187;'); ?> <?php edit_post_link('Edit', ' &#124; ', ''); ?>
          </div>
    </div>
</div>
    <?php endwhile; ?>
     <div class="navigation">
	    <?php posts_nav_link(); ?>
	</div>
    <?php endif; ?>
    
    <div class="photoSection">
        <?php if ( !function_exists('dynamic_sidebar')
        || !dynamic_sidebar('sidebar2') ) : ?>
        <?php endif; ?>
    </div>
    </div>
 <?php get_sidebar(); ?>
</div>


<?php get_footer(); ?>
<?php get_header(); ?>

<div id="wrapper">

<div id="header">
<!--NAVIGATION START-->	
<div id="mainNavigation">
<!--<ul>
   <li><a href="http://localhost:8888/rsb/?page_id=47">Home</a></li>
   <li>About</li>
   <li><a href="http://localhost:8888/rsb/?page_id=49">Contact</a></li>
</ul>-->
<?php
foreach (bjoerne_get_navigation_nodes(0) as $node) {
	$navItemSelected = ($node->is_selected() || $node->is_on_selected_path());
	if (bjoerne_is_node_visible($node)) {
	    bjoerne_println('<li class="menuItem'.($navItemSelected ? ' menuItemSelected' : '').'">');
		bjoerne_print_link($node);
		bjoerne_println('</li>');
	}
}
?>

<!--NAVIGATION END-->
</div>
</div>

<div id="box">
   
      <div class="rotatorArea">
         <?php if (function_exists('vSlider')) { vSlider(); }?>
      </div>
            <div class="logoArea"></div>
            <div class="bannerArea"><h3>Tel - 718.463.2313  |  133-45 Roosevelt Ave.  |  Flushing N.Y.</h3></div>
      
      <div class="mainContent">
         <div class="contentHeader"><h1><strong>RSB News</strong></h1></div>
            <?php if(have_posts()) : ?><?php while(have_posts()) : the_post(); ?>
         
         <div class="postHead"></div>
      
      <div class="post" id="post-<?php the_ID(); ?>">
      <h2><a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a></h2>
      <div class="entry">
        <?php the_content('<span class="moreText">Read More></span>'); ?>
          <div class="postmetadata">
               POSTED: <?php the_time('m/j/y g:i A') ?><br />
               FILED AS: <?php the_category(', ') ?><br />
               COMMENTS FEED: <?php comments_rss_link('RSS 2.0'); ?>
          </div>
      </div>

   
</div>
    <div class="postBot"></div>
    <?php endwhile; ?>
     <div class="navigation">
	    <?php posts_nav_link(); ?>

	</div>
    

    <?php endif; ?>
    
    <div class="photoSection">
        <h1><strong>RSB Flickr</strong></h1>
    </div>

      <div class="postHead_photo"></div>
      <div class="flickrStream">
        <div class="flickrThumbs">
        <?php if ( !function_exists('dynamic_sidebar')
        || !dynamic_sidebar('sidebar2') ) : ?>
        <?php endif; ?>
        </div>
    </div>
      <div class="postBot"></div>
    </div>
 <?php get_sidebar(); ?>

</div>


<?php get_footer(); ?>
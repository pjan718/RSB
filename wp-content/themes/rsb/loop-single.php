<?php

/*
Template Name: Snarfer
*/
?>
<?php get_header(); ?>


<div id="box">
    <!--<div class="rotatorArea"></div>-->
    
	   <div class="logoArea"></div>
	    <div class="barInfo">
    
</div>

<div class="mainContent">
	 
	<div class="newsHeader"></div>

			<?php
			/* Run the loop to output the post.
			 * If you want to overload this in a child theme then include a file
			 * called loop-single.php and that will be used instead.
			 */
			get_template_part( 'loop', 'single' );
			?>

			</div><!-- #content -->
		</div><!-- #container -->
 <?php get_sidebar(); ?>
</div>


<?php get_footer(); ?>
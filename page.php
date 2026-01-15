<?php get_header(); ?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        the_content();
    endwhile;
    ?>
    <div class="home-side-image">
    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/img1.jpg" alt="Image 1" class="image-1">
    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/img2.jpg" alt="Image 2" class="image-2">
</div>
</main>

<?php get_footer(); ?>

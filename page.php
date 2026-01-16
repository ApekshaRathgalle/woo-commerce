<?php
get_header(); 
?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        the_content();
    endwhile;
    ?>

    <?php if ( is_front_page() || is_home() ) : ?>
        <div class="homepage-banner">
            <div class="slideshow-container">
                
                <div class="slide fade active">
                    <div class="slide-content">
                        <h2>Spring Collection 2024</h2>
                        <p>Discover the latest trends in fashion</p>
                        <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="slide-btn">Shop Now</a>
                    </div>
                    <div class="slide-image">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/slide1.jpg" alt="Spring Collection">
                    </div>
                </div>

                <div class="slide fade">
                    <div class="slide-content">
                        <h2>Summer Essentials</h2>
                        <p>Beat the heat with our cool styles</p>
                        <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="slide-btn">Explore</a>
                    </div>
                    <div class="slide-image">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/slide2.jpg" alt="Summer Essentials">
                    </div>
                </div>

                <div class="slide fade">
                    <div class="slide-content">
                        <h2>Designer Wear</h2>
                        <p>Exclusive designer pieces just for you</p>
                        <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="slide-btn">Browse</a>
                    </div>
                    <div class="slide-image">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/slide3.jpg" alt="Designer Wear">
                    </div>
                </div>

                <div class="slide fade">
                    <div class="slide-content">
                        <h2>Accessories Collection</h2>
                        <p>Complete your look with perfect accessories</p>
                        <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="slide-btn">Shop Accessories</a>
                    </div>
                    <div class="slide-image">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/slide4.jpg" alt="Accessories">
                    </div>
                </div>

                <div class="slide fade">
                    <div class="slide-content">
                        <h2>New Arrivals</h2>
                        <p>Fresh styles added weekly</p>
                        <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="slide-btn">See What's New</a>
                    </div>
                    <div class="slide-image">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/slide5.jpg" alt="New Arrivals">
                    </div>
                </div>
            </div>
        </div>

        <div class="below-banner-content">
            <div class="content-text">
                <p>Fashion is more than just what we wear — it is a powerful form of self-expression that reflects confidence, creativity, and individuality. Our collection is designed for those who appreciate style that evolves with modern trends while staying timeless at its core. Each piece is carefully crafted with attention to detail, quality fabrics, and thoughtful design to ensure comfort without compromising elegance. From everyday essentials to statement pieces that turn heads, our fashion embraces diversity, bold choices, and effortless sophistication. We believe that clothing should empower you to feel confident in your own skin, whether you're stepping into a busy workday, enjoying a casual outing, or making an impression at a special event. Inspired by global trends and refined through a contemporary lens, our designs celebrate authenticity, versatility, and personal style. Step into a world where fashion meets passion, and let every outfit tell your story with confidence, grace, and modern flair.</p>
            </div>
            <div class="content-image">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/parae.jpg" alt="Fashion Description">
            </div>
        </div>
    <?php endif; ?>
</main>

<?php
get_footer();
?>

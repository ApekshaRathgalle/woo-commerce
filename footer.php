<footer class="site-footer">
    <div class="footer-container">
        
        <!-- Footer Column 1: About -->
        <div class="footer-column">
            <h3><?php bloginfo( 'name' ); ?></h3>
            <p class="footer-tagline">Your destination for contemporary fashion and timeless style.</p>
            <div class="footer-social">
                <a href="#" aria-label="Facebook"><span class="social-icon">📘</span></a>
                <a href="#" aria-label="Instagram"><span class="social-icon">📷</span></a>
                <a href="#" aria-label="Twitter"><span class="social-icon">🐦</span></a>
                <a href="#" aria-label="Pinterest"><span class="social-icon">📌</span></a>
            </div>
        </div>
        
        <!-- Footer Column 2: Quick Links -->
        <div class="footer-column">
            <h4>Shop</h4>
            <ul class="footer-links">
                <li><a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>">All Products</a></li>
                <li><a href="#">New Arrivals</a></li>
                <li><a href="#">Best Sellers</a></li>
                <li><a href="#">Sale</a></li>
            </ul>
        </div>
        
        <!-- Footer Column 3: Customer Service -->
        <div class="footer-column">
            <h4>Customer Care</h4>
            <ul class="footer-links">
                <li><a href="#">Contact Us</a></li>
                <li><a href="#">Shipping Info</a></li>
                <li><a href="#">Returns & Exchanges</a></li>
                <li><a href="#">Size Guide</a></li>
                <li><a href="#">FAQ</a></li>
            </ul>
        </div>
        
        <!-- Footer Column 4: Newsletter -->
        <div class="footer-column">
            <h4>Stay Connected</h4>
            <p>Subscribe to get special offers and style updates.</p>
            <form class="newsletter-form" action="#" method="post">
                <input type="email" name="newsletter_email" placeholder="Enter your email" required>
                <button type="submit">Subscribe</button>
            </form>
        </div>
    </div>
    
    <!-- Footer Bottom -->
   
</footer>

<?php wp_footer(); ?>
</body>
</html>
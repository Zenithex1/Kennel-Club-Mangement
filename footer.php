<!-- footer.php -->
<footer class="main-footer">
    <div class="footer-container">
        <div class="footer-section">
            <h3>Kennel Club</h3>
            <p>Finding loving homes for dogs in need since 2025.</p>
            <div class="social-links">
                <a href="https://facebook.com" target="_blank" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                <a href="https://instagram.com" target="_blank" class="social-icon"><i class="fab fa-instagram"></i></a>
                <a href="https://twitter.com" target="_blank" class="social-icon"><i class="fab fa-twitter"></i></a>
                <a href="https://youtube.com" target="_blank" class="social-icon"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
        
      
        
        <div class="footer-section">
            <h3>Contact Us</h3>
            <ul class="contact-info">
                <li><i class="fas fa-map-marker-alt"></i> Sankhamul, Kathmandu, Nepal</li>
                <li><i class="fas fa-phone"></i> +977 9845362432</li>
                <li><i class="fas fa-envelope"></i> info@dogadopt.com</li>
            </ul>
        </div>
    </div>
    
    <div class="footer-bottom">
        <p>&copy; 2025 DogAdopt. All Rights Reserved.</p>
    </div>
</footer>

<style>
    /* Footer Styles */
    .main-footer {
        background-color: #2C3E50;
        color: white;
        padding: 40px 20px 20px;
        margin-top: auto;
        width: 100%;
    }
    
    .footer-container {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        max-width: 1200px;
        margin: 0 auto;
        gap: 30px;
    }
    
    .footer-section {
        flex: 1;
        min-width: 250px;
        margin-bottom: 20px;
    }
    
    .footer-section h3 {
        color: #E74C3C;
        margin-bottom: 20px;
        font-size: 1.2rem;
        position: relative;
        padding-bottom: 10px;
    }
    
    .footer-section h3::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 50px;
        height: 2px;
        background-color: #E74C3C;
    }
    
    .footer-section p {
        margin-bottom: 20px;
        line-height: 1.6;
    }
    
    .social-links {
        display: flex;
        gap: 15px;
    }
    
    .social-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        background-color: #34495E;
        border-radius: 50%;
        color: white;
        transition: all 0.3s ease;
    }
    
    .social-icon:hover {
        background-color: #E74C3C;
        transform: translateY(-3px);
    }
    
    .footer-section ul {
        list-style: none;
    }
    
    .footer-section ul li {
        margin-bottom: 10px;
    }
    
    .footer-section ul li a {
        color: #ECF0F1;
        text-decoration: none;
        transition: color 0.3s ease;
    }
    
    .footer-section ul li a:hover {
        color: #E74C3C;
    }
    
    .contact-info i {
        margin-right: 10px;
        color: #E74C3C;
        width: 20px;
        text-align: center;
    }
    
    .footer-bottom {
        text-align: center;
        padding-top: 20px;
        margin-top: 30px;
        border-top: 1px solid #34495E;
    }
    
    @media (max-width: 768px) {
        .footer-container {
            flex-direction: column;
            gap: 20px;
        }
        
        .footer-section {
            min-width: 100%;
        }
    }
</style>

<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
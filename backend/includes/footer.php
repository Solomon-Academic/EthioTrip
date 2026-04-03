    </main>
    <footer class="footer">
        <div class="footer-grid">
            <div class="footer-col">
                <a href="dashboard.php" class="logo">Ethio<span>Trip</span></a>
                <p>Authentic Ethiopian journeys curated for the modern explorer.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="footer-col">
                <h4>Explore</h4>
                <ul>
                    <li><a href="../../frontend/Destination.html">Destinations</a></li>
                    <li><a href="../../frontend/packages.html">Packages</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Connect</h4>
                <ul>
                    <li><a href="../../frontend/about.html">About Us</a></li>
                    <li><a href="../../frontend/about.html">Contact Us</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">© <?php echo date('Y'); ?> EthioTrip Ethiopia. All rights reserved.</div>
    </footer>
    
    <style>
        .footer {
            background: #1a1a1a;
            color: white;
            padding: 60px 8% 40px;
            margin-top: 60px;
        }
        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .footer-col h4 {
            margin-bottom: 20px;
            color: white;
        }
        .footer-col ul {
            list-style: none;
        }
        .footer-col li {
            margin-bottom: 10px;
        }
        .footer-col a {
            color: #bbb;
            text-decoration: none;
        }
        .footer-col a:hover {
            color: #d4af37;
        }
        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        .social-links a {
            width: 35px;
            height: 35px;
            background: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: white;
        }
        .footer-bottom {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #333;
            color: #777;
        }
    </style>
    
    <script src="js/validation.js"></script>
</body>
</html>
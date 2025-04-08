<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TrainDekho - Book Train Tickets</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            position: relative;
            min-height: 100vh;
            background: #000;
        }

        .header {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 10px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo {
            height: 40px;
        }

        .nav-links {
            display: flex;
            gap: 20px;
        }

        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
        }

        .auth-buttons {
            display: flex;
            gap: 15px;
        }

        .auth-buttons a {
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
        }

        .login-btn {
            background-color: #003366;
            color: white;
        }

        .register-btn {
            border: 1px solid #003366;
            color: #003366;
        }

        .main-content {
            max-width: 1200px;
            margin: 50px auto;
            padding: 0 20px;
            position: relative;
            z-index: 1;
        }

        .search-container {
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .service-icons {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 20px;
        }

        .service-icon {
            text-align: center;
            color: #666;
            text-decoration: none;
        }

        .service-icon.active {
            color: #003366;
        }

        .service-icon img {
            width: 24px;
            height: 24px;
            margin-bottom: 5px;
        }

        .search-form {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-top: 20px;
        }

        .form-group {
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #666;
            font-size: 12px;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .search-btn {
            background-color: #003366;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            margin-top: 24px;
        }

        .offers-section {
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 8px;
            margin-top: 40px;
        }

        .offers-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 0 10px;
        }

        .offer-tabs {
            display: flex;
            gap: 20px;
        }

        .tab {
            color: #666;
            text-decoration: none;
            padding: 5px 0;
            position: relative;
        }

        .tab.active {
            color: #003366;
            font-weight: bold;
        }

        .tab.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: #003366;
        }

        .view-all {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .arrow-buttons {
            display: flex;
            gap: 5px;
        }

        .arrow-btn {
            width: 30px;
            height: 30px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .offers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .offer-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
        }

        .offer-image {
            position: relative;
        }

        .offer-image img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .terms {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0,0,0,0.6);
            color: white;
            padding: 4px 8px;
            font-size: 12px;
            border-radius: 4px;
        }

        .offer-content {
            padding: 15px;
        }

        .offer-content h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
            color: #333;
        }

        .offer-content p {
            margin: 0 0 10px 0;
            color: #666;
            font-size: 14px;
        }

        .promo-code {
            color: #003366;
            font-weight: bold;
        }

        .book-now, .know-more {
            display: inline-block;
            color: #0066cc;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
        }

        .footer {
            background-color: #003366;
            color: white;
            padding: 40px 0;
            margin-top: 50px;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
            padding: 0 20px;
        }

        .footer-section h3 {
            margin-bottom: 20px;
        }

        .footer-section ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-section ul li {
            margin-bottom: 10px;
        }

        .footer-section a {
            color: white;
            text-decoration: none;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            margin-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .explore-section {
            background: rgba(255, 255, 255, 0.9);
            padding: 40px 20px;
            margin-top: 40px;
            border-radius: 8px;
        }

        .explore-title {
            text-align: center;
            margin-bottom: 40px;
            color: #333;
            font-size: 28px;
        }

        .explore-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .explore-card {
            display: flex;
            align-items: center;
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            position: relative;
            transition: all 0.3s ease;
        }

        .explore-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .explore-icon {
            width: 50px;
            height: 50px;
            margin-right: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .explore-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .explore-content {
            flex: 1;
        }

        .explore-content h3 {
            margin: 0 0 5px 0;
            color: #333;
            font-size: 16px;
        }

        .explore-content p {
            margin: 0;
            color: #666;
            font-size: 14px;
            line-height: 1.4;
        }

        .explore-arrow {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #003366;
            text-decoration: none;
            font-size: 20px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .explore-card:hover .explore-arrow {
            opacity: 1;
        }

        @media (max-width: 768px) {
            .explore-grid {
                grid-template-columns: 1fr;
            }
            
            .explore-card {
                margin: 10px 0;
            }
        }

        .ratings-trust-section {
            background: rgba(255, 255, 255, 0.9);
            padding: 40px 20px;
            margin: 40px auto;
            max-width: 1200px;
            border-radius: 8px;
        }

        .ratings-container {
            display: flex;
            justify-content: center;
            gap: 60px;
            align-items: center;
        }

        .rating-box, .trust-box {
            flex: 1;
            max-width: 400px;
            padding: 20px;
        }

        .rating-box h3, .trust-box h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 20px;
        }

        .rating-number {
            font-size: 48px;
            font-weight: bold;
            color: #003366;
            margin-bottom: 15px;
        }

        .rating-bars {
            margin-bottom: 15px;
        }

        .rating-bar {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }

        .star {
            width: 30px;
            color: #666;
            font-size: 14px;
        }

        .bar-container {
            flex: 1;
            height: 8px;
            background-color: #f0f0f0;
            border-radius: 4px;
            margin-left: 10px;
        }

        .bar {
            height: 100%;
            background-color: #4CAF50;
            border-radius: 4px;
        }

        .total-ratings {
            color: #666;
            font-size: 14px;
        }

        .trust-box {
            text-align: center;
        }

        .trust-content {
            position: relative;
            display: inline-block;
        }

        .laurel-wreath {
            position: relative;
            width: 200px;
            height: 200px;
        }

        .laurel-wreath img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .trust-number {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }

        .trust-number .number {
            display: block;
            font-size: 24px;
            font-weight: bold;
            color: #003366;
        }

        .trust-number .label {
            display: block;
            font-size: 18px;
            color: #666;
        }

        @media (max-width: 768px) {
            .ratings-container {
                flex-direction: column;
                gap: 30px;
            }

            .rating-box, .trust-box {
                width: 100%;
            }

            .laurel-wreath {
                width: 150px;
                height: 150px;
            }
        }

        @media (max-width: 768px) {
            .offer-tabs {
                display: none;
            }
            
            .offers-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Add slideshow background styles */
        .background-slideshow {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        .background-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            animation: slideshow 30s linear infinite;
            transition: opacity 1s ease-in-out;
        }

        .background-slide::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }

        .background-slide:nth-child(1) {
            background-image: url('images/1ga.jpg');
            animation-delay: 0s;
        }

        .background-slide:nth-child(2) {
            background-image: url('images/login1.jpg');
            animation-delay: 6s;
        }

        .background-slide:nth-child(3) {
            background-image: url('images/login2.jpg');
            animation-delay: 12s;
        }

        .background-slide:nth-child(4) {
            background-image: url('images/n1.jpg');
            animation-delay: 18s;
        }

        .background-slide:nth-child(5) {
            background-image: url('images/2ga.jpg');
            animation-delay: 24s;
        }

        @keyframes slideshow {
            0%, 15% {
                opacity: 1;
            }
            20%, 95% {
                opacity: 0;
            }
            100% {
                opacity: 0;
            }
        }

        .error-message {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }

        input:invalid {
            border-color: #dc3545;
        }

        input:valid {
            border-color: #28a745;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="nav-container">
            <div class="logo-container">
                <img src="images/TRAIN MANIA YOU TUBE CHANNEL LOGO.png" alt="TrainDekho" class="logo">
                <span>TrainDekho</span>
            </div>
            <div class="auth-buttons">
                <a href="login1.php" class="login-btn">Login</a>
                <a href="register.php" class="register-btn">Register</a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="search-container">
            <div class="service-icons">
                <a href="#" class="service-icon active">
                    <img src="images/TRAIN MANIA YOU TUBE CHANNEL LOGO.png" alt="Train">
                    <span>Trains</span>
                </a>
                <!-- Add more service icons as needed -->
            </div>

            <form class="search-form" action="search_index.php" method="GET">
                <div class="form-group">
                    <label>From</label>
                    <input type="text" name="from" placeholder="Enter city or station" list="cities-list" required>
                    <datalist id="cities-list">
                        <option value="Mumbai">
                        <option value="Pune">
                        <option value="Kerala">
                        <option value="Bhopal">
                        <option value="Hyderabad">
                        <option value="Karnataka">
                        <option value="Tamil Nadu">
                        <option value="Bangalore">
                        <option value="New Delhi">
                    </datalist>
                </div>
                <div class="form-group">
                    <label>To</label>
                    <input type="text" name="to" placeholder="Enter city or station" list="cities-list" required>
                </div>
                <div class="form-group">
                    <label>Travel Date</label>
                    <input type="date" name="date" id="travelDate" required 
                           min="" <!-- Will be set by JavaScript -->
                        
                    <span id="dateError" class="error-message"></span>
                </div>
                <div class="form-group">
                    <label>Class</label>
                    <select name="class" required>
                        <option value="ALL">All Classes</option>
                        <option value="1A">First AC</option>
                        <option value="2A">Second AC</option>
                        <option value="3A">Third AC</option>
                        <option value="SL">Sleeper</option>
                    </select>
                </div>
                <button type="submit" class="search-btn">Search Trains</button>
            </form>
        </div>

        <div class="offers-section">
            <div class="offers-header">
                <h2>Offers</h2>
                <div class="offer-tabs">
                    <a href="#" class="tab active">Trains</a>
                </div>
                <div class="view-all">
                    <span>VIEW ALL</span>
                    <div class="arrow-buttons">
                        <button class="arrow-btn prev">←</button>
                        <button class="arrow-btn next">→</button>
                    </div>
                </div>
            </div>

            <div class="offers-grid">
                <!-- Offer Card 1 -->
                <div class="offer-card">
                    <div class="offer-image">
                        <img src="images/maha1.avif" alt="Maha Shivratri Offer">
                        <span class="terms">T&C'S APPLY</span>
                    </div>
                    <div class="offer-content">
                        <h3>Maha Shivratri-special Deals:</h3>
                        <p>Up to 15% OFF*</p>
                        <a href="#" class="book-now">BOOK NOW</a>
                    </div>
                </div>

                <!-- Offer Card 2 -->
                <div class="offer-card">
                    <div class="offer-image">
                        <img src="images/book.avif" alt="Book Trains">
                        <span class="terms">T&C'S APPLY</span>
                    </div>
                    <div class="offer-content">
                        <h3>Book Trains</h3>
                        <p>with Trip Guarantee starting @ ₹1*</p>
                        <a href="#" class="book-now">BOOK NOW</a>
                    </div>
                </div>

                <!-- Offer Card 3 -->
                <div class="offer-card">
                    <div class="offer-image">
                        <img src="images/exterior.jpg" alt="Wedding Travel">
                        <span class="terms">T&C'S APPLY</span>
                    </div>
                    <div class="offer-content">
                        <h3>Heading to a Wedding Soon?</h3>
                        <p>Avail Trip Guarantee on Trains Starting @ ₹1*</p>
                        <p class="promo-code">Code: WEDDINGTRAVEL</p>
                        <a href="#" class="book-now">BOOK NOW</a>
                    </div>
                </div>

                <!-- Offer Card 4 -->
                <div class="offer-card">
                    <div class="offer-image">
                        <img src="images/login2.jpg" alt="Trip Guarantee">
                        <span class="terms">T&C'S APPLY</span>
                    </div>
                    <div class="offer-content">
                        <h3>Presenting TRIP GUARANTEE on Trains</h3>
                        <p>Waitlisted tickets no more. Convert your unconfirmed train tickets to flights, cabs & more.</p>
                        <a href="#" class="know-more">KNOW MORE</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Explore More Section -->
        <div class="explore-section">
            <h2 class="explore-title">Explore More With TrainDekho</h2>
            
            <div class="explore-grid">
                <!-- IRCTC Authorized Partner -->
                <div class="explore-card">
                    <div class="explore-icon">
                        <img src="images/authorized.svg" alt="IRCTC Partner">
                    </div>
                    <div class="explore-content">
                        <h3>IRCTC Authorized Partner</h3>
                        <p>TrainDekho is authorized partner of IRCTC, booking train tickets since 2023.</p>
                    </div>
                </div>

                <!-- Live Station Status -->
                <div class="explore-card">
                    <div class="explore-icon">
                        <img src="images/livestation.svg" alt="Station Status">
                    </div>
                    <div class="explore-content">
                        <h3>Live Station Status</h3>
                        <p>Get a complete list of trains that shall be arriving at the railway station.</p>
                    </div>
                    <a href="#" class="explore-arrow">→</a>
                </div>

                <!-- Live Train Status -->
                <div class="explore-card">
                    <div class="explore-icon">
                        <img src="images/livetrain.svg" alt="Train Status">
                    </div>
                    <div class="explore-content">
                        <h3>Live Train Status</h3>
                        <p>Get Live Status Updates instantly on Trains with TrainDekho.</p>
                    </div>
                    <a href="#" class="explore-arrow">→</a>
                </div>

                <!-- IRCTC Train Food Booking -->
                <div class="explore-card">
                    <div class="explore-icon">
                        <img src="images/trainfood.svg" alt="Food Booking">
                    </div>
                    <div class="explore-content">
                        <h3>IRCTC Train Food Booking</h3>
                        <p>Enjoy booking IRCTC Food & Get Food Delivered on the Train</p>
                    </div>
                    <a href="#" class="explore-arrow">→</a>
                </div>

                <!-- Instant Refunds -->
                <div class="explore-card">
                    <div class="explore-icon">
                        <img src="images/instanrefund.svg" alt="Instant Refunds">
                    </div>
                    <div class="explore-content">
                        <h3>Instant Refunds & Cancellations</h3>
                        <p>Get an instant refund and book your next train ticket without any hassle.</p>
                    </div>
                    <a href="#" class="explore-arrow">→</a>
                </div>

                <!-- Customer Service -->
                <div class="explore-card">
                    <div class="explore-icon">
                        <img src="images/_icon_train_24.svg" alt="Customer Service">
                    </div>
                    <div class="explore-content">
                        <h3>24*7 Customer Service</h3>
                        <p>We work 24 Hrs. a day to make sure our availability whenever our customers need us.</p>
                    </div>
                    <a href="#" class="explore-arrow">→</a>
                </div>
            </div>
        </div>
    </main>

    <!-- Add this section just before the footer -->
    <div class="ratings-trust-section">
        <div class="ratings-container">
            <div class="rating-box">
                <h3>Highest Rated* Travel App</h3>
                <div class="rating-number">4.6</div>
                <div class="rating-bars">
                    <div class="rating-bar">
                        <span class="star">5★</span>
                        <div class="bar-container">
                            <div class="bar" style="width: 85%;"></div>
                        </div>
                    </div>
                    <div class="rating-bar">
                        <span class="star">4★</span>
                        <div class="bar-container">
                            <div class="bar" style="width: 60%;"></div>
                        </div>
                    </div>
                    <div class="rating-bar">
                        <span class="star">3★</span>
                        <div class="bar-container">
                            <div class="bar" style="width: 30%;"></div>
                        </div>
                    </div>
                    <div class="rating-bar">
                        <span class="star">2★</span>
                        <div class="bar-container">
                            <div class="bar" style="width: 15%;"></div>
                        </div>
                    </div>
                </div>
                <div class="total-ratings">4,83,459 ratings</div>
            </div>
            <div class="trust-box">
                <h3>Trusted by</h3>
                <div class="trust-content">
                    <div class="laurel-wreath">
                        <img src="images/tr.png" alt="Trust Badge">
                    </div>
                   
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>About TrainDekho</h3>
                <ul>
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Contact Us</a></li>
                    <li><a href="#">Careers</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Products</h3>
                <ul>
                    <li><a href="#">Train Tickets</a></li>
                    <li><a href="#">Holiday Packages</a></li>
                    <li><a href="#">Travel Insurance</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Help & Support</h3>
                <ul>
                    <li><a href="#">FAQ</a></li>
                    <li><a href="#">Terms & Conditions</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Connect With Us</h3>
                <ul>
                    <li><a href="#">Facebook</a></li>
                    <li><a href="#">Twitter</a></li>
                    <li><a href="#">Instagram</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 TrainDekho. All rights reserved.</p>
        </div>
    </footer>

    <!-- Add this right after the <body> tag -->
    <div class="background-slideshow">
        <div class="background-slide"></div>
        <div class="background-slide"></div>
        <div class="background-slide"></div>
        <div class="background-slide"></div>
        <div class="background-slide"></div>
    </div>

    <script>
    // Remove or comment out this function
    // function redirectToLogin(event) {
    //     event.preventDefault(); // Prevent the form from submitting normally
    //     window.location.href = 'login1.php'; // Redirect to login page
    //     return false;
    // }

    // Set minimum date to today when page loads
    window.onload = function() {
        const today = new Date();
        const maxDate = new Date();
        maxDate.setMonth(maxDate.getMonth() + 4); // Allow booking up to 4 months in advance

        // Format dates as YYYY-MM-DD
        const todayFormatted = today.toISOString().split('T')[0];
        const maxDateFormatted = maxDate.toISOString().split('T')[0];

        const travelDateInput = document.getElementById('travelDate');
        travelDateInput.min = todayFormatted;
        travelDateInput.max = maxDateFormatted;
        
        // Set default value to today
        travelDateInput.value = todayFormatted;
    }

    function validateDate(input) {
        const selectedDate = new Date(input.value);
        const today = new Date();
        const maxDate = new Date();
        maxDate.setMonth(maxDate.getMonth() + 4);
        
        // Reset time part for accurate date comparison
        selectedDate.setHours(0,0,0,0);
        today.setHours(0,0,0,0);
        
        const errorElement = document.getElementById('dateError');

        if (selectedDate < today) {
            errorElement.textContent = "Please select a future date";
            input.setCustomValidity("Please select a future date");
        } else if (selectedDate > maxDate) {
            errorElement.textContent = "Booking only available for next 4 months";
            input.setCustomValidity("Booking only available for next 4 months");
        } else {
            errorElement.textContent = "";
            input.setCustomValidity("");
        }
    }

    // Add event listener for form submission
    document.querySelector('.search-form').addEventListener('submit', function(event) {
        const travelDateInput = document.getElementById('travelDate');
        validateDate(travelDateInput);
        
        if (!travelDateInput.checkValidity()) {
            event.preventDefault();
            return false;
        }
    });
    </script>
</body>
</html> 
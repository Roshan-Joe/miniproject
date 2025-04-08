<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "collegep";

$conn = new mysqli($servername, $username, $password, $dbname);

// Get unique stations from the database
$sql = "SELECT DISTINCT source_station, destination_station 
        FROM trains 
        WHERE status = 'active'";
$result = $conn->query($sql);

$stations = array();
while($row = $result->fetch_assoc()) {
    if(!in_array($row['source_station'], $stations)) {
        $stations[] = $row['source_station'];
    }
    if(!in_array($row['destination_station'], $stations)) {
        $stations[] = $row['destination_station'];
    }
}
$conn->close();
?>

<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Train Booking</title>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <style>
        .animated-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background-image: url('images/train1bg.jpg');
            background-size: cover;
            background-position: center;
            animation: fadeInOut 20s infinite;
            opacity: 0.15;
        }

        @keyframes fadeInOut {
            0% { opacity: 0.1; }
            50% { opacity: 0.2; }
            100% { opacity: 0.1; }
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f8f8;
        }

        header {
            background-color: #003366;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header nav a {
            color: white;
            text-decoration: none;
            margin: 0 10px;
        }

        .booking-section {
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)),
                url('images/train1bg.jpg');
            background-size: cover;
            background-position: center;
            padding: 40px 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .booking-container {
            border: none;
            padding: 30px;
            border-radius: 15px;
            background-color: rgba(255, 255, 255, 0.95);
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
        }

        .booking-container h1 {
            color: #1a237e;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.5em;
            text-transform: uppercase;
            letter-spacing: 2px;
            border-bottom: 3px solid #ff5722;
            padding-bottom: 10px;
        }

        .input-group {
            margin-bottom: 25px;
            position: relative;
        }

        .input-group input, 
        .input-group select {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: rgba(255, 255, 255, 0.9);
        }

        .input-group input:focus,
        .input-group select:focus {
            border-color: #1a237e;
            box-shadow: 0 0 10px rgba(26, 35, 126, 0.2);
            outline: none;
        }

        .swap-icon {
            text-align: center;
            margin: 15px 0;
            font-size: 24px;
            color: #1a237e;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .swap-icon:hover {
            transform: scale(1.2);
        }

        .checkbox-group {
            display: flex;
            gap: 20px;
            margin: 25px 0;
            flex-wrap: wrap;
            padding: 15px;
            background-color: rgba(26, 35, 126, 0.05);
            border-radius: 8px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .checkbox-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .button-group {
            display: flex;
            gap: 20px;
            margin-top: 30px;
        }

        .search-btn,
        .booking-btn {
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s ease;
            flex: 1;
        }

        .search-btn {
            background-color: #ff5722;
            color: white;
        }

        .booking-btn {
            background-color: #ff9800;
            color: white;
        }

        .search-btn:hover,
        .booking-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .search-btn:active,
        .booking-btn:active {
            transform: translateY(1px);
        }

        .search-btn.loading {
            position: relative;
            pointer-events: none;
        }

        .search-btn.loading::after {
            content: "";
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin: -10px 0 0 -10px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .booking-form {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 30%;
        }

        .booking-form h2 {
            margin-top: 0;
        }

        .booking-form input, .booking-form select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .booking-form button {
            background-color: orange;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .cancellation-form button.cancel-button {
            background-color: #dc3545;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .cancellation-form button.cancel-button:hover {
            background-color: #c82333;
        }

        .train-image {
            width: 30%;
        }

        .train-image img {
            width: 100%;
            border-radius: 5px;
        }

        .additional-services {
            text-align: center;
            padding: 20px 10%;
        }

        .additional-services h3 {
            margin-bottom: 20px;
        }

        .service-icons {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }

        .service-icons div {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            width: 100px;
        }

        .service-icons div img {
            width: 50px;
            height: 50px;
            margin-bottom: 10px;
        }

        .holiday-packages {
            padding: 20px 10%;
        }

        .holiday-packages h3 {
            text-align: center;
            margin-bottom: 20px;
        }

        .packages {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .package {
            width: 30%;
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .package img {
            width: 100%;
        }

        .package-details {
            padding: 10px;
        }

        footer {
            background-color: #003366;
            color: white;
            padding: 20px;
            text-align: center;
        }

        footer a {
            color: orange;
            text-decoration: none;
        }

        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: white;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1000;
            border-radius: 4px;
            top: 100%;
            right: 0;
        }

        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        .dropdown-content a:hover {
            background-color: #f1f1f1;
            border-radius: 4px;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        .dropbtn:hover {
            color: #f1f1f1;
        }

        .ui-autocomplete {
            max-height: 200px;
            overflow-y: auto;
            overflow-x: hidden;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            z-index: 1000;
        }

        .ui-autocomplete .ui-menu-item {
            padding: 8px 15px;
            font-size: 14px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
        }

        .ui-autocomplete .ui-menu-item:last-child {
            border-bottom: none;
        }

        .ui-autocomplete .ui-menu-item:hover {
            background-color: #f5f5f5;
        }

        .ui-helper-hidden-accessible {
            display: none;
        }

        #from-station, #to-station {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        #from-station:focus, #to-station:focus {
            outline: none;
            border-color: #003366;
            box-shadow: 0 0 5px rgba(0,51,102,0.2);
        }

        .search-results {
            margin-top: 30px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .table-responsive {
            overflow-x: auto;
        }

        .train-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .train-table th,
        .train-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .train-table th {
            background-color: #003366;
            color: white;
            font-weight: 500;
        }

        .train-table tr:hover {
            background-color: #f5f5f5;
        }

        .book-btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .book-btn:hover {
            background-color: #45a049;
        }

        .no-trains {
            text-align: center;
            padding: 40px 20px;
            margin-top: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .no-trains-img {
            width: 200px;
            margin-bottom: 20px;
        }

        .no-trains h2 {
            color: #333;
            margin-bottom: 10px;
        }

        .no-trains p {
            color: #666;
            margin: 5px 0;
        }

        @media (max-width: 768px) {
            .train-table {
                font-size: 14px;
            }
            
            .train-table th,
            .train-table td {
                padding: 8px 10px;
            }
            
            .book-btn {
                padding: 6px 12px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="animated-background"></div>
    <header>
        <div style="display: flex; align-items: center;">
            <img src="images/TRAIN MANIA YOU TUBE CHANNEL LOGO.png" alt="IRCTC Logo" style="height: 60px;">
            <h1 style="margin: 0; padding: 0 10px;">TrainDekho</h1>
        </div>
        <nav>
            <?php
            if (isset($_SESSION['username'])) {
                echo '
                <div class="dropdown" style="position: relative; display: inline-block;">
                    <a class="dropbtn" style="color: white; cursor: pointer; text-decoration: none; padding: 10px;">
                        Welcome, ' . htmlspecialchars($_SESSION['username']) . ' ▼
                    </a>
                    <div class="dropdown-content">
                        <a href="profile.php">My Profile</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </div>';
            } else {
                echo '<a href="login1.php">Login</a>';
                echo '<a href="register.php">Register</a>';
            }
            ?>
            <a href="food.php">E-CATERING</a>
            <a href="cancel_ticket.php" style="color: #ff5722;">Cancel Ticket</a>
            <a href="HELP.html">Help & Support</a>
            <a href="contactus.html">Contact Us</a>
        </nav>
    </header>

    <div class="booking-section">
        <div class="booking-container">
            <h1>Train Search</h1>
            
            <div class="form-group">
                <label>From</label>
                <input type="text" id="from-station" name="from" placeholder="Enter city or station" required>
            </div>
            
            <div class="swap-icon">
                ⇅
            </div>

            <div class="form-group">
                <label>To</label>
                <input type="text" id="to-station" name="to" placeholder="Enter city or station" required>
            </div>

            <div class="input-group">
                <input type="date" id="journey-date">
            </div>

            <div class="input-group">
                <select id="class">
                    <option value="">All Classes</option>
                    <option value="1A">First AC</option>
                    <option value="2A">Second AC</option>
                    <option value="3A">Third AC</option>
                    <option value="SL">Sleeper</option>
                </select>
            </div>

            <div class="input-group">
                <select id="quota">
                    <option value="GENERAL">GENERAL</option>
                    <option value="TATKAL">TATKAL</option>
                    <option value="LADIES">LADIES</option>
                </select>
            </div>

            <div class="checkbox-group">
                <div class="checkbox-item">
                    <input type="checkbox" id="disability">
                    <label for="disability">Person With Disability Concession</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" id="flexible">
                    <label for="flexible">Flexible With Date</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" id="berth">
                    <label for="berth">Train with Available Berth</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" id="railway-pass">
                    <label for="railway-pass">Railway Pass Concession</label>
                </div>
            </div>

            <div class="button-group">
                <button type="button" class="search-btn" onclick="searchTrains()">Search</button>
                <button class="booking-btn">Easy Booking on AskDISHA</button>
            </div>
        </div>

        <div class="footer">
            <p>Customers can use enhanced interface for their IRCTC related queries!! <a href="https://equery.irctc.co.in">https://equery.irctc.co.in</a></p>
            <p>Customer Care Numbers : 14646/08044647999 /08035734999 (Language: Hindi & English)</p>
        </div>
    </div>

   

    <div class="additional-services">
        <h3>Have you not found the right one? Find a service suitable for you here.</h3>
        <div class="service-icons">
            <div>
                <img src="images/hotelimg.png" alt="Hotels1">
                <a href="contactus.html">Hotels</a>
            </div>
            <div>
                <img src="images/raildristi.png" alt="Rail Drishti">
                <p>Rail Drishti</p>
            </div>
            <div>
                <img src="images/e-cat.png" alt="E-Catering">
                <p>E-Catering</p>
            </div>
            <!-- <div>
                <img src="images/Bus.png" alt="Bus">
                <p>Bus</p>
            </div>
            <div>
                <img src="images/Holiday Packages.png" alt="Holiday Packages">
                <p>Holiday Packages</p>
            </div>
            <div>
                <img src="images/Tourist Train.png" alt="Tourist Train">
                <p>Tourist Train</p>
            </div> -->
            
            <!-- <div>
                <img src="images/Charter Train.png" alt="Charter Train">
                <p>Charter Train</p>
            </div> -->
            <div>
                <img src="images/Gallery.png" alt="Gallery">
                <p>Gallery</p>
            </div>
        </div>
    </div>

    <div class="holiday-packages">
        <h3>HOLIDAYS</h3>
        <div class="packages">
            <div class="package">
                <img src="images/exterior.jpg" alt="Package 1">
                <div class="package-details">
                    <h4>Maharajas' Express</h4>
                    <p>Luxury train experiences.</p>
                </div>
            </div>
            <div class="package">
                <img src="images/Thailand.jpg" alt="Package 2">
                <div class="package-details">
                    <h4>International Packages</h4>
                    <p>Explore international destinations.</p>
                </div>
            </div>
            <div class="package">
                <img src="images/Kashmir.jpg" alt="Package 3">
                <div class="package-details">
                    <h4>Domestic Air Packages</h4>
                    <p>Best deals for domestic travel.</p>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <p>Customer Care Numbers: 14646 / 08044647999 / 08035734999</p>
    </footer>

    <script>
        function searchTrains() {
            const fromStation = document.getElementById('from-station').value;
            const toStation = document.getElementById('to-station').value;

            // Validate inputs
            if (!fromStation || !toStation) {
                alert('Please select both source and destination stations');
                return;
            }

            if (fromStation === toStation) {
                alert('Source and destination cannot be the same!');
                return;
            }

            // Redirect to search results page with parameters
            window.location.href = `search_results.php?from=${encodeURIComponent(fromStation)}&to=${encodeURIComponent(toStation)}`;
        }

        const swapIcon = document.querySelector('.swap-icon');
        swapIcon.innerHTML = '⇅';
        swapIcon.addEventListener('mouseover', function() {
            this.style.transform = 'scale(1.2) rotate(180deg)';
        });
        swapIcon.addEventListener('mouseout', function() {
            this.style.transform = 'scale(1) rotate(0deg)';
        });

        // Add station array and autocomplete functionality
        $(document).ready(function() {
            const stations = [
                "Thiruvananthapuram",
                "Kozhikode",
                "Ernakulam",
                "Palakkad",
                "Kannur",
                "Kollam",
                "Shoranur",
                "Kottayam",
                "Mangalore",
                "Alappuzha",
                "Thrissur",
                "Aluva",
                "Nilambur",
                "Malappuram",
                "Kasaragod",
                "Kayamkulam"
            ];

            // Function to prevent same source and destination selection
            function preventSameStation() {
                const fromStation = $("#from-station").val();
                const toStation = $("#to-station").val();
                
                if(fromStation === toStation && fromStation !== "") {
                    alert("Source and destination cannot be the same!");
                    $("#to-station").val("");
                }
            }

            // Autocomplete for From station
            $("#from-station").autocomplete({
                source: stations,
                minLength: 1,
                select: function(event, ui) {
                    setTimeout(preventSameStation, 100);
                }
            });

            // Autocomplete for To station
            $("#to-station").autocomplete({
                source: stations,
                minLength: 1,
                select: function(event, ui) {
                    setTimeout(preventSameStation, 100);
                }
            });

            // Additional validation on input
            $("#from-station, #to-station").on('input', preventSameStation);
        });
    </script>

    <?php
    // Add this after your form submission handling

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $from = $_POST['from'];
        $to = $_POST['to'];
        
        // Database connection
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "collegep";

        $conn = new mysqli($servername, $username, $password, $dbname);

        // Search for trains
        $sql = "SELECT * FROM trains 
                WHERE source_station = ? 
                AND destination_station = ? 
                AND status = 'active'";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $from, $to);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Show results in a table
            ?>
            <div class="search-results">
                <h2>Available Trains</h2>
                <div class="table-responsive">
                    <table class="train-table">
                        <thead>
                            <tr>
                                <th>Train Number</th>
                                <th>Train Name</th>
                                <th>Departure</th>
                                <th>Arrival</th>
                                <th>Duration</th>
                                <th>AC Fare</th>
                                <th>Sleeper Fare</th>
                                <th>Available Days</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($train = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($train['train_number']); ?></td>
                                <td><?php echo htmlspecialchars($train['train_name']); ?></td>
                                <td><?php echo date('h:i A', strtotime($train['departure_time'])); ?></td>
                                <td><?php echo date('h:i A', strtotime($train['arrival_time'])); ?></td>
                                <td><?php echo htmlspecialchars($train['duration']); ?></td>
                                <td>₹<?php echo htmlspecialchars($train['ac_fare']); ?></td>
                                <td>₹<?php echo htmlspecialchars($train['sleeper_fare']); ?></td>
                                <td><?php echo htmlspecialchars($train['running_days']); ?></td>
                                <td>
                                    <a href="booking.php?train_id=<?php echo $train['train_id']; ?>" class="book-btn">Book Now</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php
        } else {
            // Show no trains available message
            ?>
            <div class="no-trains">
                <img src="images/no-trains.png" alt="No Trains Available" class="no-trains-img">
                <h2>No Trains Available</h2>
                <p>Sorry, we couldn't find any trains between <?php echo htmlspecialchars($from); ?> and <?php echo htmlspecialchars($to); ?>.</p>
                <p>Try searching for a different route or date.</p>
            </div>
            <?php
        }
        
        $conn->close();
    }
    ?>
</body>
</html>

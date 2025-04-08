<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: rose.php");
    exit();
}

// Get booking details from POST with default values
$train_id = $_POST['train_id'] ?? null;
$passenger_names = $_POST['passenger_name'] ?? [];
$passenger_ages = $_POST['passenger_age'] ?? [];
$passenger_genders = $_POST['passenger_gender'] ?? [];
$passenger_categories = $_POST['passenger_category'] ?? [];
$selected_class = $_POST['selected_class'] ?? '';
$total_amount = isset($_POST['total_amount']) ? $_POST['total_amount'] : 0;

// Validate required data
if (!$train_id || empty($passenger_names)) {
    $_SESSION['error'] = "Missing required booking information";
    header("Location: rose.php");
    exit();
}

// Convert amount to paise (Razorpay expects amount in smallest currency unit)
$amount_in_paise = $total_amount * 100;

// Store booking details in session for later use
$_SESSION['current_booking'] = [
    'train_id' => $train_id,
    'passenger_names' => $passenger_names,
    'passenger_ages' => $passenger_ages,
    'passenger_genders' => $passenger_genders,
    'passenger_categories' => $passenger_categories,
    'selected_class' => $selected_class,
    'total_amount' => $total_amount,
    'food' => $_POST['food'] ?? []
];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - TrainDekho</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Add Razorpay SDK -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f7fa;
            color: #333;
        }

        .header {
            background: linear-gradient(135deg, #003366 0%, #004080 100%);
            color: white;
            padding: 1rem;
        }

        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .payment-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .amount-display {
            text-align: center;
            padding: 20px;
            margin: 20px 0;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .amount {
            font-size: 32px;
            font-weight: 600;
            color: #003366;
        }

        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .payment-method {
            border: 2px solid #eef2f7;
            border-radius: 8px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .payment-method:hover {
            border-color: #003366;
            transform: translateY(-2px);
        }

        .payment-method.selected {
            border-color: #4CAF50;
            background: #f1f8e9;
        }

        .payment-method img {
            width: 60px;
            height: 60px;
            object-fit: contain;
            margin-bottom: 10px;
        }

        .payment-details {
            margin-top: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #666;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
        }

        .card-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 15px;
        }

        .pay-btn {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 25px;
            font-size: 18px;
            font-weight: 500;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .pay-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(76,175,80,0.3);
        }

        .secure-badge {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }

        .secure-badge i {
            color: #4CAF50;
            margin-right: 5px;
        }

        .razorpay-btn {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 25px;
            font-size: 18px;
            font-weight: 500;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .razorpay-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(76,175,80,0.3);
        }

        .payment-success {
            text-align: center;
            padding: 20px;
            background: #e8f5e9;
            border-radius: 8px;
            margin-top: 20px;
            display: none;
        }

        .payment-success h2 {
            color: #2e7d32;
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            .card-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="text-align: center;">Complete Your Payment</h1>
    </div>

    <div class="container">
        <div class="payment-card">
            <div class="amount-display">
                <p>Total Amount to Pay</p>
                <div class="amount">₹<?php echo number_format($total_amount, 2); ?></div>
            </div>

            <button id="razorpay-button" class="razorpay-btn">Pay with Razorpay</button>

            <div id="payment-success" class="payment-success">
                <h2>Payment Successful!</h2>
                <p>Your booking has been confirmed.</p>
                <p>Payment ID: <span id="payment-id"></span></p>
            </div>

            <div class="secure-badge">
                <i class="fas fa-lock"></i> Your payment is secure and encrypted
            </div>
        </div>
    </div>

    <script>
        var options = {
            "key": "rzp_test_KNSzyXPRaX37bM",
            "amount": "<?php echo $amount_in_paise; ?>",
            "currency": "INR",
            "name": "TrainDekho",
            "description": "Train Ticket Booking",
            "image": "images/TRAIN MANIA YOU TUBE CHANNEL LOGO.png",
            "handler": function (response){
                document.getElementById('payment-success').style.display = 'block';
                document.getElementById('payment-id').textContent = response.razorpay_payment_id;
                savePaymentDetails(response.razorpay_payment_id);
            },
            "prefill": {
                "name": "<?php echo htmlspecialchars($passenger_names[0] ?? ''); ?>",
                "email": "<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>",
                "contact": "<?php echo htmlspecialchars($_SESSION['mobile'] ?? ''); ?>"
            },
            "theme": {
                "color": "#213d77"
            }
        };

        var rzp = new Razorpay(options);

        document.getElementById('razorpay-button').onclick = function(e){
            rzp.open();
            e.preventDefault();
        }

        function savePaymentDetails(paymentId) {
            // Create form data
            var formData = new FormData();
            formData.append('payment_id', paymentId);
            formData.append('train_id', '<?php echo $train_id; ?>');
            formData.append('amount', '<?php echo $total_amount; ?>');
            
            // Add food order details from session
            const foodOrders = <?php echo json_encode($_SESSION['current_booking']['food'] ?? []); ?>;
            formData.append('food_orders', JSON.stringify(foodOrders));

            // For debugging
            console.log('Sending payment details to server:', {
                payment_id: paymentId,
                train_id: '<?php echo $train_id; ?>',
                amount: '<?php echo $total_amount; ?>'
            });

            // Show processing message
            document.getElementById('payment-success').style.display = 'block';
            document.getElementById('payment-success').innerHTML = '<h2>Processing Payment...</h2><p>Please wait while we confirm your booking.</p>';

            // Send to server
            fetch('save_payment.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Server response status:', response.status);
                return response.json().catch(error => {
                    console.error('Error parsing JSON:', error);
                    return { success: false, error: 'Invalid JSON response' };
                });
            })
            .then(data => {
                console.log('Server response data:', data);
                
                if(data.success) {
                    // Show success message
                    document.getElementById('payment-success').innerHTML = '<h2>Payment Successful!</h2><p>Your booking has been confirmed.</p><p>Payment ID: ' + paymentId + '</p><p>PNR: ' + (data.pnr || '') + '</p><p>Redirecting to booking details...</p>';
                    
                    // Redirect to booking confirmation page after 2 seconds
                    setTimeout(() => {
                        window.location.href = 'booking_confirmation.php?payment_id=' + paymentId;
                    }, 2000);
                } else {
                    console.error('Payment save failed:', data.error);
                    document.getElementById('payment-success').innerHTML = '<h2>Payment processed, but booking failed</h2><p>Error: ' + (data.error || 'Unknown error') + '</p><p>Please contact support with your payment ID: ' + paymentId + '</p>';
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                document.getElementById('payment-success').innerHTML = '<h2>Communication Error</h2><p>Your payment was processed, but we had trouble confirming your booking. Please contact support with your payment ID: ' + paymentId + '</p>';
                
                // Still redirect to booking confirmation page after 5 seconds
                setTimeout(() => {
                    window.location.href = 'booking_confirmation.php?payment_id=' + paymentId;
                }, 5000);
            });
        }
    </script>
</body>
</html> 
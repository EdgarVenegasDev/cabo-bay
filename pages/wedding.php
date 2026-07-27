<?php
require_once '../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wedding Transportation | Cabo Bay</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/utilities.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body class="wedding-page">

<?php include '../includes/navbar.php'; ?>

<section class="wedding-hero">
    <div class="wedding-overlay"></div>
    <img src="../assets/media/adventure.jpg" class="wedding-bg" alt="Wedding">
    <div class="wedding-content">
        <p class="wedding-subtitle">Welcome to Los Cabos</p>
        <h1>Special Wedding</h1>
        <span class="wedding-date">May, 2026</span>
    </div>
</section>

<section class="wedding-booking">
    <div class="container">
        <div class="wedding-layout">
            <div class="wedding-info">
                <h2>Transportation Details</h2>
                <p>Please complete the form so we can coordinate your airport transportation during the wedding weekend.</p>

                <div class="pricing-box">
                    <div class="pricing-item">
                        <h3>Arrival Transfer</h3>
                        <p>SJD Airport → Hotel</p>
                        <span class="price">$85 USD</span>
                    </div>
                    <div class="pricing-item premium">
                        <h3>Round Trip</h3>
                        <p>Airport + Return Transfer</p>
                        <span class="price">$160 USD</span>
                    </div>
                </div>

                <ul>
                    <li>Private airport transportation</li>
                    <li>Luxury vehicles</li>
                    <li>Personalized service</li>
                    <li>Flight tracking included</li>
                </ul>
                <div class="wedding-note">
                    Transportation schedules will be coordinated personally with each guest prior to arrival.
                </div>
            </div>

            <div class="wedding-form-card">
                <form action="../api/process_booking.php" method="POST" id="weddingForm">
                    <input type="hidden" name="booking_type" value="wedding">
                    <input type="hidden" name="price" id="totalPrice" value="85">

                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="name" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label>Phone *</label>
                            <input type="tel" name="phone" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Arrival Date *</label>
                            <input type="date" name="date" min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Arrival Time *</label>
                            <input type="time" name="time" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Flight Number (arrival) *</label>
                            <input type="text" name="flight_number" placeholder="AA1234" required>
                        </div>
                        <div class="form-group">
                            <label>Passengers *</label>
                            <input type="number" name="passengers" min="1" max="10" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Hotel / Airbnb *</label>
                        <input type="text" name="hotel" required>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="roundtrip" id="roundtripCheckbox" value="1">
                            I would also like return transportation to the airport (+$75 USD)
                        </label>
                    </div>

                    <div id="returnSection" style="display:none;">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Return Date *</label>
                                <input type="date" name="return_date">
                            </div>
                            <div class="form-group">
                                <label>Return Pickup Time *</label>
                                <input type="time" name="return_time">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Return Flight Number (optional)</label>
                            <input type="text" name="return_flight_number" placeholder="e.g., UA5678">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Special Requests</label>
                        <textarea name="special_requests" rows="4"></textarea>
                    </div>

                    <div class="wedding-total" id="weddingTotal">Total: $85 USD</div>

                    <button type="submit" class="btn btn-primary btn-full">Reserve My Transportation</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>

<script>
const roundtripCheckbox = document.getElementById('roundtripCheckbox');
const returnSection = document.getElementById('returnSection');
const totalPriceInput = document.getElementById('totalPrice');
const weddingTotalDiv = document.getElementById('weddingTotal');

roundtripCheckbox.addEventListener('change', function () {
    if (this.checked) {
        returnSection.style.display = 'block';
        totalPriceInput.value = 160;
        weddingTotalDiv.innerHTML = 'Total: $160 USD';
        // Hacer opcionales los campos de retorno (o required si se desea)
        document.querySelector('input[name="return_date"]').required = true;
        document.querySelector('input[name="return_time"]').required = true;
    } else {
        returnSection.style.display = 'none';
        totalPriceInput.value = 85;
        weddingTotalDiv.innerHTML = 'Total: $85 USD';
        document.querySelector('input[name="return_date"]').required = false;
        document.querySelector('input[name="return_time"]').required = false;
    }
});
</script>

</body>
</html>
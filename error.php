<?php
session_start();
$error = $_SESSION['booking_error'] ?? 'An unexpected error occurred. Please try again.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error | Cabo Bay</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/components.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<section class="error-section" style="padding: 120px 20px; text-align: center; min-height: 60vh;">
    <div class="container">
        <h1>⚠️ Something went wrong</h1>
        <p><?= htmlspecialchars($error) ?></p>
        <p>Please try again or contact us directly.</p>
        <br>
        <a href="javascript:history.back()" class="btn btn-primary">Go Back</a>
        <a href="index.php" class="btn btn-secondary">Home</a>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
</body>
</html>
<?php
unset($_SESSION['booking_error']);
?>
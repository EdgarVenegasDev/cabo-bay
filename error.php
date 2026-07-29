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
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/tailwind.css">
</head>
<body class="font-sans text-slate-900 antialiased">
<?php include 'includes/navbar.php'; ?>

<section class="min-h-[70vh] bg-slate-50 flex items-center justify-center px-6 pt-32 pb-20">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-xl max-w-md w-full p-8 text-center">

        <div class="w-16 h-16 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-5">
            <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
        </div>

        <h1 class="font-serif text-2xl font-bold text-navy mb-2">Something went wrong</h1>
        <p class="text-slate-500 text-sm mb-1"><?= htmlspecialchars($error) ?></p>
        <p class="text-slate-400 text-xs mb-6">Please try again or contact us directly.</p>

        <div class="flex flex-wrap justify-center gap-3">
            <a href="javascript:history.back()" class="bg-navy text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-navy-dark transition-colors">Go Back</a>
            <a href="/index.php" class="border border-navy text-navy px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-navy hover:text-white transition-colors">Home</a>
        </div>

    </div>
</section>

<?php include 'includes/footer.php'; ?>
</body>
</html>
<?php
unset($_SESSION['booking_error']);
?>

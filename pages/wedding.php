<?php
require_once '../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Los Cabos Wedding Transportation | Cabo Bay</title>
    <meta name="description" content="Private wedding transportation in Los Cabos. Airport transfers for your wedding guests with luxury SUVs, flight tracking, and personalized coordination.">
    <link rel="canonical" href="https://cabo-bay.com/pages/wedding.php">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Jost:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Jost:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"></noscript>
    <link rel="stylesheet" href="/assets/css/tailwind.css">
</head>
<body class="font-sans text-slate-900 antialiased bg-slate-50">

<?php include '../includes/navbar.php'; ?>

<section class="relative h-[60vh] min-h-[420px] flex items-center justify-center overflow-hidden">
    <img src="/assets/media/adventure.jpg" class="absolute inset-0 w-full h-full object-cover" alt="Los Cabos wedding transportation - scenic coastline" loading="eager">
    <div class="absolute inset-0 bg-gradient-to-b from-navy-dark/70 to-navy-dark/50"></div>
    <div class="relative text-center px-6">
        <p class="text-white/80 text-sm uppercase tracking-widest mb-3">Welcome to Los Cabos</p>
        <h1 class="font-serif text-5xl lg:text-6xl text-white font-semibold mb-3">Wedding Transportation</h1>
        <span class="inline-block text-white/90 text-sm border border-white/30 rounded-full px-4 py-1.5">Private airport transfers for your special weekend</span>
    </div>
</section>

<section class="py-16 px-6">
    <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-10 items-start">

        <div>
            <h2 class="font-serif text-3xl text-navy font-semibold mb-3">Transportation Details</h2>
            <p class="text-slate-500 mb-8">Please complete the form so we can coordinate your Los Cabos airport transportation during the wedding weekend.</p>

            <div class="grid grid-cols-2 gap-4 mb-8">
                <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5">
                    <h3 class="font-semibold text-navy text-sm mb-1">Arrival Transfer</h3>
                    <p class="text-xs text-slate-500 mb-3">SJD Airport -&gt; Hotel</p>
                    <span class="text-2xl font-bold text-navy">$85 <span class="text-sm font-medium text-slate-400">USD</span></span>
                </div>
                <div class="bg-navy rounded-xl p-5 text-white">
                    <h3 class="font-semibold text-sm mb-1">Round Trip</h3>
                    <p class="text-xs text-white/70 mb-3">Airport + Return Transfer</p>
                    <span class="text-2xl font-bold">$160 <span class="text-sm font-medium text-white/70">USD</span></span>
                </div>
            </div>

            <ul class="space-y-2.5 text-sm text-slate-600 mb-8">
                <li class="flex items-start gap-2">
                    <span class="text-navy font-bold mt-0.5">v</span>
                    <span>Private Los Cabos airport transportation</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-navy font-bold mt-0.5">v</span>
                    <span>Luxury SUV vehicles</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-navy font-bold mt-0.5">v</span>
                    <span>Personalized wedding-party service</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-navy font-bold mt-0.5">v</span>
                    <span>Flight tracking included</span>
                </li>
            </ul>

            <div class="bg-amber-50 border border-amber-100 text-amber-800 text-sm rounded-xl p-4">
                Transportation schedules will be coordinated personally with each guest prior to arrival.
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 lg:p-8">
            <form action="/api/process_booking.php" method="POST" id="weddingForm" class="space-y-5">
                <input type="hidden" name="booking_type" value="wedding">
                <input type="hidden" name="trip_type" id="tripTypeInput" value="oneway">
                <input type="hidden" name="price" id="totalPrice" value="85">

                <div>
                    <label class="text-sm font-medium text-slate-700 block mb-1.5">Full Name *</label>
                    <input type="text" name="name" required
                           class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-slate-700 block mb-1.5">Email *</label>
                        <input type="email" name="email" required
                               class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700 block mb-1.5">Phone *</label>
                        <input type="tel" name="phone" required
                               class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-slate-700 block mb-1.5">Arrival Date *</label>
                        <input type="date" name="date" min="<?= date('Y-m-d') ?>" required
                               class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700 block mb-1.5">Arrival Time *</label>
                        <input type="time" name="time" required
                               class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-slate-700 block mb-1.5">Flight Number (arrival) *</label>
                        <input type="text" name="flight_number" placeholder="AA1234" required
                               class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700 block mb-1.5">Passengers *</label>
                        <input type="number" name="passengers" min="1" max="10" required
                               class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700 block mb-1.5">Hotel / Airbnb *</label>
                    <input type="text" name="hotel" required
                           class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                </div>

                <label class="flex items-start gap-2.5 text-sm text-slate-600 cursor-pointer">
                    <input type="checkbox" name="roundtrip" id="roundtripCheckbox" value="1" class="mt-0.5">
                    I would also like return transportation to the airport (+$75 USD)
                </label>

                <div id="returnSection" class="space-y-5 hidden">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-slate-700 block mb-1.5">Return Date *</label>
                            <input type="date" name="return_date"
                                   class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700 block mb-1.5">Return Pickup Time *</label>
                            <input type="time" name="return_time"
                                   class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700 block mb-1.5">Return Flight Number (optional)</label>
                        <input type="text" name="return_flight_number" placeholder="e.g., UA5678"
                               class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700 block mb-1.5">Special Requests</label>
                    <textarea name="special_requests" rows="3"
                              class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy resize-y"></textarea>
                </div>

                <div id="weddingTotal" class="text-lg font-bold text-navy">Total: $85 USD</div>

                <button type="submit" class="w-full bg-navy text-white font-semibold py-3.5 rounded-lg hover:bg-navy-dark hover:-translate-y-0.5 transition-all">
                    Reserve My Transportation
                </button>
            </form>
        </div>

    </div>
</section>

<?php include '../includes/footer.php'; ?>

<script>
(function () {
    const roundtripCheckbox = document.getElementById('roundtripCheckbox');
    const returnSection     = document.getElementById('returnSection');
    const totalPriceInput   = document.getElementById('totalPrice');
    const tripTypeInput     = document.getElementById('tripTypeInput');
    const weddingTotalDiv   = document.getElementById('weddingTotal');
    const returnDateInput   = document.querySelector('input[name="return_date"]');
    const returnTimeInput   = document.querySelector('input[name="return_time"]');

    roundtripCheckbox.addEventListener('change', function () {
        if (this.checked) {
            returnSection.classList.remove('hidden');
            totalPriceInput.value = 160;
            tripTypeInput.value = 'roundtrip';
            weddingTotalDiv.textContent = 'Total: $160 USD';
            returnDateInput.required = true;
            returnTimeInput.required = true;
        } else {
            returnSection.classList.add('hidden');
            totalPriceInput.value = 85;
            tripTypeInput.value = 'oneway';
            weddingTotalDiv.textContent = 'Total: $85 USD';
            returnDateInput.required = false;
            returnTimeInput.required = false;
        }
    });
})();
</script>

</body>
</html>

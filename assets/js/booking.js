/**
 * booking.js
 * Maneja la lógica de booking.php (formulario completo)
 * y booking-form.php (widget rápido del hero).
 */

document.addEventListener('DOMContentLoaded', function () {

    /* =========================================================
       BOOKING.PHP — formulario completo
       ========================================================= */
    const fullForm = document.getElementById('fullBookingForm');
    if (fullForm) {
        const tripTypeInput  = fullForm.querySelector('input[name="trip_type"]');
        const returnSection  = document.getElementById('returnSection');
        const returnDate     = document.getElementById('return_date');
        const returnTime     = document.getElementById('return_time');
        const returnFlight   = document.getElementById('return_flight_number');
        const serviceDate    = document.getElementById('date');

        /* Leer trip_type del hidden o del GET pre-cargado */
        function getTripType() {
            /* Puede ser hidden input o data-attr del form */
            const hidden = fullForm.querySelector('input[name="trip_type"]');
            return hidden ? hidden.value : 'oneway';
        }

        function applyTripType(type) {
            const isRound = (type === 'roundtrip');
            if (returnSection) {
                returnSection.style.display = isRound ? 'block' : 'none';
            }
            if (returnDate)   returnDate.required   = isRound;
            if (returnTime)   returnTime.required   = isRound;
            /* Vuelo de regreso: requerido solo si roundtrip */
            if (returnFlight) returnFlight.required = isRound;
        }

        /* Aplicar estado inicial (viene pre-cargado desde GET) */
        applyTripType(getTripType());

        /* Si el form tiene radios de trip_type (no llega pre-seleccionado) */
        fullForm.querySelectorAll('input[name="trip_type"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                applyTripType(this.value);
            });
        });

        /* Validar que return_date >= date */
        if (returnDate && serviceDate) {
            returnDate.addEventListener('change', function () {
                if (this.value && serviceDate.value && this.value < serviceDate.value) {
                    alert('Return date cannot be before the service date.');
                    this.value = '';
                }
            });
        }

        /* Fecha mínima = hoy */
        const today = new Date().toISOString().split('T')[0];
        if (serviceDate) serviceDate.min = today;
        if (returnDate)  returnDate.min  = today;

        /* Actualizar precio al cambiar hotel/area si viene sin pre-selección */
        const areaSelect = document.getElementById('areaSelect');
        const zoneHidden = document.getElementById('zoneHidden');
        const priceEl    = document.getElementById('priceEstimate');

        if (areaSelect) {
            areaSelect.addEventListener('change', function () {
                const opt  = this.options[this.selectedIndex];
                const zone = opt.getAttribute('data-zone') || '';
                if (zoneHidden) zoneHidden.value = zone;

                /* Actualizar precio en el resumen si existe */
                if (priceEl) {
                    const ow  = parseFloat(opt.getAttribute('data-one-way')  || 0);
                    const rt  = parseFloat(opt.getAttribute('data-round-trip')|| 0);
                    const type = getTripType();
                    priceEl.textContent = '$' + (type === 'roundtrip' ? rt : ow).toFixed(2);
                }
            });
        }
    }

    /* =========================================================
       BOOKING-FORM.PHP — widget rápido (hero)
       ========================================================= */
    const quickForm = document.getElementById('quickBookingForm');
    if (quickForm) {
        const pickupType      = document.getElementById('pickupType');
        const destinationGroup= document.getElementById('destinationGroup');
        const destinationSel  = document.getElementById('destinationSelect');
        const zoneHidden      = document.getElementById('zoneHidden');
        const returnSection   = document.getElementById('returnSection');
        const returnDateInput = document.getElementById('return_date');
        const dateInput       = document.getElementById('date');
        const modal           = document.getElementById('contactModal');

        /* Mostrar/ocultar destino según tipo de recogida */
        function toggleDestination() {
            const isAirport = pickupType && pickupType.value === 'airport';
            if (destinationGroup) destinationGroup.style.display = isAirport ? 'block' : 'none';
            if (destinationSel)   destinationSel.disabled = !isAirport;
            if (destinationSel)   destinationSel.required  =  isAirport;
        }
        if (pickupType) {
            pickupType.addEventListener('change', toggleDestination);
            toggleDestination();
        }

        /* Guardar zona al seleccionar área */
        if (destinationSel && zoneHidden) {
            destinationSel.addEventListener('change', function () {
                const opt = this.options[this.selectedIndex];
                zoneHidden.value = opt.getAttribute('data-zone') || '';
            });
        }

        /* Toggle one way / round trip */
        function toggleReturn() {
            const checked = quickForm.querySelector('input[name="trip_type"]:checked');
            const isRound = checked && checked.value === 'roundtrip';
            if (returnSection)   returnSection.style.display = isRound ? 'block' : 'none';
            if (returnDateInput) returnDateInput.required    = isRound;
        }
        quickForm.querySelectorAll('input[name="trip_type"]').forEach(function (r) {
            r.addEventListener('change', toggleReturn);
        });
        toggleReturn();

        /* Validar return_date */
        if (returnDateInput && dateInput) {
            returnDateInput.addEventListener('change', function () {
                if (this.value && dateInput.value && this.value < dateInput.value) {
                    alert('Return date cannot be before the service date.');
                    this.value = '';
                }
            });
        }

        /* Envío: abrir modal si es hotel/airbnb */
        quickForm.addEventListener('submit', function (e) {
            if (pickupType && pickupType.value === 'hotel_airbnb') {
                e.preventDefault();
                if (modal) modal.style.display = 'flex';
                return;
            }
            if (destinationSel && !destinationSel.value) {
                e.preventDefault();
                alert('Please select your destination hotel or area.');
            }
        });

        /* Cerrar modal */
        window.closeModal = function () {
            if (modal) modal.style.display = 'none';
        };
        document.querySelector('#contactModal .close')?.addEventListener('click', window.closeModal);
        window.addEventListener('click', function (e) {
            if (e.target === modal) window.closeModal();
        });
    }
});
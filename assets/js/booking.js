// Booking Modal Logic
document.addEventListener('DOMContentLoaded', () => {
    // Initialize Supabase if the config exists
    if (typeof initSupabase === 'function') {
        initSupabase();
    }

    // Open/Close Modal Logic (will be attached after header loads usually, or here if modal is in page)
    // The modal is currently in index.html, not dynamically loaded via JS (the content might be)
    // Actually index.html says: <div id="booking-modal" class="modal">...</div>

    const modal = document.getElementById('booking-modal');
    const closeBtn = document.querySelector('.close-modal');

    // We need to attach event delegation for "Book Now" buttons since header is dynamic
    document.addEventListener('click', (e) => {
        if (e.target.matches('a[href*="contact.html"].btn') || e.target.closest('a[href*="contact.html"].btn')) {
            // Optional: Intercept "Book Online" buttons to open modal instead of going to contact page
            // e.preventDefault();
            // openModal();
            // For now, let's assume specific buttons trigger it or we add a class .trigger-booking
        }

        // If we want to target buttons specifically designed for modal
        if (e.target.closest('.trigger-booking')) {
            e.preventDefault();
            openModal();
        }
    });

    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }

    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });

    function openModal() {
        if (modal) {
            modal.style.display = 'flex';
            // Load form if not loaded
            const modalContent = modal.querySelector('.modal-content');
            if (!modalContent.querySelector('form')) {
                loadBookingForm();
            }
        }
    }

    function closeModal() {
        if (modal) modal.style.display = 'none';
    }

    function loadBookingForm() {
        const modalContent = modal.querySelector('.modal-content');
        // Simple form injection
        const formHtml = `
            <form id="bookingForm" class="booking-form">
                <div style="margin-bottom: 1rem;">
                    <label style="display:block; margin-bottom:0.5rem">Name</label>
                    <input type="text" name="name" required style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius:4px;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display:block; margin-bottom:0.5rem">Email</label>
                    <input type="email" name="email" required style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius:4px;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display:block; margin-bottom:0.5rem">Phone</label>
                    <input type="tel" name="phone" required style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius:4px;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display:block; margin-bottom:0.5rem">Service Type</label>
                    <select name="service_type" required style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius:4px;">
                        <option value="">Select a service...</option>
                        <option value="Individual Therapy">Individual Therapy</option>
                        <option value="Couples Therapy">Couples Therapy</option>
                        <option value="Family Therapy">Family Therapy</option>
                        <option value="Assessment">Assessment</option>
                    </select>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display:block; margin-bottom:0.5rem">Preferred Date</label>
                    <input type="date" name="preferred_date" required style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius:4px;">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%">Confirm Booking Request</button>
            </form>
        `;

        // Append form after header (h3)
        const header = modalContent.querySelector('h3');
        if (header.nextElementSibling) {
            // already loaded?
        } else {
            header.insertAdjacentHTML('afterend', formHtml);
            attachFormHandler();
        }
    }

    function attachFormHandler() {
        const form = document.getElementById('bookingForm');
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = form.querySelector('button');
            const originalText = btn.innerText;
            btn.disabled = true;
            btn.innerText = 'Processing...';

            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            try {
                // 1. Save to Supabase
                const { error: dbError } = await supabaseClient
                    .from('bookings')
                    .insert([data]);

                if (dbError) throw dbError;

                // 2. Trigger notification (via Edge Function)
                const { error: notiError } = await sendNotification({
                    type: 'booking',
                    ...data
                });

                if (notiError) {
                    console.error('Notification error:', notiError);
                }

                alert('Booking request received! We will contact you shortly.');
                closeModal();
                form.reset();

            } catch (err) {
                console.error('Submission error:', err);
                alert('Error submitting booking: ' + (err.message || 'Please try again later.'));
            } finally {
                btn.disabled = false;
                btn.innerText = originalText;
            }
        });
    }
});

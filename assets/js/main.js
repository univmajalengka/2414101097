document.addEventListener('DOMContentLoaded', function () {
    // Mobile Menu Toggle
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    mobileMenuButton.addEventListener('click', () => mobileMenu.classList.toggle('hidden'));

    // Toast Notification
    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toast-message');
        toastMessage.textContent = message;
        toast.className = `fixed bottom-4 right-4 text-white px-6 py-3 rounded-lg shadow-lg transform transition-transform duration-300 z-50 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
        setTimeout(() => toast.classList.remove('translate-y-full'), 100);
        setTimeout(() => toast.classList.add('translate-y-full'), 3000);
    }

    // Add to Cart with AJAX
    const addToCartForms = document.querySelectorAll('form[data-add-to-cart]');
    addToCartForms.forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('cart.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('cart-count').textContent = data.cart_count;
                        showToast(data.message);
                    } else {
                        showToast(data.message, 'error');
                    }
                })
                .catch(error => showToast('Terjadi kesalahan.', 'error'));
        });
    });

    // Remove from Cart with AJAX
    document.querySelectorAll('a[data-remove-from-cart]').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            fetch(`cart.php?remove=${this.dataset.removeFromCart}`)
                .then(response => response.json())
                .then(data => data.status === 'success' ? window.location.href = 'cart.php' : showToast(data.message, 'error'))
                .catch(error => showToast('Terjadi kesalahan.', 'error'));
        });
    });

    // Scroll Reveal Animation
    const revealElements = document.querySelectorAll('.reveal');
    const revealOnScroll = () => {
        revealElements.forEach(element => {
            if (element.getBoundingClientRect().top < window.innerHeight - 100) {
                element.classList.add('revealed');
            }
        });
    };
    window.addEventListener('scroll', revealOnScroll);
    revealOnScroll();
});
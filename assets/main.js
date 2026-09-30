document.addEventListener('DOMContentLoaded', function () {

    // ===== Confirm before destructive actions =====
    document.addEventListener('click', function (e) {
        const el = e.target.closest('[data-confirm]');
        if (el && !confirm(el.dataset.confirm)) {
            e.preventDefault();
        }
    });

    // ===== Login-required modal =====
    const loggedIn = document.body.dataset.loggedIn === '1';
    const modal    = document.getElementById('loginModal');

    function openModal() {
        if (!modal) return;
        modal.hidden = false;
        document.body.classList.add('modal-open');
    }

    function closeModal() {
        if (!modal) return;
        modal.hidden = true;
        document.body.classList.remove('modal-open');
    }

    if (modal) {
        modal.querySelectorAll('[data-modal-close]').forEach(function (el) {
            el.addEventListener('click', closeModal);
        });
        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeModal();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.hidden) closeModal();
        });
    }

    // ===== Make product cards fully clickable =====
    document.querySelectorAll('.ProductCard').forEach(function (card) {
        const link = card.querySelector('a[href^="product.php"]');
        if (!link) return;

        card.style.cursor = 'pointer';
        card.addEventListener('click', function (e) {
            if (e.target.closest('form') || e.target.closest('button') || e.target.closest('a')) return;
            window.location.href = link.href;
        });
    });

    // ===== AJAX add-to-cart =====
    document.querySelectorAll('form[data-add-cart]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            if (!loggedIn) {
                openModal();
                return;
            }

            const button = form.querySelector('button[type="submit"]');
            const original = button ? button.textContent : '';

            if (button) {
                button.disabled = true;
                button.textContent = 'Adding...';
            }

            fetch('cart.php', {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                document.querySelectorAll('.CartCount').forEach(function (el) {
                    el.textContent = data.cart_count;
                });
                showToast(data.message, data.success);
            })
            .catch(function () {
                showToast('Could not add to cart. Please try again.', false);
            })
            .finally(function () {
                if (button) {
                    button.disabled = false;
                    button.textContent = original;
                }
            });
        });
    });

    // ===== Toast =====
    function showToast(message, success) {
        const toast = document.createElement('div');
        toast.className = 'Toast ' + (success ? 'ToastSuccess' : 'ToastError');
        toast.textContent = message;
        document.body.appendChild(toast);

        requestAnimationFrame(function () {
            toast.classList.add('ToastShow');
        });

        setTimeout(function () {
            toast.classList.remove('ToastShow');
            setTimeout(function () { toast.remove(); }, 300);
        }, 2200);
    }

});
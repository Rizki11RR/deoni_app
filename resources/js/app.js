import '@tabler/core/dist/js/tabler.min.js';
import '../css/app.css';

document.querySelectorAll('[data-confirm]').forEach((element) => {
    element.addEventListener('click', (event) => {
        if (!window.confirm(element.dataset.confirm)) {
            event.preventDefault();
        }
    });
});

document.querySelectorAll('form').forEach((form) => {
    form.addEventListener('submit', (event) => {
        const selectedProducts = form.querySelectorAll('input[name^="items["][name$="][product_id]"]:checked');

        if (form.action.includes('/orders') && selectedProducts.length === 0) {
            event.preventDefault();
            window.alert('Pilih minimal satu produk.');
            return;
        }

        if (form.action.includes('/orders')) {
            form.querySelectorAll('input[name^="items["][name$="][quantity]"], select[name^="items["][name$="][uom_id]"]').forEach((input) => {
                const productId = input.name.replace(/(quantity|uom_id)$/, 'product_id');
                const checkbox = form.querySelector(`input[name="${productId}"]`);
                input.disabled = !checkbox?.checked;
            });
        }

        const button = form.querySelector('button[type="submit"]');
        if (button) {
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
        }
    });
});

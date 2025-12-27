document.addEventListener('DOMContentLoaded', function(){
    // Quantity buttons: update server and UI
    document.querySelectorAll('.cart-item').forEach(item => {
        const key = item.dataset.key;
        const qtySpan = item.querySelector('.qty');
        const dec = item.querySelector('.qty-decrease');
        const inc = item.querySelector('.qty-increase');
        const rem = item.querySelector('.remove');

        if (inc) inc.addEventListener('click', async () => {
            let qty = parseInt(qtySpan.textContent) + 1;
            await updateQty(key, qty);
            location.reload();
        });

        if (dec) dec.addEventListener('click', async () => {
            let qty = Math.max(1, parseInt(qtySpan.textContent) - 1);
            await updateQty(key, qty);
            location.reload();
        });

        if (rem) rem.addEventListener('click', (e) => {
            e.preventDefault();
            // open confirm modal
            const confirmOverlay = document.getElementById('confirmOverlay');
            confirmOverlay.style.display = 'flex';
            confirmOverlay.dataset.key = key;
        });
    });
    
    // Confirm modal actions
    const confirmOverlay = document.getElementById('confirmOverlay');
    if (confirmOverlay) {
        const ok = document.getElementById('confirmOk');
        const cancel = document.getElementById('confirmCancel');
        ok.addEventListener('click', async () => {
            const key = confirmOverlay.dataset.key;
            if (!key) return;
            const res = await removeItem(key);
            if (res && res.success) {
                // remove element from DOM
                const el = document.querySelector('.cart-item[data-key="' + key + '"]');
                if (el) el.remove();
                // update counts
                document.querySelectorAll('.cart-count').forEach(c => c.textContent = res.count);
                // update summary: reload page simple and safe
                location.reload();
            } else {
                alert('Erreur lors de la suppression');
            }
            confirmOverlay.style.display = 'none';
        });
        cancel.addEventListener('click', () => { confirmOverlay.style.display = 'none'; });
    }

    // Checkout modal
    const checkoutBtn = document.querySelector('.checkout');
    const checkoutOverlay = document.getElementById('checkoutOverlay');
    const checkoutForm = document.getElementById('checkoutForm');
    if (checkoutBtn && checkoutOverlay && checkoutForm) {
        checkoutBtn.addEventListener('click', async () => {
            // fetch latest summary
            try {
                const res = await fetch('cart.php', {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({action:'get'})});
                const data = await res.json();

                // If cart is empty, redirect user to products page instead of opening checkout
                if (data && Array.isArray(data.cart) && data.cart.length === 0) {
                    window.location.href = 'produits.php';
                    return;
                }
                // update summary fields if present
                if (data && data.success) {
                    // compute subtotal
                    let subtotal = 0;
                    data.cart.forEach(i => subtotal += parseFloat(i.total_price));
                    // Default shipping is 0 until a wilaya is explicitly selected
                    const defaultShipping = 0;
                    let shipping = defaultShipping;
                    let total = subtotal + shipping;
                    document.getElementById('sum_subtotal').textContent = subtotal.toLocaleString('fr-FR') + ' DA';
                    document.getElementById('sum_shipping').textContent = shipping.toLocaleString('fr-FR') + ' DA';
                    document.getElementById('sum_total').textContent = total.toLocaleString('fr-FR') + ' DA';

                    // wire wilaya select to update shipping when changed
                    const wilayaSelect = document.getElementById('wilayaSelect');
                    if (wilayaSelect) {
                        const updateShippingDisplay = () => {
                            const opt = wilayaSelect.options[wilayaSelect.selectedIndex];
                            const price = opt && opt.dataset && opt.dataset.price ? parseInt(opt.dataset.price, 10) : 0;
                            if (!isNaN(price) && price > 0) shipping = price; else shipping = defaultShipping;
                            total = subtotal + shipping;
                            document.getElementById('sum_shipping').textContent = shipping.toLocaleString('fr-FR') + ' DA';
                            document.getElementById('sum_total').textContent = total.toLocaleString('fr-FR') + ' DA';
                            // update main page summary shipping too
                            const mainShip = document.getElementById('main_shipping');
                            if (mainShip) mainShip.textContent = shipping.toLocaleString('fr-FR') + ' DA';
                        };
                        wilayaSelect.removeEventListener('change', updateShippingDisplay);
                        wilayaSelect.addEventListener('change', updateShippingDisplay);
                        // initialize if a wilaya is already selected
                        updateShippingDisplay();
                    }
                }
            } catch(e){ console.error(e); }
            checkoutOverlay.style.display = 'flex';
        });

        document.getElementById('checkoutCancel').addEventListener('click', () => { checkoutOverlay.style.display = 'none'; });

        checkoutForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(checkoutForm);
            const payload = { action: 'checkout' };
            formData.forEach((v,k) => payload[k] = v);
            try {
                const res = await fetch('cart.php', {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload)});
                const data = await res.json();
                if (data && data.success) {
                    checkoutOverlay.style.display = 'none';
                    const successOverlay = document.getElementById('successOverlay');
                    document.getElementById('successMessage').textContent = 'Merci ! Votre commande #' + data.order_id + ' a été enregistrée.';
                    successOverlay.style.display = 'flex';
                    // update cart count
                    document.querySelectorAll('.cart-count').forEach(c => c.textContent = '0');
                } else {
                    alert('Erreur: ' + (data.error || 'Impossible de passer la commande'));
                }
            } catch (e) { console.error(e); alert('Erreur de communication'); }
        });

        document.getElementById('successOk').addEventListener('click', () => {
            document.getElementById('successOverlay').style.display = 'none';
            // redirect to homepage
            window.location.href = 'index.php';
        });
    }
});

async function updateQty(key, qty) {
    try {
        const res = await fetch('cart.php', {method: 'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({action:'update', key: key, qty: qty})});
        return await res.json();
    } catch (e) { console.error(e); }
}

async function removeItem(key) {
    try {
        const res = await fetch('cart.php', {method: 'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({action:'remove', key: key})});
        return await res.json();
    } catch (e) { console.error(e); }
}
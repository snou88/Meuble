document.addEventListener('DOMContentLoaded', function () {

    /*************** FONCTIONS UTILES ***************/
    async function updateQty(key, qty) {
        try {
            const res = await fetch('cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'update', key: key, qty: qty })
            });
            return await res.json();
        } catch (e) {
            console.error(e);
            return null;
        }
    }

    async function removeItem(key) {
        try {
            const res = await fetch('cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'remove', key: key })
            });
            return await res.json();
        } catch (e) {
            console.error(e);
            return null;
        }
    }

    /**
     * showOrderPopup(msg)
     * Affiche le popup de succès avec le message `msg`.
     * Reste visible jusqu'au clic sur OK (aucune fermeture automatique).
     */
    function showOrderPopup(msg) {
        const overlay = document.getElementById('successOverlay');
        const messageEl = document.getElementById('successMessage');
        if (!overlay || !messageEl) return;

        // Met à jour le texte
        messageEl.innerHTML = msg;

        // Empêcher fermeture par clic sur l'overlay (on veut rester jusqu'à OK)
        overlay.style.display = 'flex';
        overlay.style.pointerEvents = 'auto';

        // S'assurer que le clic sur le contenu ne propage pas et n'éteint rien
        const modal = overlay.querySelector('.modal');
        if (modal) {
            modal.addEventListener('click', (ev) => ev.stopPropagation());
        }

        // Bouton OK : ferme et redirige vers l'accueil pour éviter resoumission
        const okBtn = document.getElementById('successOk');
        if (okBtn) {
            okBtn.onclick = () => {
                // cacher l'overlay
                overlay.style.display = 'none';
                // rediriger vers index.php (prévenir double commande sur refresh)
                window.location.href = 'index.php';
            };
        }
    }

    /*************** QUANTITÉ ET SUPPRESSION ***************/
    document.querySelectorAll('.cart-item').forEach(item => {
        const key = item.dataset.key;
        const qtySpan = item.querySelector('.qty');
        const dec = item.querySelector('.qty-decrease');
        const inc = item.querySelector('.qty-increase');
        const rem = item.querySelector('.remove');

        if (inc) inc.addEventListener('click', async () => {
            const current = parseInt(qtySpan.textContent) || 1;
            const qty = current + 1;
            await updateQty(key, qty);
            location.reload();
        });

        if (dec) dec.addEventListener('click', async () => {
            const current = parseInt(qtySpan.textContent) || 1;
            const qty = Math.max(1, current - 1);
            await updateQty(key, qty);
            location.reload();
        });

        if (rem) rem.addEventListener('click', (e) => {
            e.preventDefault();
            const confirmOverlay = document.getElementById('confirmOverlay');
            if (!confirmOverlay) return;
            confirmOverlay.style.display = 'flex';
            confirmOverlay.dataset.key = key;
        });
    });

    // Confirm modal actions (supprimer un item)
    const confirmOverlay = document.getElementById('confirmOverlay');
    if (confirmOverlay) {
        const ok = document.getElementById('confirmOk');
        const cancel = document.getElementById('confirmCancel');
        if (ok) {
            ok.addEventListener('click', async () => {
                const key = confirmOverlay.dataset.key;
                if (!key) return;
                const res = await removeItem(key);
                if (res && res.success) {
                    const el = document.querySelector(`.cart-item[data-key="${key}"]`);
                    if (el) el.remove();
                    document.querySelectorAll('.cart-count').forEach(c => c.textContent = res.count);
                    // reload summary safely
                    location.reload();
                } else {
                    alert('Erreur lors de la suppression');
                }
                confirmOverlay.style.display = 'none';
            });
        }
        if (cancel) {
            cancel.addEventListener('click', () => { confirmOverlay.style.display = 'none'; });
        }
    }

    /*************** CHECKOUT ***************/
    const checkoutBtn = document.querySelector('.checkout');
    const checkoutOverlay = document.getElementById('checkoutOverlay');
    const checkoutForm = document.getElementById('checkoutForm');

    if (checkoutBtn && checkoutOverlay && checkoutForm) {

        // Ouvrir le checkout (récupérer résumé)
        checkoutBtn.addEventListener('click', async () => {
            try {
                const res = await fetch('cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'get' })
                });
                const data = await res.json();

                if (data && Array.isArray(data.cart) && data.cart.length === 0) {
                    window.location.href = 'produits.php';
                    return;
                }

                if (data && data.success) {
                    // Calculs du résumé
                    let subtotal = 0;
                    data.cart.forEach(i => subtotal += parseFloat(i.total_price || 0));
                    const defaultShipping = 0;
                    let shipping = defaultShipping;
                    let total = subtotal + shipping;

                    const sumSubtotalEl = document.getElementById('sum_subtotal');
                    const sumShippingEl = document.getElementById('sum_shipping');
                    const sumTotalEl = document.getElementById('sum_total');

                    if (sumSubtotalEl) sumSubtotalEl.textContent = subtotal.toLocaleString('fr-FR') + ' DA';
                    if (sumShippingEl) sumShippingEl.textContent = shipping.toLocaleString('fr-FR') + ' DA';
                    if (sumTotalEl) sumTotalEl.textContent = total.toLocaleString('fr-FR') + ' DA';

                    // Mise à jour du shipping selon la wilaya
                    const wilayaSelect = document.getElementById('wilayaSelect');
                    if (wilayaSelect) {
                        const updateShippingDisplay = () => {
                            const opt = wilayaSelect.options[wilayaSelect.selectedIndex];
                            const price = opt && opt.dataset && opt.dataset.price ? parseInt(opt.dataset.price, 10) : 0;
                            shipping = (!isNaN(price) && price > 0) ? price : defaultShipping;
                            total = subtotal + shipping;
                            if (sumShippingEl) sumShippingEl.textContent = shipping.toLocaleString('fr-FR') + ' DA';
                            if (sumTotalEl) sumTotalEl.textContent = total.toLocaleString('fr-FR') + ' DA';
                            const mainShip = document.getElementById('main_shipping');
                            if (mainShip) mainShip.textContent = shipping.toLocaleString('fr-FR') + ' DA';
                        };
                        // remove/add listener safely
                        wilayaSelect.removeEventListener('change', updateShippingDisplay);
                        wilayaSelect.addEventListener('change', updateShippingDisplay);
                        updateShippingDisplay();
                    }
                }
            } catch (e) {
                console.error(e);
            }

            checkoutOverlay.style.display = 'flex';
        });

        // Annuler checkout
        const cancelBtn = document.getElementById('checkoutCancel');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => {
                checkoutOverlay.style.display = 'none';
            });
        }

        // Submit checkout (empêche double envoi)
        checkoutForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Trouver le bouton submit (button ou input)
            const submitBtn = checkoutForm.querySelector('button[type="submit"], input[type="submit"]');

            // Désactiver le bouton pour éviter double clic
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.dataset.oldText = submitBtn.textContent || submitBtn.value || '';
                if (submitBtn.tagName.toLowerCase() === 'button') submitBtn.textContent = 'Envoi...';
                else submitBtn.value = 'Envoi...';
            }

            const formData = new FormData(checkoutForm);
            const payload = { action: 'checkout' };
            formData.forEach((v, k) => payload[k] = v);

            try {
                const res = await fetch('cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();

                if (data && data.success) {
                    // Fermer overlay checkout
                    checkoutOverlay.style.display = 'none';

                    // Mettre à jour compteurs UI
                    document.querySelectorAll('.cart-count').forEach(c => c.textContent = '0');

                    // Nettoyer l'historique pour réduire la resoumission sur refresh
                    try {
                        history.replaceState(null, '', window.location.pathname);
                    } catch (err) {
                        console.warn('replaceState failed', err);
                    }

                    // Afficher le popup (attend le clic OK pour rediriger)
                    showOrderPopup(`
Nous vous remercions pour votre confiance. Votre commande a bien été reçue et sera confirmée par notre équipe dans les plus brefs délais.<br><br>
تم استلام طلبكم بنجاح. سيتواصل معكم فريقنا في أقرب وقت لتأكيد الطلب. شكرًا لثقتكم بنا
                    `);

                    // Ne pas réactiver le submit : on redirigera sur OK

                } else {
                    alert('Erreur: ' + (data && data.error ? data.error : 'Impossible de passer la commande'));
                    // réactiver le bouton si erreur
                    if (submitBtn) {
                        if (submitBtn.tagName.toLowerCase() === 'button') submitBtn.textContent = submitBtn.dataset.oldText || 'Envoyer';
                        else submitBtn.value = submitBtn.dataset.oldText || 'Envoyer';
                        submitBtn.disabled = false;
                    }
                }
            } catch (err) {
                console.error(err);
                alert('Erreur de communication');
                if (submitBtn) {
                    if (submitBtn.tagName.toLowerCase() === 'button') submitBtn.textContent = submitBtn.dataset.oldText || 'Envoyer';
                    else submitBtn.value = submitBtn.dataset.oldText || 'Envoyer';
                    submitBtn.disabled = false;
                }
            }
        });

    } // end if checkoutBtn && checkoutOverlay && checkoutForm

}); // end DOMContentLoaded

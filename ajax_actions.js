document.addEventListener('DOMContentLoaded', () => {
    
    // Intercept form submissions for cart and wishlist
    const forms = document.querySelectorAll('form[action="cart_action.php"]');
    
    forms.forEach(form => {
        form.addEventListener('submit', async (e) => {
            const formData = new FormData(form);
            
            // If it's an update_cart or remove_cart inside cart.php, let it reload 
            // because we need to recalculate prices and update the whole view easily.
            // Alternatively, we could do full ajax cart, but let's focus on wishlist/add_cart first.
            const action = formData.get('action');
            if (action === 'update_cart' || action === 'remove_cart') {
                return; // Let standard form submission happen for cart page
            }
            
            e.preventDefault();
            formData.append('ajax', '1');
            
            try {
                const response = await fetch('cart_action.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                if (result.status === 'success') {
                    
                    // Update cart badge
                    const badges = document.querySelectorAll('.cart-badge');
                    badges.forEach(b => {
                        b.textContent = result.cart_count;
                        b.style.display = result.cart_count > 0 ? 'flex' : 'none';
                    });
                    
                    // Visual feedback
                    const btn = form.querySelector('button[type="submit"]');
                    const actionInput = form.querySelector('input[name="action"]');
                    if (action === 'add_cart') {
                        if (btn) {
                            btn.innerHTML = '<ion-icon name="checkmark-outline"></ion-icon> Tirar do carrinho';
                            btn.classList.remove('primary-btn');
                            btn.classList.add('ghost-btn');
                            btn.style.color = '#ff0055';
                            btn.style.borderColor = '#ff0055';
                        }
                        if (actionInput) actionInput.value = 'remove_cart_ajax';
                        
                        // If we are on card.php, update cartVariants array
                        if (typeof cartVariants !== 'undefined') {
                            const variantInput = document.querySelector('input[name="variante"]:checked') || document.querySelector('input[name="variante"]');
                            if (variantInput && !cartVariants.includes(variantInput.value)) {
                                cartVariants.push(variantInput.value);
                            }
                        }
                        
                    } else if (action === 'remove_cart_ajax') {
                        if (btn) {
                            btn.innerHTML = '<ion-icon name="cart"></ion-icon> Adicionar ao Carrinho';
                            btn.classList.remove('ghost-btn');
                            btn.classList.add('primary-btn');
                            btn.style.color = '';
                            btn.style.borderColor = '';
                        }
                        if (actionInput) actionInput.value = 'add_cart';
                        
                        // If we are on card.php, update cartVariants array
                        if (typeof cartVariants !== 'undefined') {
                            const variantInput = document.querySelector('input[name="variante"]:checked') || document.querySelector('input[name="variante"]');
                            if (variantInput) {
                                const index = cartVariants.indexOf(variantInput.value);
                                if (index > -1) {
                                    cartVariants.splice(index, 1);
                                }
                            }
                        }
                        
                    } else if (action === 'add_wishlist') {
                        if (btn) {
                            btn.innerHTML = '<ion-icon name="heart" style="color:#ff0055;"></ion-icon> Lista de Desejos';
                            btn.style.color = '#ff0055';
                            btn.style.borderColor = '#ff0055';
                        }
                        if (actionInput) actionInput.value = 'remove_wishlist';
                        
                    } else if (action === 'remove_wishlist') {
                        // Check if we are on wishlist page by looking for the closest wishlist-item
                        const item = form.closest('.wishlist-item');
                        if (item) {
                            item.style.opacity = '0';
                            setTimeout(() => item.remove(), 300);
                        } else {
                            // We are on card.php or another page, revert visual state
                            if (btn) {
                                btn.innerHTML = '<ion-icon name="heart-outline"></ion-icon> Lista de Desejos';
                                btn.style.color = '';
                                btn.style.borderColor = '';
                            }
                            if (actionInput) actionInput.value = 'add_wishlist';
                        }
                    }
                } else {
                    if (result.message === 'Not logged in') {
                        window.location.href = 'login.php';
                    }
                }
            } catch (err) {
                console.error('AJAX Error:', err);
            }
        });
    });
});

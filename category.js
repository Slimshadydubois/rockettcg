document.addEventListener('DOMContentLoaded', () => {
    const priceRange = document.getElementById('priceRange');
    const priceValue = document.getElementById('priceValue');
    const checkboxes = document.querySelectorAll('.filter-cb');
    const cards = document.querySelectorAll('.card-item-link');
    
    // Initialize data-index for original sorting order
    cards.forEach((card, index) => {
        card.dataset.index = index;
    });

    // Update price visual
    priceRange.addEventListener('input', (e) => {
        priceValue.textContent = `R$ ${e.target.value},00`;
        filterCards();
    });

    checkboxes.forEach(cb => {
        cb.addEventListener('change', filterCards);
    });

    function filterCards() {
        const maxPrice = parseFloat(priceRange.value);
        
        // Collect checked attributes
        const activeFilters = {
            rarity: [],
            condition: [],
            type: []
        };

        checkboxes.forEach(cb => {
            if (cb.checked) {
                activeFilters[cb.dataset.filter].push(cb.value);
            }
        });

        cards.forEach(card => {
            const cardPrice = parseFloat(card.dataset.price);
            const cardRarity = card.dataset.rarity;
            const cardCondition = card.dataset.condition;
            const cardType = card.dataset.type;

            let show = true;

            // Filter Price
            if (cardPrice > maxPrice) show = false;

            // Filter Rarity
            const cardRarities = (cardRarity || '').split(',');
            if (activeFilters.rarity.length > 0 && !activeFilters.rarity.some(r => cardRarities.includes(r))) show = false;
            
            // Filter Condition
            if (activeFilters.condition.length > 0 && !activeFilters.condition.includes(cardCondition)) show = false;
            
            // Filter Type
            if (activeFilters.type.length > 0 && !activeFilters.type.includes(cardType)) show = false;

            // Apply Display
            if (show) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });

        // Handle Sorting
        const sortOrder = document.getElementById('sortOrder') ? document.getElementById('sortOrder').value : 'default';
        const catalogGrid = document.getElementById('cardsGrid');
        if (catalogGrid) {
            // Get all cards (both visible and hidden)
            let cardsArray = Array.from(cards);
            
            cardsArray.sort((a, b) => {
                const priceA = parseFloat(a.dataset.price);
                const priceB = parseFloat(b.dataset.price);
                const indexA = parseInt(a.dataset.index);
                const indexB = parseInt(b.dataset.index);
                
                if (sortOrder === 'price_asc') {
                    if (priceA === priceB) return indexA - indexB;
                    return priceA - priceB;
                } else if (sortOrder === 'price_desc') {
                    if (priceA === priceB) return indexA - indexB;
                    return priceB - priceA;
                } else {
                    return indexA - indexB; // default relevance
                }
            });
            
            // Reorder DOM
            cardsArray.forEach(card => catalogGrid.appendChild(card));
        }
    }

    // Replace click event to add visual feedback
    const applyBtn = document.getElementById('applyFilters');
    if (applyBtn) {
        // Remove the previous event listener added
        applyBtn.removeEventListener('click', filterCards);
        applyBtn.addEventListener('click', (e) => {
            e.preventDefault();
            filterCards();
            
            // Visual feedback
            const originalText = applyBtn.innerHTML;
            applyBtn.innerHTML = 'Filtros Aplicados!';
            applyBtn.style.backgroundColor = '#4caf50'; // green
            
            setTimeout(() => {
                applyBtn.innerHTML = originalText;
                applyBtn.style.backgroundColor = '';
            }, 1000);
        });
    }

    // Add event listener for sortOrder
    const sortSelect = document.getElementById('sortOrder');
    if (sortSelect) {
        sortSelect.addEventListener('change', filterCards);
    }
});

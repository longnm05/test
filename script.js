document.addEventListener('DOMContentLoaded', () => {

    // 1. Hover effect for Product Cards (Glow follows cursor)
    const cards = document.querySelectorAll('.product-card');
    cards.forEach(card => {
        card.addEventListener('mousemove', e => {
            const glow = card.querySelector('.card-glow');
            if (glow) {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                glow.style.left = `${x - 75}px`;
                glow.style.top = `${y - 75}px`;
            }
        });
    });

    // 2. Navigation Scroll Effect
    window.addEventListener('scroll', () => {
        const nav = document.querySelector('.glass-header');
        if (window.scrollY > 50) {
            nav.style.boxShadow = 'var(--glass-shadow)';
            nav.style.background = 'rgba(10, 10, 12, 0.7)';
        } else {
            nav.style.boxShadow = 'none';
            nav.style.background = 'var(--glass-bg)';
        }
    });

    // 3. Search & Filter Logic
    const magicBtn = document.querySelector('.ai-btn');
    const searchInput = document.querySelector('.smart-search input');
    const productCards = document.querySelectorAll('.product-card');
    const noProductsFound = document.getElementById('noProductsFound');
    let currentCategoryFilter = 'all';

    const performSearch = () => {
        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
        let foundCount = 0;

        productCards.forEach(card => {
            const title = card.querySelector('h3').textContent.toLowerCase();
            const category = card.querySelector('.category').textContent.toLowerCase();

            const matchesSearch = title.includes(query) || category.includes(query);
            const matchesCategory = currentCategoryFilter === 'all' || category.includes(currentCategoryFilter);

            if (matchesSearch && matchesCategory) {
                card.style.display = 'block';
                foundCount++;
            } else {
                card.style.display = 'none';
            }
        });

        if (noProductsFound) {
            noProductsFound.style.display = foundCount === 0 ? 'block' : 'none';
        }
    };

    if (magicBtn && searchInput) {
        magicBtn.addEventListener('click', (e) => {
            e.preventDefault();
            performSearch();
        });
        searchInput.addEventListener('input', performSearch);
    }

    // 4. Quick View Logic
    const quickViewOverlay = document.getElementById('quickViewOverlay');
    if (quickViewOverlay) {
        const closeQuickViewBtn = document.getElementById('closeQuickView');
        const qvImage = document.getElementById('qvImage');
        const qvTitle = document.getElementById('qvTitle');
        const qvCategory = document.getElementById('qvCategory');
        const qvPrice = document.getElementById('qvPrice');
        const qvAddToCart = document.getElementById('qvAddToCart');
        const qvQty = document.getElementById('qvQty');

        document.querySelectorAll('.quick-view').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const card = e.target.closest('.product-card');
                const imgUrl = card.querySelector('.card-image img').src;
                const title = card.querySelector('h3').textContent;
                const category = card.querySelector('.category').textContent;
                const price = card.querySelector('.price').textContent;
                const addToCartBtn = card.querySelector('.add-to-cart');
                
                qvImage.src = imgUrl;
                qvTitle.textContent = title;
                qvCategory.textContent = category;
                qvPrice.textContent = price;
                qvQty.value = 1;
                
                qvAddToCart.setAttribute('data-id', addToCartBtn.getAttribute('data-id'));
                quickViewOverlay.classList.add('active');
            });
        });

        closeQuickViewBtn.addEventListener('click', () => quickViewOverlay.classList.remove('active'));
        quickViewOverlay.addEventListener('click', (e) => {
            if(e.target === quickViewOverlay) quickViewOverlay.classList.remove('active');
        });

        qvAddToCart.addEventListener('click', () => {
            const id = qvAddToCart.getAttribute('data-id');
            const qty = parseInt(qvQty.value) || 1;
            addToCart(id, qty, qvAddToCart);
        });
    }

    // 5. AJAX Add to Cart Function
    function addToCart(productId, quantity, buttonElement) {
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('quantity', quantity);

        fetch('add_to_cart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Update badge
                const cartBadge = document.getElementById('cartBadge');
                if (cartBadge) cartBadge.textContent = data.total_items;

                // Success effect
                const originalHTML = buttonElement.innerHTML;
                buttonElement.innerHTML = '<i class="fa-solid fa-check"></i> Đã thêm';
                buttonElement.style.background = '#00ff88';
                buttonElement.style.color = '#000';
                
                setTimeout(() => {
                    buttonElement.innerHTML = originalHTML;
                    buttonElement.style.background = '';
                    buttonElement.style.color = '';
                    if (quickViewOverlay) quickViewOverlay.classList.remove('active');
                    // Tùy chọn: Chuyển hướng sang trang giỏ hàng
                    // window.location.href = 'cart.php';
                }, 1000);
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi thêm vào giỏ hàng');
        });
    }

    // Bind Add to Cart buttons on product cards
    document.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const id = btn.getAttribute('data-id');
            addToCart(id, 1, btn);
        });
    });

});

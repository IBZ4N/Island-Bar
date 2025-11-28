document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const productCards = document.querySelectorAll('.product-card');
    const searchInput = document.getElementById('catalog-search');
    const searchBtn = document.getElementById('search-btn');
    let currentCategory = 'all';
    let currentSearch = '';
    
    function filterProducts() {
        productCards.forEach(card => {
            const cardCategory = card.getAttribute('data-category');
            const cardName = card.getAttribute('data-name') || '';
            const cardDescription = card.getAttribute('data-description') || '';
            const searchTerm = currentSearch.toLowerCase().trim();
            
            const matchesCategory = currentCategory === 'all' || cardCategory === currentCategory;
            const matchesSearch = searchTerm === '' || 
                cardName.includes(searchTerm) || 
                cardDescription.includes(searchTerm);
            
            if (matchesCategory && matchesSearch) {
                card.style.display = 'block';
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 10);
            } else {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.display = 'none';
                }, 300);
            }
        });
    }
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            currentCategory = this.getAttribute('data-category');
            filterProducts();
        });
    });
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            currentSearch = this.value;
            filterProducts();
        });
        
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                filterProducts();
            }
        });
    }
    
    if (searchBtn) {
        searchBtn.addEventListener('click', function() {
            if (searchInput) {
                currentSearch = searchInput.value;
                filterProducts();
            }
        });
    }
    
    const productButtons = document.querySelectorAll('.product-btn');
    productButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productCard = this.closest('.product-card');
            const productName = productCard.querySelector('.product-name').textContent;
            alert('Información detallada de: ' + productName + '\n\nPróximamente disponible en el dashboard.');
        });
    });
});


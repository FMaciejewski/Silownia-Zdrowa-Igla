function priceChange(element, period) {
    document.querySelectorAll('.period-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    element.classList.add('active');

    fetch(`../backend/api/get_prices.php?period=${period}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            document.getElementById('price-normal').textContent = `${data.normal}zł`;
            document.getElementById('price-premium').textContent = `${data.premium}zł`;
            document.getElementById('price-vip').textContent = `${data.vip}zł`;
        })
        .catch(error => {
            console.error('Error fetching prices:', error);
        });
}

window.addEventListener('load', () => {
    const defaultBtn = document.querySelector('.period-btn.active') || 
                      document.querySelector('.period-btn');
    if (defaultBtn) {
        const defaultPeriod = defaultBtn.dataset.period || '1';
        priceChange(defaultBtn, defaultPeriod);
    }
});
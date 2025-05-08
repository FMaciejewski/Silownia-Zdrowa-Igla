function priceChange(element, period) {
    document.querySelectorAll('.period-btn').forEach(btn => btn.classList.remove('active'));
    element.classList.add('active');

    fetch(`../backend/api/get_prices.php?period=${period}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('price-normal').textContent = `${data.normal}zł`;
            document.getElementById('price-premium').textContent = `${data.premium}zł`;
            document.getElementById('price-vip').textContent = `${data.vip}zł`;
        })
        .catch(error => console.error('Error fetching prices:', error));

    window.onload = () => {
      zmienCeny(document.querySelector('.buttonperiod-btn.active'), '1');
    };
    

    }
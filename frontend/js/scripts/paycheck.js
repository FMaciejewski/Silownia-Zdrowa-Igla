document.addEventListener('DOMContentLoaded', function() {
  const paymentRadios = document.querySelectorAll('input[name="payment-type"]');
  
  paymentRadios.forEach(radio => {
    radio.addEventListener('change', function() {
      document.querySelectorAll('.payment-fields').forEach(field => {
        field.style.display = 'none';
      });
      
      if (this.checked) {
        const fieldsClass = this.value + '-fields';
        document.querySelector('.' + fieldsClass).style.display = 'block';
      }
    });
  });


  const urlParams = new URLSearchParams(window.location.search);
  document.getElementById('rodzaj').value = urlParams.get('rodzaj') || '';
  document.getElementById('miasto').value = urlParams.get('miasto') || '';
  document.getElementById('okres').value = urlParams.get('okres') || '';
  document.getElementById('data_rozpoczecia').value = urlParams.get('data_rozpoczecia') || '';


  document.getElementById('summary-rodzaj').textContent = document.getElementById('rodzaj').value || '-';
  document.getElementById('summary-miasto').textContent = document.getElementById('miasto').value || '-';
  document.getElementById('summary-okres').textContent = document.getElementById('okres').value ? 
    document.getElementById('okres').value + ' miesiące' : '-';
  document.getElementById('summary-data').textContent = document.getElementById('data_rozpoczecia').value || '-';
  

  const okres = parseInt(document.getElementById('okres').value) || 1;
  const basePrice = 50; 
  document.getElementById('summary-price').textContent = (basePrice * okres) + ',00 zł';
});
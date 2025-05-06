document.addEventListener('DOMContentLoaded', () => {
  console.log('Aplikacja zainicjalizowana!')


  fetch('/Silownia-Zdrowa-Igla/backend/api/init.php')
  .then(response => {
    if (!response.ok) {
      throw new Error(`HTTP error! Status: ${response.status}`);
    }
    return response.json();
  })
  .then(data => {
    console.log(data);
  })
  .catch(error => {
    console.error("Fetch error:", error.message);
  });


})
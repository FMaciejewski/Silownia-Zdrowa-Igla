document.addEventListener('DOMContentLoaded', () => {
  console.log('Aplikacja zainicjalizowana!')

  fetch('//api/get-posts.php')
  .then(res => res.json())
  .then(data => console.log(data));


})
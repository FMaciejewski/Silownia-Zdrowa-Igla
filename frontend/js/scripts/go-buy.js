function handleBuyClick() {
fetch('../backend/api/session-check.php')
    .then(response => response.json())
    .then(data => {
    if (data.loggedIn) {
        window.location.href = '../frontend/sites/gym-pass.html';
    } else {
        window.location.href = '../frontend/sites/log-in.html';
    }
    })
    .catch(() => {
    alert('Błąd sprawdzania sesji.');
    });
}
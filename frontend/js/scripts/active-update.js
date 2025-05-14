fetch("/Silownia-Zdrowa-Igla/backend/api/active-update.php", { cache: "no-store" })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log("Zaktualizowane karnety:", data);
    })
    .catch(error => {
        console.error("Błąd podczas aktualizacji karnetów:", error);
    });
document.addEventListener("DOMContentLoaded", () => {
    const rodzajSelect = document.getElementById("rodzaj");
    const okresSelect = document.getElementById("okres");
    const priceElement = document.querySelector(".price");

    async function fetchPrice() {
        const rodzaj = rodzajSelect.value;
        const okres = okresSelect.value;

        try {
            const response = await fetch(`../../backend/api/summary.php?rodzaj=${rodzaj}&okres=${okres}`);
            const data = await response.json();

            if (response.ok) {
                priceElement.textContent = `Cena: ${data.price} zł`;
            } else {
                priceElement.textContent = "Nie znaleziono ceny.";
                console.error(data.error);
            }
        } catch (error) {
            priceElement.textContent = "Błąd podczas pobierania ceny.";
            console.error("Błąd:", error);
        }
    }

    rodzajSelect.addEventListener("change", fetchPrice);
    okresSelect.addEventListener("change", fetchPrice);

    fetchPrice();
});
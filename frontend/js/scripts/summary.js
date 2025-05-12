document.addEventListener("DOMContentLoaded", () => {
    let rodzajSelect;
    let okresSelect;

    if (document.getElementById("rodzaj") === null || document.getElementById("okres") === null) {
        const params = new URLSearchParams(window.location.search);
        rodzajSelect = { value: params.get("rodzaj") };
        okresSelect = { value: params.get("okres") };
    } else {
        rodzajSelect = document.getElementById("rodzaj");
        okresSelect = document.getElementById("okres");
    }

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

    if (rodzajSelect instanceof HTMLElement && okresSelect instanceof HTMLElement) {
        rodzajSelect.addEventListener("change", fetchPrice);
        okresSelect.addEventListener("change", fetchPrice);
    }

    fetchPrice();
});
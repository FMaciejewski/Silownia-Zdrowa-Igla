document.addEventListener("DOMContentLoaded", () => {
  fetch("../../backend/api/pass-page.php")
    .then((response) => response.json())
    .then((data) => {
      const section = document.querySelector(".pass-section");

      if (data.error) {
        section.innerHTML = `<div class="error-message"><p>Błąd: ${data.error}</p></div>`;
        return;
      }

      const pass = data.Pass;
      const formatDate = (datetime) => datetime?.split(" ")[0];
      const today = new Date().toISOString().split("T")[0];
      section.innerHTML = `
                <div class="pass-header">
                    <h2>Twój karnet</h2>
                    <img src="../${data.ProfilePicture}" alt="Profile Image" class="profile-image">
                    <p><strong>Imię:</strong> ${data.FirstName}</p>
                    <p><strong>Nazwisko:</strong> ${data.LastName}</p>
                </div>
            `;

      if (!pass || !pass.PurchaseDate || !pass.ExpiryDate) {
        section.innerHTML += `
                    <div class="pass-status inactive">
                        <p><strong>Brak aktywnego karnetu</strong></p>
                    </div>
                    <button onclick="window.location.href='gym-pass.html'" class="buy-pass-button">Kup karnet</button>
                `;
      } else {
        const purchaseDate = pass.PurchaseDate.split(" ")[0];
        const expiryDate = pass.ExpiryDate.split(" ")[0];
        const isFutureStart = purchaseDate > today;
        const isExpired = expiryDate < today;

        let statusText;
        if (isFutureStart) {
          statusText = `
                        <div class="pass-status future">
                            <p><strong>Karnet nieaktywny</strong> (${pass.Type})</p>
                            <p>Rozpocznie się ${formatDate(pass.PurchaseDate)}</p>
                        </div>`;
        } else if (isExpired) {
          statusText = `
                        <div class="pass-status expired">
                            <p><strong>Karnet wygasł</strong> (${pass.Type})</p>
                            <p>Wygasł ${formatDate(pass.ExpiryDate)}</p>
                        </div>`;
        } else {
          statusText = `
                        <div class="pass-status active">
                            <p><strong>Karnet aktywny</strong> (${pass.Type})</p>
                            <p>Ważny do ${formatDate(pass.ExpiryDate)}</p>
                        </div>`;
          section.innerHTML += `
                        <div class="active-pass-qrcode">
                            <img src="../assets/images/qrcode.png" alt="QR Kod" class="qr-code">
                        </div>
                    `;
        }

        section.innerHTML += `
                    ${statusText}
                    <div class="pass-details">
                        <p><strong>Data rozpoczęcia:</strong> ${formatDate(pass.PurchaseDate)}</p>
                        <p><strong>Data zakończenia:</strong> ${formatDate(pass.ExpiryDate)}</p>
                    </div>
                    <div class="pass-actions">
                        <button onclick="window.location.href='gym-pass.html'" class="buy-pass-button">
                            ${isExpired ? "Kup nowy karnet" : "Przedłuż karnet"}
                        </button>
                        ${isFutureStart ? `<button onclick="changeStartDate('${pass.PurchaseDate.split(" ")[0]}')" class="change-date-button">Zmień datę rozpoczęcia</button>` : ""}

                    </div>
                `;
      }
    })
    .catch((error) => {
      console.error("Błąd:", error);
      document.querySelector(".pass-section").innerHTML = `
                <div class="error-message">
                    <p>Nie udało się załadować danych.</p>
                </div>
            `;
    });
});

function changeStartDate(currentDate) {
  const newDate = prompt(
    "Podaj nową datę rozpoczęcia (RRRR-MM-DD):",
    currentDate,
  );
  if (!newDate) return;

  fetch("../../backend/api/change-pass-date.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      newStartDate: newDate,
    }),
  })
    .then((response) => {
      if (!response.ok) {
        return response.text().then((text) => {
          throw new Error(text || "Unknown error");
        });
      }
      return response.json();
    })
    .then((data) => {
      if (data.success) {
        alert("Zmieniono datę rozpoczęcia.");
        location.reload();
      } else {
        throw new Error(data.error || "Unknown error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert(`Błąd: ${error.message}`);
    });
}

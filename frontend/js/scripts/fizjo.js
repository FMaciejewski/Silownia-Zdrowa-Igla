document.addEventListener("DOMContentLoaded", function () {
  window.calendarRedirect = function(physioId) {
    localStorage.setItem("selectedDoctorId", physioId);
    window.location.href = `appointments.html`;
  }

  fetch("../../backend/api/fizjo.php")
    .then((response) => response.json())
    .then((data) => {
      const tbody = document.getElementById("physio-table-body");
      tbody.innerHTML = "";

      data.forEach((physio) => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
                    <td>${escapeHtml(physio.name)} ${escapeHtml(physio.surname)}</td>
                    <td>${escapeHtml(physio.specialization)}</td>
                    <td>
                        <button class="action-btn calendar-btn" 
                            onclick="calendarRedirect('${physio.DoctorID}')">
                            Kalendarz
                        </button>
                        <button class="action-btn chat-btn" 
                            onclick="window.location.href='chat.html?receiver_id=${physio.id}'">
                            Chat
                        </button>
                    </td>
                `;
        tbody.appendChild(tr);
      });
    })
    .catch((error) => {
      document.getElementById("physio-table-body").innerHTML =
        `<tr><td colspan="3">Błąd ładowania danych.</td></tr>`;
      console.error(error);
    });

  function escapeHtml(text) {
    if (typeof text !== "string") return "";
    return text
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }
});

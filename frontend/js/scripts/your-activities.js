fetch("../../backend/api/your-activities.php", {
  credentials: "include",
  headers: {
    Accept: "application/json",
    "Content-Type": "application/json",
  },
})
  .then((res) => {
    if (!res.ok) {
      throw new Error(`HTTP error! status: ${res.status}`);
    }

    const contentType = res.headers.get("content-type");
    if (!contentType || !contentType.includes("application/json")) {
      throw new Error("Response is not JSON");
    }

    return res.json();
  })
  .then((data) => {
    console.log("Received data:", data);

    const container = document.getElementById("dashboard-tiles");
    if (!container) {
      console.error("Container element not found");
      return;
    }

    container.innerHTML = "";

    if (!data.success) {
      container.textContent = data.error || "Wystąpił błąd";
      return;
    }

    if (data.role === "fizjo") {
      if (!data.data || !Array.isArray(data.data) || data.data.length === 0) {
        container.textContent = "Brak zapisanych pacjentów.";
        return;
      }

      data.data.forEach((appt) => {
        const div = document.createElement("div");
        div.className = "tile";

        // Tworzenie elementów z informacjami
        const patientInfo = document.createElement("div");
        patientInfo.className = "patient-info";
        patientInfo.innerHTML = `
        <strong>${appt.FullName}</strong>
        <div class="appointment-time">
          <span class="date">${appt.DateOnly}</span>
          <span class="time">${appt.TimeRange}</span>
        </div>
        ${appt.Cause ? `<div class="cause">Powód: ${appt.Cause}</div>` : ""}
      `;

        const cancelBtn = document.createElement("button");
        cancelBtn.textContent = "Odwołaj";
        cancelBtn.className = "cancel-btn";
        cancelBtn.onclick = () => cancelItem(appt.AppointmentID, "appointment");

        div.appendChild(patientInfo);
        div.appendChild(cancelBtn);
        container.appendChild(div);
      });
    } else if (data.role === "trainer") {
      if (!data.data || !Array.isArray(data.data) || data.data.length === 0) {
        container.textContent = "Brak utworzonych treningów.";
        return;
      }

      data.data.forEach((training) => {
        const div = document.createElement("div");
        div.className = "tile";

        const trainingInfo = document.createElement("div");
        trainingInfo.className = "training-info";
        trainingInfo.innerHTML = `
        <strong>${training.Title}</strong>
        <div class="training-schedule">
          <span class="date">${training.DateOnly}</span>
          <span class="time-range">${training.TimeRange}</span>
        </div>
        <div class="participants">Uczestników: ${training.CurrentParticipants}/${training.MaxParticipants}</div>
        ${training.Location ? `<div class="location">📍 ${training.Location}</div>` : ""}
      `;

        const cancelBtn = document.createElement("button");
        cancelBtn.textContent = "Odwołaj";
        cancelBtn.className = "cancel-btn";
        cancelBtn.onclick = () => cancelItem(training.TrainingID, "training");

        div.appendChild(trainingInfo);
        div.appendChild(cancelBtn);
        container.appendChild(div);
      });
    } else if (data.role === "client") {
      // Nowa struktura dla klientów - obsługa trainings i appointments
      const hasTrainings =
        data.data.trainings && data.data.trainings.length > 0;
      const hasAppointments =
        data.data.appointments && data.data.appointments.length > 0;

      if (!hasTrainings && !hasAppointments) {
        container.textContent =
          "Nie masz żadnych zapisów na treningi ani wizyt.";
        return;
      }

      // Wyświetl treningi
      if (hasTrainings) {
        const trainingsHeader = document.createElement("h3");
        trainingsHeader.textContent = "Twoje treningi";
        trainingsHeader.className = "section-header";
        container.appendChild(trainingsHeader);

        data.data.trainings.forEach((training) => {
          const div = document.createElement("div");
          div.className = "tile training-tile";

          const trainingInfo = document.createElement("div");
          trainingInfo.className = "training-info";
          trainingInfo.innerHTML = `
          <strong>${training.Title}</strong>
          <div class="trainer">Trener: ${training.TrainerName}</div>
          <div class="training-schedule">
            <span class="date">${training.DateOnly}</span>
            <span class="time-range">${training.TimeRange}</span>
          </div>
          ${training.Location ? `<div class="location">📍 ${training.Location}</div>` : ""}
        `;

          const cancelButton = document.createElement("button");
          cancelButton.textContent = "Zrezygnuj";
          cancelButton.className = "cancel-btn";
          cancelButton.onclick = () =>
            cancelItem(training.TrainingID, "training");

          div.appendChild(trainingInfo);
          div.appendChild(cancelButton);
          container.appendChild(div);
        });
      }

      // Wyświetl wizyty u fizjoterapeuty
      if (hasAppointments) {
        const appointmentsHeader = document.createElement("h3");
        appointmentsHeader.textContent = "Twoje wizyty u fizjoterapeuty";
        appointmentsHeader.className = "section-header";
        container.appendChild(appointmentsHeader);

        data.data.appointments.forEach((appointment) => {
          const div = document.createElement("div");
          div.className = "tile appointment-tile";

          const appointmentInfo = document.createElement("div");
          appointmentInfo.className = "appointment-info";
          appointmentInfo.innerHTML = `
          <strong>Wizyta u fizjoterapeuty</strong>
          <div class="doctor">Lekarz: ${appointment.DoctorName}</div>
          <div class="appointment-schedule">
            <span class="date">${appointment.DateOnly}</span>
            <span class="time-range">${appointment.TimeRange}</span>
          </div>
          ${appointment.Cause ? `<div class="cause">Powód: ${appointment.Cause}</div>` : ""}
        `;

          const cancelButton = document.createElement("button");
          cancelButton.textContent = "Odwołaj wizytę";
          cancelButton.className = "cancel-btn";
          cancelButton.onclick = () =>
            cancelItem(appointment.AppointmentID, "appointment");

          div.appendChild(appointmentInfo);
          div.appendChild(cancelButton);
          container.appendChild(div);
        });
      }
    } else if (data.role === "admin") {
      // Obsługa dla admina
      if (!data.data) {
        container.textContent = "Brak danych do wyświetlenia.";
        return;
      }

      const statsDiv = document.createElement("div");
      statsDiv.className = "admin-stats";
      statsDiv.innerHTML = `
      <div class="stat-item">
        <strong>Użytkownicy:</strong> ${data.data.users_count || 0}
      </div>
      <div class="stat-item">
        <strong>Aktywne karnety:</strong> ${data.data.active_passes || 0}
      </div>
    `;
      container.appendChild(statsDiv);

      if (data.data.trainings && data.data.trainings.length > 0) {
        const trainingsHeader = document.createElement("h3");
        trainingsHeader.textContent = "Nadchodzące treningi";
        trainingsHeader.className = "section-header";
        container.appendChild(trainingsHeader);

        data.data.trainings.forEach((training) => {
          const div = document.createElement("div");
          div.className = "tile admin-training-tile";

          const trainingInfo = document.createElement("div");
          trainingInfo.className = "training-info";
          trainingInfo.innerHTML = `
          <strong>${training.Title}</strong>
          <div class="trainer">Trener: ${training.TrainerName}</div>
          <div class="training-schedule">
            <span class="time">${training.StartTimeFormatted}</span> - 
            <span class="time">${training.EndTimeFormatted}</span>
          </div>
        `;

          div.appendChild(trainingInfo);
          container.appendChild(div);
        });
      }
    }
  })
  .catch((error) => {
    console.error("Błąd:", error);

    const container = document.getElementById("dashboard-tiles");
    if (container) {
      if (error.message.includes("JSON")) {
        container.textContent = "Błąd: Serwer zwrócił nieprawidłową odpowiedź.";
      } else if (error.message.includes("HTTP")) {
        container.textContent = "Błąd połączenia z serwerem.";
      } else {
        container.textContent = "Błąd ładowania danych.";
      }
    }
  });

function cancelItem(id, type) {
  if (!confirm("Czy na pewno chcesz anulować tę pozycję?")) {
    return;
  }

  fetch(`../../backend/api/cancel.php?id=${id}&type=${type}`, {
    method: "POST",
    credentials: "include",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
    },
  })
    .then((res) => {
      if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);

      const contentType = res.headers.get("content-type");
      if (!contentType || !contentType.includes("application/json")) {
        throw new Error("Response is not JSON");
      }

      return res.json();
    })
    .then((response) => {
      if (response.success) {
        alert("Operacja zakończona sukcesem");
        location.reload();
      } else {
        alert(
          "Błąd: " + (response.message || response.error || "Nieznany błąd"),
        );
      }
    })
    .catch((error) => {
      console.error("Błąd:", error);
      alert("Wystąpił błąd przy operacji: " + error.message);
    });
}

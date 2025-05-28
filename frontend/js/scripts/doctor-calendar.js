document.addEventListener("DOMContentLoaded", function () {
  let tempStart = null;
  let tempEnd = null;

  const calendarEl = document.getElementById("calendar");
  const popup = document.getElementById("eventPopup");
  const form = document.getElementById("eventForm");
  const detail = document.getElementById("eventDetail");
  const cancelBtn = document.getElementById("cancelEventBtn");
  const joinBtn = document.getElementById("joinBtn");
  const editBtn = document.getElementById("editBtn");
  const deleteBtn = document.getElementById("deleteBtn");
  const leaveBtn = document.getElementById("leaveBtn");

  const doctorSelect = document.getElementById("doctor-id");
  fetch("../../backend/api/get-doctors.php")
    .then((response) => response.json())
    .then((doctors) => {
      doctors.forEach((doctor) => {
        const option = document.createElement("option");
        option.value = doctor.DoctorID;
        option.textContent = `${doctor.Degree} ${doctor.FirstName} ${doctor.LastName}`;
        doctorSelect.appendChild(option);
      });
      const savedDoctorId = localStorage.getItem("selectedDoctorId");
      if (doctors.length > 0) {
        doctorSelect.value = savedDoctorId;
        const event = new Event("change");
        doctorSelect.dispatchEvent(event);
      }
    });

  function formatLocalDateTime(date) {
    const offset = date.getTimezoneOffset();
    const localDate = new Date(date.getTime() - offset * 60 * 1000);
    return localDate.toISOString().slice(0, 16);
  }

  const calendar = new FullCalendar.Calendar(calendarEl, {
    locale: "pl",
    initialView: "timeGridWeek",
    allDaySlot: false,
    slotMinTime: "06:00:00",
    slotMaxTime: "23:59:00",
    selectable: true,
    editable: false,
    eventOverlap: false,
    selectOverlap: false,
    eventDisplay: "block",
    weekends: false,
    headerToolbar: {
      left: "prev,next today",
      center: "title",
      right: "",
    },
    slotLabelFormat: {
      hour: "2-digit",
      minute: "2-digit",
      meridiem: false,
    },
    buttonText: {
      today: "Dziś",
      month: "Miesiąc",
      week: "Tydzień",
      day: "Dzień",
    },
    select: function (info) {
      tempStart = info.startStr;
      tempEnd = info.endStr;
      document.getElementById("eventStart").value = tempStart;
      document.getElementById("eventEnd").value = tempEnd;
      document.getElementById("eventDoctorID").value = parseInt(
        doctorSelect.value,
      );
      popup.style.display = "block";
    },
    selectAllow: function (selectInfo) {
      const start = selectInfo.start;
      const end = selectInfo.end;
      const now = new Date();

      const duration = (end - start) / (1000 * 60); // w minutach
      const isFuture = start >= now;

      return isFuture && duration <= 30;
    },
    eventDidMount: function (info) {
      info.el.style.cursor = "pointer";

      info.el.addEventListener("mouseenter", function () {
        info.el.style.filter = "brightness(1.2)";
        info.el.style.boxShadow = "0 0 10px rgba(0,0,0,0.3)";
        info.el.style.transition = "0.2s all ease-in-out";
      });

      info.el.addEventListener("mouseleave", function () {
        info.el.style.filter = "none";
        info.el.style.boxShadow = "none";
      });
    },
    eventClick: function (event) {
      const isOwn = event.event.extendedProps.isOwn;

      if (!isOwn) return;

      detailCause = event.event.title;
      detailDoctor.innerText = event.event.extendedProps.Doctor;
      detailPatient.innerText = event.event.extendedProps.Patient;
      detailStart.innerText = event.event.start.toLocaleString();

      deleteBtn.style.display = "block";
      deleteBtn.addEventListener("click", function () {
        window.location.href = `../../backend/api/delete-appointment.php?appointmentId=${event.event.id}`;
      });

      fetch(
        "../../backend/api/can-edit-appointment.php?appointmentId=" +
          event.event.id,
      )
        .then((response) => response.json())
        .then((data) => {
          if (data.canEdit) {
            editBtn.style.display = "block";
            editBtn.addEventListener("click", function () {
              detail.style.display = "none";
              editForm.style.display = "block";

              editAppointmentCause.value = event.event.title;
              editAppointmentStart.value = formatLocalDateTime(
                event.event.start,
              );
              editEventId.value = event.event.id;
              editEventDoctorId.value = parseInt(doctorSelect.value);
            });
          }
        });

      detail.style.display = "block";
    },
    eventContent: function (arg) {
      const data = arg.event.extendedProps;
      const title = arg.event.title;
      const div = document.createElement("div");
      div.className = "info-event";
      div.innerHTML = `<b>${title}</b>`;
      return { domNodes: [div] };
    },
    events: [],
  });

  calendar.render();

  doctorSelect.addEventListener("change", function () {
    localStorage.setItem("selectedDoctorId", doctorSelect.value);
    calendar.getEvents().forEach((event) => {
      event.remove();
    });
    fetch(
      "../../backend/api/render-appointment-calendar.php?doctorId=" +
        parseInt(doctorSelect.value),
    )
      .then((response) => response.json())
      .then((data) => {
        const events = data.events;
        events.forEach((event) => {
          calendar.addEvent({
            id: event.AppointmentID,
            title: event.isOwn ? event.title : "Zajęte",
            start: event.start,
            end: event.end,
            backgroundColor: event.isOwn ? "#3788d8" : "#d9534f",
            borderColor: event.isOwn ? "#3788d8" : "#d43f3a",
            textColor: "#fff",
            extendedProps: {
              Patient: event.Patient,
              Doctor: event.Doctor,
              isOwn: event.isOwn,
            },
          });
        });
      });
    fetch(
      "../../backend/api/get-doctor-hours.php?doctorId=" +
        parseInt(doctorSelect.value),
    )
      .then((response) => response.json())
      .then((data) => {
        calendar.setOption("slotMinTime", data.WorkStartDate);
        calendar.setOption("slotMaxTime", data.WorkEndDate);
        const start = parseInt(data.WorkStartDate.split(":")[0]);
        const end = parseInt(data.WorkEndDate.split(":")[0]);
        const slotHeight = 50;
        const totalHeight = (end - start) * slotHeight + 125;
        calendar.setOption("height", totalHeight);
      });
  });

  cancelBtn.addEventListener("click", function () {
    popup.style.display = "none";
    form.reset();
  });

  cancelDetailBtn.addEventListener("click", function () {
    detail.style.display = "none";
    editBtn.style.display = "none";
    deleteBtn.style.display = "none";
    joinBtn.style.display = "none";
  });
});

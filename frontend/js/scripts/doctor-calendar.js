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
      })});

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
    eventOverlap: true,
    eventMaxStack: 1,
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
      document.getElementById("eventDoctorID").value = parseInt(doctorSelect.value);
      popup.style.display = "block";
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
        detailCause = event.event.title;
      detailDoctor.innerText = event.event.extendedProps.Doctor;
      detailCreator.innerText = event.event.extendedProps.Patient;
      detailStart.innerText = event.event.start.toLocaleString();

      fetch(
        "../../backend/api/can-edit-appointment.php",
      )
        .then((response) => response.json())
        .then((data) => {
          if (data.canEdit) {
            editBtn.style.display = "block";
            editBtn.addEventListener("click", function () {
              detail.style.display = "none";
              editForm.style.display = "block";

              editEventTitle.value = event.event.title;
              editEventStart.value = formatLocalDateTime(event.event.start);
            });
            deleteBtn.style.display = "block";
            deleteBtn.addEventListener("click", function () {
              window.location.href = `../../backend/api/delete-training.php?trainingId=${event.event.id}`;
            });
          } else {
            if (data.joined) {
              leaveBtn.style.display = "block";
              leaveBtn.addEventListener("click", function () {
                window.location.href = `../../backend/api/leave-training.php?trainingId=${event.event.id}`;
              });
            } else {
              joinBtn.style.display = "block";
              joinBtn.addEventListener("click", function () {
                window.location.href = `../../backend/api/join-training.php?trainingId=${event.event.id}`;
              });
            }
          }
        });

      detail.style.display = "block";
    },
    eventContent: function (arg) {
      const data = arg.event.extendedProps;
      const title = arg.event.title;
      const div = document.createElement("div");
      div.innerHTML = `<b>${title}</b>
        ${data.Patient ? `<br><small>Pacjent: ${data.Patient}</small>` : ""}
        ${data.Doctor ? `<br><small>Doktor:${data.Doctor}</small>` : ""}`;
      return { domNodes: [div] };
    },
    events: [],
  });

  fetch("../../backend/api/render-appointment-calendar.php")
    .then((response) => response.json())
    .then((data) => {
      const events = data.events;
      events.forEach((event) => {
        calendar.addEvent({
          id: event.TrainingID,
            title: event.Cause,
          start: event.StartTime,
          end: event.EndTime,
          extendedProps: {
            Patient: event.Patient,
            Doctor: event.Doctor,
          },
        });
      });
    });

  calendar.render();

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

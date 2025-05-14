document.addEventListener("DOMContentLoaded", () => {
  console.log("Aplikacja zainicjalizowana!");

  fetch("/Silownia-Zdrowa-Igla/backend/api/init.php")
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`);
      }
      return response.json();
    })
    .then((data) => {
      console.log(data);
    })
    .catch((error) => {
      console.error("Fetch error:", error.message);
    });

    const calendarEl = document.getElementById('calendar');

    const calendar = new FullCalendar.Calendar(calendarEl, {
    locale: 'pl',
    initialView: 'timeGridWeek',
    allDaySlot: false,
    slotMinTime: '06:00:00',
    slotMaxTime: '23:59:00',
    selectable: false,
    editable: false,
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: ''
    },
    slotLabelFormat: {
      hour: '2-digit',
      minute: '2-digit',
      meridiem: false
    },
    buttonText: {
      today: 'Dziś',
      month: 'Miesiąc',
      week: 'Tydzień',
      day: 'Dzień'
    },
    eventContent: function (arg) {
      const data = arg.event.extendedProps;
      const title = arg.event.title;
      const div = document.createElement('div');
      div.innerHTML = `<b>${title}</b>
        ${data.createdBy ? `<br><small>Twórca: ${data.createdBy}</small>` : ''}
        ${data.maxParticipants ? `<br><small>Miejsca:${data.participants}/${data.maxParticipants}</small>` : ''}`;
      return { domNodes: [div] };
    },
    events: []
  });

  fetch('../backend/api/render-calendar.php')
    .then(response => response.json())
    .then(data => {
      const events = data.events;
      events.forEach(event => {
        calendar.addEvent({
          id: event.TrainingID,
          title: event.Title,
          start: event.StartTime,
          end: event.EndTime,
          extendedProps: {
            createdBy: event.FirstName + ' ' + event.LastName,
            description: event.Description,
            location: event.Location,
            price : event.Price,
            participants: event.Participants,
            maxParticipants: event.MaxParticipants
          }
        });
      });
    })

  calendar.render();
});

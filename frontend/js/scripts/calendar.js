document.addEventListener('DOMContentLoaded', function () {
  let tempStart = null;
  let tempEnd = null;

  const calendarEl = document.getElementById('calendar');
  const popup = document.getElementById('eventPopup');
  const form = document.getElementById('eventForm');
  const detail = document.getElementById('eventDetail');
  const cancelBtn = document.getElementById('cancelEventBtn');
  const joinBtn = document.getElementById('joinBtn');

  const calendar = new FullCalendar.Calendar(calendarEl, {
    locale: 'pl',
    initialView: 'timeGridWeek',
    allDaySlot: false,
    slotMinTime: '06:00:00',
    slotMaxTime: '23:59:00',
    selectable: true,
    editable: true,
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
    select: function (info) {
      tempStart = info.startStr;
      tempEnd = info.endStr;
      document.getElementById('eventStart').value = tempStart;
      document.getElementById('eventEnd').value = tempEnd;
      popup.style.display = 'block';
    },
    eventClick: function (event) {
      detailTitle.innerText = event.event.title;
      detailDescription.innerText = event.event.extendedProps.description;
      detailLocation.innerText = event.event.extendedProps.location;
      detailPrice.innerText = event.event.extendedProps.price;
      detailCreator.innerText = event.event.extendedProps.createdBy;
      detailParticipants.innerText = event.event.extendedProps.participants;
      detailMax.innerText = event.event.extendedProps.maxParticipants;
      detailStart.innerText = event.event.start.toLocaleString();
      detailEnd.innerText = event.event.end.toLocaleString();

      joinBtn.addEventListener('click', function () {
        window.location.href = `../../backend/api/join-training.php?trainingId=${event.event.id}`;
      });

      detail.style.display = 'block';
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

  fetch('../../backend/api/render-calendar.php')
    .then(response => response.json())
    .then(data => {
      data.forEach(event => {
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

  cancelBtn.addEventListener('click', function () {
    popup.style.display = 'none';
    form.reset();
  });

  cancelDetailBtn.addEventListener('click', function () {
    detail.style.display = 'none';
  });
});

document.addEventListener('DOMContentLoaded', function () {
  let tempStart = null;
  let tempEnd = null;

  const calendarEl = document.getElementById('calendar');
  const popup = document.getElementById('eventPopup');
  const form = document.getElementById('eventForm');
  const detail = document.getElementById('eventDetail');
  const cancelBtn = document.getElementById('cancelEventBtn');
  const joinBtn = document.getElementById('joinBtn');
  const editBtn = document.getElementById('editBtn');
  const deleteBtn = document.getElementById('deleteBtn');

  function formatLocalDateTime(date) {
    const offset = date.getTimezoneOffset();
    const localDate = new Date(date.getTime() - offset * 60 * 1000);
    return localDate.toISOString().slice(0, 16);
  }

  const calendar = new FullCalendar.Calendar(calendarEl, {
    locale: 'pl',
    initialView: 'timeGridWeek',
    allDaySlot: false,
    slotMinTime: '06:00:00',
    slotMaxTime: '23:59:00',
    selectable: true,
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
    select: function (info) {
      tempStart = info.startStr;
      tempEnd = info.endStr;
      document.getElementById('eventStart').value = tempStart;
      document.getElementById('eventEnd').value = tempEnd;
      popup.style.display = 'block';
    },
    eventDidMount: function(info) {
      info.el.style.cursor = 'pointer';

      info.el.addEventListener('mouseenter', function () {
          info.el.style.filter = 'brightness(1.2)';
          info.el.style.boxShadow = '0 0 10px rgba(0,0,0,0.3)';
          info.el.style.transition = '0.2s all ease-in-out';
      });

      info.el.addEventListener('mouseleave', function () {
          info.el.style.filter = 'none';
          info.el.style.boxShadow = 'none';
      });
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

      fetch('../../backend/api/can-edit-training.php?trainingId=' + event.event.id)
        .then(response => response.json())
        .then(data => {
          if (data.canEdit) {
            editBtn.style.display = 'block';
            editBtn.addEventListener('click', function () {
              detail.style.display = 'none';
              editForm.style.display = 'block';

              editEventId.value = event.event.id;
              editEventTitle.value = event.event.title;
              editEventDescription.value = event.event.extendedProps.description;
              editEventLocation.value = event.event.extendedProps.location;
              editEventPrice.value = event.event.extendedProps.price;
              editEventStart.value = formatLocalDateTime(event.event.start);
              editEventEnd.value = formatLocalDateTime(event.event.end);
              editEventMax.value = event.event.extendedProps.maxParticipants;
            });
            deleteBtn.style.display = 'block';
            deleteBtn.addEventListener('click', function () {
              window.location.href = `../../backend/api/delete-training.php?trainingId=${event.event.id}`;
            });
          } else {
            joinBtn.style.display = 'block';
            joinBtn.addEventListener('click', function () {
              window.location.href = `../../backend/api/join-training.php?trainingId=${event.event.id}`;
            });
          }
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
      const events = data.events;
      const role = data.role;
      events.forEach(event => {
        calendar.addEvent({
          id: event.TrainingID,
          title: event.Title,
          start: event.StartTime,
          end: event.EndTime,
          extendedProps: {
            createdBy: event.createdBy,
            description: event.Description,
            location: event.Location,
            price : event.Price,
            participants: event.Participants,
            maxParticipants: event.MaxParticipants
          }
        });
      });
      if(role === 'client'){
        calendar.setOption('selectable', false);
      }
    })

  calendar.render();

  cancelBtn.addEventListener('click', function () {
    popup.style.display = 'none';
    form.reset();
  });

  cancelDetailBtn.addEventListener('click', function () {
    detail.style.display = 'none';
    editBtn.style.display = 'none';
    deleteBtn.style.display = 'none';
    joinBtn.style.display = 'none';
  });
});

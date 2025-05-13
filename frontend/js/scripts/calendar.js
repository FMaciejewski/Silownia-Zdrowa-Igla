document.addEventListener('DOMContentLoaded', function () {
  let tempStart = null;
  let tempEnd = null;

  const calendarEl = document.getElementById('calendar');
  const popup = document.getElementById('eventPopup');
  const form = document.getElementById('eventForm');
  const cancelBtn = document.getElementById('cancelEventBtn');

  const calendar = new FullCalendar.Calendar(calendarEl, {
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
    select: function (info) {
      tempStart = info.startStr;
      tempEnd = info.endStr;
      document.getElementById('eventStart').value = tempStart;
      document.getElementById('eventEnd').value = tempEnd;
      popup.style.display = 'block';
    },
    eventContent: function (arg) {
      const data = arg.event.extendedProps;
      const title = arg.event.title;
      const div = document.createElement('div');
      div.innerHTML = `<b>${title}</b>
        ${data.createdBy ? `<br><small>Twórca: ${data.createdBy}</small>` : ''}
        ${data.maxParticipants ? `<br><small>Limit: ${data.maxParticipants} osób</small>` : ''}`;
      return { domNodes: [div] };
    },
    events: []
  });

  calendar.render();

  cancelBtn.addEventListener('click', function () {
    popup.style.display = 'none';
    form.reset();
  });
});

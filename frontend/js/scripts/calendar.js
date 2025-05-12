$(document).ready(function () {
  $('#calendar').fullCalendar({
    header: {
      left: 'prev,next today',
      center: 'title',
      right: ''
    },
    defaultView: 'agendaWeek',
    allDaySlot: false,
    minTime: '06:00:00',
    maxTime: '23:59:00',
    editable: true,
    selectable: true,
    selectHelper: true,

    eventRender: function(event, element) {
    if (event.createdBy || event.maxParticipants) {
        element.find('.fc-title').append(
        "<br/><small>Twórca: " + event.createdBy + "</small>" +
        "<br/><small>Limit: " + event.maxParticipants + " osób</small>"
        );
    }
    },

    select: function (start, end) {
        const title = prompt('Wpisz tytuł wydarzenia:');
        const createdBy = prompt('Kto to stworzył?');
        const maxParticipants = prompt('Ile osób max?');

      if (title) {
        $('#calendar').fullCalendar('renderEvent', {
          title: title,
          start: start,
          end: end,
          allDay: false,
          createdBy: createdBy,
            maxParticipants: maxParticipants
        }, true);
      }

      $('#calendar').fullCalendar('unselect');
    },

    events: [
      {
        title: 'Trening nóg',
        start: '2025-05-12T10:00:00',
        end: '2025-05-12T11:00:00',
        createdBy: 'user1',
        maxParticipants: 5,
      },
      {
        title: 'Kac morderca',
        start: '2025-05-13T13:00:00',
        end: '2025-05-13T14:30:00',
        createdBy: 'user2',
        maxParticipants: 10,
      }
    ]
     
  });
});

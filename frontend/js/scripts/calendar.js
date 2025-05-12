let tempStart = null;
let tempEnd = null;

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

    select: function (start, end) {
        tempStart = start;
        tempEnd = end;
        $('#eventPopup').show();
    },

    eventRender: function (event, element) {
      if (event.createdBy || event.maxParticipants) {
        element.find('.fc-title').append(
          "<br/><small>Twórca: " + event.createdBy + "</small>" +
          "<br/><small>Limit: " + event.maxParticipants + " osób</small>"
        );
      }
    },

    events: []
  });

  $('#saveEventBtn').click(function (e) {
    e.preventDefault();

    const title = $('#eventTitle').val();
    const creator = $('#eventCreator').val();
    const max = $('#eventMax').val();

    if (title && creator && max) {
      $('#calendar').fullCalendar('renderEvent', {
        title: title,
        start: tempStart,
        end: tempEnd,
        allDay: false,
        createdBy: creator,
        maxParticipants: max
      }, true);

      $('#eventPopup').hide();
      $('#eventTitle').val('');
      $('#eventCreator').val('');
      $('#eventMax').val('');
      $('#calendar').fullCalendar('unselect');
    } else {
      alert('Proszę wypełnić wszystkie pola!');
    }
  });

  $('#cancelEventBtn').click(function () {
    $('#eventPopup').hide();
    $('#calendar').fullCalendar('unselect');
  });
});

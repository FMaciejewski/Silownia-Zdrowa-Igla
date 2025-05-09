const Calendar = tui.Calendar;

const calendar = new Calendar('#calendar', {
defaultView: 'week',
usageStatistics: false,
taskView: false,
scheduleView: ['time'],
template: {
    monthDayname: ({ day }) => `<span class="day-name">${day}</span>`
}
});

calendar.createEvents([
{
    id: '1',
    calendarId: '1',
    title: 'Siłka z koksem',
    category: 'time',
    start: '2025-05-10T10:30:00',
    end: '2025-05-10T12:30:00',
    color: '#fff',
    bgColor: '#e91e63',
    dragBgColor: '#e91e63',
    borderColor: '#e91e63',
    isReadOnly: false
},
{
    id: '2',
    calendarId: '1',
    title: 'Cardio z płaczem',
    category: 'time',
    start: '2025-05-15T14:00:00',
    end: '2025-05-15T15:00:00',
    bgColor: '#0077cc',
    borderColor: '#0077cc',
    dragBgColor: '#0077cc'
}
]);

calendar.setOptions({
week: {
    startDayOfWeek: 1,
    visibleWeeksCount: 2,
    dayNames: ['Ndz' ,'Pon', 'Wt', 'Śr', 'Czw', 'Pt', 'Sob'],
    taskView: false,
    milestoneView: false,
}
});

function addEvent() {
const nazwa = prompt("Podaj nazwę wydarzenia:");
calendar.createEvents([{
    id: '3',
    calendarId: '1',
    title: nazwa,
    category: 'time',
    start: '2025-05-10T12:30:00',
    end: '2025-05-10T15:30:00',
    color: '#fff',
    bgColor: '#e91e63',
    dragBgColor: '#e91e63',
    borderColor: '#e91e63',
    isReadOnly: false
}]);
}
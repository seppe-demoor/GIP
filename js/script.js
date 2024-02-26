var calendar;
var Calendar = FullCalendar.Calendar;
var events = [];

$(function() {
    if (!!scheds) {
        Object.keys(scheds).map(k => {
            var row = scheds[k];
            events.push({ id: row.id, title: row.title, start: row.start_time, end: row.end_time });
        });
    }

    var date = new Date();
    var d = date.getDate(),
        m = date.getMonth(),
        y = date.getFullYear();

    calendar = new Calendar(document.getElementById('calendar'), {
        headerToolbar: {
            left: 'prev,next today',
            right: 'dayGridMonth,dayGridWeek,list',
            center: 'title',
        },
        selectable: true,
        themeSystem: 'bootstrap',
        events: events,
        eventClick: function(info) {
            var _details = $('#event-details-modal');
            var id = info.event.id;
            if (!!scheds[id]) {
                _details.find('#title').text(scheds[id].title);
                _details.find('#description').text(scheds[id].description);
                _details.find('#start').text(new Date(scheds[id].start_time).toLocaleString('nl-NL'));
                _details.find('#end').text(new Date(scheds[id].end_time).toLocaleString('nl-NL'));
                _details.find('#edit,#delete').attr('data-id', id);
                _details.modal('show');
            } else {
                alert("Evenement is niet gedefinieerd");
            }
        },
        eventDidMount: function(info) {
            // Do Something after events mounted
        },
        editable: true
    });

    calendar.render();

    // Form reset listener
    $('#schedule-form').on('reset', function() {
        $(this).find('input:hidden').val('');
        $(this).find('input:visible').first().focus();
    });

    // Edit Button
    $('#edit').click(function() {
        var id = $(this).attr('data-id');
        if (!!scheds[id]) {
            var _form = $('#schedule-form');
            _form.find('[name="id"]').val(id);
            _form.find('[name="title"]').val(scheds[id].title);
            _form.find('[name="description"]').val(scheds[id].description);
            _form.find('[name="start_time"]').val(new Date(scheds[id].start_time).toISOString().slice(0, 16));
            _form.find('[name="end_time"]').val(new Date(scheds[id].end_time).toISOString().slice(0, 16));
            $('#event-details-modal').modal('hide');
            _form.find('[name="title"]').focus();
        } else {
            alert("Evenement is niet gedefinieerd");
        }
    });

    // Delete Button / Deleting an Event
    $('#delete').click(function() {
        var id = $(this).attr('data-id');
        if (!!scheds[id]) {
            var _conf = confirm("Weet u zeker dat u dit geplande evenement wilt verwijderen?");
            if (_conf === true) {
                location.href = "./delete_schedule.php?id=" + id;
            }
        } else {
            alert("Evenement is niet gedefinieerd");
        }
    });
});

[].push.apply(FullCalendar.globalLocales, function () {
  'use strict';

  var l0 = {
    code: 'af',
    week: {
      dow: 1, // Maandag is die eerste dag van die week.
      doy: 4, // Die week wat die 4de Januarie bevat is die eerste week van die jaar.
    },
    buttonText: {
      prev: 'Vorige',
      next: 'Volgende',
      today: 'Vandag',
      year: 'Jaar',
      month: 'Maand',
      week: 'Week',
      day: 'Dag',
      list: 'Agenda',
    },
    allDayText: 'Heeldag',
    moreLinkText: 'Addisionele',
    noEventsText: 'Daar is geen gebeurtenisse nie',
  };

  

  var l1 = {
    code: 'nl',
    week: {
      dow: 1, // Monday is the first day of the week.
      doy: 4, // The week that contains Jan 4th is the first week of the year.
    },
    buttonText: {
      prev: 'Vorige',
      next: 'Volgende',
      today: 'Vandaag',
      year: 'Jaar',
      month: 'Maand',
      week: 'Week',
      day: 'Dag',
      list: 'Agenda',
    },
    allDayText: 'Hele dag',
    moreLinkText: 'extra',
    noEventsText: 'Geen evenementen om te laten zien',
  };

  

  var localesAll = [
    l0, l1, 
  ];

  return localesAll;

}());

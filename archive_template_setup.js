'use strict';

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

events = events.map(function (event) {
  var dates = event.dates;

  var parsedDates = dates.map(function (date) {
    return _extends({}, date, {
      m: moment().year(Number(date.year)).month(Number(date.month) - 1).date(Number(date.day)).hour(0).minute(0).second(0)
    });
  }).filter(function (date) {
    return date.m.isAfter(moment());
  }).sort(function (a, b) {
    return a.m.unix() - b.m.unix();
  });
  return _extends({}, event, {
    dates: parsedDates,
    url: event.url.replace('#038;', '')
  });
});

var allDates = [];
events.forEach(function (event) {
  event.dates.forEach(function (date) {
    allDates.push({
      m: moment(date.m),
      eventId: event.id
    });
  });
});

allDates = allDates.sort(function (a, b) {
  return a.m.unix() - b.m.unix();
});
var firstDate = moment(allDates[0].m);
var lastDate = moment(allDates[allDates.length - 1].m);

var months = [];
// month = { month: 0 - 11, year: 1970 - 2200, events: [ array of event ids ] }
//set the iterator to the first month
var iterator = moment(firstDate).date(1);

var _loop = function _loop() {
  //iterate trough all events and get the ids of events that are this month
  var eventsIndexes = [];
  events.forEach(function (event, index) {
    if (eventsIndexes.indexOf(index) !== -1) {
      return;
    }
    event.dates.forEach(function (date) {
      if (eventsIndexes.indexOf(index) !== -1) {
        return;
      }
      //if date is in the current month
      if (date.m.isAfter(iterator) && date.m.isBefore(moment(iterator).add(1, 'months'))) {
        eventsIndexes.push(index);
      }
    });
  });
  months.push({
    number: iterator.month(),
    name: monthLocales[iterator.month()],
    year: iterator.year(),
    eventsIndexes: [].concat(eventsIndexes)
  });
  iterator.add(1, 'months');
};

while (iterator.isBefore(lastDate)) {
  _loop();
}
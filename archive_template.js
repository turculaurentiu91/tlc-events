var app = new Vue({
  el: '#archive-template',
  data: {
    events: {...events},
    months: {...months},
  },
  computed: {},
  methods: {
    filteredDatesByMonth: function(event, monthNumber) {
      return event.dates.filter(function(date) {
        return date.m.month() === monthNumber;
      }).sort((a,b) => a.m.unix() - b.m.unix());
    },
  },
});
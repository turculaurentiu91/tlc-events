function generateSlug(string) {
  return string.toLowerCase().replace(" ", "_").replace(/[^a-zA-Z-_]/g, "");
}

var ID = function () {
  return '_' + Math.random().toString(36).substr(2, 9);
};

Vue.component('nav-link', {
  props: ['active'],
  template: '<a href="#" \
    class="w3-bar-item w3-button" \
    v-bind:class="{\'w3-gray\' : active}"\
    v-on:click.prevent="$emit(\'change-page\')"\
    ><slot></slot></a>',
});

Vue.component('date-input', {
  props: {
    date: {
      type: Object,
      default: function() {
        var date = new Date();
        return {
          day: date.getDate(),
          month: date.getMonth() +1,
          year: date.getFullYear(),
          locations: [],
        }
      }
    },
    dayLabel: {type: String, default: 'Day'}, 
    monthLabel: {type: String, default: 'Month'}, 
    yearLabel: {type: String, default: 'Year'},
    startHourLabel: {type: String},
    endHourLabel: {type: String},
    startMinLabel: {type: String},
    endMinLabel: {type: String},
    index: {type: Number},
  },

  template: `
  <div class="w3-row-padding w3-padding" v-bind:class="{'w3-light-gray' : !(index % 2)}">
    <div class="w3-col w3-margin" style="width: 10em;"><h4>Date {{index+1}}:</h4></div>

    <div class="w3-col" style="width: 15em;">
      <label>Datum</label>
      <date-input-field 
        v-bind:day="date.day"
        v-bind:month="date.month"
        v-bind:year="date.year"
        v-on:change-day="$emit('change-day', {index: index,  value: $event})"
        v-on:change-month="$emit('change-month', {index: index,  value: $event})"
        v-on:change-year="$emit('change-year', {index: index,  value: $event})"
      ></date-input-field>
    </div>
    
    <div class="w3-col" style="width: 9em;">
      <label>Beginnend bij</label>
      <time-input
        v-bind:hour="date.startHour"
        v-bind:min="date.startMin"
        v-on:change-hour="$emit('change-start-hour', {index: index,  value: $event})"
        v-on:change-min="$emit('change-start-min', {index: index,  value: $event})"
      ></time-input>
    </div>

    <div class="w3-col" style="width: 9em;">
      <label>Eindigend op</label>
      <time-input
        v-bind:hour="date.endHour"
        v-bind:min="date.endMin"
        v-on:change-hour="$emit('change-end-hour', {index: index,  value: $event})"
        v-on:change-min="$emit('change-end-min', {index: index,  value: $event})"
      ></time-input>
    </div>
    <div class="w3-rest w3-right-align">
      <button class="w3-button w3-margin w3-red w3-round" @click.prevent="$emit('delete-date', index)"><span class="dashicons dashicons-no"></span></button>
    </div>
  </div> 
`,
});

Vue.component('date-input-field', {
  props: ['day', 'month', 'year'],
  template: `
    <div class="custom-input" v-bind:class="{'custom-input--focused':focused}"
      @click.self="onClick">
        <input class="custom-input__input" type="text" style="text-align: right;"
        @focus="focusDay" @blur="blurDay"
        v-bind:value="day" ref="dayInput" @input.prevent="changeDay">
        <span class="custom-input__colon">/</span>
        <input class="custom-input__input" type="text" style="text-align: center;"
        @focus="focusMonth" @blur="blurMonth" @input.prevent="changeMonth"
        v-bind:value="month" ref="monthInput">
        <span class="custom-input__colon">/</span>
        <input class="custom-input__input custom-input__input--wide" 
        type="text" style="text-align: left;"
        @focus="focusYear" @blur="blurYear" @input.prevent="changeYear"
        v-bind:value="year" ref="yearInput">
    </div>
  `,
  data: function() { return {
    focused: false,
    tempDay: this.day,
    tempMonth: this.month,
    tempYear: this.year,
  }; },
  methods: {
    onClick: function(e){
      this.$refs.dayInput.focus();
    },
    changeDay: function(e){
      let val = e.target.value;
      if (val.length >= 2) {
        this.$refs.monthInput.focus();
        val = val.slice(0, 2);
        this.$emit('change-day', val);
      }
      this.tempDay = val;
    },
    focusDay: function(e){
      this.focused = true;
      this.$refs.dayInput.select();
    },
    blurDay: function(e){
      this.focused = false;
      this.$emit(
        'change-day', 
        moment().date(this.tempDay).format("DD")
      )
    },
    changeMonth: function(e){
      let val = e.target.value;
      if (val.length >= 2) {
        this.$refs.yearInput.focus();
        val = val.slice(0, 2);
        this.$emit('change-month', val);
      }
      this.tempMonth = val;
    },
    focusMonth: function(e){
      this.focused = true;
      this.$refs.monthInput.select();
    },
    blurMonth: function(e){
      this.focused = false;
      this.$emit(
        'change-month', 
        moment().month(this.tempMonth -1).format("MM")
      )
    },
    changeYear: function(e){
      let val = e.target.value;
      if (val.length >= 4) {
        //this.$refs.yearInput.blur();
        val = val.slice(0, 4);
        this.$emit('change-year', val);
      }
      this.tempYear = val;
    },
    focusYear: function(e){
      this.focused = true;
      this.$refs.yearInput.select();
    },
    blurYear: function(e){
      this.focused = false;
      this.$emit(
        'change-year', 
        moment().year(this.tempYear).format("YYYY")
      )
    },
  },
});

Vue.component('time-input', {
  template: `
  <div class="custom-input" v-bind:class="{'custom-input--focused':focused}"
    @click.self="onClick">
      <input class="custom-input__input" type="text" style="text-align: right;"
      @focus="focusHour" @blur="blurHour"
      v-bind:value="hour" ref="hourInput" @input.prevent="changeHour">
      <span class="custom-input__colon">:</span>
      <input class="custom-input__input" type="text" 
      style="text-align: left;"
      @focus="focusMin" @blur="blurMin" @input.prevent="changeMin"
      v-bind:value="min" ref="minInput">
  </div>
  `,
  props: ['hour', 'min'],
  data: function() { return {
    focused: false,
    tempHour: this.hour,
    tempMin: this.min,
  }; },
  computed: {
  },
  methods: {
    changeHour: function(e) {
      let val = e.target.value;
      if (val.length >= 2) {
        this.$refs.minInput.focus();
        val = val.slice(0, 2);
        this.$emit('change-hour', val);
      }
      this.tempHour = val;
    },
    changeMin: function(e) {
      let val = e.target.value;
      if (val.length >= 2) {
        //this.$refs.minInput.blur();
        val = val.slice(0, 2);
        this.$emit('change-min', val);
      }
      this.tempMin = val;
    },
    blurHour: function(){
      this.focused = false;
      this.$emit(
        'change-hour',
        moment().hour(Number(this.tempHour)).format("HH")
      );
    },
    blurMin: function(){
      this.focused = false;
      this.$emit(
        'change-min',
        moment().minute(Number(this.tempMin)).format("mm")
      );
    },
    focusMin: function(e) {
      this.focused = true;
      e.target.select();
    },
    focusHour: function(e) {
      this.focused = true;
      e.target.select();
    },
    onClick: function(e) {
      this.$refs.hourInput.focus();
    }
  },

})

Vue.component('location-input', {
  props: ['nameLabel', 'index', 'location', 'startMinLabel', 'startHourLabel', 'endMinLabel', 'endHourLabel', 'cityLabel', 'addressLabel'],
  template: `
  <div class="w3-padding w3-padding-24" v-bind:class="{\'w3-light-gray\' : !(index % 2)}">
    <div class="w3-row-padding">
      <div class="w3-col" style="width: 20em;">
        <label>{{nameLabel}}</label>
        <input 
          type="text" v-bind:value="location.name" class="w3-input w3-border" v-bind:placeholder="nameLabel"
          @input="$emit(\'change-name\', {index: index, value: $event.target.value})"
        >
      </div>

      <div class="w3-col" style="width: 15em;">
        <label>{{cityLabel}}</label>
        <input 
          type="text" v-bind:value="location.city" class="w3-input w3-border" v-bind:placeholder="cityLabel"
          @input="$emit(\'change-city\', {index: index, value: $event.target.value})"
        >
      </div>

      <div class="w3-col" style="width: 9em;">
        <label>Beginnend bij</label>
        <time-input
          v-bind:hour="location.startHour"
          v-bind:min="location.startMin"
          v-on:change-hour="$emit('change-start-hour', {index: index,  value: $event})"
          v-on:change-min="$emit('change-start-min', {index: index,  value: $event})"
        ></time-input>
      </div>

      <div class="w3-col" style="width: 9em;">
        <label>Eindigend op</label>
        <time-input
          v-bind:hour="location.endHour"
          v-bind:min="location.endMin"
          v-on:change-hour="$emit('change-end-hour', {index: index,  value: $event})"
          v-on:change-min="$emit('change-end-min', {index: index,  value: $event})"
        ></time-input>
      </div>

      <div class="w3-col" style="width: 5em;">
        <label>Positie</label>
        <input 
          type="text" v-bind:id="'tlc-loc-pos-' + location.id" v-bind:value="location.position" class="w3-input w3-border" placeholder="Positie"
          @input="$emit(\'change-position\', {index: index, value: $event.target.value})"
        >
      </div>

      <div class="w3-rest w3-right-align">
        <button 
          class="w3-button w3-red w3-round w3-margin-top" 
          @click.prevent="$emit(\'delete-location\', index)"
        >
          <span class="dashicons dashicons-no"></span>
        </button>
      </div>
    </div>
    <div class="w3-row-padding w3-padding">
      <div class="w3-rest">
        <label>{{addressLabel}}</label>
        <input 
          type="text" v-bind:value="location.address" class="w3-input w3-border" v-bind:placeholder="addressLabel"
          @input="$emit(\'change-address\', {index: index, value: $event.target.value})"
        > 
      </div>
    </div>
  </div>
  `,
});

Vue.component('form-input', {
  props: ['index', 'label', 'value', 'slug', 'position'],
  template: `
  <div class="w3-padding w3-padding-24" v-bind:class="{\'w3-light-gray\' : !(index % 2)}">
    <div class="w3-row-padding">
      <div class="w3-col l1 w3-padding"><label>{{label}}</label></div>
      <div class="w3-col l3">
        <input class="w3-input w3-border" type="text" v-bind:value="value" 
        v-on:input="$emit('input', {index: index, value: $event.target.value })" >
      </div>
      <div class="w3-col l2 w3-padding"><label>Slug: {{slug}}</label></div>
      <div class="w3-col l1 w3-padding"><label>Positie</label></div>
      <div class="w3-col l2">
        <input class="w3-input w3-border" type="text" v-bind:value="position" v-bind:id="'tlc-form-fields-'+slug"
        v-on:input="$emit('input-position', {index: index, value: $event.target.value })" >
      </div>
      <div class="w3-rest w3-right-align">
        <button 
          class="w3-button w3-red w3-round" 
          @click.prevent="$emit(\'delete\', index)"
        >
          <span class="dashicons dashicons-no"></span>
        </button>
      </div>
    </div>
  </div>
  `,
});



const app = new Vue({
  el: '#event-metabox',
  data: {
    page: 'dates',
    dates: rawDates === "" ? [{
      day: new Date().getDate(),
      month: new Date().getMonth() +1,
      year: new Date().getFullYear(),
      id: ID(),
      locations: [],
      startHour: new Date().getHours(),
      startMin: new Date().getMinutes(),
      endHour: new Date().getHours(),
      endMin: new Date().getMinutes(),
    }] : JSON.parse(window.atob(rawDates)),
    formFields: rawFormFields === '' ? [{value: 'Email', slug: generateSlug('Email'), position: 0},] : JSON.parse(window.atob(rawFormFields)),
    locationsSelectedDate: 0,
    subsSelectedDate: 0,
    subsSelectedLoc: 0,
  },

  computed: {
    jsonDates: function() {
      return window.btoa(JSON.stringify(this.dates));
    },

    jsonFormFields: function(){
      return window.btoa(JSON.stringify(this.formFields));
    },

    locations: function() {
      return this.dates[this.locationsSelectedDate].locations;
    },
  },

  methods: {
    exportToCsv: function() {
      var a = window.document.createElement('a');
    
      var csvString = Papa.unparse(
        JSON.stringify(this.dates[this.subsSelectedDate].locations[this.subsSelectedLoc].subscriptions),
        { quotes: true }
      );
      a.href = window.URL.createObjectURL(new Blob([csvString], {type: 'text/csv'}));
      a.download = 'subscriptions.csv';

      // Append anchor to body.
      document.body.appendChild(a);
      a.click();

      // Remove anchor from body
      document.body.removeChild(a);
    },

    inputFormField: function(data) {
      this.formFields[data.index].value = data.value;
      this.formFields[data.index].slug = generateSlug(data.value);
    },

    newFormField: function(field) {
      this.formFields.push({
        value: field,
        slug: generateSlug(field),
        position: this.formFields.length,
      });
    },

    changeFormFieldsPos: function(data) {
      this.formFields[data.index].position = Number(data.value);
      this.formFields = this.formFields.sort((a,b) => a.position-b.position);
      jQuery('#tlc-form-fields-' + this.formFields[data.index].slug).focus();
    },

    deleteSub: function(sub){
      this.dates[this.subsSelectedDate].locations[this.subsSelectedLoc].subscriptions =
        this.dates[this.subsSelectedDate].locations[this.subsSelectedLoc].subscriptions.filter(s => s !== sub);
    },

    deleteFormField: function(indexToDelete){
      this.formFields = this.formFields.filter(function(field, index){
        return index !== indexToDelete;
      });
    },

    changeLocationsSelectedDate: function(event) {
      this.locationsSelectedDate = Number(event.target.value);
    },

    newLocation: function(time) {
      const date = new Date();
      this.dates[this.locationsSelectedDate].locations.push({
        name: '',
        city: '',
        address: '',
        startHour: time.startHour,
        endHour: time.endHour,
        startMin: time.startMin,
        endMin: time.endMin,
        id: ID(),
        subscriptions: [],
        position: this.locations.length,
      });
    },

    newDate: function() {
      const date = new Date();
      this.dates.push({
        day: date.getDate(),
        month: date.getMonth() +1,
        year: date.getFullYear(),
        locations: [],
        id: ID(),
        startHour: date.getHours(),
        startMin: date.getMinutes(),
        endHour: date.getHours(),
        endMin: date.getMinutes(),
      });
    },

    changeCity: function(data) {
      this.dates[this.locationsSelectedDate].locations[data.index].city = data.value;
    },

    changeLocPosition: function(data) {
      this.dates[this.locationsSelectedDate].locations[data.index].position = Number(data.value);
      this.dates[this.locationsSelectedDate].locations = 
        this.dates[this.locationsSelectedDate].locations.sort((a, b) => a.position - b.position);
      jQuery(
        '#tlc-loc-pos-' + 
        this.dates[this.locationsSelectedDate].locations[data.index].id
      ).focus();
    },

    changeName: function(data) {
      this.dates[this.locationsSelectedDate].locations[data.index].name = data.value;
    },

    changeAddress: function(data) {
      this.dates[this.locationsSelectedDate].locations[data.index].address = data.value;
    },

    changeLocStartHour: function(data) {
      data.value = data.value;
      this.dates[this.locationsSelectedDate].locations[data.index].startHour = data.value;
    },
    
    changeLocStartMin: function(data) {
      data.value = data.value;
      this.dates[this.locationsSelectedDate].locations[data.index].startMin = data.value;
    },

    changeLocEndHour: function(data) {
      data.value = data.value;
      this.dates[this.locationsSelectedDate].locations[data.index].endHour = data.value;
    },
    
    changeLocEndMin: function(data) {
      data.value = data.value;
      this.dates[this.locationsSelectedDate].locations[data.index].endMin = data.value;
    },
    
    changeStartHour: function(data) {
      data.value = data.value;
      this.dates[data.index].startHour = data.value;
    },
    
    changeEndHour: function(data) {
      data.value = data.value;
      this.dates[data.index].endHour = data.value;
    },
    
    changeStartMin: function(data) {
      data.value = data.value;
      this.dates[data.index].startMin = data.value;
    },

    changeEndMin: function(data) {
      data.value = data.value;
      this.dates[data.index].endMin = data.value;
    },

    deleteLocation: function(indexToDelete) {

      this.dates[this.locationsSelectedDate].locations =
      this.dates[this.locationsSelectedDate].locations.filter(
        function(loc, index) {
          return index !== indexToDelete;
        }
      );
    },

    changeDateMonth: function(data) {
      data.value = data.value;
      this.dates[data.index].month = data.value;
    },
    changeDateDay: function(data) {
      data.value = data.value;
      this.dates[data.index].day = data.value;
    },
    changeDateYear: function(data) {
      data.value = data.value;
      this.dates[data.index].year = data.value;
    },

    copyLocations: function() {
      const locations = this.dates[this.locationsSelectedDate].locations;
      this.dates = this.dates.map(function(date, index){
        if (index === this.locationsSelectedDate) {
          return date
        }
        return {
          ...date, 
          locations: [...locations],
        }
      });
    },

    deleteDate(indexToRemove) {
      if (this.locationsSelectedDate === indexToRemove && this.locationsSelectedDate !== 0 && indexToRemove === this.dates.length -1) {
        this.locationsSelectedDate =  this.locationsSelectedDate -1;
      }

      this.dates = this.dates.filter(function(date, index) {return index !== indexToRemove});
    }
  }
});
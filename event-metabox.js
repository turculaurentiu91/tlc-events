function generateSlug(string) {
  return string.toLowerCase().replace(" ", "_").replace("-","_").replace(/[^a-zA-Z-_]/g, "");
}

var ID = function () {
  return '_' + Math.random().toString(36).substr(2, 9);
};

Vue.component('filter-sub', {
  props: ['filterFields'],
  template: `
    <div class="w3-panel">
      <span v-for="(field, key) in filterFields" class="filter-el">
        <input type="checkbox" v-bind:checked="field"
        @click.prevent="$emit('check', {key: key, value: $event.target.checked})">
        <label>{{key}}</label>
      </span>
    </div>
  `
})

Vue.component('insert-sub', {
  props: ['dateId', 'locationId', 'eventId', 'formFields', 'show', 'apiEndpoint'],
  data: function() { return {
    formData: {},
    fetching: false,
  }; },
  created: function() {
    this.formFields.forEach(field => {
      this.$set(this.formData, field.slug, '');
    });
  },
  methods: {
    close: function() { 
      this.$emit('close');
      this.formFields.forEach(field => {
        this.$set(this.formData, field.slug, '');
      });
      this.fetching = false; 
    },
    submit: function() {
      this.fetching = true;
      const req_data = {
        event_id: this.eventId,
        location_id: this.locationId,
        date_id: this.dateId,
        ...this.formData,
      };
      fetch(this.apiEndpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json; charset=utf-8', },
        body: JSON.stringify(req_data),
      })
      .then(res => res.json())
      .then(res => {
        this.$emit('added', res);
        this.close();
      })
      .catch(err => {
        console.error(err);
        this.fetching = false;
        this.$emit('close');
      });
    }
  },
  template: `
  <div style="display: block; z-index: 1000" v-if="show" class="w3-modal">
    <div class="w3-modal-content">
      <div class="w3-container">
        <span @click="close" 
        class="w3-button w3-display-topright">&times;</span>
        <br>
        <div class="w3-panel" style="margin-top: 30px;">
          <form @submit.prevent="submit">
            <div class="w3-margin" v-for="field in formFields">
              <label v-bind:for="field.slug">{{field.value}}</label>
              <input 
                type="text" 
                class="w3-input w3-border" 
                v-model="formData[field.slug]" 
                v-if="field.type === 'text' || !field.type"
              >
              <textarea
               class="w3-input w3-border"
               v-model="formData[field.slug]"
               v-if="field.type === 'textarea'"
              ></textarea>

              <select 
                v-model="formData[field.slug]"
                v-if="field.type === 'select' && field.selectOptions"
                class="w3-select w3-border"
              >
                <option disabled selected value="">kies een</option>
                <option v-for="option in field.selectOptions">{{option}}</option>
              </select>

            </div>
            <div class="w3-margin">
              <button class="w3-button w3-teal w3-block" v-bind:disabled="fetching">
                {{fetching ? "indienen" : "voorleggen"}}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  `,
});

Vue.component('delete-sub', {
  props: ['dateId', 'locationId', 'eventId', 'subscriptionId', 'show', 'apiEndpoint'],
  data: function() { return {
    fetching: "initial",

  }; },
  methods: {
    deleteSub: function(notify) {
      this.fetching = 'fetching';
      fetch(this.apiEndpoint, {
        method: 'POST',
        headers: {'Content-Type': 'application/json; charset=utf-8'},
        body: JSON.stringify({
          'date_id': this.dateId,
          'location_id': this.locationId,
          'subscription_id': this.subscriptionId,
          'event_id': this.eventId,
          'notify': notify,
        })
      })
      .then(res => {
        if (!res.ok) {
          throw new Error('failed fetching');
        }
        return res.json();
      })
      .then(res => {
        this.fetching = "success";
        this.$emit('deleted', res);
      })
      .catch(error => {
        this.fetching = "error";
        console.error(error);
      })
    },
    deleteSubNoNotify() {
      this.deleteSub(undefined);
    },
    deleteSubWithNotify() {
      this.deleteSub(true);
    },
    close: function()  {
      this.fetching = "initial";
      this.$emit('close', {});
    }
  },
  template: `
  <div style="display: block; z-index: 1000" v-if="show" class="w3-modal">
    <div class="w3-modal-content">
      <div class="w3-container">
        <span @click="close" 
        class="w3-button w3-display-topright">&times;</span>
        <br>
        <div class="w3-panel" style="margin-top: 30px;">
          <div v-if="fetching == 'initial'">
            <h3 style="margin-bottom: 40px;">Wil je een e-mail sturen naar de deelnemer?</h3>
            <div class="w3-bar">
              <button class="w3-bar-item w3-button w3-green" @click.prevent="deleteSubWithNotify"
               style="width:33.3%">Ja</button>
              <button class="w3-bar-item w3-button w3-red" @click.prevent="deleteSubNoNotify"
              style="width:33.3%">Nee</button>
              <button class="w3-bar-item w3-button w3-teal" @click.prevent="close" 
              style="width:33.3%">Annuleren</button>
            </div>
          </div>
          <div v-if="fetching != 'initial'">
              <h3 v-if="fetching == 'fetching'">Aanmelding verwijderen</h3>
              <h3 v-if="fetching == 'success'">Succesvol verwijderd</h3>
              <h3 v-if="fetching == 'error'">Fout bij verwijderen van aanmelding</h3>

              <button class="w3-button w3-block w3-teal" v-bind:disabled="fetching == 'fetching'"
              @click.prevent="close">Annuleren</button>
            </div>
        </div>
      </div>
    </div>
  </div>
  `,
});

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
  props: ['nameLabel', 'index', 'location', 'customTime', 'cityLabel', 'addressLabel', 'dragging'],
  data: function() { return {
    dragged: false,
    dragover: false,
  }; },
  methods: {
    handleDragstart: function(e) {
      this.dragged = true;
      e.dataTransfer.setData('text/plain', this.index);
      //this.$emit('dragging', true);
    },
    handleDragend: function(e) {
      this.dragged = false;
    },
    handleDragover: function(e) {
      this.dragover = true;
    },
    handleDrop: function(e) {
      this.dragover = false;
      this.$emit('dragging', false);
      this.$emit('drop', {
        dragIndex: Number(e.dataTransfer.getData('text/plain')),
        dropIndex: Number(this.index),
        dropPos: Number(this.location.position),
      });
    }
  },
  template: `
  <div v-on:dragstart="handleDragstart" v-on:drop="handleDrop"
  draggable="true" v-on:dragover.prevent="handleDragover" 
  v-show="!dragged" v-on:dragend="handleDragend"
  v-on:dragleave.self="dragover = false">

    <div class="form-fields-dragover" v-show="dragover"
    v-on:dragleave.self="dragover = false">&nbsp;</div>

    <div class="w3-padding w3-padding-24" v-bind:class="{\'w3-light-gray\' : !(index % 2)}"
    v-on:dragleave.self="dragover = false">

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

        <div class="w3-col" style="width: 9em;" v-if="Number(customTime)">
          <label>Beginnend bij</label>
          <time-input
            v-bind:hour="location.startHour"
            v-bind:min="location.startMin"
            v-on:change-hour="$emit('change-start-hour', {index: index,  value: $event})"
            v-on:change-min="$emit('change-start-min', {index: index,  value: $event})"
          ></time-input>
        </div>

        <div class="w3-col" style="width: 9em;" v-if="Number(customTime)">
          <label>Eindigend op</label>
          <time-input
            v-bind:hour="location.endHour"
            v-bind:min="location.endMin"
            v-on:change-hour="$emit('change-end-hour', {index: index,  value: $event})"
            v-on:change-min="$emit('change-end-min', {index: index,  value: $event})"
          ></time-input>
        </div>
        <!--
        <div class="w3-col" style="width: 5em;">
          <label>Positie</label>
          <input 
            type="text" v-bind:id="'tlc-loc-pos-' + location.id" v-bind:value="location.position" class="w3-input w3-border" placeholder="Positie"
            @input="$emit(\'change-position\', {index: index, value: $event.target.value})"
          >
        </div> -->

        <div class="w3-rest w3-right-align">
          <button 
            class="w3-button w3-red w3-round w3-margin-top" 
            @click.prevent="$emit(\'delete-location\', index)"
          >
            <span class="dashicons dashicons-no"></span>
          </button>
        </div>
      </div>
      <div class="w3-row-padding w3-padding" v-show="!dragging">
        <div class="w3-rest">
          <label>{{addressLabel}}</label>
          <input 
            type="text" v-bind:value="location.address" class="w3-input w3-border" v-bind:placeholder="addressLabel"
            @input="$emit(\'change-address\', {index: index, value: $event.target.value})"
          > 
        </div>
      </div>
      <div class="w3-panel" v-show="!dragging">
        <input class="w3-check" type="checkbox" @click="$emit('checked', {index: index, value: $event.target.checked})" 
        v-bind:id="'loc-checkbox-'+index" v-bind:checked="Number(customTime)">
        <label v-bind:for="'loc-checkbox-'+index">Aangepaste tijden</label>
      </div>
    </div>
  </div>
  `,
});

Vue.component('form-input', {
  props: ['index', 'label', 'value', 'slug', 'position', "canDelete", "type", "selectOptions"],
  template: `
  <div v-show="!dragged" draggable="true" 
  v-on:dragover.prevent="draggedOver = true"
  v-on:dragstart="handleDrag"
  v-on:dragend="dragged = false" v-on:drop="handleDrop">

    <div v-if="draggedOver" v-on:dragleave.self="draggedOver = false" 
    class="form-fields-dragover">&nbsp;</div>

    <div class="w3-padding w3-padding-24" v-on:dragleave.self="draggedOver = false"
    v-bind:class="{\'w3-light-gray\' : !(index % 2)}">

      <div class="w3-row-padding">
        <div class="w3-col l1 w3-padding"><label>{{label}}</label></div>
        <div class="w3-col l3">
          <input class="w3-input w3-border" type="text" v-bind:value="value" 
          v-on:input="$emit('input', {index: index, value: $event.target.value })" 
          v-bind:disabled="canDelete">
        </div>
        <div class="w3-col l2 w3-padding"><label>Slug: {{slug}}</label></div>
        <div class="w3-col l2 w3-padding">
          <select 
            class="w3-select w3-border" 
            @change="$emit('type-change', { index: index, value: $event.target.value })"
            v-bind:disabled="canDelete"
          >
            <option value="text" v-bind:selected="computedType === 'text'">Tekst</option>
            <option value="textarea" v-bind:selected="computedType === 'textarea'">Tekstgebied</option>
            <option value="select" v-bind:selected="computedType === 'select'">Opties</option>
          </select>
        </div>
        <div class="w3-col l1 w3-padding" v-if="type == 'select'">
          <button class="w3-button" v-bind:disabled="canDelete" @click.prevent="displayMore = !displayMore">
            <span v-if="!displayMore">Opties <span class="dashicons dashicons-arrow-down-alt2"></span> </span>
            <span v-if="displayMore">Sluit opties <span class="dashicons dashicons-arrow-up-alt2"></span> </span>
          </button>
        </div>
        <div class="w3-rest w3-right-align">
          <button v-bind:disabled="canDelete"
            class="w3-button w3-red w3-round" 
            @click.prevent="$emit(\'delete\', index)"
          >
            <span class="dashicons dashicons-no"></span>
          </button>
        </div>
      </div>
      <div
        v-if="type === 'select' && displayMore"
        class="w3-panel"
      >
        <input type="text" v-model="displayMoreInput" class="regular-text" @keypress.enter="addNewOption" placeholder="Optie">
        <a href="#" class="button-secondary" @click.prevent="addNewOption" >Voeg een nieuwe optie toe</a>
        <div v-if="selectOptions">
          <span class="w3-tag w3-gray" style="margin: 5px;" v-for="(option, optionIndex) in selectOptions">
            <span style="cursor: pointer;" @click.prevent="$emit('remove-option', {index: index, optionIndex: optionIndex})">&times;</span>
            {{option}}
          </span>
        </div>
      </div>
    </div>
  </div>
  `,
  data: function() { return { 
    draggedOver: false,
    dragged: false,
    displayMore: false,
    displayMoreInput: '',
  }; },
  computed: {
    computedType() {
      return this.type ? this.type : "text";
    }
  },
  methods: {
    addNewOption(e) {
      e.preventDefault();
      this.$emit('add-option', {index: this.index, value: this.displayMoreInput});
      this.displayMoreInput = '';
    },

    handleDrag: function(e) {
      this.dragged = true;
      e.dataTransfer.setData('text/plain', this.index);
    },
    handleDrop: function(e) {
      this.draggedOver=false;
      const draggedIndex = e.dataTransfer.getData('text/plain');
      this.$emit('drop', {
        dragIndex: draggedIndex, 
        dropPos: this.position,
        dropIndex: this.index,
      });
    },
  },
});



const app = new Vue({
  el: '#event-metabox',
  data: {
    adminEmail: admin_email || "",
    page: 'dates',
    locDragging: false,
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
    formFields: rawFormFields === '' ? 
      [ 
        {value: 'Bedrijfsnaam', slug: generateSlug('Bedrijfsnaam'), position: 1 },
        {value: 'Voornaam', slug: generateSlug('Voornaam'), position: 2 },
        {value: 'Tussenvoegsels', slug: generateSlug('Tussenvoegsels'), position: 3 },
        {value: 'Achternaam', slug: generateSlug('Achternaam'), position: 4 },
        {value: 'E-mailadres', slug: generateSlug('E-mailadres'), position: 5 },
        {value: 'Telefoonnummer', slug: generateSlug('Telefoonnummer'), position: 6 },
      ] : 
      JSON.parse(window.atob(rawFormFields)),
    locationsSelectedDate: 0,
    subsSelectedDate: 0,
    subsSelectedLoc: 0,
    subsSelectedSub: 0,
    showDeleteModal: false,
    showInsertSubForm: false,
    filterFields: {},
  },

  created: function() {
    const filterFields = {};
    this.formFields.forEach(field => {
      filterFields[field.value] = true;
    });
    filterFields['Geregistreerd Op'] = true;
    filterFields['Verwijderd Op'] = true;
    filterFields['id'] = true;
    this.filterFields = filterFields;
  },

  watch: {
    formFields: function(newVal) {
      for (const key in this.filterFields) {
        if (
            newVal.findIndex(field => field.value === key) === -1 &&
            key !== "Geregistreerd Op" &&
            key !== "Verwijderd Op" &&
            key !== "id"
          ) {
          delete this.filterFields[key];
        }
      }

      newVal.forEach(field => {
        if (this.filterFields[field.value] === undefined) {
          this.$set(this.filterFields, field.value, true);
        }
      });
    }
  },

  computed: {

    hasAnySubs: function() {
      for (date of this.dates) {
        for (loc of date.locations) {
          if (loc.subscriptions.length) {
            return true;
          }
        }
      }
      return false;
    },

    formFieldsHasEmail: function() {
      return this.formFields.findIndex(field => field.slug === "e_mailadres") !== -1;
    },

    jsonDates: function() {
      return window.btoa(JSON.stringify(this.dates));
    },

    jsonFormFields: function(){
      return window.btoa(JSON.stringify(this.formFields));
    },

    locations: function() {
      return this.dates[this.locationsSelectedDate].locations;
    },

    subscriptions: function() {
      return this.dates[this.subsSelectedDate].locations[this.subsSelectedLoc].subscriptions;
    },

    subsTable: function() {
      
      return this.subscriptions.map(sub => {
        const mappedSub = {};

        for (const key of Object.keys(sub)) {
          let newKey = undefined;
          if (key === "geregistreerd_op") {
            newKey = "Geregistreerd Op";
          } else if (key === "verwijderd_op" ) {
            newKey = "Verwijderd Op"
          } else if(key === "id") {
            newKey = key;
          } else {
            newKey = this.formFields.find(f => f.slug === key).value;
          }
          mappedSub[newKey] = sub[key];
        }

        return mappedSub;
      });

    },

    filteredSubsTable: function() {
      const filteredSubsTable = this.subsTable.map(el => {
        return { ...el};
      });
      filteredSubsTable.forEach((sub, index) => {
        for (const key in sub) {
          if(!this.filterFields[key]) {
            delete filteredSubsTable[index][key];
          }
        }
      });
      return filteredSubsTable;
    },
  },

  methods: {

    addFormFieldOption({index, value}) {
      if (value === '') { return; }
      const newFormFields = [...this.formFields];
      const options = newFormFields[index].selectOptions ?
        [...newFormFields[index].selectOptions, value] : [value]
      newFormFields[index].selectOptions = options;
      this.formFields = newFormFields;
    },

    removeFormFieldOption({index, optionIndex}) {
      const newFormFields = [...this.formFields];
      newFormFields[index].selectOptions = newFormFields[index].selectOptions.filter((op, ind) => ind !== optionIndex);
      this.formFields = newFormFields;
    },

    changeFormFieldType({index, value}) {
      const newFormFields = [...this.formFields];
      newFormFields[index].type = value;
      this.formFields = newFormFields;
    },

    locationDrop: function(e) {
      const newLoc = [ ...this.locations ];
      newLoc[e.dragIndex].position = e.dropPos;

      for(let i = e.dropIndex; i < newLoc.length; i++){
        if (i !== e.dragIndex) {
          newLoc[i].position++;
        }
      }

      this.dates[this.locationsSelectedDate].locations = 
        newLoc.sort((a,b) => a.position - b.position);
    },
    formFieldDrop: function(e) {
      e.dragIndex = Number(e.dragIndex);
      const newFormFields = [...this.formFields];
      newFormFields[e.dragIndex].position = e.dropPos;

      for (let i = e.dropIndex; i < newFormFields.length ; i++ ) {
        if (i !== e.dragIndex) {
          newFormFields[i].position++;
        }
      }

      this.formFields = newFormFields.sort((a,b) => a.position - b.position)
    },

    exportToCsv: function() {
      var a = window.document.createElement('a');
    
      var csvString = Papa.unparse(
        JSON.stringify(this.dates[this.subsSelectedDate].locations[this.subsSelectedLoc].subscriptions),
        { quotes: true }
      );
      a.href = window.URL.createObjectURL(new Blob([csvString], {type: 'text/csv'}));
      a.download = 'aanmeldingen.csv';

      // Append anchor to body.
      document.body.appendChild(a);
      a.click();

      // Remove anchor from body
      document.body.removeChild(a);
    },

    inputFormField: function(data) {
      const newFormFields = [...this.formFields];
      newFormFields[data.index].value = data.value;
      newFormFields[data.index].slug = generateSlug(data.value);
      this.formFields = newFormFields;
    },

    newFormField: function(field) {
      this.formFields.push({
        value: field,
        slug: generateSlug(field),
        position: this.formFields.length+1,
        type: 'text',
      });
    },

    changeFormFieldsPos: function(data) {
      this.formFields[data.index].position = Number(data.value);
      this.formFields = this.formFields.sort((a,b) => a.position-b.position);
      jQuery('#tlc-form-fields-' + this.formFields[data.index].slug).focus();
    },

    selectDeleteSub: function(index){
      this.subsSelectedSub = index;
      this.showDeleteModal = true;
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
        customTime: "0",
        id: ID(),
        subscriptions: [],
        position: this.locations.length+1,
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

    locationCheck: function(e) {
      this.dates[this.locationsSelectedDate].locations[e.index].customTime = e.value;
      if (!e.value) {
        this.dates[this.locationsSelectedDate].locations[e.index].startHour =
          this.dates[this.locationsSelectedDate].startHour;

        this.dates[this.locationsSelectedDate].locations[e.index].endHour =
          this.dates[this.locationsSelectedDate].endHour;

        this.dates[this.locationsSelectedDate].locations[e.index].startMin =
          this.dates[this.locationsSelectedDate].startMin;

        this.dates[this.locationsSelectedDate].locations[e.index].endMin =
          this.dates[this.locationsSelectedDate].endMin;
      }
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
    },
    deleteSub(e) {
      this.dates[this.subsSelectedDate].locations[this.subsSelectedLoc]
        .subscriptions[this.subsSelectedSub].verwijderd_op = e.verwijderd_op;
      
    },
    insertedSub: function(sub) {
      this.dates[this.subsSelectedDate].locations[this.subsSelectedLoc].subscriptions.push(sub);
    },
    exportToXlsl: function() {
      const wb = XLSX.utils.table_to_book(document.getElementById('subs-table'));
      XLSX.writeFile(wb, 'subs_table.xlsx', {});
    }
  }
});
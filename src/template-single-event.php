<script src="https://cdn.jsdelivr.net/npm/vue@2.5.17/dist/vue.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment.min.js" 
integrity="sha256-CutOzxCRucUsn6C6TcEYsauvvYilEniTXldPa6/wu0k=" crossorigin="anonymous"></script>

<?php
if (isset($_GET['unsubscribe'])) {
  $unsub_data = json_decode(base64_decode($_GET['unsubscribe']), true);
  $postDates = json_decode(base64_decode(get_post_meta($post->ID, 'tlc-dates', true)), true);
  $date_key = null;
  $loc_key = null;
  foreach($postDates as $key => $date)
  {
    if ($date['id'] == $unsub_data['date_id'])
    {
      $date_key = $key;
    }
  }

  if (!$date_key) { wp_die(__("Invalid unsubscribe link", "tlc-events")); }

  foreach($postDates[$date_key]['locations'] as $key => $value)
  {
    if ($value['id'] == $unsub_data['location_id'])
    {
      $loc_key = $key;
    }
  }

  if (!$loc_key) { wp_die(__("Invalid unsubscribe link", "tlc-events")); }
  
  foreach($postDates[$date_key]['locations'][$loc_key]['subscriptions'] as $key => $val)
  {
    if ($val['email'] == $unsub_data['email'])
    {
      unset($postDates[$date_key]['locations'][$loc_key]['subscriptions'][$key]);
    }
  }
  update_post_meta($post->ID, 'tlc-dates', base64_encode(json_encode($postDates)));

?> 
<script>
  var tlc_msg = "<?= __("You have sucessifully unsubscribed from this event") ?>";
</script>
<?php
} else {
    echo '<script>var tlc_msg = undefined;</script>';
}
?>

<script>
  const rawDates = "<?= get_post_meta($post->ID, 'tlc-dates', true) ?>";    
  const rawFormFields = "<?= get_post_meta($post->ID, 'tlc-form-fields', true) ?>";
</script>

<div class="hop-events" id="tlc-events">
  <h3><?= __("Dates and Locations", "tlc-events") ?>:</h3>
  <div class="hop-events-single" v-for="(date, dateIndex) in dates">
    <div v-for="(loc, locIndex) in date.locations" class="hop-events-data">
    	<div class="hop-events-meta"><span class="events">{{loc.name}} | {{date.day}}-{{date.month}}-{{date.year}} | {{transTime(loc.startHour, loc.startMin)}} - {{transTime(loc.endHour, loc.endMin)}}</span></div>
    	<div class="hop-events-button"><button class="default-btn-shortcode dt-btn dt-btn-m" @click="subscribeClick(dateIndex, locIndex)" v-if="isEmailPresent"><b><?= __("Subscribe", "tlc-events") ?></b></button></div>
		<div class="hop-events-info"><span class="events"><?= __("Address", "tlc-events") ?>: </span>{{loc.address}}, {{loc.city}}</div>
  </div></ hr>


  <div class="w3-modal" style="display: block; z-index:1000!important;" v-show="displaySubscriptionForm">
    <div class="w3-modal-content">
      <div class="w3-container">
        <span @click="hideSubscriptionForm" 
        class="w3-button w3-display-topright" v-bind:class="{'w3-disabled' : subscribing}">&times;</span>
        <h3><b><?= __("Subscription Form", "tlc-events") ?></b></h3><hr><br>
        <form action="#" v-on:submit.prevent="subscribe">
          <div class="w3-margin" v-for="field in formFields">
            <label v-bind:for="field.slug">{{field.value}}</label>
            <input 
              v-bind:type="field.slug == 'email' ? 'email' : 'text'" 
              v-bind:name="field.slug" 
              v-bind:id="field.slug" 
              v-bind:placeholder="field.value" 
              v-model="formData[field.slug]"
              required
              style="width: 100%!important"
            >
          </div>
          <div class="w3-margin">
            <input type="submit" v-bind:disabled="subscribing"
              class="w3-button w3-white w3-text-red w3-border w3-border-red w3-hover-red w3-bar w3-round"
              value="<?= __("Subscribe", "tlc-events") ?>" v-if="!subscribing">
            
            <input type="submit" 
              class="w3-button w3-white w3-disabled w3-text-red w3-border w3-border-red w3-hover-red w3-bar w3-round"
              value="<?= __("Subscribing...", "tlc-events") ?>" v-if="subscribing">
          </div>
        </form>

        <div class="w3-panel">
          <h4><b>{{selectedDate.day}}-{{selectedDate.month}}-{{selectedDate.year}}</b></h4><hr>
          <p> <b><?= __("City", "tlc-events") ?>:</b> {{selectedLocation.city}} | 
            <b><?= __("starting at", "tlc-events") ?>:</b> {{transTime(selectedLocation.startHour, selectedLocation.startMin)}} </p>
          <p> <b><?= __("Address", "tlc-events") ?>:</b> {{selectedLocation.address}} </p> <hr><br>
        </div>
      </div>
    </div>
  </div>

  <div class="w3-modal" style="display: block; z-index:1000!important;" v-show="displayMessageModal">
    <div class="w3-modal-content">
      <div class="w3-container">
        <span @click.prevent="closeMessageModal" 
        class="w3-button w3-display-topright">&times;</span>
        <div style="margin: 50px 0">
          <div class="w3-container">
            <h3  v-bind:class="{'w3-text-red' : error}">
            <span class="dashicons dashicons-admin-comments w3-text-blue w3-xxxlarge" 
            style="margin-right: 30px;" v-if="!error"></span> 
            <span class="dashicons dashicons-warning w3-xxlarge" style="margin-right: 20px;" v-if="error"></span>
            {{message}}</h3>
          </div>
          <button @click.prevent="closeMessageModal" 
            class="w3-button w3-white w3-text-red w3-margin-top w3-border w3-border-red w3-hover-red w3-bar w3-round">
            <?= __("Close", "tlc-events") ?>
          </button>
        </div>
      </div>
    </div>
  </div>

</div>


<script>
'use strict';

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var app = new Vue({
  el: '#tlc-events',
  created: function(){
    this.dates = this.dates.map(date => {
      const locations = date.locations.sort((a,b) => a.position - b.position);
      return {
        ...date,
        locations
      };
    });

    this.formFields = this.formFields.sort((a,b) => a.locations - b.locations);
  },
  data: {
    dates: rawDates == "" ? [] : JSON.parse(window.atob(rawDates)).filter(function (date) {
      return !isDateOutdated(date) && date.locations.length > 0;
    }),
    formFields: rawFormFields == '' ? [] : JSON.parse(window.atob(rawFormFields)),
    formData: {},
    displaySubscriptionForm: false,
    displayMessageModal: tlc_msg !== undefined ? true : false,
    selectedDateIndex: 0,
    selectedLocationIndex: 0,
    message: tlc_msg !== undefined ? tlc_msg : '',
    error: false,
    subscribing: false
  },
  created: function created() {
    this.formFields.forEach(function (field) {
      this.$set(this.formData, field.slug, '');
    }, this);
  },

  methods: {
    displayMessage: function displayMessage(message, error) {
      error = (typeof error === 'undefined' ? 'undefined' : _typeof(error)) === undefined ? false : error;
      this.message = message;
      this.error = error;
      this.displayMessageModal = true;
    },

    subscribe: function subscribe() {
      var _this = this;

      this.subscribing = true;
      var reqData = JSON.stringify(_extends({
        event_id: "<?= $post->ID ?>",
        date_id: this.selectedDateIndex,
        location_id: this.selectedLocationIndex
      }, this.formData));

      fetch('<?= get_site_url() ?>/wp-json/tlc-events/subscribe', {
        method: 'POST',
        cache: 'no-cache',
        headers: { 'Content-Type': 'application/json; charset=utf-8' },
        body: reqData
      }).then(function (res) {
        return res.json();
      }).then(function (res) {
        if (res.status === 'success') {
          _this.displaySubscriptionForm = false;
          _this.displayMessage('<?= __("You have successfully subscribed to this event!") ?>');
          _this.subscribing = false;
        } else if (res.code == 'already_subscribed') {
          throw new Error('already_subscribed');
        } else {
          throw new Error(res.code);
        }
      }).catch(function (err) {
        _this.displaySubscriptionForm = false;
        _this.subscribing = false;
        if (err.message == 'already_subscribed') {
          _this.displayMessage('<?= __("You have already subscribed for this event!") ?>', true);
        } else {
          console.error(err);
          _this.displayMessage('<?= __("There is an internal server error, please try again later.") ?>', true);
        }
      });
    },

    closeMessageModal: function closeMessageModal() {
      this.message = '';
      this.displayMessageModal = false;
    },

    subscribeClick: function subscribeClick(dateIndex, locIndex) {
      this.displaySubscriptionForm = true;
      this.selectedDateIndex = dateIndex;
      this.selectedLocationIndex = locIndex;
    },

    transTime: function(h, m){
      const time = moment().hour(Number(h)).minute(Number(m));
      return time.format("HH:mm");
    },

    hideSubscriptionForm: function hideSubscriptionForm() {
      this.displaySubscriptionForm = false;
      this.formFields.forEach(function (field) {
        this.formData[field] = '';
      }, this);
    }
  },
  computed: {
    isEmailPresent: function isEmailPresent() {
      return -1 !== this.formFields.findIndex(function (field) {
        return field.slug === 'email';
      });
    },
    selectedDate: function selectedDate() {
      return this.dates[this.selectedDateIndex];
    },
    selectedLocation: function selectedLocation() {
      const loc = this.selectedDate.locations[this.selectedLocationIndex];
      return loc;
    }
  }
});

function isDateOutdated(date) {
  date.year == Number(date.year);
  date.month == Number(date.month);
  date.day == Number(date.day);
  var dateMoment = moment().year(date.year).month(date.month - 1).date(date.day);
  return moment().isAfter(dateMoment);
}
</script>

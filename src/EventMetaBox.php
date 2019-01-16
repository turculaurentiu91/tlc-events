<?php
namespace TlcEvents;

class EventMetaBox {
  public function __construct()
  {
    if (is_admin() ) {
      add_action( 'load-post.php', array( $this, 'initMetabox' ));
      add_action( 'load-post-new.php', array( $this, 'initMetabox' ));
    }
  }

  public function initMetabox()
  {
    add_action('add_meta_boxes', array( $this, 'addMetabox'));
    add_action('save_post', array($this, 'saveMetabox'), 10, 2);
  }

  public function addMetabox()
  {
    add_meta_box(
      'event_info',
      __('Event details', 'rlc-events'),
      array($this, 'renderMetabox'),
      'tlc-event'
    );
  }
  public function renderMetabox($post)
  {
    wp_nonce_field( 'tlc-events-nonce-action', 'tlc-events-nonce' );

    $admin_email = get_post_meta($post->ID, 'tlc-admin-email', true);
    if (!$admin_email) {
      $admin_email = get_option('tlc-events-admin-email');
    }

    ?>
    <script>
      const rawDates = "<?= get_post_meta($post->ID, 'tlc-dates', true) ?>";    
      const rawFormFields = "<?= get_post_meta($post->ID, 'tlc-form-fields', true) ?>";
      const admin_email = "<?= $admin_email ?>";

    </script>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="<?= plugins_url('event-metabox.css', dirname(__FILE__)) ?>">
    <script src="https://cdn.jsdelivr.net/npm/vue@2.5.17/dist/vue.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment.min.js" 
    integrity="sha256-CutOzxCRucUsn6C6TcEYsauvvYilEniTXldPa6/wu0k=" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>

    <div class="event-metabox w3-card-2" id="event-metabox">
      <input type="hidden" name="tlc-dates" id="tlc-dates" v-bind:value="jsonDates" >
      <input type="hidden" name="tlc-form-fields" id="tlc-form-fields" v-bind:value="jsonFormFields" >
      <div class="w3-sidebar w3-bar-block w3-border-right" style="width:15%; position: absolute!important;">
        <h3 class="w3-bar-item w3-margin-top">Menu</h3> 
        <nav-link 
          v-bind:active="page === 'dates'"
          v-on:change-page="page = 'dates'"
        ><?= __("Dates", "tlc-events") ?></nav-link>
        <nav-link 
          v-bind:active="page === 'locations'"
          v-on:change-page="page = 'locations'"
        ><?= __("Locations", "tlc-events") ?></nav-link>
        <nav-link 
          v-bind:active="page === 'subscription-form'"
          v-on:change-page="page = 'subscription-form'"
        ><?= __("Subscription Form", "tlc-events") ?></nav-link>
        <nav-link 
          v-bind:active="page === 'subscriptions'"
          v-on:change-page="page = 'subscriptions'"
        ><?= __("Subscriptions", "tlc-events") ?></nav-link>
        <nav-link 
          v-bind:active="page === 'email-template'"
          v-on:change-page="page = 'email-template'"
        ><?= __("Email Template", "tlc-events") ?></nav-link>
      </div>

      <div style="margin-left:15%">
       <div class="tlc-pages" v-bind:class="{'w3-hide' : page !== 'dates'}">
        <h3 class="w3-card w3-red w3-padding w3-margin-top" v-if="dates.length === 0">
          <?= __("You must specify at least one date!", "tlc-events") ?>
        </h3>

        <div class="w3-panel">
          <div class="w3-section w3-border" v-if="dates.length > 0">
            <date-input 
              v-for="(date, index) in dates" 
              v-bind:date="date"
              :key="index"
              v-bind:index="index"
              day-label="<?= __("Day", "tlc-events") ?>"
              month-label="<?= __("Month", "tlc-events") ?>"
              year-label="<?= __("Year", "tlc-events") ?>"
              start-hour-label = "<?=__("Start","tlc-events") . ' ' . __("Hour", "tlc-events") ?>" 
              start-min-label = "<?=__("Start","tlc-events") . ' ' . __("Minute", "tlc-events") ?>" 
              end-hour-label = "<?=__("End","tlc-events") . ' ' . __("Hour", "tlc-events") ?>" 
              end-min-label = "<?=__("End","tlc-events") . ' ' . __("Minute", "tlc-events") ?>" 
              v-on:change-month= "changeDateMonth"
              v-on:change-day="changeDateDay"
              v-on:change-year="changeDateYear"
              v-on:delete-date="deleteDate"
              v-on:change-start-hour="changeStartHour"
              v-on:change-end-hour="changeEndHour"
              v-on:change-start-min="changeStartMin"
              v-on:change-end-min="changeEndMin"
            ></date-input>
          </div>

          <button 
            class="w3-blue w3-round w3-button w3-margin tlc-newDate-button" @click.prevent="newDate">
            <span class="dashicons dashicons-plus"></span> <?= __("New Date", "tlc-events") ?>
          </button>

          <p class="tlc-subnote"><?= __("Deleting one date will result in loss of all the locations and subscriptions 
          related to that date, and the user will not be notified!") ?></p>
        </div>
       </div>
        <div class="tlc-pages" v-bind:class="{'w3-hide' : page !== 'locations'}">
          <h3 class="w3-card w3-red w3-padding w3-margin-top" v-if="dates.length === 0">
            <?= __("You must specify at least one date!", "tlc-events") ?>
          </h3>
          <div class="w3-padding w3-margin-top" v-if="dates.length > 0">
            <h3 class="w3-card w3-red w3-padding w3-margin-top" v-if="locations.length === 0">
              <?= __("You must specify at least one location for this date!", "tlc-events") ?>
            </h3>

            <div class="w3-section w3-row-padding">
              <div class="w3-col l2"><label><?= __("Select Date", "tlc-events") ?>: </label></div>
              <div class="w3-col l3">
                <select class="w3-select w3-border" @change="changeLocationsSelectedDate" :value="locationsSelectedDate">
                  <option v-for="(date, index) in dates" v-bind:value="index">
                    {{date.day}}-{{date.month}}-{{date.year}}
                  </option>
                </select>
              </div>
              <div class="w3-col l2">
                <button class="w3-button w3-round w3-blue" style="margin-top: -3px;" @click.prevent="copyLocations">
                  <?= __("Copy those locations for all dates", "tlc-events") ?>
                </button>
              </div>
            </div>
            
            <div class="w3-section w3-border" v-if="locations.length > 0">
              <location-input
                v-for="(location, index) in locations"
                :key="index"
                v-bind:location="location"
                v-bind:index="index"
                v-bind:custom-time="location.customTime"
                city-label="<?= __("City", "tlc-events") ?>"
                name-label="<?= __("Name", "tlc-events") ?>" 
                address-label="<?= __("Address", "tlc-events") ?>"
                v-on:change-city="changeCity"
                v-on:change-name="changeName"
                v-on:change-address="changeAddress"
                v-on:change-start-hour="changeLocStartHour"
                v-on:change-start-min="changeLocStartMin"
                v-on:change-end-hour="changeLocEndHour"
                v-on:change-end-min="changeLocEndMin"
                v-on:checked="locationCheck"
                v-on:delete-location="deleteLocation"
                v-bind:dragging="locDragging"
                v-on:dragging="locDragging = $event"
                v-on:drop="locationDrop"              
              >
              
              </location-input>
            </div>

            <button 
              class="w3-blue w3-round w3-button w3-margin tlc-newDate-button" @click.prevent="newLocation(dates[locationsSelectedDate])">
              <span class="dashicons dashicons-plus"></span> <?= __("New Location", "tlc-events") ?>
            </button>

            <p class="tlc-subnote"><?= __("Deleting one location will result in the loss of all the subscriptions 
            for that location in that date, and the user will not be notified! The deletion will take effect ony
            after the post is saved or updated.") ?></p>
          </div>
        </div>
        <div class="tlc-pages" v-bind:class="{'w3-hide' : page !== 'subscription-form'}">
          <div class="w3-container">
            <h3><?= __("Subscription Form", "tlc-events") ?></h3>
            <p>Hier kun je het inschrijfformulier beheren. Standaard moet de gebruiker invoeren
              op zijn minst de e-mail. Als u de E-mailadres van het formulier verwijdert, is de abonnementsmonteur
              is volledig te verwijderen en de gebruiker kan niet op de knop Abonneren klikken in de gebeurtenisweergave.
              Dit is handig voor evenementen waarvoor geen abonnement vereist is.</p>
            <p>U kunt formuliervelden niet invoegen of verwijderen als er abonnementen zijn geregistreerd en u kunt ze ook niet bewerken.</p>
          </div>
          <div class="w3-section w3-border w3-margin" v-if="formFields.length > 0">
            <form-input
              v-for="(field, index) in formFields"
              :key="index"
              v-bind:index="index"
              label= "<?= __("Field Name","tlc-events") ?>"
              v-bind:can-delete="hasAnySubs"
              v-bind:value="field.value"
              v-on:delete="deleteFormField"
              v-on:input="inputFormField"
              v-on:input-position="changeFormFieldsPos"
              v-bind:slug="field.slug"
              v-bind:position="field.position"
              v-on:drop="formFieldDrop"
              v-bind:type="field.type"
              v-on:type-change="changeFormFieldType"
              v-bind:select-options="field.selectOptions"
              v-on:add-option="addFormFieldOption"
              v-on:remove-option="removeFormFieldOption"
            >
            </form-input>
          </div>

          <button v-bind:disabled="hasAnySubs"
              class="w3-blue w3-round w3-button w3-margin tlc-newDate-button" @click.prevent="newFormField('<?= __("new field","tlc-events") ?>')">
              <span class="dashicons dashicons-plus"></span> <?= __("New Form Field", "tlc-events") ?>
            </button>
        </div>
        <div class="tlc-pages" v-bind:class="{'w3-hide' : page !== 'subscriptions'}">
          <div class="w3-padding w3-padding-24">
            <div class="w3-row-padding">
              <div class="w3-col l2"><label> <?= __("Select a date", "tlc-events") ?>: </label></div>
              <div class="w3-col l3">
                <select class="w3-select w3-border" v-model="subsSelectedDate" @change="subsSelectedLoc = 0">
                  <option v-for="(date, index) in dates" v-bind:value="index">{{date.day}}-{{date.month}}-{{date.year}}</option>
                </select>
              </div>

              <div class="w3-col l2"><label> <?= __("Select a location", "tlc-events") ?>: </label></div>
              <div class="w3-col l5">
                <select class="w3-select w3-border" v-model="subsSelectedLoc" v-if="dates[subsSelectedDate].locations.length > 0">
                  <option v-for="(loc, index) in dates[subsSelectedDate].locations" v-bind:value="index">
                    {{loc.name}}
                  </option>
                </select>
              </div>
            </div>
          </div>

          <div class="w3-margin w3-padding w3-red" 
              v-if="dates[subsSelectedDate].locations.length <= 0">
            <h3><?= __("No locations for selected date", "tlc-events") ?></h3>
          </div> 

          <div v-if="dates[subsSelectedDate].locations.length > 0">
            <div class="w3-margin w3-padding" 
              v-if="!subsTable
              || subsTable.length <= 0">
              <h3><?= __("No subsTable for this date and location", "tlc-events") ?></h3>
            </div>

            <div v-if="subsTable" class="w3-responsive" style="width: 95%;">
              <table class="w3-table-all w3-margin" style="width: 95%;" v-if="subsTable.length > 0" id="subs-table">
                <tr>
                  <th v-for="(value, key) in filteredSubsTable[0]"><span style="display: inline-block">{{key}}</span></th>
                  <th></th>
                </tr>
                <tr v-for="(sub, index) in filteredSubsTable">
                  <td v-for="value in sub"><span style="display: inline-block">{{value}}</span></td>
                  <td style="width: 2em;">
                    <button @click.prevent="selectDeleteSub(index)" class="w3-button w3-round w3-text-red">
                      <span class="dashicons dashicons-no"></span>
                    </button>
                  </td>
                </tr>
              </table>
            </div>
            <h3 class="w3-margin-left">Filteren op:</h3>
            <div v-if="dates[subsSelectedDate].locations.length > 0" ><filter-sub 
              v-bind:filter-fields="filterFields"
              v-on:check="filterFields[$event.key] = $event.value"
              v-if="subsTable.length > 0"
            ></filter-sub></div>
            <button 
              class="w3-blue w3-round w3-button w3-margin tlc-newDate-button" @click.prevent="exportToCsv">
              <?= __("Export Table to CSV", "tlc-events") ?>
            </button>

            <button 
              class="w3-blue w3-round w3-button w3-margin tlc-newDate-button" @click.prevent="exportToXlsl">
              Exporteer naar Excel
            </button>

            <button 
              class="w3-teal w3-round w3-button w3-margin tlc-newDate-button" 
              @click.prevent="showInsertSubForm = true" v-bind:disabled="!formFieldsHasEmail">
              Voeg een aanmelding toe
            </button>
          </div>
        </div>
        <div class="tlc-pages" v-bind:class="{'w3-hide' : page !== 'email-template'}">
          <div class="w3-padding">
            <?php 
            wp_editor( 
              get_post_meta($post->ID, 'tlc-email-template', true), 
              'tlc-email-template', 
              array());
            ?> 
          </div>
          <div class="w3-panel w3-container" style="padding-bottom: 50px!important;">
            <p><?= __("Available tags", "tlc-events") ?>: %location% %city% %date% %start_time% %end_time% %address% %event_title% %unsubscribe_link%</p>
            <p><?= __("Other tags are derrived from form fields slugs. For example, if a form field slug is \"full_name\" an valid tag is &#37;full_name&#37; and it will be replaced with what user submitted in that form field in the subscription form.", "tlc-events") ?></p>
            <br><br>
            <label for="tlc-admin-email">Notificatie e-mailadres</label>
            <input type="email" id="tlc-admin-email" name="tlc-admin-email" v-model="adminEmail" class="w3-input" reqired>
            
            <p class="tlc-subnote"><?= __("Invalid tags will not be replaced in the final email") ?></p>
          </div>
        </div>
      </div>
      <div v-if="dates[subsSelectedDate].locations.length > 0"> <delete-sub
          v-if="subsTable.length > 0"
          v-bind:show="showDeleteModal"
          api-endpoint="<?= get_site_url() . "/wp-json/tlc-events/admin/unsubscribe" ?>"
          v-bind:date-id="dates[subsSelectedDate].id"
          v-bind:location-id="dates[subsSelectedDate].locations[subsSelectedLoc].id"
          v-bind:subscription-id="dates[subsSelectedDate].locations[subsSelectedLoc].subscriptions[subsSelectedSub].id"
          v-bind:event-id="<?= $post->ID ?>"
          v-on:close="showDeleteModal = false"
          v-on:deleted="deleteSub"
        ></div>
      </delete-sub>
      <insert-sub
        api-endpoint="<?= get_site_url() . "/wp-json/tlc-events/subscribe" ?>"
        v-bind:show="showInsertSubForm"
        v-bind:form-fields="formFields"
        v-bind:date-id="subsSelectedDate"
        v-bind:location-id="subsSelectedLoc"
        v-bind:event-id="<?= $post->ID ?>"
        v-on:close="showInsertSubForm = false"
        v-on:added="insertedSub"
      ></insert-sub>
    </div>
    <script src="<?= plugins_url('papaparse.min.js', dirname(__FILE__)) ?>"></script>
    <script src="<?= plugins_url( 'event-metabox.js', dirname(__FILE__) ) ?>"></script>
    <?php
  }
  public function saveMetabox($postId, $post)
  {
    // Add nonce for security and authentication.
		$nonce_name   = isset($_POST['tlc-events-nonce']) ? $_POST['tlc-events-nonce'] : null;
		$nonce_action = 'tlc-events-nonce-action';

		// Check if a nonce is set.
		if ( ! isset( $nonce_name ) )
			return;

		// Check if a nonce is valid.
		if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) )
			return;

		// Check if the user has permissions to save data.
		if ( ! current_user_can( 'edit_post', $postId ) )
			return;

		// Check if it's not an autosave.
		if ( wp_is_post_autosave( $postId ) )
			return;

		// Check if it's not a revision.
		if ( wp_is_post_revision( $postId ) )
			return;

    $dates = $_POST['tlc-dates'];
    $formFields = $_POST['tlc-form-fields'];
    update_post_meta($postId, 'tlc-dates', $dates);
    update_post_meta($postId, 'tlc-form-fields', $formFields);
    update_post_meta($postId, 'tlc-email-template', $_POST['tlc-email-template']);
    update_post_meta($postId, 'tlc-admin-email', $_POST['tlc-admin-email']);
  }
}
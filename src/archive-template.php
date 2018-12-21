<?php 
  $query = new WP_Query(array(
  'post_type' => 'tlc-event',
  'post_status' => 'publish',
  'posts_per_page' => -1,
));

$events = array();
global $post;
while ($query->have_posts()) {
  $query->the_post();
  $events[] = array(
    'id' => $post->ID,
    'title' =>$post->post_title,
    'dates' => json_decode(base64_decode(get_post_meta($post->ID, 'tlc-dates', true))),
    'formFields' => json_decode(base64_decode(get_post_meta($post->ID, 'tlc-form-fields', true))),
    'url' => get_permalink($post),
    'img' => get_the_post_thumbnail_url($post->ID, 'full'),
  );
}

$monthsLocale = array(
  __('January', 'tlc-events'),
  __('February', 'tlc-events'),
  __('March', 'tlc-events'),
  __('April', 'tlc-events'),
  __('May', 'tlc-events'),
  __('June', 'tlc-events'),
  __('July', 'tlc-events'),
  __('August', 'tlc-events'),
  __('September', 'tlc-events'),
  __('October', 'tlc-events'),
  __('November', 'tlc-events'),
  __('December', 'tlc-events'),
);

wp_reset_query();
?>

<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<link rel="stylesheet" href="<?= plugins_url('archive-template.css', dirname(__FILE__)) ?>">
<script src="https://cdn.jsdelivr.net/npm/vue@2.5.17/dist/vue.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment.min.js" 
integrity="sha256-CutOzxCRucUsn6C6TcEYsauvvYilEniTXldPa6/wu0k=" crossorigin="anonymous"></script>
<script>
  var events = <?= json_encode($events) ?>; 
  var monthLocales = <?= json_encode($monthsLocale) ?>;
</script>
<script src="<?= plugins_url('archive_template_setup.js', dirname(__FILE__)) ?>"></script>

<div id="archive-template">
  <div v-for="month in months" class="w3-border-top" style="margin-top: 20px; padding-top: 20px;">
    <h3 class="w3-text-red w3-margin-left">{{month.year}}</h3>
    <p class="w3-margin-left">{{month.name}}</p>
    <div class="w3-row-padding w3-margin-bottom w3-padding-bottom" v-for="eventIndex in month.eventsIndexes">
      <div class="w3-col l2 m2">
        <a v-bind:href="events[eventIndex].url">
          <img v-if="events[eventIndex].img" v-bind:src="events[eventIndex].img" 
          v-bind:alt="'Event thumbnail' + eventIndex" style="width: 100%" >
        </a>
        
      </div>
      <div class="w3-col l8 m8">
        <div class="">
          <span class="" v-for="(date, dateIndex) in filteredDatesByMonth(events[eventIndex], month.number)">
              {{date.m.format('DD')}} {{month.name}} {{date.m.format('YYYY')}}
          </span>
        </div>
        
        <a class="tlc-event-title-link w3-text-red w3-xlarge" v-bind:href="events[eventIndex].url">
          <b>{{events[eventIndex].title}}</b>
        </a>
        
        <p style="margin: 0;" v-for="loc in filteredDatesByMonth(events[eventIndex], month.number)[0].locations">
        {{loc.name}} | {{loc.city}} | {{loc.address}}
        </p>
      </div>
    </div>
  </div>
</div>

<script src="<?= plugins_url('archive_template.js', dirname(__FILE__)) ?>"></script>
<?php 
  $query = new WP_Query(array(
  'post_type' => 'tlc-event',
  'post_status' => 'publish',
  'posts_per_page' => -1,
));

// --GET FIRST THREE EVENTS

$events = array();
global $post;
while ($query->have_posts()) {
  $query->the_post();
  $content = strip_tags($post->post_content);
  $excript = strlen($content) <= 74 ? $content : substr($content, 0, 73) . "&hellip;";
  $events[] = array(
    'id' => $post->ID,
    'title' =>$post->post_title,
    'dates' => json_decode(base64_decode(get_post_meta($post->ID, 'tlc-dates', true)), true),
    'url' => get_permalink($post),
    'img' => get_the_post_thumbnail_url($post->ID, 'full'),
    'excript' => $excript,
  );
}

wp_reset_query();
// -- ADD A PHP DATE OBJECT IN THE EACH DATE
foreach ($events as $ekey => $event) {
  foreach ($event['dates'] as $dkey => $date) {
    $events[$ekey]['dates'][$dkey]['phpDate'] = 
      DateTime::createFromFormat(
        "d m Y H i",
        $date['day'] 
        . " " . $date['month']
        . " " . $date['year']
        . " " . $date['startHour']
        . " " . $date['startMin']
      ); 
  }
}

// --FILTER OUT THE OUTDATED DATES

$now = DateTime::createFromFormat(
  "d m Y H i",
  current_time("d m Y H i")
);

foreach ($events as $ekey => $event) {
  foreach($event['dates'] as $dkey => $date) {
    if ($date['phpDate'] < $now) {
      unset($events[$ekey]['dates'][$dkey]);
    }
  }

  // -- SORT THE DATES IN THE EVENT
  usort($events[$ekey]['dates'], function($a, $b) {
    return $a['phpDate']->getTimestamp() - $b['phpDate']->getTimestamp();
  });

  if (count($event['dates']) < 1) {
    unset($events[$ekey]);
  }
}

// -- SORT EVENTS BY THE FIRST DATE
usort($events, function($a, $b) {
  return $a['dates'][0]['phpDate']->getTimestamp() -
    $b['dates'][0]['phpDate']->getTimestamp();
});

$events = array_slice($events, 0, 3);
?>

<style>
    .tlc-card {
        margin-top 0;
        background-color: #f8f8f9;
        animation: fadeInFromNone .5s ease-out;
        display: flex;
        align-items: flex-start;
        position: relative;
        box-sizing: border-box;
    }
    
    .tlc-card:not(:first-child) {
        margin-top: 10px;
    }
    
    .tlc-card__thumbnail-wrap {
        width: 40%;
        padding: 0;
        position: relative;
        box-sizing: border-box;
    }
    
    .tlc-card__thumbnail {
        padding: 10px 0;
        position: relative;
        display: block;
        
    }
    
    .tlc-card__thumbnail a {
        display: block;
        padding: 10px 0;
        line-heingh:0;
        overflow: hidden;
    }
    
    .tlc-card__thumbnail a:hover > img {
        transform: scale(1.5);
    }
    
    .tlc-card__thumbnail a img {
        width: 230px;
        max-width: 100%;
        height: auto;
        transform: scale(1);
        transition: transform 3s;
    }
    
    .tlc-card__content {
        width: 60%;
        padding: 10px;
        position: relative;
        box-sizing: border-box;
        max-width: 100%;
        z-index: 10;
    }
    
    .tlc-card__content h3 {
        margin-bottom: 5px;
        font-weight: bold;
        font: normal normal normal 25px / 36px "Roboto", Helvetica, Arial, Verdana, sans-serif;
        
    }
    
    .tlc-card__content h3 a {
        font-family: "eurostile","opensans",sans-serif;
        font-weight: normal;
        transition: color .35s;
        color: #ee0a00;
        display: inline-block;
    }
    
    .tlc-card__content p {
        margin-bottom: 1em !important;
    }
</style>

<div >
  <?php foreach($events as $event) : ?>
  <article class="tlc-card">
    <div class="tlc-card__thumbnail-wrap">
      <div class="tlc-card__thumbnail">
        <a href="<?= $event['url'] ?>">
          <img class="blog-thumb-lazy-load preload-me blog-thumb-lazy-load-show is-loaded" 
          src="<?= $event['img'] ?>" 
          srcset="<?= $event['img'] ?>" style="will-change: auto;">
        </a>
      </div>
    </div>


    <div class="tlc-card__content">

      <h3>
        <a href="<?= $event['url'] ?>" title="<?= $event['title'] ?>" rel="bookmark"><?= $event['title'] ?></a>
      </h3>

      <div class="entry-meta"><a href="#" title="00:00" class="data-link" rel="bookmark"><time>
        <?php
          $date = $event['dates'][0];
          $date = "{$date['day']}-{$date['month']}-{$date['year']} {$date['startHour']}:{$date['startMin']}";
          $location = count($event['dates'][0]['locations']) > 1 ? $event['dates'][0]['locations'][0]['name'] : "Meerdere locaties";
          if (count($event['dates']) > 1) {
            echo "Meerdere data, eerst komende: {$date} | {$location}";
          } else {
            echo "{$date} | {$location}";
          }
        ?>
      </time></a></div>
      <div class="entry-excerpt"><p>&nbsp; <?= $event['excript'] ?></p>
    </div>
      <a href="<?= $event['url'] ?>" class="post-details details-type-link" rel="nofollow">Lees verder...<i class="fa fa-caret-right" aria-hidden="true"></i></a>
    </div>
  </article>
  <?php endforeach; ?>

<div>
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

<div >
  <?php foreach($events as $event) : ?>
  <article>
    <div class="post-thumbnail-wrap">
      <div class="post-thumbnail">
        <a href="<?= $event['url'] ?>" class="post-thumbnail-rollover layzr-bg-transparent">
          <img class="blog-thumb-lazy-load preload-me blog-thumb-lazy-load-show is-loaded" 
          src="<?= $event['img'] ?>" 
          alt="" title="sluiting-tijdens-kerst-en-nieuw" width="469" height="265" sizes="230px" 
          srcset="<?= $event['img'] ?> 469w" style="will-change: auto;">
        </a>
      </div>
    </div>


    <div class="post-entry-content">

      <h3 class="entry-title">
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
<?php

  if (!current_user_can('manage_options'))
  {
    wp_die( __('You do not have sufficient permissions to access this page.') );
  }

  if (isset($_POST['tlc-smtp-host'])) {
    update_option('tlc-events-smtp-host', $_POST['tlc-smtp-host']);
  }

  if (isset($_POST['tlc-smtp-port'])) {
    update_option('tlc-events-smtp-port', $_POST['tlc-smtp-port']);
  }

  if (isset($_POST['tlc-smtp-user'])) {
    update_option('tlc-events-smtp-user', $_POST['tlc-smtp-user']);
  }

  if (isset($_POST['tlc-smtp-password'])) {
    update_option('tlc-events-smtp-password', $_POST['tlc-smtp-password']);
  }

  if (isset($_POST['tlc-admin-email'])) {
    update_option('tlc-events-admin-email', $_POST['tlc-admin-email']);
  }

  if (isset($_POST['tlc-unsub-template'])) {
    update_option('tlc-events-unsub-template', $_POST['tlc-unsub-template']);
  }

  if (isset($_POST['tlc-rewrite'])) {
    update_option('tlc-events-rewrite', $_POST['tlc-rewrite']);
    \TlcEvents\EventPostType::getInstance()->flush_rules();
  }

  $smtpHost = get_option('tlc-events-smtp-host');
  $smtpPort = get_option('tlc-events-smtp-port');
  $smtpUser = get_option('tlc-events-smtp-user');
  $smtpPassword = get_option('tlc-events-smtp-password');

  $adminEmail = get_option('tlc-events-admin-email');

  $link = get_option('tlc-events-rewrite', 'events');

  $unsub_template = get_option('tlc-events-unsub-template');
?>

<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<div class="wrap">
  <h2><?= __("TLC Events Settings Page", "tlc-events") ?></h2>

  <div class="w3-card w3-panel w3-white" style="width: 50%;">
    <div class="w3-panel" style="">
      <form action="" method="POST" name="tlc-events-settings-form">
        <div class="w3-row-padding w3-margin-bottom">
          <div class="w3-col l6">
            <label for="tlc-smtp-host"><?= __("Email SMTP Server Host") ?></label>
            <input type="text" name="tlc-smtp-host" id="tlc-smtp-host" value="<?= $smtpHost ?>" class="w3-input w3-border">
          </div>

          <div class="w3-col l3">
            <label for="tlc-smtp-port"><?= __("Email SMTP Port") ?></label>
            <input type="number" style="height: 40px;" name="tlc-smtp-port" id="tlc-smtp-port" value="<?= $smtpPort ?>" class="w3-input w3-border">
          </div>
        </div>

        <div class="w3-row-padding w3-margin-bottom">
          <div class="w3-col l6">
            <label for="tlc-smtp-user"><?= __("Email SMTP Server Username") ?></label>
            <input type="text" name="tlc-smtp-user" id="tlc-smtp-user" value="<?= $smtpUser ?>" class="w3-input w3-border">
          </div>

          <div class="w3-col l6">
            <label for="tlc-smtp-password"><?= __("Email SMTP Password") ?></label>
            <input type="text" style="height: 40px;" name="tlc-smtp-password" id="tlc-smtp-password" value="<?= $smtpPassword ?>" class="w3-input w3-border">
          </div>
        </div>

        <div class="w3-row-padding w3-margin-bottom">
          <div class="w3-col l6" >
            <label for="tlc-admin-email"><?= __("Admin Email") ?></label>
            <input type="text" style="height: 40px;" name="tlc-admin-email" id="tlc-admin-email" value="<?= $adminEmail ?>" class="w3-input w3-border">
          </div>
        </div>

        <div class="w3-row-padding w3-margin-bottom">
          <div class="w3-col l4" > <?= get_site_url() ?>/</div>
          <div class="w3-col l4 s4 m4" >
            <input type="text" style="height: 40px;" name="tlc-rewrite" id="tlc-rewrite" value="<?= $link ?>" class="w3-input w3-border">
          </div>
          <div class="w3-col l4 s4 m4" >/%postname%</div>
        </div>
        <br>
        <div class="w3-margin">
          <label for="tlc-unsub-template">Abonnement e-mailmelding verwijderen</Label>
          <?php 
            wp_editor( 
              $unsub_template, 
              'tlc-unsub-template', 
              array()
            );
          ?>
          <style>
            #tlc-unsub-template_ifr {
              height: 300px!important;
            }
          </style>
          <p>Te gebruiken tags: %location% %city% %date% %start_time% %end_time% %address% %event_title% %event_link%</p>
        </div>

        

        <input type="submit" value="<?= __("Submit", "tlc-events") ?>" class="w3-button w3-blue w3-margin">
      </form>
      <h3><?= __("Archive shortcode is") ?>: [tlc_events_archive]</h3>
    </div>
  </div>
</div>
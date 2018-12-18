<?php
namespace TlcEvents;

class Main {

  private $eventPostType;
  private $api;
  private $settingsPage;

  public function __construct()
  {
    add_action('phpmailer_init', array($this, 'send_smtp_email'));
    $this->eventPostType = EventPostType::getInstance();
    $this->api = new Api();
    $this->settingsPage = new SettingsMenu();
  }

  public static function activation_hook()
  {
    EventPostType::getInstance()->flush_rules();
  }

  public static function deactivation_hook()
  {
    global $wp_rewrite;
    $wp_rewrite->flush_rules( false );
  }

  private function get_settings()
  {
    return array(
      'smtpHost' => get_option('tlc-events-smtp-host'),
      'smtpPort' => get_option('tlc-events-smtp-port'),
      'smtpUser' => get_option('tlc-events-smtp-user'),
      'smtpPassword' => get_option('tlc-events-smtp-password'),
    
      'adminEmail' => get_option('tlc-events-admin-email'),
    );
  }

  public function send_smtp_email($phpmailer)
  {
    $settings = $this->get_settings();

    if(WP_DEBUG == true) { $phpmailer->isMail(); }
    else { $phpmailer->isSmtp(); } 
    $phpmailer->Host       = $settings['smtpHost'];
    $phpmailer->SMTPAuth   = true;
    $phpmailer->Port       = $settings['smtpPort'];
    $phpmailer->SMTPSecure = 'ssl';
    $phpmailer->Username   = $settings['smtpUser'];
    $phpmailer->Password   = $settings['smtpPassword'];
    $phpmailer->From       = $settings['smtpUser'];
    $phpmailer->FromName   = get_option('blogname') . __("Notification", "tlc-events");
  }
}
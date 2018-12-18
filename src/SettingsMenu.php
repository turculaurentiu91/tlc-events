<?php
namespace TlcEvents;

class SettingsMenu
{
  public function __construct()
  {
    add_action('admin_menu', array($this, 'add_submenu'));
  }

  public function add_submenu()
  {
    \add_submenu_page(
      'edit.php?post_type=tlc-event',
      __("Settings", "tlc-events"),
      __("Settings", "tlc-events"),
      'manage_options',
      'tlc-events-settings',
      array($this, 'render_settings_page')
    );
  }

  public function render_settings_page()
  {
    include "settings-menu-template.php";
  }
}
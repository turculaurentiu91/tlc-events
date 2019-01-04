<?php
namespace TlcEvents;

class EventPostType
{
  private $metabox;
  private static $instance;

  public function __construct()
  {
    add_action('init', array($this, 'create_post_type'));
    add_action('init', array($this, 'register_shortcode'));
    add_action('load-options-permalink.php', array($this, 'flush_rules'));
    add_filter( 'post_type_link', array($this, 'set_link'), 10, 2 );
    add_filter('the_content', array($this, 'render_event_content'));
    $this->metabox = new EventMetaBox();
  }

  public static function getInstance()
  {
    if (isset(self::$instance)) {
      return self::$instance;
    } else {
      self::$instance = new EventPostType();
      return self::$instance;
    }
  }

  public function render_event_content($content)
  {
    global $post;
    if ($post->post_type == "tlc-event") {
      ob_start();
      include "template-single-event.php";
      $content .= ob_get_contents();
      ob_end_clean();
    } 
    return $content;
  }

  public function set_link($url, $post)
  {
    $event_link = get_option('tlc-events-rewrite');
    $event_link = $event_link ? $event_link : 'events';

    if (get_post_type($post) == 'tlc-event') {
      if (get_option('permalink_structure')) {
        return get_site_url() . "/{$event_link}/" . $post->post_name;
      }
    }

    return $url;
  }

  public function flush_rules()
  {
    $this->register_custom_permalink();
    flush_rewrite_rules();
  }

  public function register_custom_permalink()
  {
    $event_link = get_option('tlc-events-rewrite');
    $event_link = $event_link ? $event_link : 'events';

    add_rewrite_rule(
      "^{$event_link}/([^/]*)/?",
      'index.php?tlc-event=$matches[1]',
      'top'
    );
  }

  public function register_shortcode()
  {
    add_shortcode('tlc_events_archive', function($atts){
      ob_start();
      include "archive-template.php";
      $content = ob_get_contents();
      ob_end_clean();
      return $content;
    });

    add_shortcode('tlc_events_upcoming', function($atts){
      ob_start();
      include "upcoming-template.php";
      $content = \ob_get_contents();
      ob_end_clean();
      return $content;
    });
  }

  public function create_post_type()
  {
    $labels = array(
      'name'                  => _x( 'Events', 'Post Type General Name', 'tlc-events' ),
      'singular_name'         => _x( 'Event', 'Post Type Singular Name', 'tlc-events' ),
      'menu_name'             => __( 'Events', 'tlc-events' ),
      'name_admin_bar'        => __( 'Event' ),
      'archives'              => __( 'Event Archives', 'tlc-events' ),
      'attributes'            => __( 'Event Attributes', 'tlc-events' ),
      'parent_item_colon'     => __( 'Parent Event:', 'tlc-events' ),
      'all_items'             => __( 'All Events', 'tlc-events' ),
      'add_new_item'          => __( 'Add New Event', 'tlc-events' ),
      'add_new'               => __( 'Add New', 'tlc-events' ),
      'new_item'              => __( 'New Event', 'tlc-events' ),
      'edit_item'             => __( 'Edit Event', 'tlc-events' ),
      'update_item'           => __( 'Update Event', 'tlc-events' ),
      'view_item'             => __( 'View Event', 'tlc-events' ),
      'view_items'            => __( 'View Events', 'tlc-events' ),
      'search_items'          => __( 'Search Event', 'tlc-events' ),
      'not_found'             => __( 'Not found', 'tlc-events' ),
      'not_found_in_trash'    => __( 'Not found in Trash', 'tlc-events' ),
      'featured_image'        => __( 'Featured Image', 'tlc-events' ),
      'set_featured_image'    => __( 'Set featured image', 'tlc-events' ),
      'remove_featured_image' => __( 'Remove featured image', 'tlc-events' ),
      'use_featured_image'    => __( 'Use as featured image', 'tlc-events' ),
      'insert_into_item'      => __( 'Insert into event', 'tlc-events' ),
      'uploaded_to_this_item' => __( 'Uploaded to this event', 'tlc-events' ),
      'items_list'            => __( 'Events list', 'tlc-events' ),
      'items_list_navigation' => __( 'Events list navigation', 'tlc-events' ),
      'filter_items_list'     => __( 'Filter events list', 'tlc-events' ),
    );
    $args = array(
      'label'                 => __( 'Event', 'tlc-events' ),
      'description'           => __( 'Events', 'tlc-events' ),
      'labels'                => $labels,
      'supports'              => array( 'title', 'editor', 'thumbnail',),
      'taxonomies'            => array(),
      'hierarchical'          => false,
      'public'                => true,
      'show_ui'               => true,
      'show_in_menu'          => true,
      'menu_position'         => 5,
      'show_in_admin_bar'     => true,
      'show_in_nav_menus'     => false,
      'can_export'            => true,
      'has_archive'           => false,
      'exclude_from_search'   => false,
      'publicly_queryable'    => true,
      'capability_type'       => 'page',
      'menu_icon'             => plugins_url( 'calendar.png', dirname(__FILE__) ),
      'rewrite'               => false,
    );
    register_post_type( 'tlc-event', $args );
  
  }
}
<?php
namespace TlcEvents;

class Api
{
  public function __construct()
  {
    add_action('rest_api_init', array($this, 'register_routes'));
  }

  public function register_routes()
  {
    $namespace = 'tlc-events';

    register_rest_route(
      $namespace,
      '/subscribe',
      array(
        'methods' => 'POST',
        'callback' => array($this, 'post_subscription')
      )
    );

    register_rest_route(
      $namespace,
      'admin/unsubscribe',
      array(
        'methods' => 'POST',
        'callback' => array($this, 'admin_unsubscribe')
      )
    );
  }

  public function admin_unsubscribe($request)
  {
    $req_data = $request->get_json_params();
    //Check if event_id is submitted
    if (!isset($req_data['event_id']))
    {
      return new \WP_Error( 'no_event_id', 'Event Id Missing', array('status' => 400));
    }

    //check if event_id is valid and get its dates
    $eventDates = 
      json_decode(
        base64_decode(
          get_post_meta(
            $req_data['event_id'], 'tlc-dates', true
          )
        ), 
        true
      );
    
      if($eventDates == null) 
      {
        return new \WP_Error('invalid_event_id', 'Invalid Event Id', array('status' => 400));
      }

      //check if date_id and location_id are set
      if ( !isset($req_data['date_id']) || !isset($req_data['location_id']) )
      {
        return new \WP_Error('no_date_location_id', 'No date_id or location_id fields', array('status' => 400));
      }

      //validate date_id and get the date
      $date = null;
      $dateKey = null;
      foreach ($eventDates as $key => $value) {
        if ($value['id'] === $req_data['date_id']) {
          $date = $value;
          $dateKey = $key;
        }
      }

      if($date === null) {
        return new \WP_Error('invalid_date_id', 'Invalid date ID', array('status' => 400));
      }

      //validate location_id and get the location
      $location = null;
      $locKey = null;
      foreach($date['locations'] as $key => $value) {
        if ($value['id'] === $req_data['location_id'] ) {
          $location = $value;
          $locKey = $key;
        }
      }

      if($location === null) {
        return new \WP_Error('invalid_location_id', 'Invalid location ID', array('status' => 400));
      }

      $subscription = null;
      $subKey = null;
      foreach($location['subscriptions'] as $key => $value) {
        if ($value['id'] === $req_data['subscription_id'] ) {
          $subscription = $value;
          $subKey = $key;
        }
      }

      if($subscription === null) {
        return new \WP_Error('invalid_location_id', 'Invalid location ID', array('status' => 400));
      }

      //set the deleted at value to curent date 
      $subscription['verwijderd_op'] = current_time("d-m-Y H:i");
      $eventDates[$dateKey]['locations'][$locKey]['subscriptions'][$subKey] =
        $subscription;
      
      //update the database
      $encodedDates = base64_encode(json_encode($eventDates));
      update_post_meta($req_data['event_id'], 'tlc-dates', $encodedDates);

      //notify the user if its set in the req_data
      if (isset($req_data['notify'])) {
        if ($req_data['notify']) {

          $find_tags = array('%location%', '%city%', '%date%', '%start_time%', '%end_time%',
             '%address%', '%event_title%', '%event_link%');
      
          $replace_tags = array(
            $location['name'],
            $location['city'],
            "{$date['day']}/{$date['month']}/{$date['year']}",
            "{$location['startHour']}:{$location['startMin']}",
            "{$location['endHour']}:{$location['endMin']}",
            $location['address'],
            get_post($req_data['event_id'])->post_title,
            "<a href=\"". get_post($req_data['event_id'])->guid ."\">Event</a>",
          );
      
          $email = str_replace($find_tags, $replace_tags, get_option('tlc-events-unsub-template'));
      
          wp_mail(
            $subscription['e_mailadres'],
            __("Subscription Notification", "tlc-events"),
            $email
          );

        }
      }

      //return the subscription
      return $subscription;
  }
  

  private function notify_admin($email, $event, $date, $location, $admin_email)
  {
    wp_mail(
      $admin_email,
      'New Subscription',
      "<h2>New subscription</h2> 
      Event: {$event}<br>
      Date: {$date['day']}-{$date['month']}-{$date['year']}<br>
      Location: City: {$location['city']} | Address: {$location['address']}<br>
      Subscriber Email: {$email}" 
    );
  }

  private function notify_sub($data)
  {
    $find_tags = array('%location%', '%city%', '%date%', '%start_time%', '%end_time%', '%address%', '%event_title%', '%unsubscribe_link%');
    foreach($data['form-fields'] as $key => $field)
    {
      $find_tags[] = "%" .$key. "%";
    }

    $replace_tags = array(
      $data['location'],
      $data['city'],
      "{$data['date']['day']}-{$data['date']['month']}-{$data['date']['year']}",
      "{$data['start_hour']}:{$data['start_min']}",
      "{$data['end_hour']}:{$data['end_min']}",
      $data['address'],
      $data['event'],
      '<a href="' .$data['unsubscribe_link'] .'">' . __("Unsubscribe","tlc-events") . '</a>'
    );

    foreach($data['form-fields'] as $key => $value) {
      $replace_tags[] = $value;
    }

    $email = str_replace($find_tags, $replace_tags, $data['email-template'])[0];

    //var_dump(array($find_tags, $replace_tags, $email));

    wp_mail(
      $data['email'],
      __("Subscription Notification", "tlc-events"),
      $email
    );
  }

  public function post_subscription($request)
  {
    $req_data = $request->get_json_params();
    if (!isset($req_data['event_id']))
    {
      return new \WP_Error( 'no_event_id', 'Event Id Missing', array('status' => 400));
    }

    $eventDates = 
      json_decode(
        base64_decode(
          get_post_meta(
            $req_data['event_id'], 'tlc-dates', true
          )
        ), 
        true
      );
    
      if($eventDates == null) 
      {
        return new \WP_Error('invalid_event_id', 'Invalid Event Id', array('status' => 400));
      }

      $eventFormFields = 
      json_decode(
        base64_decode(
          get_post_meta(
            $req_data['event_id'], 'tlc-form-fields', true
          )
        ), 
        true
      );

      if ( !isset($req_data['date_id']) || !isset($req_data['location_id']) )
      {
        return new \WP_Error('no_date_location_id', 'No date_id or location_id fields', array('status' => 400));
      }

      if (!isset($eventDates[$req_data['date_id']]))
      {
        return new \WP_Error('invalid_date_id', 'Invald date_id field', array('status' => 400));
      }

      $date = $eventDates[$req_data['date_id']];
      $location = $date['locations'][$req_data['location_id']];

      if (!isset($location))
      {
        return new \WP_Error('invalid_location_id', 'Invald location_id field', array('status' => 400));
      }

      foreach($eventFormFields as $field) 
      {
        if (!isset($req_data[$field['slug']]))
        {
          return new \WP_Error("no_{$field['slug']}", "No {$field['slug']} Set", array('status' => 400));
        }
      }

      $subscription = array();
      foreach($eventFormFields as $field)
      {
        $subscription[$field['slug']] = $req_data[$field['slug']];
      }

      $subscription['geregistreerd_op'] = current_time("d-m-Y H:i");
      $subscription['verwijderd_op'] = " ";
      $subscription['id'] = Helper::randString();

      $eventDates[$req_data['date_id']]['locations'][$req_data['location_id']]['subscriptions'][] = $subscription;
      $encodedDates = base64_encode(json_encode($eventDates));
      update_post_meta($req_data['event_id'], 'tlc-dates', $encodedDates);

      $unsubscribeData = base64_encode(json_encode(array(
        'event_id' => $req_data['event_id'],
        'subscription_id' => $subscription['id'],
        'date_id' => $date['id'],
        'location_id' => $location['id'],
      )));

      $unsubscribe_link = 
        get_permalink(get_post($req_data['event_id'])) .
        "/?unsubscribe=" . 
        $unsubscribeData;

      $this->notify_admin(
        $req_data['e_mailadres'],
        get_post($req_data['event_id'])->post_title,
        $eventDates[$req_data['date_id']],
        $eventDates[$req_data['date_id']]['locations'][$req_data['location_id']],
        get_post_meta($req_data['event_id'], 'tlc-admin-email', true)
      );

      $this->notify_sub(array(
        'email' => $req_data['e_mailadres'],
        'date' => $eventDates[$req_data['date_id']],
        'event' => get_post($req_data['event_id'])->post_title,
        'location' => $location['name'],
        'city' => $location['city'],
        'address' => $location['address'],
        'start_hour' => $location['startHour'],
        'start_min' => $location['startMin'],
        'end_hour' => $location['endHour'],
        'end_min' => $location['endMin'],
        'unsubscribe_link' => $unsubscribe_link,
        'form-fields' => $subscription,
        'email-template' => get_post_meta($req_data['event_id'], 'tlc-email-template')
      ));
      return $subscription;
  }
}
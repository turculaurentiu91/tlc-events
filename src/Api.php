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
  }

  

  private function notify_admin($email, $event, $date, $location)
  {
    wp_mail(
      get_option('tlc-events-admin-email'),
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
    $find_tags = array('%city%', '%date%', '%time%', '%address%', '%event_title%', '%unsubscribe_link%');
    foreach($data['form-fields'] as $key => $field)
    {
      $find_tags[] = "%" .$key. "%";
    }

    $replace_tags = array(
      $data['city'],
      "{$data['date']['day']}-{$data['date']['month']}-{$data['date']['year']}",
      "{$data['hour']}:{$data['min']}",
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

      if (isset($location['subscriptions']))
      {
        foreach($location['subscriptions'] as $sub)
        {
          if ($sub['e_mailadres'] == $req_data['e_mailadres'])
          {
            return new \WP_Error('already_subscribed', 'Already Subscribed');
          }
        }
      }

      $eventDates[$req_data['date_id']]['locations'][$req_data['location_id']]['subscriptions'][] = $subscription;
      $encodedDates = base64_encode(json_encode($eventDates));
      update_post_meta($req_data['event_id'], 'tlc-dates', $encodedDates);

      $unsubscribeData = base64_encode(json_encode(array(
        'event_id' => $req_data['event_id'],
        'email' => $req_data['e_mailadres'],
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
        $eventDates[$req_data['date_id']]['locations'][$req_data['location_id']]
      );

      $this->notify_sub(array(
        'email' => $req_data['e_mailadres'],
        'date' => $eventDates[$req_data['date_id']],
        'event' => get_post($req_data['event_id'])->post_title,
        'city' => $location['city'],
        'address' => $location['address'],
        'hour' => $location['startHour'],
        'min' => $location['startMin'],
        'unsubscribe_link' => $unsubscribe_link,
        'form-fields' => $subscription,
        'email-template' => get_post_meta($req_data['event_id'], 'tlc-email-template')
      ));

      return array(
        'status' => 'success', 
        'link' => $unsubscribe_link,
      );
  }
}
<?php


class BCalendar
{
    public $client;
    public $service;

    function __construct($info)
    {
        @session_start();
        $this->client = new Google_Client();
        $this->client->setApplicationName($info['name']);
        $this->client->setDeveloperKey($info['key_api']);
        
        if (isset($_SESSION['service_token'])) {
            $this->client->setAccessToken($_SESSION['service_token']);
        }
        $this->service = new Google_Service_Calendar($this->client);

        $cred = new Google_Auth_AssertionCredentials($info['key_client'], array(
            'https://www.googleapis.com/auth/calendar',
            'https://www.googleapis.com/auth/calendar.readonly',
        ), file_get_contents(realpath(dirname(__FILE__)."/../client/".$info['key'])));
        
        $this->client->setAssertionCredentials($cred);
        //$cred->sub = 'espaciounido@gmail.com';

        if ($this->client->getAuth()->isAccessTokenExpired()) {
            $this->client->getAuth()->refreshTokenWithAssertion(); // fails on this line
        }
        $_SESSION['service_token'] = $this->client->getAccessToken();
    }
    
    function deleteCalendar($calendarId)
    {
        return $createdCalendar = $this->service->calendars->delete($calendarId);
    }   

    function createCalendar($summary)
    {
        
        $calendar = new Google_Service_Calendar_Calendar();
        $calendar->setSummary($summary);
        $calendar->setTimeZone('America/Bogota');
        
        $createdCalendar = $this->service->calendars->insert($calendar);
        $id = $createdCalendar->getId();

        $this->addAcl($id);

        return $id;
    }
    
    function addAcl($calendarId){
        $rule = new Google_Service_Calendar_AclRule();
        $scope = new Google_Service_Calendar_AclRuleScope();

        $scope->setType("default");
        //$scope->setValue("scopeValue");
        $rule->setScope($scope);
        $rule->setRole("reader");

        $createdRule = $this->service->acl->insert($calendarId, $rule);
        
        return $createdRule->getId();
    }

    function addEvent($calendarId, $summary, $description, $location, $dataTimeStart, $dataTimeEnd, $email, $accept)
    {
        
        $event = new Google_Service_Calendar_Event();
        $event->setSummary($summary);
        $event->setLocation($location);
        $event->setDescription($description);
        $event->setVisibility('public');

        $start = new Google_Service_Calendar_EventDateTime();
        $start->setDateTime($dataTimeStart);
        $start->setTimeZone('America/Bogota');
        $event->setStart($start);
        
        $end = new Google_Service_Calendar_EventDateTime();
        $end->setDateTime($dataTimeEnd);
        $end->setTimeZone('America/Bogota');
        $event->setEnd($end);

        $reminder1 = new Google_Service_Calendar_EventReminder();
        $reminder1->setMethod('email');
        $reminder1->setMinutes('55');

        $reminder2 = new Google_Service_Calendar_EventReminder();
        $reminder2->setMethod('email');
        $reminder2->setMinutes('15');

        $reminder = new Google_Service_Calendar_EventReminders();
        $reminder->setUseDefault('false');
        $reminder->setOverrides(array($reminder1,  $reminder2));
        $event->setReminders($reminder);

        //$event->setRecurrence(array('RRULE:FREQ=WEEKLY;UNTIL=20110701T170000Z'));
        $attendee1 = new Google_Service_Calendar_EventAttendee();
        $attendee1->setEmail($email);
        if($accept == "true"){
           $attendee1->setResponseStatus('accepted');
        }
        $attendees        = array(
            $attendee1
        );
        
        $event->attendees = $attendees;
        
        $optParams = Array(
            'sendNotifications' => true,
            'maxAttendees' => 1000
        );

        /*$creator = new Google_Service_Calendar_EventCreator();
        $creator->setDisplayName("UNAD Calendar");
        $creator->setEmail("106295480288-s6a44jaogn7pembonh8mudn4gutbn28n@developer.gserviceaccount.com");

        $event->setCreator($creator);*/

        $nEvent = $this->service->events->insert($calendarId, $event, $optParams);
        

        return $nEvent;
    }
    
    function getCalendar($summary)
    {
        $calendarList = $this->service->calendarList->listCalendarList();
        
        while (true) {
            foreach ($calendarList->getItems() as $calendarListEntry) {
                if ($summary == $calendarListEntry->getSummary()) {
                    return $calendarListEntry;
                }
            }
            $pageToken = $calendarList->getNextPageToken();
            if ($pageToken) {
                $optParams    = array(
                    'pageToken' => $pageToken
                );
                $calendarList = $this->service->calendarList->listCalendarList($optParams);
            } else {
                break;
            }
        }
        
        return null;
    }
    
    function getEvents($calendarId)
    {
        $events = $this->service->events->listEvents($calendarId);
        $arrayEvents = array();
        while(true) {
          foreach ($events->getItems() as $event) {
            $arrayEvents[] = $event;
          }
          $pageToken = $events->getNextPageToken();
          if ($pageToken) {
            $optParams = array('pageToken' => $pageToken);
            $events = $this->service->events->listEvents('primary', $optParams);
          } else {
            break;
          }
        }
        return $arrayEvents;
    }

    function getService()
    {
       return $this->service;
    }

    function getCalendars()
    {
        $calendarList = $this->service->calendarList->listCalendarList();
        $array = array();
        while (true) {
            foreach ($calendarList->getItems() as $calendarListEntry) {
                $array[] = $calendarListEntry;
            }
            $pageToken = $calendarList->getNextPageToken();
            if ($pageToken) {
                $optParams    = array(
                    'pageToken' => $pageToken
                );
                $calendarList = $this->service->calendarList->listCalendarList($optParams);
            } else {
                break;
            }
        }
        
        return $array;
    }

    function setAttendees($calendarId, $eventId, $email, $accept)
    {
        $event = $this->service->events->get($calendarId, $eventId);
        
        $attendees = array();

        foreach ($event->attendees as $key => $value) {
           $attendees[] = $value;
        }

        $attendee1 = new Google_Service_Calendar_EventAttendee();
        $attendee1->setEmail($email);

        if($accept == "true"){
           $attendee1->setResponseStatus('accepted');
        }
         
        $attendees[] = $attendee1;
        $event->attendees = $attendees;
        $optParams = Array(
            'sendNotifications' => true,
            'maxAttendees' => 1000
        );
        
        $updatedEvent = $this->service->events->update($calendarId, $event->getId(), $event, $optParams);
        return $updatedEvent->getUpdated();
    }

}


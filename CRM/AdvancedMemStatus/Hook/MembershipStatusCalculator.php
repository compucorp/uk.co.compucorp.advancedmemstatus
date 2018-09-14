<?php


class CRM_AdvancedMemStatus_Hook_MembershipStatusCalculator {

  private $membershipStatus;

  private $arguments;

  private $membership;

  public function __construct(&$membershipStatus, $arguments, $membership) {
    $this->membershipStatus = &$membershipStatus;
    $this->arguments = $arguments;
    $this->membership = $membership;
  }

  public function calculate() {
    if ($this->isStatusDoesNotApplyToMembership($this->membershipStatus['id'])) {
      $this->recalculateMembershipStatus();
    }
  }

  private function isStatusDoesNotApplyToMembership($statusId) {
    $exceptionStatusesForMembershipType = $this->getExceptionStatusesForMembershipType();
    if (in_array($statusId, $exceptionStatusesForMembershipType)) {
      return TRUE;
    }

    return FALSE;
  }

  private function getExceptionStatusesForMembershipType() {
    $currentMembershipType = $this->membership['membership_type_id'];

    return [3];  // TODO: replace [3] with outside call to get Statuses expcetions
  }

  private function recalculateMembershipStatus() {
    $membershipNewStatus = $this->getMembershipStatusByDate();

    $this->membershipStatus['name'] = $membershipNewStatus['name'];
    $this->membershipStatus['id'] = $membershipNewStatus['id'];
  }

  private function getMembershipStatusByDate() {
    $startDate = $this->arguments['start_date'];
    $endDate = $this->arguments['end_date'];
    $joinDate = $this->arguments['join_date'];
    $statusDate =  $this->arguments['status_date'];

    $excludeIsAdmin = FALSE;
    if (empty($this->membership['is_override'])) {
      $excludeIsAdmin = TRUE;
    }

    $membershipDetails = array();

    $dates = array('start', 'end', 'join');
    $events = array('start', 'end');
    foreach ($dates as $dat) {
      if (${$dat . 'Date'} && ${$dat . 'Date'} != "null") {
        ${$dat . 'Date'} = CRM_Utils_Date::customFormat(${$dat . 'Date'}, '%Y%m%d');
        ${$dat . 'Year'} = substr(${$dat . 'Date'}, 0, 4);
        ${$dat . 'Month'} = substr(${$dat . 'Date'}, 4, 2);
        ${$dat . 'Day'} = substr(${$dat . 'Date'}, 6, 2);
      }
      else {
        ${$dat . 'Date'} = '';
      }
    }
    //fix for CRM-3570, if we have statuses with is_admin=1,
    //exclude these statuses from calculatation during import.
    $where = "is_active = 1";
    if ($excludeIsAdmin) {
      $where .= " AND is_admin != 1";
    }
    $query = "
 SELECT   *
 FROM     civicrm_membership_status
 WHERE    {$where}
 ORDER BY weight ASC";
    $membershipStatus = CRM_Core_DAO::executeQuery($query);
    $hour = $minute = $second = 0;
    while ($membershipStatus->fetch()) {
      if ($this->isStatusDoesNotApplyToMembership($membershipStatus->id)) {
        continue;
      }

      $startEvent = NULL;
      $endEvent = NULL;
      foreach ($events as $eve) {
        foreach ($dates as $dat) {
          // calculate start-event/date and end-event/date
          if (($membershipStatus->{$eve . '_event'} == $dat . '_date') &&
            ${$dat . 'Date'}
          ) {
            if ($membershipStatus->{$eve . '_event_adjust_unit'} &&
              $membershipStatus->{$eve . '_event_adjust_interval'}
            ) {
              // add in months
              if ($membershipStatus->{$eve . '_event_adjust_unit'} == 'month') {
                ${$eve . 'Event'} = date('Ymd', mktime($hour, $minute, $second,
                  ${$dat . 'Month'} + $membershipStatus->{$eve . '_event_adjust_interval'},
                  ${$dat . 'Day'},
                  ${$dat . 'Year'}
                ));
              }
              // add in days
              if ($membershipStatus->{$eve . '_event_adjust_unit'} == 'day') {
                ${$eve . 'Event'} = date('Ymd', mktime($hour, $minute, $second,
                  ${$dat . 'Month'},
                  ${$dat . 'Day'} + $membershipStatus->{$eve . '_event_adjust_interval'},
                  ${$dat . 'Year'}
                ));
              }
              // add in years
              if ($membershipStatus->{$eve . '_event_adjust_unit'} == 'year') {
                ${$eve . 'Event'} = date('Ymd', mktime($hour, $minute, $second,
                  ${$dat . 'Month'},
                  ${$dat . 'Day'},
                  ${$dat . 'Year'} + $membershipStatus->{$eve . '_event_adjust_interval'}
                ));
              }
              // if no interval and unit, present
            }
            else {
              ${$eve . 'Event'} = ${$dat . 'Date'};
            }
          }
        }
      }
      // check if statusDate is in the range of start & end events.
      if ($startEvent && $endEvent) {
        if (($statusDate >= $startEvent) && ($statusDate <= $endEvent)) {
          $membershipDetails['id'] = $membershipStatus->id;
          $membershipDetails['name'] = $membershipStatus->name;
        }
      }
      elseif ($startEvent) {
        if ($statusDate >= $startEvent) {
          $membershipDetails['id'] = $membershipStatus->id;
          $membershipDetails['name'] = $membershipStatus->name;
        }
      }
      elseif ($endEvent) {
        if ($statusDate <= $endEvent) {
          $membershipDetails['id'] = $membershipStatus->id;
          $membershipDetails['name'] = $membershipStatus->name;
        }
      }

      if ($membershipDetails) {
        break;
      }
    }
    //end fetch
    $membershipStatus->free();

    return $membershipDetails;
  }
  
}

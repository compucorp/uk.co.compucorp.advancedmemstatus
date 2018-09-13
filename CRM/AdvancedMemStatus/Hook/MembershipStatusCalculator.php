<?php


class CRM_AdvancedMemStatus_Hook_MembershipStatusCalculator {

  private $membershipStatus;

  private $arguments;

  private $membership;

  public function __construct(&$membershipStatus, $arguments, $membership) {
    $this->membershipStatus = $membershipStatus;
    $this->arguments = $arguments;
    $this->membership = $this->getMembership($membership['membership_id']);
  }

  private function getMembership($membershipID) {
    return civicrm_api3('Membership', 'getsingle', [
      'id' => $membershipID,
    ]);
  }

  public function calculate() {
    if ($this->isMembershipStatusExcludedFromMembershipType()) {
      $membershipNewStatus = $this->getMembershipStatusByDate();
      $this->membershipStatus['name'] = $membershipNewStatus['name'];
      $this->membershipStatus['id'] = $membershipNewStatus['id'];
    }
  }

  private function isMembershipStatusExcludedFromMembershipType() {
    $exceptionRulesForMembershipType = $this->getExceptionRulesForMembershipType();

    if (in_array($this->membershipStatus['id'], $exceptionRulesForMembershipType)) {
      return TRUE;
    }

    return FALSE;
  }

  private function getExceptionRulesForMembershipType() {
    $currentMembershipType = $this->membership['membership_type_id'];
    return CRM_AdvancedMemStatus_Service_StatusExclusionManager::getExclusionsForMembershipType($currentMembershipType);
  }

  private function getMembershipStatusByDate(
    $startDate, $endDate, $joinDate,
    $statusDate = 'today', $excludeIsAdmin = FALSE, $membershipTypeID, $membership = array()
  ) {
    $membershipDetails = array();
    if (!$statusDate || $statusDate == 'today') {
      $statusDate = getdate();
      $statusDate = date('Ymd',
        mktime($statusDate['hours'],
          $statusDate['minutes'],
          $statusDate['seconds'],
          $statusDate['mon'],
          $statusDate['mday'],
          $statusDate['year']
        )
      );
    }
    else {
      $statusDate = CRM_Utils_Date::customFormat($statusDate, '%Y%m%d');
    }
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
      // returns FIRST status record for which status_date is in range.
      if ($membershipDetails) {
        break;
      }
    }
    //end fetch
    $membershipStatus->free();
    //we bundle the arguments into an array as we can't pass 8 variables to the hook otherwise
    // the membership array might contain the pre-altered settings so we don't want to merge this
    $arguments = array(
      'start_date' => $startDate,
      'end_date' => $endDate,
      'join_date' => $joinDate,
      'status_date' => $statusDate,
      'exclude_is_admin' => $endDate,
      'membership_type_id' => $membershipTypeID,
      'start_event' => $startEvent,
      'end_event' => $endEvent,
    );
    CRM_Utils_Hook::alterCalculatedMembershipStatus($membershipDetails, $arguments, $membership);
    return $membershipDetails;
  }
}

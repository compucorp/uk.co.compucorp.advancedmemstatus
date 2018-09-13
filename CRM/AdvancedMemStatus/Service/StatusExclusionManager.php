<?php

class CRM_AdvancedMemStatus_Service_StatusExclusionManager {
  const EXCLUSION_CUSTOM_FIELD_NAME = 'xxxxxxxxx';

  public static function getExclusionsForMembershipType($membershipTypeID) {
    $membershipType = civicrm_api3('MembershipType', 'getsingle', [
      'sequential' => 1,
      'id' => $membershipTypeID,
    ]);

    $excludedStatuses = explode(
      CRM_CORE_DAO::VALUE_SEPARATOR,
      $membershipType[self::EXCLUSION_CUSTOM_FIELD_NAME]
    );

    return $excludedStatuses;
  }

}

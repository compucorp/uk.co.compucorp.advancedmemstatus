<?php

class CRM_AdvancedMemStatus_Service_StatusExclusionManager {
  const EXCLUSION_CUSTOM_FIELD_NAME = 'excluded_membership_statuses';

  public static function getExclusionsForMembershipType($membershipTypeID) {
    $membershipType = civicrm_api3('MembershipType', 'getsingle', [
      'sequential' => 1,
      'id' => $membershipTypeID,
    ]);

    $customFieldKey = 'custom_' . self::getCustomFieldId();
    $excludedStatuses = [];
    if (!empty($membershipType[$customFieldKey])) {
      $excludedStatuses = $membershipType[$customFieldKey];
    }

    return $excludedStatuses ;
  }

  private static function getCustomFieldId() {
    $result = civicrm_api3('CustomField', 'get', [
      'sequential' => 1,
      'return' => ['id'],
      'custom_group_id' => 'membership_status_exceptions',
      'name' => self::EXCLUSION_CUSTOM_FIELD_NAME,
    ]);

    $id = NULL;
    if (!empty($result['id'])) {
      $id = $result['id'];
    }

    return $id;
  }

}

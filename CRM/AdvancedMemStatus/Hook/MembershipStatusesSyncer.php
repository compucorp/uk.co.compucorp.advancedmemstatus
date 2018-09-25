<?php

class CRM_Advancedmemstatus_Hook_MembershipStatusesSyncer {

  public function sync($membershipStatusId) {
    $membershipStatus = civicrm_api3('MembershipStatus', 'get', [
      'sequential' => 1,
      'id' => $membershipStatusId,
    ])['values'][0];

    $isOptionValueActive = empty($membershipStatus['is_active']) ? 0 : 1;

    $membershipStatusOptionValue = civicrm_api3('OptionValue', 'get', [
      'sequential' => 1,
      'option_group_id' => 'excluded_membership_statuses',
      'value' => $membershipStatus['name'],
    ]);

    $params = [
      'option_group_id' => 'excluded_membership_statuses',
      'name' => $membershipStatus['name'],
      'value' => $membershipStatus['name'],
      'label' => $membershipStatus['label'],
      'is_active' => $isOptionValueActive,
    ];

    if (!empty($membershipStatusOptionValue['id'])) {
      $params['id'] = $membershipStatusOptionValue['id'];
    }

    civicrm_api3('OptionValue', 'create', $params);
  }

}

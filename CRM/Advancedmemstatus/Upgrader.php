<?php

use CRM_Advancedmemstatus_ExtensionUtil as ExtensionUtil;

class CRM_Advancedmemstatus_Upgrader extends CRM_Advancedmemstatus_Upgrader_Base {

  public function install() {
    $this->addMembershipTypeToCustomGroupEntities();
    $this->addCustomGroupsAndFields();
    $this->syncMembershipStatusesWithExcludedMembershipStatusesOptionGroup();
  }

  private function addMembershipTypeToCustomGroupEntities() {
    $optionValueParams = [
      'option_group_id' => 'cg_extend_objects',
      'name' => 'civicrm_membership_type',
      'label' => 'MembershipType',
      'value' => 'MembershipType',
    ];

    $cgextendOptionValue = civicrm_api3('OptionValue', 'get', [
      'option_group_id' => $optionValueParams['option_group_id'],
      'name' => $optionValueParams['name'],
    ]);

    if (!$cgextendOptionValue['count']) {
      civicrm_api3('OptionValue', 'create', $optionValueParams);
    }
  }

  private function addCustomGroupsAndFields() {
    $customGroupsXMLFile = ExtensionUtil::path('xml/customgroups.xml');
    $import = new CRM_Utils_Migrate_Import();
    $import->run($customGroupsXMLFile);
  }

  private function syncMembershipStatusesWithExcludedMembershipStatusesOptionGroup() {
    $membershipStatuses = $this->getAllMembershipStatues();
    foreach ($membershipStatuses as $membershipStatus) {
      $this->createExcludedMembershipStatusesOptionValueIfNotExist(
        $membershipStatus['name'],
        $membershipStatus['is_active'],
        $membershipStatus['label']
      );
    }
  }

  private function getAllMembershipStatues() {
    $membershipStatuses = civicrm_api3('MembershipStatus', 'get', [
      'sequential' => 1,
      'return' => ['id', 'name', 'label', 'is_active'],
      'options' => ['limit' => 0],
    ]);

    if (!empty($membershipStatuses['values'])) {
      return $membershipStatuses['values'];
    }

    return [];
  }

  private function createExcludedMembershipStatusesOptionValueIfNotExist($name, $isActive, $label) {
    $optionValue = civicrm_api3('OptionValue', 'get', [
      'sequential' => 1,
      'option_group_id' => 'excluded_membership_statuses',
      'name' => $name,
    ]);

    $isOptionValueActive = empty($isActive) ? 0 : 1;
    if (!$optionValue['count']) {
      civicrm_api3('OptionValue', 'create', [
        'option_group_id' => 'excluded_membership_statuses',
        'name' => $name,
        'value' => $name,
        'label' => $label,
        'is_active' => $isOptionValueActive,
      ]);
    }
  }

}

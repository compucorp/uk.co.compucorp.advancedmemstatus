<?php

require_once 'advancedmemstatus.civix.php';
use CRM_Advancedmemstatus_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function advancedmemstatus_civicrm_config(&$config) {
  _advancedmemstatus_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function advancedmemstatus_civicrm_xmlMenu(&$files) {
  _advancedmemstatus_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function advancedmemstatus_civicrm_install() {
  _advancedmemstatus_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function advancedmemstatus_civicrm_postInstall() {
  _advancedmemstatus_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function advancedmemstatus_civicrm_uninstall() {
  _advancedmemstatus_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function advancedmemstatus_civicrm_enable() {
  _advancedmemstatus_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function advancedmemstatus_civicrm_disable() {
  _advancedmemstatus_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function advancedmemstatus_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _advancedmemstatus_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function advancedmemstatus_civicrm_managed(&$entities) {
  _advancedmemstatus_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function advancedmemstatus_civicrm_caseTypes(&$caseTypes) {
  _advancedmemstatus_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function advancedmemstatus_civicrm_angularModules(&$angularModules) {
  _advancedmemstatus_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function advancedmemstatus_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _advancedmemstatus_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function advancedmemstatus_civicrm_entityTypes(&$entityTypes) {
  _advancedmemstatus_civix_civicrm_entityTypes($entityTypes);
}

function advancedmemstatus_civicrm_postSave_civicrm_membership_status($dao) {
  $syncer = new CRM_Advancedmemstatus_Hook_MembershipStatusesSyncer();
  $syncer->sync($dao->id);
}

function advancedmemstatus_civicrm_alterCalculatedMembershipStatus(&$membershipStatus, $arguments, $membership) {
  $membershipStatusCalculator = new CRM_AdvancedMemStatus_Hook_MembershipStatusCalculator($membershipStatus, $arguments, $membership);
  $membershipStatusCalculator->calculate();
}

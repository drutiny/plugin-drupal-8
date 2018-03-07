<?php

namespace Drutiny\Plugin\Drupal8\Audit;

use Drutiny\Audit;
use Drutiny\Sandbox\Sandbox;

/**
 * User #1
 */
class UntrustedRoles extends Audit {

  /**
   *
   */
  public function audit(Sandbox $sandbox) {
    // Get the machine names of the untrusted roles
    $untrusted_roles = $sandbox->getParameter('untrusted_roles', ['anonymous', 'authenticated']);
    
    // Load all of Drupal's permissions so that we have access to the
    // "restrict access" property.
    $command = "echo json_encode(\\Drupal::service('user.permissions')->getPermissions());";
    $command = "\"$command\"";
    $all_permissions = $sandbox->drush(['format' => 'json'])->ev($command);

    $rows = [];
    foreach ($untrusted_roles as $role) {
      $untrusted_permissions = [];
      // Get all permissions assigned to the untrusted role.
      $role_permissions = $sandbox->drush(['format' => 'json'])
        ->roleList($role);
      
      // Check each permission assigned to the untrusted role and determine if
      // it is administrative.
      // Administrative permissions will either have the "restrict access"
      // property set, or the permission name contains the string "administer".
      foreach ($role_permissions as $permission_name => $array) {
        if ( isset($all_permissions[$permission_name]['restrict access']) ||
          strstr($permission_name, 'administer') !== FALSE) {
          $untrusted_permissions[] = $all_permissions[$permission_name]['title'];
        }
      }

      if (!empty($untrusted_permissions)) {
        $rows[] = [
          'role' => $role,
          'permissions' => implode(', ', $untrusted_permissions),
        ];
      }
    }

    $sandbox->setParameter('rows', $rows);

    return empty($rows) ? AUDIT::SUCCESS : AUDIT::FAIL;
  }
}

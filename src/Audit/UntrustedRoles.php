<?php

namespace Drutiny\Plugin\Drupal8\Audit;

use Drutiny\Audit;
use Drutiny\Sandbox\Sandbox;

/**
 * User #1
 */
class UntrustedRoles extends Audit {

  public function audit(Sandbox $sandbox) {
    $rows = $sandbox->drush()->evaluate(function ($roles) {
      // Load all of Drupal's permissions so that we have access to the
      // "restrict access" property.
      $all_permissions = \Drupal::service('user.permissions')->getPermissions();

      $rows = [];
      foreach ($roles as $role) {
        $untrusted_permissions = [];

        // Get all permissions assigned to the untrusted role.
        $roleObj = \Drupal\user\Entity\Role::load($role);
        $permissions = $roleObj->getPermissions();

        // Check each permission assigned to the untrusted role and determine if
        // it is administrative.
        // Administrative permissions will either have the "restrict access"
        // property set, or the permission name contains the string "administer".
        foreach ($permissions as $permission) {
          if (isset($all_permissions[$permission]['restrict access']) ||
            strstr($permission, 'administer') !== FALSE ) {
              $untrusted_permissions[] = $all_permissions[$permission]['title'];
          }
        }

        if (!empty($untrusted_permissions)) {
          $rows[] = [
            'role' => $role,
            'permissions' => implode(', ', $untrusted_permissions),
          ];
        }
      }
      return $rows;
    }, [
      'roles' => $sandbox->getParameter('untrusted_roles', ['anonymous', 'authenticated'])
    ]);

    $sandbox->setParameter('rows', $rows);

    return empty($rows) ? AUDIT::SUCCESS : AUDIT::FAIL;
  }
}

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

//    // Get the details for user #1.
//    $user = $sandbox->drush(['format' => 'json'])
//                    ->userInformation(1);
//
//    $user = (object) array_pop($user);
//
//    $errors = [];
//    $fixups = [];
//
//    // Username.
//    $pattern = $sandbox->getParameter('blacklist');
//    if (preg_match("#${pattern}#i", $user->name)) {
//      $errors[] = "Username '$user->name' is too easy to guess.";
//    }
//    $sandbox->setParameter('username', $user->name);
//
//    // Email address.
//    $email = $sandbox->getParameter('email');
//
//    if (!empty($email) && ($email !== $user->mail)) {
//      $errors[] = "Email address '$user->mail' is not set correctly.";
//    }
//
//    // Status.
//    $status = (bool) $sandbox->getParameter('status');
//    if ($status !== (bool) $user->status) {
//      $errors[] = 'Status is not set correctly. Should be ' . ($user->status ? 'active' : 'inactive') . '.';
//    }
//
//    $sandbox->setParameter('errors', $errors);
//    return empty($errors) ? TRUE : Audit::WARNING;
    return true;
  }
//
  public function remediate(Sandbox $sandbox)
  {

    // Get the details for user #1.
    $user = $sandbox->drush(['format' => 'json'])
                    ->userInformation(1);

    $user = (object) array_pop($user);

    $output = $sandbox->drush()->evaluate(function ($uid, $status, $password, $email, $username) {
      $user =  \Drupal\user\Entity\User::load($uid);
      if ($status) {
        $user->activate();
      }
      else {
        $user->block();
      }
      $user->setPassword($password);
      $user->setEmail($email);
      $user->setUsername($username);
      $user->set('init', $email);
      $user->save();
      return TRUE;
    }, [
      'uid' => $user->uid,
      'status' => (int) (bool) $sandbox->getParameter('status'),
      'password' => $this->generateRandomString(),
      'email' => $sandbox->getParameter('email'),
      'username' => $this->generateRandomString()
    ]);

    return $this->check($sandbox);
  }

  /**
   * Generate a random string.
   *
   * @param int $length
   *   [optional]
   *   the length of the random string.
   *
   * @return string
   *   the random string.
   */
//  public function generateRandomString($length = 32) {
//
//    // Generate a lot of random characters.
//    $state = bin2hex(random_bytes($length * 2));
//
//    // Remove non-alphanumeric characters.
//    $state = preg_replace("/[^a-zA-Z0-9]/", '', $state);
//
//    // Trim it down.
//    return substr($state, 0, $length);
//  }

}

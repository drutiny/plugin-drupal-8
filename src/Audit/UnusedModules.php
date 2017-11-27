<?php

namespace Drutiny\Plugin\Drupal8\Audit;

use Drutiny\Audit;
use Drutiny\Sandbox\Sandbox;
use Drutiny\Driver\DrushFormatException;

/**
 *  Cron last run.
 */
class UnusedModules extends Audit {

  /**
   *
   */
  public function audit(Sandbox $sandbox) {

    try {
      $list = $sandbox->drush(['format' => 'json'])->pmInfo();
    }
    catch (DrushFormatException $e) {
      return FALSE;
    }

    $installed_paths = [];
    $disabled = [];
    foreach ($list as $project => $info) {

      if (strpos($info['package'], 'Core') !== FALSE) {
        continue;
      }
      if ($info['type'] == 'theme') {
        continue;
      }
      if ($info['status'] == 'enabled') {
        $installed_paths[] = $info['path'];
        continue;
      }
      $disabled[$project] = $info;
    }

    $unused = [];
    foreach ($disabled as $project => $info) {
      foreach ($installed_paths as $path) {
        if (strpos($info['path'], $path) !== FALSE) {
          continue 2;
        }
      }
      $unused[] = $info['title'];
    }

    $sandbox->setParameter('unused_modules', $unused);

    return !count($unused);
  }

}

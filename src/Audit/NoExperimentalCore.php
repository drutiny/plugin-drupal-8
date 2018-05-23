<?php

namespace Drutiny\Plugin\Drupal8\Audit;

use Drutiny\Audit;
use Drutiny\Sandbox\Sandbox;

/**
 * Generic module is disabled check.
 */
class NoExperimentalCore extends Audit {

  /**
   *
   */
  public function audit(Sandbox $sandbox)
  {

    $info = $sandbox->drush([
      'format' => 'json',
      'status' => 'Enabled',
      'core',
    ])->pmList();

    $info = array_filter($info, function ($package) {
      return strpos('experimental', strtolower($package['package'])) !== FALSE;
    });

    if (empty($info)) {
      return TRUE;
    }

    $sandbox->setParameter('modules', array_values($info));
    return FALSE;
  }

}

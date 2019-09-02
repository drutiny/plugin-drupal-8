<?php

namespace Drutiny\Plugin\Drupal8\Audit  ;

use Drutiny\Audit;
use Drutiny\Sandbox\Sandbox;
use Drutiny\Annotation\Token;
use Drutiny\Driver\DrushFormatException;

/**
 * Look for modules with available updates.
 * @Token(
 *  name = "updates",
 *  type = "array",
 *  description = "Description of module updates available."
 * )
 */
class ModuleUpdateStatus extends Audit {

  /**
   * @inheritdoc
   */
  public function audit(Sandbox $sandbox) {
    try {
      $output = $sandbox->drush()->pmSecurity('--format=json');
    }
    catch (DrushFormatException $e) {
      if (!$output = json_decode($e->getOutput(), TRUE)) {
        throw $e;
      }
    }

    $sandbox->setParameter('updates', array_values($output));

    if (empty($output)) {
      return TRUE;
    }

    // Just normal updates available.
    return FALSE;
  }

}

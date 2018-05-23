<?php

namespace Drutiny\Plugin\Drupal8\Audit;

use Drutiny\Audit;
use Drutiny\Sandbox\Sandbox;
use Drutiny\Driver\DrushFormatException;
use Drutiny\Annotation\Param;

/**
 * Check a purge plugin exists.
 * @Param(
 *  name = "plugin",
 *  description = "The plugins to check exists",
 *  type = "string"
 * )
 */
class PurgePluginExists extends Audit {

  /**
   * @inheritDoc
   */
  public function audit(Sandbox $sandbox) {
    $plugin_name = $sandbox->getParameter('plugin');

    try {
      $config = $sandbox->drush([
        'format' => 'json',
        'include-overridden' => NULL,
        ])->configGet('purge.plugins');
      $plugins = $config['purgers'];

      foreach ($plugins as $plugin) {
        if ($plugin['plugin_id'] == $plugin_name) {
          return TRUE;
        }
      }
    }
    catch (\Drutiny\Driver\DrushFormatException $e) {
      $sandbox->setParameter('exception', $e->getMessage());
    }

    return FALSE;
  }


}

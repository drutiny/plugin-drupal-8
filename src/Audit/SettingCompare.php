<?php

namespace Drutiny\Plugin\Drupal8\Audit;

use Drutiny\Audit;
use Drutiny\Sandbox\Sandbox;
use Drutiny\Audit\AbstractComparison;

/**
 * Check a configuration is set correctly.
 */
class SettingCompare extends AbstractComparison {

  /**
   * @inheritDoc
   */
  public function audit(Sandbox $sandbox) {
    $key = $sandbox->getParameter('key');
    $value = $sandbox->getParameter('value');

    $settings = $sandbox->drush()->evaluate(function () {
      return \Drupal\Core\Site\Settings::getAll();
    });

    if (!is_array($settings)){
      throw new \Exception("Settings retrieved were not in a known format. Expected Array.");
    }

    $keys = explode('.', $key);

    while ($k = array_shift($keys)) {
      if (!isset($settings[$k])) {
        $sandbox->logger()->info("Could not find '$k' value in $key. No such setting exists.");
        return FALSE;
      }
      $settings = $settings[$k];
    }

    $reading = $settings;

    $sandbox->setParameter('reading', $reading);

    return $this->compare($reading, $value, $sandbox);
  }

}

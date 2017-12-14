<?php

namespace Drutiny\Plugin\Drupal8\Audit;

use Drutiny\Audit;
use Drutiny\Sandbox\Sandbox;
use Drutiny\Driver\DrushFormatException;
use Drutiny\RemediableInterface;
use Drutiny\Audit\AbstractComparison;

/**
 * Check a configuration is set correctly.
 */
class ConfigCheck extends AbstractComparison implements RemediableInterface {

  /**
   * @inheritDoc
   */
  public function audit(Sandbox $sandbox) {
    $collection = $sandbox->getParameter('collection');
    $key = $sandbox->getParameter('key');
    $value = $sandbox->getParameter('value');

    $config = $sandbox->drush([
      'format' => 'json',
      'include-overridden' => NULL,
      ])->configGet($collection, $key);
    $reading = $config[$collection . ':' . $key];

    $sandbox->setParameter('reading', $reading);

    return $this->compare($reading, $value, $sandbox);
  }

  /**
   * @inheritDoc
   */
  public function remediate(Sandbox $sandbox) {
    $collection = $sandbox->getParameter('collection');
    $key = $sandbox->getParameter('key');
    $value = $sandbox->getParameter('value');
    $sandbox->drush()->configSet($collection, $key, $value);
    return $this->check($sandbox);
  }

}

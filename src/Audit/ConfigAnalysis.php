<?php

namespace Drutiny\Plugin\Drupal8\Audit;

use Drutiny\Audit;
use Drutiny\Sandbox\Sandbox;
use Drutiny\Driver\DrushFormatException;
use Drutiny\RemediableInterface;
use Drutiny\Audit\AbstractAnalysis;
use Drutiny\Annotation\Param;
use Drutiny\Annotation\Token;

/**
 * Check a configuration is set correctly.
 * @Param(
 *  name = "collection",
 *  type = "string",
 *  description = "The collection the config belongs to.",
 * )
 * @Param(
 *  name = "expression",
 *  type = "string",
 *  description = "The expression language expression to evaluate.",
 * )
 * @Token(
 *  name = "config",
 *  type = "mixed",
 *  description = "The returned collection config.",
 * )
 */
class ConfigAnalysis extends AbstractAnalysis {

  /**
   * @inheritDoc
   */
  public function gather(Sandbox $sandbox) {
    $collection = $sandbox->getParameter('collection');

    $config = $sandbox->drush([
      'format' => 'json',
      'include-overridden' => NULL,
      ])->configGet($collection);

    $sandbox->setParameter('config', $config);
  }
}

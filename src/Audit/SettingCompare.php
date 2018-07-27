<?php

namespace Drutiny\Plugin\Drupal8\Audit;

use Drutiny\Audit;
use Drutiny\Sandbox\Sandbox;
use Drutiny\Audit\AbstractComparison;
use Drutiny\Annotation\Param;
use Drutiny\Annotation\Token;

/**
 * Check a configuration is set correctly.
 * @Param(
 *   name = "key",
 *   description = "The settings key to evauate",
 *   type = "string"
 * )
 * @Param(
 *   name = "value",
 *   description = "The value of the key you want to compare against.",
 *   type = "string"
 * )
 * @Token(
 *   name = "reading",
 *   description = "The value retrieve from the key in the Drupal site.",
 *   type = "mixed"
 * )
 * @Param(
 *  name = "conditional_expression",
 *  type = "string",
 *  default = "true",
 *  description = "The expression language to evaludate. See https://symfony.com/doc/current/components/expression_language/syntax.html"
 * )
 */
class SettingCompare extends AbstractComparison {
  use ConditionalTrait;

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

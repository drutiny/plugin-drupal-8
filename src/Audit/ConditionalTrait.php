<?php

namespace Drutiny\Plugin\Drupal8\Audit;

use Drutiny\Container;
use Drutiny\Sandbox\Sandbox;
use Drutiny\Annotation\Param;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * @Param(
 *  name = "conditional_expression",
 *  type = "string",
 *  default = "true",
 *  description = "The expression language to evaludate. See https://symfony.com/doc/current/components/expression_language/syntax.html"
 * )
 */
trait ConditionalTrait  {

  protected function requireConditionalEnvironment(Sandbox $sandbox)
  {
    $status = $sandbox->drush(['format' => 'json'])->status();

    $sandbox->setParameter('status', $status);

    $info = $sandbox->drush()->evaluate(function () {
      return [
        'ini_get_all' => ini_get_all(),
        'php_uname' => php_uname(),
        'apache_get_modules' => !function_exists('apache_get_modules') ?: apache_get_modules(),
        'phpversion' => phpversion(),
        'stream_get_wrappers' => stream_get_wrappers(),
        'stream_get_transports' => stream_get_transports(),
        'stream_get_filters' => stream_get_filters(),
        'getenv' => getenv(),
      ];
    });

    $sandbox->setParameter('info', $info);

    $expressionLanguage = new ExpressionLanguage();
    $variables  = $sandbox->getParameterTokens();

    $expression = $sandbox->getParameter('conditional_expression', 'true');
    Container::getLogger()->info(__CLASS__ . ': ' . $expression);
    Container::getLogger()->debug(__CLASS__ . ': ' . print_r($variables, TRUE));
    return $expressionLanguage->evaluate($expression, $variables);
  }
}

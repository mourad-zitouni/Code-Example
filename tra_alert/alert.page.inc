<?php

use Drupal\Core\Render\Element;

/**
 * Prepares variables for alert templates.
 *
 * Default template: alert.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_alert(array &$variables) {

  $alert = $variables['elements']['#alert'];

  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
  $variables['alert'] = $alert;
  $variables['elements']['#cache']['max-age'] = 0;
}

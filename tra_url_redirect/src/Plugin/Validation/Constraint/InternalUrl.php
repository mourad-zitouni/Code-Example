<?php

namespace Drupal\tra_url_redirect\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the submitted value is a internal url.
 *
 * @Constraint(
 *   id = "InternalUrl",
 *   label = @Translation("Internal Url", context = "Validation"),
 *   type = "string"
 * )
 */
class InternalUrl extends Constraint {

  // The message that will be shown if the value is not an internal url.
  public $notInternalUrl = 'This URL is not valid, please use autocomplete.';

}
<?php

namespace Drupal\tra_alert\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks for unpublication date.
 *
 * @Constraint(
 *   id = "UnpublishOn",
 *   label = @Translation("Unpublish on", context = "Validation"),
 *   type = "entity:alert"
 * )
 */
class UnpublishOn extends Constraint {

  public $unpublishOnDate = 'The unpublish on date must be later than the publish on date.';

}
<?php

namespace Drupal\tra_alert\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the UnpublishOn constraint.
 */
class UnpublishOnValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($item, Constraint $constraint) {
    $entity = $item->getEntity();
    $startTime = $entity->publish_on->value;
    $endTime = $entity->unpublish_on->value;

    if (isset($endTime) && $endTime < $startTime) {
      $this->context->addViolation($constraint->unpublishOnDate);
    }

  }

}
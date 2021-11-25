<?php

namespace Drupal\tra_url_redirect\Plugin\Validation\Constraint;

use Drupal\node\Entity\Node;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the InternalUrl constraint.
 */
class InternalUrlValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    foreach ($items as $item) {
      // Check if the value is internal url.
      if (!$this->isInternalUrl($item->getValue()['uri'])) {
        $this->context->addViolation($constraint->notInternalUrl);
      }
    }
  }

  /**
   * Is internal url.
   *
   * @param string $value
   * @return boolean $isInternal
   */
  private function isInternalUrl($value) {
    $isInternal = false;
    $path = explode('/', $value);

    if ((isset($path[0]) && $path[0] == 'entity:node') && is_numeric($path[1])) {
      // Check node exist with given node id.
      $node = Node::load($path[1]);

      if (is_object($node)) {
        $isInternal = true;
      }
    }

    return $isInternal;
  }

}
<?php

namespace Drupal\tra_alert\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\tra_alert\AlertManager;
use Drupal\tra_alert\Entity\AlertInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the PublishedAlerts constraint.
 */
class PublishedAlertsValidator extends ConstraintValidator implements ContainerInjectionInterface {
  /**
   *
   */
  const MAX_PUBLISHED_ITEMS = 4;
  /**
   * @var AlertManager
   */
  protected $alertManager;

  /**
   * PublishedAlertsValidator constructor.
   * @param AlertManager $alert_manager
   */
  public function __construct(AlertManager $alert_manager) {
    $this->alertManager = $alert_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alert.manager')
    );
  }

  /**
   * @param AlertInterface $entity
   * @param Constraint $constraint
   * @throws \Exception
   */
  public function validate($entity, Constraint $constraint) {
    if(!$entity->getPublishedDate()) {
      return;
    }
    $published_items = $entity->getunpublishedDate() ? $this->alertManager->getPublishedAlertBetween(
      $entity->getPublishedDate(TRUE),
      $entity->getunpublishedDate(TRUE)
    ) : $this->alertManager->getAvailableAlertAt($entity->getPublishedDate(TRUE), NULL, FALSE);

    if(!$entity->isNew() && isset($published_items[$entity->id()])) {
      unset($published_items[$entity->id()]);
    }

    if (count($published_items) >= self::MAX_PUBLISHED_ITEMS) {
      $this->context->addViolation($constraint->maxAlertsPublished($published_items));
    }
  }
}
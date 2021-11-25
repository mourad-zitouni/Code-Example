<?php

namespace Drupal\tra_alert\EventSubscriber;

use Drupal\Core\Entity\ContentEntityFormInterface;
use Drupal\hook_event_dispatcher\Event\Form\FormBaseAlterEvent;
use Drupal\tra_alert\Entity\AlertInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class EntityEventSubscriber.
 */
class EntityEventSubscriber implements EventSubscriberInterface {
  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [];
  }
}
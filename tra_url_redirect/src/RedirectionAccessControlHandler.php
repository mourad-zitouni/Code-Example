<?php

namespace Drupal\tra_url_redirect;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Redirection entity.
 *
 * @see \Drupal\tra_url_redirect\Entity\Redirection.
 */
class RedirectionAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\tra_url_redirect\Entity\RedirectionInterface $entity */
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view published redirection entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit redirection entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete redirection entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add redirection entities');
  }

}

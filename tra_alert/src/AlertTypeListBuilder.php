<?php

namespace Drupal\tra_alert;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Class AlertTypeListBuilder
 */
class AlertTypeListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Label');
    $header['description'] = $this->t('Description');
    $header['id'] = $this->t('Machine name');
    $header['weight'] = $this->t('Weight');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\tra_alert\Entity\AlertTypeInterface $entity */
    $row['label'] = $entity->label();
    $row['description'] = $entity->getDescription();
    $row['id'] = $entity->id();
    $row['weight'] = $entity->getWeight();

    return $row + parent::buildRow($entity);
  }
  /**
   * {@inheritdoc}
   */
  protected function getEntityIds() {
    $query = $this->getStorage()->getQuery()
      ->sort('weight');

    if ($this->limit) {
      $query->pager($this->limit);
    }

    return $query->execute();
  }
}
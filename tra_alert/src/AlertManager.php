<?php

namespace Drupal\tra_alert;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\tra_alert\Entity\AlertInterface;
use Drupal\tra_alert\Entity\AlertTypeInterface;
use Psr\Log\LoggerInterface;

/**
 * Defines an alert manager.
 */
class AlertManager {

  /**
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $alertStorage;
  /**
   * AlertManager constructor.
   * @param EntityTypeManagerInterface $entity_type_manager
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->alertStorage = $entity_type_manager->getStorage('alert');
  }

  /**
   * @param \DateTime $date
   * @param null $langcode
   * @param bool $strict
   * @return array
   */
  public function getAvailableAlertAt(\DateTime $date, $langcode = NULL, $strict = TRUE) {

    $query = $this->alertStorage->getQuery('OR');

    $publish_on = $query->andConditionGroup()
      ->notExists('unpublish_on')
      ->condition('publish_on' , 0 , '>');

    $unpublish_on = $query->andConditionGroup()
      ->exists('unpublish_on')
      ->condition('publish_on' , 0 , '>')
      ->condition('unpublish_on', $date->getTimestamp(), '>=');

    if ($strict) {
      $publish_on->condition('publish_on', $date->getTimestamp(), '<=');
      $unpublish_on->condition('publish_on', $date->getTimestamp(), '<=');
    }

    $query->condition($publish_on)
      ->condition($unpublish_on);


    $alerts = $query->execute();
    $alerts = array_map(function ($id) use ($date){
      /** @var AlertInterface $alert */
      $alert = $this->alertStorage->load($id);
      return $alert;
    },$alerts);

    return array_filter($alerts);
  }

  /**
   * @param \DateTime $date_start
   * @param \DateTime|NULL $date_end
   * @param null $langcode
   * @return AlertInterface[]
   */
  public function getPublishedAlertBetween(\DateTime $date_start , \DateTime $date_end, $langcode = NULL) {
   return $this->getAvailableAlertAt($date_start, $langcode) + $this->getAvailableAlertAt($date_end, $langcode);
  }

  /**
   * @param LanguageInterface|null $language
   * @param bool $all
   * @return AlertInterface[]
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getCurrentAlerts(LanguageInterface $language = NULL, $all = FALSE) {

    $query = $this->alertStorage->getQuery()
      ->condition('status', 1)
      ->sort('publish_on')
      ->sort('id');
    $alerts = $query->execute();
    $now = new \DateTime();

    $alerts = array_map(function ($id) use ($language, $now){
      /** @var AlertInterface $alert */
      $alert = $this->alertStorage->load($id);
      if ($language && !$alert->hasTranslation($language->getId())) {
        return NULL;
      }

      $alert = $alert->getTranslation($language->getId());
      return $alert->isPublishedAt($now) ? $alert : NULL;

    }, $alerts);
    $alerts = array_filter($alerts);

    /** @var AlertTypeInterface[] $bundles */
    $bundles = $this->entityTypeManager->getStorage('alert_type')->loadMultiple();
    uasort($alerts, function (AlertInterface $x, AlertInterface $y) use ($bundles){
      $x_bundle = $bundles[$x->bundle()];
      $y_bundle = $bundles[$y->bundle()];
      if($x_bundle->getWeight() == $y_bundle->getWeight()) {
        return 0;
      }
      return $x_bundle->getWeight() < $y_bundle->getWeight() ? -1 : 1;
    });

    return $all ? $alerts : array_slice($alerts, 0, 4);
  }

  /**
   * @param AlertInterface $alert
   * @param string $view_mode
   * @return array
   */
  public function loadRender(AlertInterface $alert, $view_mode = 'default') {
    $view_builder = $this->entityTypeManager->getViewBuilder('alert');
    return $view_builder->view($alert, $view_mode);
  }

  /**
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Exception
   */
  public function publish() {
    $request_time = new \DateTime();

    $query = $this->alertStorage->getQuery()
      ->exists('publish_on')
      ->condition('publish_on', 0, '>')
      ->condition('publish_on', $request_time->getTimestamp(), '<=')
      ->sort('publish_on')
      ->sort('id');
    $alertids = $query->execute();

    /** @var AlertInterface[] $alerts */
    $alerts = $this->entityTypeManager->getStorage('alert')->loadMultiple($alertids);
    foreach ($alerts as $alert) {
      $alert->setPublished();
      $this->alertStorage->save($alert);
    }

  }

  /**
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Exception
   */
  public function unpublish() {
    $request_time =  new \DateTime();

    $query = $this->alertStorage->getQuery()
      ->exists('unpublish_on')
      ->condition('unpublish_on', $request_time->getTimestamp(), '<=')
      ->sort('unpublish_on')
      ->sort('id');
    $alertids = $query->execute();

    /** @var AlertInterface[] $alerts */
    $alerts = $this->entityTypeManager->getStorage('alert')->loadMultiple($alertids);
    foreach ($alerts as $alert) {
      $alert->setUnpublished();
      $this->alertStorage->save($alert);
    }

  }
}
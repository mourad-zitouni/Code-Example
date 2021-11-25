<?php

namespace Drupal\tra_alert\Entity;
use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Alert bundle interface.
 */
interface AlertTypeInterface extends ConfigEntityInterface {
  /**
   * {@inheritdoc}
   */
  public function getDescription();

  /**
   * {@inheritdoc}
   */
  public function setDescription($description);

  /**
   * @return array
   */
  public function getDefaultMessage();

  /**
   * @return string
   */
  public function getDefaultMessageValue();

  /**
   * @return string
   */
  public function getDefaultMessageFormat();

  /**
   * @param array $defaultMessage
   */
  public function setDefaultMessage($defaultMessage);

  /**
   * @return int
   */
  public function getWeight();

  /**
   * @param int $weight
   */
  public function setWeight($weight);
}
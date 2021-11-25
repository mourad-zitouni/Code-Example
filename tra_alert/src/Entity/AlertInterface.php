<?php

namespace Drupal\tra_alert\Entity;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;


/**
 * Interface AlertInterface
 * @package Drupal\tra_alert\Entity
 */
interface AlertInterface extends ContentEntityInterface, EntityChangedInterface, EntityPublishedInterface {
  /**
   *
   */
  const RECENTLY = -1;
  /**
   *
   */
  const CURRENTLY = 0;
  /**
   *
   */
  const UPCOMING = 1;
  /**
   * Gets the Alert name.
   *
   * @return string
   *   Name of the Alert.
   */
  public function getTitle();

  /**
   * Sets the Alert name.
   *
   * @param string $title
   *   The Alert title.
   *
   * @return \Drupal\tra_alert\Entity\AlertInterface
   *   The called Alert entity.
   */
  public function setTitle($title);

  /**
   * @return string
   */
  public function getFrontTitle();

  /**
   * @param string $title
   */
  public function setFrontTitle($title);

  /**
   * Gets the Alert creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Alert.
   */
  public function getCreatedTime();

  /**
   * Sets the Alert creation timestamp.
   *
   * @param int $timestamp
   *   The Alert creation timestamp.
   *
   * @return \Drupal\tra_alert\Entity\AlertInterface
   *   The called Alert entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * @param bool $format
   * @return \DateTime|int|null
   * @throws \Exception
   */
  public function getPublishedDate($format = FALSE);

  /**
   * @param int $time
   * @throws \Drupal\Core\TypedData\Exception\ReadOnlyException
   */
  public function setPublishedDate($time = 0);

  /**
   * @param bool $format
   * @return \DateTime|int|null
   * @throws \Exception
   */
  public function getUnpublishedDate($format = FALSE);

  /**
   * @param int $time
   * @throws \Drupal\Core\TypedData\Exception\ReadOnlyException
   */
  public function setunpublishedDate($time = 0);

  /**
   * @param \DateTime $date
   * @return bool
   * @throws \Exception
   */
  public function isPublishedAt(\DateTime $date);

  /**
   * @param \DateTime|NULL $date
   * @return mixed
   * @throws \Exception
   */
  public function getPublicationStatus(\DateTime $date = NULL);

  /**
   * @param \DateTime $date_start
   * @param \DateTime|NULL $date_end
   * @return bool
   * @throws \Exception
   */
  public function isPublishedBetween(\DateTime $date_start , \DateTime $date_end = NULL);
}
<?php

namespace Drupal\tra_url_redirect\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Url;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Redirection entities.
 *
 * @ingroup tra_url_redirect
 */
interface RedirectionInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Gets the Redirection name.
   *
   * @return string
   *   Name of the Redirection.
   */
  public function getName();

  /**
   * Sets the Redirection name.
   *
   * @param string $name
   *   The Redirection name.
   *
   * @return \Drupal\tra_url_redirect\Entity\RedirectionInterface
   *   The called Redirection entity.
   */
  public function setName($name);

  /**
   * Gets the Redirection creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Redirection.
   */
  public function getCreatedTime();

  /**
   * Sets the Redirection creation timestamp.
   *
   * @param int $timestamp
   *   The Redirection creation timestamp.
   *
   * @return \Drupal\tra_url_redirect\Entity\RedirectionInterface
   *   The called Redirection entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the Redirection source.
   *
   * @param bool $format
   * @return string|Url
   */
  public function getSource($format = FALSE);

  /**
   * Sets the Redirection source.
   *
   * @param string $source
   *
   * @return \Drupal\tra_url_redirect\Entity\RedirectionInterface
   *   The called Redirection entity.
   */
  public function setSource($source);

  /**
   * Gets the Redirection destination.
   *
   * @param bool $format
   * @return string|Url
   */
  public function getDestination($format = FAlSE);

  /**
   * Sets the Redirection destination.
   *
   * @param string $destination
   *
   * @return \Drupal\tra_url_redirect\Entity\RedirectionInterface
   *   The called Redirection entity.
   */
  public function setDestination($destination);

  /**
   * Gets the Redirection type.
   *
   * @return string
   */
  public function getType();

  /**
   * @return boolean
   */
  public function isExternal();
  /**
   * Sets the Redirection type.
   *
   * @param string $type
   *
   * @return \Drupal\tra_url_redirect\Entity\RedirectionInterface
   *   The called Redirection entity.
   */
  public function setType($type);

}

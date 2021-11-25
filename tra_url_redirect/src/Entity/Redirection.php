<?php

namespace Drupal\tra_url_redirect\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Url;
use Drupal\link\LinkItemInterface;
use Drupal\user\UserInterface;

/**
 * Defines the redirection entity.
 *
 * @ingroup redirection
 *
 * @ContentEntityType(
 *   id = "redirection",
 *   label = @Translation("redirection"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\tra_url_redirect\RedirectionListBuilder",
 *
 *     "form" = {
 *       "default" = "Drupal\tra_url_redirect\Form\RedirectionForm",
 *       "add" = "Drupal\tra_url_redirect\Form\RedirectionForm",
 *       "edit" = "Drupal\tra_url_redirect\Form\RedirectionForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "access" = "Drupal\tra_url_redirect\RedirectionAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "transilien_redirection",
 *   admin_permission = "administer redirection entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "created" = "created",
 *     "changed" = "changed",
 *     "uid" = "user_id",
 *     "source" = "source",
 *     "destination_path" = "destination_path",
 *     "type" = "type",
 *   },
 *   links = {
 *     "canonical" = "/admin/content/redirection/view/{redirection}",
 *     "add-form" = "/admin/content/redirection/add",
 *     "edit-form" = "/admin/content/redirection/{redirection}/edit",
 *     "delete-form" = "/admin/content/redirection/{redirection}/delete",
 *     "collection" = "/admin/content/redirection",
 *   }
 * )
 */

class Redirection extends ContentEntityBase implements RedirectionInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSource($format = FALSE) {
    $source = $this->get('source')->first()->getValue();
    return $format ? Url::fromUri($source['uri'], $source['options']) : $source;
  }

  /**
   * {@inheritdoc}
   */
  public function setSource($source) {
    $this->set('source', $source);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getDestination($format = FALSE) {
    $destination = $this->get('destination_path')->first()->getValue();
    return $format ? Url::fromUri($destination['uri'], $destination['options']) : $destination;
  }

  /**
   * {@inheritdoc}
   */
  public function setDestination($destination) {
    $this->set('destination_path', $destination);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->get('type')->value;
  }
  /**
   * {@inheritdoc}
   */
  public function isExternal(){
    return (int)$this->getType() === 1;
  }
  /**
   * {@inheritdoc}
   */
  public function setType($type) {
    $this->set('type', $type);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }


  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = parent::baseFieldDefinitions($entity_type);

    // Standard field, used as unique if primary index.
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Redirection entity.'))
      ->setDisplayOptions('form', [
        'type' => 'number',
        'weight' => 1,
      ])
      ->setReadOnly(TRUE);

    // Standard field, unique outside of the scope of the current project.
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Redirection entity.'))
      ->setReadOnly(TRUE);

    $fields['source'] = BaseFieldDefinition::create('link')
      ->setLabel(t('Source'))
      ->setSettings([
        'link_type' => LinkItemInterface::LINK_INTERNAL,
        'title' => DRUPAL_DISABLED,
      ])
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -6,
      ))
      ->setDisplayOptions('form', [
        'type' => 'link_default',
        'weight' => -3,
      ])
      ->addConstraint('InternalUrl')
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['destination_path'] = BaseFieldDefinition::create('link')
      ->setLabel(t('Destination'))
      ->setSettings([
        'link_type' => LinkItemInterface::LINK_GENERIC,
        'title' => DRUPAL_DISABLED,
      ])
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -2,
      ))
      ->setDisplayOptions('form', [
        'type' => 'link_default',
        'weight' => -2,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['type'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('External'))
      ->setDescription(t('Redirection type : (1 = external, 0 = internal).'))
      ->setDefaultValue(FALSE)
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -1,
      ))
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => TRUE,
        ],
        'weight' => -1,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the Redirection was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the Redirection was last edited.'));

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setRevisionable(FALSE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(FALSE)
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', FALSE);

    return $fields;
  }
}

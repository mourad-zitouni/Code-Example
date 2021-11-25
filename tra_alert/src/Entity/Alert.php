<?php

namespace Drupal\tra_alert\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\link\LinkItemInterface;
use Drupal\user\UserInterface;

/**
 * Defines the alert entity.
 *
 * @ContentEntityType(
 *   id = "alert",
 *   label = @Translation("Alert"),
 *   base_table = "tra_alert",
 *   data_table = "tra_alert_field_data",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "bundle" = "bundle",
 *     "uid" = "uid",
 *     "label" = "title",
 *     "created" = "created",
 *     "changed" = "changed",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   constraints = {
 *     "PublishedAlerts" = {}
 *   },
 *   fieldable = TRUE,
 *   translatable = TRUE,
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\tra_alert\AlertListBuilder",
 *     "access" = "Drupal\tra_alert\AlertEntityAccessControlHandler",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler",
 *     "form" = {
 *       "default" = "Drupal\tra_alert\Form\AlertForm",
 *       "add" = "Drupal\tra_alert\Form\AlertForm",
 *       "edit" = "Drupal\tra_alert\Form\AlertForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   links = {
 *     "canonical" = "/alert/{alert}",
 *     "add-page" = "/alert/add",
 *     "add-form" = "/alert/add/{alert_type}",
 *     "edit-form" = "/alert/{alert}/edit",
 *     "delete-form" = "/alert/{alert}/delete",
 *     "collection" = "/admin/content/alerts",
 *   },
 *   admin_permission = "administer alert types",
 *   bundle_entity_type = "alert_type",
 *   field_ui_base_route = "entity.alert_type.edit_form",
 * )
 */
class Alert extends ContentEntityBase implements AlertInterface {
  use EntityChangedTrait;
  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->get('title')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTitle($name) {
    $this->set('title', $name);
    return $this;
  }

  /**
   * @return string
   */
  public function getFrontTitle() {
    return $this->get('front_title')->value;
  }

  /**
   * @param string $title
   */
  public function setFrontTitle($title) {
    $this->set('front_title', $title);
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
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }
  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\Core\Entity\Exception\UnsupportedEntityTypeDefinitionException
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Alert entity.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Admin Title'))
      ->setTranslatable(TRUE)
      ->setDescription(t('The title displayed on admin page.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['front_title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setTranslatable(TRUE)
      ->setDescription(t('The title displayed in front.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -3,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['image'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Image'))
      ->setTranslatable(TRUE)
      ->setSettings([
        'target_type' => 'media',
        'handler' => 'default',
        'handler_settings' => [
          'target_bundles' => ['image']
        ]
      ])
      ->setDisplayOptions('form', [
        'type'   => 'entity_browser_entity_reference',
        'weight' => 0,
        'settings' =>[
          'entity_browser' => 'media_entity_browser_modal',
          'field_widget_display' => 'rendered_entity',
          'field_widget_edit' => FALSE,
          'field_widget_remove' => TRUE,
          'field_widget_replace' => TRUE,
          'selection_mode' => 'selection_append',
          'open' => FALSE,
          'field_widget_display_settings' => [
            'view_mode' => 'default'
          ]
        ]
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['file'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('File'))
      ->setTranslatable(TRUE)
      ->setSettings([
        'target_type' => 'media',
        'handler' => 'default',
        'handler_settings' => [
          'target_bundles' => ['file', 'remote_file']
        ]
      ])
      ->setDisplayOptions('form', [
        'type'   => 'entity_browser_entity_reference',
        'weight' => 0,
        'settings' =>[
          'entity_browser' => 'media_files_browser',
          'field_widget_display' => 'rendered_entity',
          'field_widget_edit' => FALSE,
          'field_widget_remove' => TRUE,
          'field_widget_replace' => TRUE,
          'selection_mode' => 'selection_append',
          'open' => TRUE,
          'field_widget_display_settings' => [
            'view_mode' => 'default'
          ]
        ]
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['target_url'] = BaseFieldDefinition::create('link')
      ->setLabel(t('Target Url'))
      ->setSettings([
        'link_type' => LinkItemInterface::LINK_GENERIC,
        'title' => DRUPAL_REQUIRED,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'link_default'
      ])
      ->setTranslatable(TRUE);

    $fields['text'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Texte'))
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'text_default',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'text_textfield',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setRequired(TRUE);

    $fields['status']->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => TRUE,
        ],
        'weight' => 50,
      ])
      ->setDefaultValue(FALSE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['publish_on'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Publish on'))
      ->setDisplayOptions('form', [
        'weight' => 30,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setTranslatable(FALSE)
      ->setRequired(TRUE);

    $fields['unpublish_on'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Unpublish on'))
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp_no_default',
        'weight' => 30,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setTranslatable(FALSE)
      ->addConstraint('UnpublishOn');

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the Alert was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the Alert was last edited.'));

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

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'uid' => \Drupal::currentUser()->id(),
      'publish_on' => 0,
    ];
  }

  /**
   * @param $field
   * @param bool $format
   * @return \DateTime|int|null
   * @throws \Exception
   */
  protected function getDateValue($field, $format = FALSE) {
    if (!$this->hasField($field) || !($values = $this->get($field)->getValue())) {
      return NULL;
    }

    $values = reset($values);
    if(!isset($values['value'])) {
      return NULL;
    }

    if(!$format) {
      return (int)$values['value'];
    }

    $formatted = new \DateTime();
    $formatted->setTimestamp($values['value']);
    return $formatted;
  }

  /**
   * @param bool $format
   * @return \DateTime|int|null
   * @throws \Exception
   */
  public function getPublishedDate($format = FALSE) {
    return $this->getDateValue('publish_on', $format);
  }

  /**
   * @param int $time
   * @throws \Drupal\Core\TypedData\Exception\ReadOnlyException
   */
  public function setPublishedDate($time = 0) {
    $this->get('publish_on')->setValue($time);
  }

  /**
   * @param bool $format
   * @return \DateTime|int|null
   * @throws \Exception
   */
  public function getUnpublishedDate($format = FALSE) {
    return $this->getDateValue('unpublish_on', $format);
  }

  /**
   * @param int $time
   * @throws \Drupal\Core\TypedData\Exception\ReadOnlyException
   */
  public function setUnpublishedDate($time = 0) {
    $this->get('unpublish_on')->setValue($time);
  }
  /**
   * @param \DateTime $date
   * @return bool
   * @throws \Exception
   */
  public function isPublishedAt(\DateTime $date) {
    $timestamp = $date->getTimestamp();
    $start = $this->getPublishedDate();
    $end = $this->getUnpublishedDate();

    if(!$start) {
      return FALSE;
    }
    return $end ? ($start <= $timestamp && $end >= $timestamp) : ($start <= $timestamp);
  }

  /**
   * @param \DateTime|NULL $date
   * @return mixed
   * @throws \Exception
   */
  public function getPublicationStatus(\DateTime $date = NULL) {
    if (!$date) {
      $date = new \DateTime();
    }
    $timestamp = $date->getTimestamp();
    $start = $this->getPublishedDate();
    $end = $this->getUnpublishedDate();

    if(!$start) {
      return self::RECENTLY;
    }

    if (!$end) {
      return $start <= $timestamp ? self::CURRENTLY : self::UPCOMING;
    }
    else {
      return $timestamp < $start ? self::UPCOMING : ($timestamp <= $end ? self::CURRENTLY : self::RECENTLY);
    }

  }

  /**
   * @param \DateTime $date_start
   * @param \DateTime|NULL $date_end
   * @return bool
   * @throws \Exception
   */
  public function isPublishedBetween(\DateTime $date_start , \DateTime $date_end = NULL) {
    if($date_end) {
      return $this->isPublishedAt($date_start) || $this->isPublishedAt($date_end);
    }
    return $this->getPublicationStatus($date_start) !== self::RECENTLY;
  }


}
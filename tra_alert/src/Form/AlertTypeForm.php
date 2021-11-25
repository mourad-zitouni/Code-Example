<?php

namespace Drupal\tra_alert\Form;

use Drupal\Core\Entity\BundleEntityFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\tra_alert\Entity\AlertTypeInterface;

class AlertTypeForm extends BundleEntityFormBase {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    /** @var AlertTypeInterface $entity_type */
    $entity_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $entity_type->label(),
      '#description' => $this->t("Label for the %content_entity_id entity type (bundle).", ['%content_entity_id' => 'Alert']),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $entity_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\tra_alert\Entity\AlertType::load',
      ],
      '#disabled' => !$entity_type->isNew(),
    ];

    $form['description'] = [
      '#title' => $this->t('Description'),
      '#type' => 'textarea',
      '#default_value' => $entity_type->getDescription(),
      '#description' => $this->t('This text will be displayed on the "Add %content_entity_id" page.', ['%content_entity_id' => 'Alert']),
    ];

    $form['defaultMessage'] = [
      '#title' => $this->t('Default message'),
      '#type' => 'text_format',
      '#default_value' => $entity_type->getDefaultMessageValue(),
      '#format' => $entity_type->getDefaultMessageFormat(),
    ];

    $form['weight'] = [
      '#title' => $this->t('Weight'),
      '#type' => 'number',
      '#default_value' => $entity_type->getWeight()
    ];

    return $this->protectBundleIdElement($form);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {

    $entity_type = $this->entity;
    $status = $entity_type->save();
    $message_params = [
      '%label' => $entity_type->label(),
      '%content_entity_id' => 'Alert',
    ];

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label %content_entity_id entity type.', $message_params));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label %content_entity_id entity type.', $message_params));
    }

    $form_state->setRedirectUrl($entity_type->toUrl('collection'));
  }
}
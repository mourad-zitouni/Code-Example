<?php

namespace Drupal\tra_alert\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\tra_alert\Entity\AlertInterface;
use Drupal\tra_alert\Entity\AlertTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for AlertForm edit forms.
 *
 * @ingroup tra_alert
 */
class AlertForm extends ContentEntityForm {

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * RedirectionForm constructor.
   * @param MessengerInterface $messenger
   * @param EntityRepositoryInterface $entity_repository
   * @param EntityTypeBundleInfoInterface $entity_type_bundle_info
   * @param TimeInterface $time
   */
  public function __construct(
    MessengerInterface $messenger,
    EntityRepositoryInterface $entity_repository,
    EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL,
    TimeInterface $time = NULL)
  {
    $this->messenger = $messenger;
    parent::__construct($entity_repository, $entity_type_bundle_info, $time);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger'),
      $container->get('entity.repository'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time')
    );
  }

  /**
   * {@inheritdoc}
   * @throws \Exception
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $form['#after_build'][] = [AlertForm::class, 'afterBuildForm'];

    /** @var AlertInterface $alert */
    $alert = $this->entity;

    /** @var AlertTypeInterface $type */
    $type = $this->entityTypeManager
      ->getStorage('alert_type')
      ->load($alert->bundle());

    $schedule_state = 1;
    if($alert->isNew()) {
      $form['text']['widget'][0]['#default_value'] = $type->getDefaultMessageValue();
      $form['text']['widget'][0]['#format'] = $type->getDefaultMessageFormat();
      $schedule_state = 0;
    }

    foreach (['publish_on', 'unpublish_on'] as $field) {
      $form[$field] += [
        '#states' => [
          'invisible' => [
            ':input[name="schedule_state"]' => ['checked' => FALSE],
          ]
        ]
      ];
    }

    if(!$alert->getPublishedDate()) {
      $schedule_state = 0;
      $now = new \DateTime();
      $form['publish_on']['widget'][0]['value']['#default_value'] = new DrupalDateTime($now->format('Y-m-d'));
    }

    $form['schedule_state'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Schedule publication'),
      '#return_value' => 1,
      '#default_value' => $schedule_state,
    ];

    return $form;
  }

  /**
   * @param array $element
   * @param FormStateInterface $form_state
   * @return array
   */
  public static function afterBuildForm(array $element, FormStateInterface $form_state) {
    if (isset($element['group_publication'])) {
      $element['schedule_state']['#weight'] = isset($element['group_publication']['#weight']) ? (int)$element['group_publication']['#weight'] - 1 : 10;

      $element['group_publication'] +=[
        '#states' => [
          'invisible' => [
            ':input[name="schedule_state"]' => ['checked' => FALSE],
          ]
        ]
      ];
    }
    return $element;
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\Core\TypedData\Exception\ReadOnlyException
   */
  public function buildEntity(array $form, FormStateInterface $form_state) {
    /** @var AlertInterface $entity */
    $entity = parent::buildEntity($form, $form_state);

    if(!$form_state->getValue('schedule_state', 0)) {
      $entity->setPublishedDate(0);
      $entity->setunpublishedDate(NULL);
    }
    return $entity;
  }

  /**
   * {@inheritdoc}
   * @throws \Exception
   */
  public function save(array $form, FormStateInterface $form_state) {

    /** @var AlertInterface $entity */
    $entity = &$this->entity;
    $entity->setPublished(FALSE);

    if ($entity->getPublicationStatus() === AlertInterface::CURRENTLY && $form_state->getValue('schedule_state', 0)) {
      $entity->setPublished(TRUE);
    }

    $status = $entity->save();
    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addStatus($this->t('Created the %label Alert.', [
          '%label' => $entity->label(),
        ]));
        break;
      default:
        $this->messenger()->addStatus($this->t('Saved the %label Alert.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.alert.collection');
  }
}
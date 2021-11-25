<?php

namespace Drupal\tra_url_redirect\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\tra_url_redirect\Entity\RedirectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Component\Utility\UrlHelper;

/**
 * Form controller for Redirection edit forms.
 *
 * @ingroup tra_url_redirect
 */
class RedirectionForm extends ContentEntityForm {

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The Database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * RedirectionForm constructor.
   * @param MessengerInterface $messenger
   * @param EntityRepositoryInterface $entity_repository
   * @param EntityTypeBundleInfoInterface $entity_type_bundle_info
   * @param TimeInterface $time
   * @param Connection $database
   */
  public function __construct(
    MessengerInterface $messenger,
    Connection $database,
    EntityRepositoryInterface $entity_repository,
    EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL,
    TimeInterface $time = NULL) {
    $this->messenger = $messenger;
    $this->database = $database;
    parent::__construct($entity_repository, $entity_type_bundle_info, $time);
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger'),
      $container->get('database'),
      $container->get('entity.repository'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = &$this->entity;

    $status = parent::save($form, $form_state);
    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addStatus($this->t('Created the %id Redirection.', [
          '%id' => $entity->id(),
        ])); break;
      default:
        $this->messenger()->addStatus($this->t('Saved the %id Redirection.', [
          '%id' => $entity->id(),
        ]));
    }
    $form_state->setRedirect('entity.redirection.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity RedirectionInterface */
    $form = parent::buildForm($form, $form_state);

    $front_url = Settings::get('front_url', '');
    $target_bundles = ['target_bundles' => ['editorial' => 'editorial', 'hub' => 'hub']];

    $form['source']['widget'][0]['uri']['#selection_handler'] = "default:node";
    $form['source']['widget'][0]['uri']['#selection_settings'] = $target_bundles;
    $form['source']['widget'][0]['uri']['#field_prefix'] = $front_url;
    $source_description = new TranslatableMarkup('Use autocomplete to choose an editorial content.');
    $form['source']['widget'][0]['uri']['#description'] = $source_description;

    $form['destination_path']['widget'][0]['uri']['#selection_handler'] = "default:node";
    $form['destination_path']['widget'][0]['uri']['#selection_settings'] = $target_bundles;
    $destination_description = new TranslatableMarkup('Use autocomplete to choose an editorial content or http://www.example.com for an external url.');
    $form['destination_path']['widget'][0]['uri']['#description'] = $destination_description;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $id = !empty($form_state->getValue('id')[0]['value']) ? $form_state->getValue('id')[0]['value'] : null;
    $source = $form_state->getValue('source')[0]['uri'];
    $destination = $form_state->getValue('destination_path')[0]['uri'];
    $type = $form_state->getValue('type')['value'];

    if ($source == $destination) {
      $form_state->setErrorByName('destination_path', t('The destination url is the same as the source.'));
    }

    // Check if source exists.
    if($this->getPathFromSources($source, $id)) {
      $form_state->setErrorByName('source', t('The source url already exists.'));
    }

    // Validate external urls.
    if (($type == 1 && !UrlHelper::isValid($destination, true)) || ($type == 0 && !$this->validateInternal($destination))) {
      $form_state->clearErrors();
      $form_state->setErrorByName('destination_path', t('The destination url is not valid.'));
    }

    // Check for infinite redirection.
    if (!$this->infiniteRedirectionCheck($source, $destination)) {
      $form_state->setErrorByName('destination_path', t('Be careful, this will make an infinite redirection.'));
    }
  }

  /**
   * Validate internal destination url.
   *
   */
  private function validateInternal($path) {
    $url = parse_url($path);
    $scheme = $url['scheme'];

    if (!in_array($scheme, ['entity', 'internal'])) {
      return false;
    }

    return ($scheme == 'internal') ? preg_match('/^\/[a-z0-9.\/]*/', $url['path']) : true;
  }

  /**
   * @param string $path
   * @param integer $id
   * @return array|boolean $result
   */
  private function getPathFromSources($path, $id = null) {
    $query = $this->database->select('transilien_redirection', 'R')
      ->fields('R')
      ->condition('source__uri', $path,'=');

    // Add id check on redirection edit.
    if (!empty($id)) {
      $query->condition('id', $id,'!=');
    }
    $result = $query->execute()->fetchAssoc();

    return !empty($result) ? $result : false;
  }

  /**
   * @param $source
   * @param $destination
   * @return bool
   */
  private function infiniteRedirectionCheck($source, $destination) {
    $result = $this->database->select('transilien_redirection', 'R')
      ->fields('R')
      ->condition('destination_path__uri', $source,'=')
      ->execute()
      ->fetchAssoc();

    if (!$result) {
      return true;
    }

    // Source found should not be the same as destination entered.
    if ($result['source__uri'] == $destination) {
      return false;
    }

    // Source found should not be a destination for entered destination.
    return $this->infiniteRedirectionCheck($result['source__uri'], $destination);

  }
}
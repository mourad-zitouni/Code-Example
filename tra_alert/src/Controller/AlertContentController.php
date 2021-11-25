<?php

namespace Drupal\tra_alert\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\tra_alert\Entity\AlertTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Class AlertContentController
 * @package Drupal\tra_alert\Controller
 */
class AlertContentController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  /**
   * @var DateFormatterInterface
   */
  protected $dateFormatter;
  /**
   * @var RendererInterface
   */
  protected $renderer;

  /**
   * AlertContentController constructor.
   * @param EntityTypeManagerInterface $entity_type_manager
   * @param DateFormatterInterface $date_formatter
   * @param RendererInterface $renderer
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, DateFormatterInterface $date_formatter, RendererInterface $renderer)   {
    $this->entityTypeManager = $entity_type_manager;
    $this->dateFormatter = $date_formatter;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('date.formatter'),
      $container->get('renderer')
    );
  }

  /**
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function addPage() {
    $build = [
      '#theme' => 'alert_add_list',
      '#cache' => [
        'tags' => $this->entityTypeManager->getDefinition('alert_type')->getListCacheTags(),
      ],
    ];

    $content = [];
    $storage = $this->entityTypeManager->getStorage('alert_type');
    $query = $storage->getQuery()->sort('weight');

    // Only use alert types the user has access to.
    foreach ($query->execute() as $id) {
      $type = $storage->load($id);
      $access = $this->entityTypeManager->getAccessControlHandler('alert')
        ->createAccess($type->id(), NULL, [], TRUE);

      if ($access->isAllowed()) {
        $content[$type->id()] = $type;
      }

      $this->renderer->addCacheableDependency($build, $access);
    }

    // Bypass the alert/add listing if only one content type is available.
    if (count($content) === 1) {
      $type = array_shift($content);
      return $this->redirect('alert.add', ['alert_type' => $type->id()]);
    }

    $build['#content'] = $content;

    return $build;
  }

  /**
   * @param AlertTypeInterface $alert_type
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   */
  public function addPageTitle(AlertTypeInterface $alert_type) {
    return $this->t('Create alert : @name', ['@name' => $alert_type->label()]);
  }
}
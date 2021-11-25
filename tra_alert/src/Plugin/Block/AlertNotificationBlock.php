<?php

namespace Drupal\tra_alert\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\MainContentBlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\tra_alert\AlertManager;
use Drupal\tra_alert\Entity\AlertInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'AlertNotificationBlock' block.
 *
 * @Block(
 *  id = "alert_notification_block",
 *  admin_label = @Translation("Alert notification block"),
 * )
 */
class AlertNotificationBlock extends BlockBase implements ContainerFactoryPluginInterface, MainContentBlockPluginInterface {
  /**
   * @var array
   */
  protected $mainContent;

  /**
   * @var LanguageManagerInterface
   */
  protected $languageManager;
  /**
   * @var AlertManager
   */
  protected $alertManager;
  /**
   * AlertNotificationBlock constructor.
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param LanguageManagerInterface $language_manager
   * @param AlertManager $alert_manager
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    LanguageManagerInterface $language_manager,
    AlertManager $alert_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->languageManager = $language_manager;
    $this->alertManager = $alert_manager;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('language_manager'),
      $container->get('alert.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $alerts = $this->alertManager->getCurrentAlerts($this->languageManager->getCurrentLanguage());
    return [
      '#theme' => 'block_alerts',
      '#attached' => [
        'library' => ['tra_alert/alert.block']
      ],
      '#items' => array_map(function (AlertInterface $alert) {
        return [
          'title' => $alert->getFrontTitle(),
          'type' => $alert->bundle(),
          'id' => $alert->id(),
          'target' => sprintf('panel-alert-%s', $alert->id()),
          'content' => $this->alertManager->loadRender($alert)
        ];
      }, $alerts),
      '#cache' => [
        'max-age' => 0,
        'tags' => ['alert:list']
      ]
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['label_display']['#access'] = FALSE;
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function setMainContent(array $main_content) {
    $this->mainContent = $main_content;
  }
}

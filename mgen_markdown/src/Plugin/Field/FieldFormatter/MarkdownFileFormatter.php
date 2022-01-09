<?php

/**
 * @file
 * Contains \Drupal\mgen_markdown\Plugin\field\formatter\MarkdownFileFormatter.
 */

namespace Drupal\mgen_markdown\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use League\CommonMark\CommonMarkConverter;

/**
 * Plugin implementation of the 'markdown_file' formatter.
 *
 * @FieldFormatter(
 *   id = "markdown_file",
 *   label = @Translation("Markdown File"),
 *   field_types = {
 *     "entity_reference",
 *      "file"
 *   }
 * )
 */
class MarkdownFileFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * MarkdownFileFormatter constructor.
   * @param $plugin_id
   * @param $plugin_definition
   * @param FieldDefinitionInterface $field_definition
   * @param array $settings
   * @param $label
   * @param $view_mode
   * @param array $third_party_settings
   * @param EntityTypeManagerInterface $entity_type_manager
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    $label,
    $view_mode,
    array $third_party_settings,
    EntityTypeManagerInterface $entity_type_manager
  ) {

    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
	 * {@inheritdoc}
	 */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
		return new static(
			$plugin_id,
			$plugin_definition,
			$configuration['field_definition'],
			$configuration['settings'],
			$configuration['label'],
			$configuration['view_mode'],
			$configuration['third_party_settings'],
			$container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $converter = new CommonMarkConverter([
      'html_input' => 'strip',
      'allow_unsafe_links' => false,
    ]);

    foreach ($items as $delta => $item) {
      $file_id = $item->getValue()['target_id'];
      /** @var FileInterface $file */
      $file = $this->entityTypeManager->getStorage('file')->load($file_id);
      $output = '';
      $markdown = file_get_contents($file->getFileUri());
      $output = $converter->convertToHtml($markdown);

      $elements[$delta] = [
        '#markup' => $output
      ];
    }
    return $elements;
  }
}

<?php
namespace Drupal\tra_alert\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityDescriptionInterface;
/**
 * Alert Type
 *
 * @ConfigEntityType(
 *   id = "alert_type",
 *   label = @Translation("Alert Type"),
 *   bundle_of = "alert",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *   },
 *   config_prefix = "alert_type",
 *   config_export = {
 *     "uuid",
 *     "id",
 *     "label",
 *     "description",
 *     "defaultMessage",
 *     "weight"
 *   },
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\tra_alert\AlertTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\tra_alert\Form\AlertTypeForm",
 *       "add" = "Drupal\tra_alert\Form\AlertTypeForm",
 *       "edit" = "Drupal\tra_alert\Form\AlertTypeForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer alert types",
 *   links = {
 *     "canonical" = "/admin/structure/alert_type/{alert_type}",
 *     "add-form" = "/admin/structure/alert_type/add",
 *     "edit-form" = "/admin/structure/alert_type/{alert_type}/edit",
 *     "delete-form" = "/admin/structure/alert_type/{alert_type}/delete",
 *     "collection" = "/admin/structure/alert_type",
 *   }
 * )
 */
class AlertType extends ConfigEntityBundleBase implements AlertTypeInterface {
  /**
   * The Alert type ID.
   *
   * @var string
   */
  protected $id;
  /**
   * The Alert type label.
   *
   * @var string
   */
  protected $label;
  /**
   * A brief description of alert type.
   *
   * @var string
   */
  protected $description;
  /**
   * @var array
   */
  protected $defaultMessage = [
    'value' => '',
    'format' => 'full_html'
  ];
  /**
   * @var integer
   */
  protected $weight = 0;

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($description) {
    $this->description = $description;
  }

  /**
   * @return array
   */
  public function getDefaultMessage() {
    return $this->defaultMessage;
  }
  /**
   * @return string
   */
  public function getDefaultMessageValue() {
    return isset($this->defaultMessage['value']) ? $this->defaultMessage['value'] : '';
  }  /**
   * @return string
   */
  public function getDefaultMessageFormat() {
    return isset($this->defaultMessage['format']) ? $this->defaultMessage['format'] : 'full_html';
  }

  /**
   * @param array $defaultMessage
   */
  public function setDefaultMessage($defaultMessage) {
    $this->defaultMessage = $defaultMessage;
  }

  /**
   * @return int
   */
  public function getWeight() {
    return $this->weight;
  }

  /**
   * @param int $weight
   */
  public function setWeight($weight) {
    $this->weight = $weight;
  }

  
}
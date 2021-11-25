<?php

namespace Drupal\tra_alert\Plugin\Validation\Constraint;

use Drupal\tra_alert\Entity\AlertInterface;
use Symfony\Component\Validator\Constraint;

/**
 * Checks that we don't publish more than max alerts.
 *
 * @Constraint(
 *   id = "PublishedAlerts",
 *   label = @Translation("Published Alerts", context = "Validation"),
 *   type = "timestamp"
 * )
 */
class PublishedAlerts extends Constraint {

  // The message that will be shown if there is already max alerts published.
  public $maxAlertsPublished = 'You have already reached the max published alerts.';

  /**
   * @param AlertInterface[] $alerts
   * @return string
   * @throws \Exception
   */
  public function maxAlertsPublished ($alerts) {
    $messages[] = t($this->maxAlertsPublished);

    foreach ($alerts as $id => $alert) {
      $dates = [
        $alert->getPublishedDate() ? $alert->getPublishedDate(TRUE)->format('d/m/Y H:i') : '',
        $alert->getunpublishedDate() ? $alert->getunpublishedDate(TRUE)->format('d/m/Y H:i') : ''
      ];

      $messages[] = t('<b><a target="_blank" href="@url">@title</a> available on @date</b> is also published at this date range.', [
        '@title' => $alert->getTitle(),
        '@url' => $alert->toUrl('edit-form')->toString(),
        '@type' => $alert->bundle(),
        '@date' => join(' - ', array_filter($dates))
      ]);
    }
    return join('<br>', $messages);
  }

}
<?php

namespace Drupal\tra_drush_commands\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drush\Commands\DrushCommands;
use Drupal\Core\Site\Settings;

class TraCustomCommands extends DrushCommands {

  const DOMAIN_PATTERN = '#^(http|https):\/\/(?<name>.*\D)(?<version>[0-9]){0,1}\.tri(\.socrate)?\.vsct\.fr$#';
  const PROD_URL = '/^https:\/\/transilien.secure.force.com\/FormContact/ix';

  /**
   *
   */
  const DOMAIN_NAME = [
    'dev' => 'develop',
    'rel' => 'assemblage',
    'rec' => 'recette',
    'int' => 'integration',
    'prep' => 'preprod',
  ];

  const IFRAME_URL = 'https://recette-transilien.cs189.force.com/FormContact';


  protected $database;
  protected $passwordHasher;
  protected $settings;
  protected $environment;

  /**
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  public function __construct($database, $passwordHasher, Settings $settings, EntityTypeManagerInterface $entity_type_manager) {
    $this->database = $database;
    $this->passwordHasher = $passwordHasher;
    $this->settings = $settings;
    $this->entityTypeManager = $entity_type_manager;

    $base_url = $this->settings->get('front_url', FALSE) ? $this->settings->get('front_url') : $this->settings->get('base_url', FALSE);
    if(preg_match(static::DOMAIN_PATTERN, $base_url, $matches)) {
      $this->environment = $matches['name'] === 'dev' ? 'dev' : array_search($matches['name'], static::DOMAIN_NAME);
    }
  }

  /**
   * Drush command to sanitizes passwords.
   *
   * @command tra_commands:sanitize
   * @aliases tra-san
   * @usage tra_commands:sanitize
   */
  public function sanitize() {
    $message = 'Environment not suitable for password sanitization.';
    if ($this->environment !== false) {
      $users = $this->database->select('users_field_data', 'u')->fields('u', ['uid', 'name'])->condition('u.uid', 0, '>')->execute()->fetchAll();

      foreach ($users as $user) {
        $password = sprintf('%s@%s', strtolower(str_replace(' ', '', $user->name)), static::DOMAIN_NAME[$this->environment]);
        $hash = $this->passwordHasher->hash($password);

        $query = $this->database->update('users_field_data')
          ->fields(['pass' => $hash])
          ->condition('uid', $user->uid, '=')
          ->execute();
      }
      $message = 'User passwords sanitized.';
    }

    $this->output()->writeln($message);
    $this->updateFormContactUrl();
    $this->output()->writeln('Form contact urls updated.');
  }

  /**
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function updateFormContactUrl() {
    $paragraphs = $this->entityTypeManager->getStorage('paragraph')->loadByProperties(['type' => 'iframe']);
    foreach ($paragraphs as $paragraph) {
      if (!$paragraph->hasField('field_iframe_url'))
        continue;

      if (!$paragraph->get('field_iframe_url')->isEmpty()) {
        $url = $paragraph->get('field_iframe_url')->getValue()[0]['value'];
        if (preg_match(self::PROD_URL, $url)) {
          $paragraph->set('field_iframe_url', self::IFRAME_URL);
          $paragraph->save();
        }
      }
    }
  }

}
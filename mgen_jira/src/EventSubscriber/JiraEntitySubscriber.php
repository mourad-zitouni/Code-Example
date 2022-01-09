<?php

namespace Drupal\mgen_jira\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityViewEvent;
use Drupal\jira_rest\JiraRestWrapperService;
use Drupal\user\UserInterface;
use JiraRestApi\Issue\IssueFieldV3;

/**
 * JiraEntitySubscriber class.
 */
class JiraEntitySubscriber implements EventSubscriberInterface {

  /**
   * Jira Rest API Wrapper.
   *
   * @var \Drupal\jira_rest\JiraRestWrapperService
   */
  protected $jiraRestWrapperService;

  /**
   * JiraEntitySubscriber constructor.
   * @param JiraRestWrapperService $jiraRestWrapperService
   */
  public function __construct(JiraRestWrapperService $jiraRestWrapperService) {
    $this->jiraRestWrapperService = $jiraRestWrapperService;

  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      HookEventDispatcherInterface::ENTITY_INSERT => 'entityInsert',
    ];
  }

  public function entityInsert(EntityInsertEvent $event) {
    $entity = $event->getEntity();

    if (!$entity instanceof UserInterface) {
      return;
    }

    if ($entity->hasRole('developer')) {
      $userName = $entity->name->value;

      $description = sprintf('Permissions to be added to the new user : %s.', $userName);
      $issue = $this->addIssue($description);
      if ($issue) {
        \Drupal::logger('mgen_jira')->notice(t('Issue created for user @user', ['@user' => $userName]));
      }
      else {
        \Drupal::logger('mgen_jira')->error(t('There was a problem creating issue for user @username', ['@user' => $userName]));
      }
    }
  }

  public function addIssue(string $description) {
    $issueField = new IssueFieldV3();
    // @todo: add project key to config.

    $issueField->setProjectKey("MP")
      ->setSummary("New user added")
      ->setIssueType("Story")
      //->setAssigneeAccountId('61b8b1c9599f18006a3b4589')
      ->addDescriptionParagraph($description);

    $issue = $this->jiraRestWrapperService->getIssueService()->create($issueField);
    if (!empty($issue->id)) {
      return $issue;
    }
    return false;
    
  }
}
<?php

namespace Drupal\mgen_jira\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\jira_rest\JiraRestWrapperService;
use JiraRestApi\JiraException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use JiraRestApi\Configuration\ArrayConfiguration;
use JiraRestApi\Issue\IssueFieldV3;
use JiraRestApi\Project\ProjectService;
use Http\Message\Authentication\BasicAuth;
use Apigee\Edge\Client;
use Apigee\Edge\Api\Management\Controller\ApiProductController;
use Drupal\apigee_edge\OauthCredentials;
use Drupal\apigee_edge\OauthTokenStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * JiraRestController class.
 */
class JiraRestController extends ControllerBase {
  /**
   * Jira Rest API Wrapper.
   *
   * @var \Drupal\jira_rest\JiraRestWrapperService
   */
  protected $jiraRestWrapperService;

  /**
   * The OAuth token storage.
   *
   * @var \Drupal\apigee_edge\OauthTokenStorageInterface
   */
  private $oauthTokenStorage;

  /**
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Class constructor.
   */
  public function __construct(JiraRestWrapperService $jira_rest_wrapper_service, OauthTokenStorageInterface $oauth_token_storage, 
  EntityTypeManagerInterface $entity_type_manager) {
    $this->jiraRestWrapperService = $jira_rest_wrapper_service;
    $this->oauthTokenStorage = $oauth_token_storage;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('jira_rest_wrapper_service'),
      $container->get('apigee_edge.authentication.oauth_token_storage'),
      $container->get('entity_type.manager')
    );
  }

  public function add() {
    $key = \Drupal::service('key.repository')->getKey('apigee_trial_key');
    //$credentials = new OauthCredentials($key);
    //dump($key->getKeyType()->getAuthenticationType($key)); die;
    /** @var \Drupal\apigee_edge\SDKConnectorInterface $sdk_connector */
    $sdk_connector = \Drupal::service('apigee_edge.sdk_connector');
    $this->cleanUpOauthTokenData();
    $sdk_connector->testConnection($key);
    $client = $sdk_connector->getClient();
    //dump($client);
    //$apiProducts = $this->entityTypeManager->getStorage('api_product')->loadMultiple();
    //dump($apiProducts);


    $api_product = 'prodtest';
    $query_params = [
      'query' =>'list',
      'entity' => 'apps',
    ];
    $endpoint = $client->getUriFactory()
      ->createUri("/organizations/mgenportalnew/apiproducts/{$api_product}")
      ->withQuery(http_build_query($query_params));
    try {
      $response = $client->get("https://apigee.googleapis.com/v1/organizations/mgenportalnew/apiproducts/{$api_product}");
    }
    catch (\Exception $exception) {
      // Handle exception.
    }
    
    

    $results = json_decode((string) $response->getBody());
    //dump($results);
    
    $this->cleanUpOauthTokenData();

    return [
      '#markup' => $results->displayName,
    ];
  }

  /**
   * Removes Oauth token data.
   */
  private function cleanUpOauthTokenData(): void {
    if ($this->oauthTokenStorage instanceof OauthTokenFileStorage) {
      $this->oauthTokenStorage->removeTokenFile();
    }
    else {
      $this->oauthTokenStorage->removeToken();
    }
  }

  public function getIssue() {
    $issueService = $this->jiraRestWrapperService->getIssueService();

    try {

      $queryParam = [
        'fields' => [  // default: '*all'
          'summary',
          'description',
          'comment'
        ],
        'expand' => [
          'renderedFields',
          'names',
          'schema',
          'transitions',
          'operations',
          'editmeta',
          'changelog',
        ]
      ];

      $issue = $issueService->get('MP-11', $queryParam);
      dump($issue->fields);

      foreach ($issue->fields as $field) {
        //$field->;
      }
    } catch (JiraException $e) {
      print("Error Occured! " . $e->getMessage());
    }

    return [
      '#markup' => 'hhhhh',
    ];
  }

  public function showProjects() {
    $config = new ArrayConfiguration(
      array(
        'jiraHost' => 'https://mouradzitouni.atlassian.net',
        // for basic authorization:
        'jiraUser' => 'mourad.zitouni.pro@gmail.com',
        'jiraPassword' => 'K4WPdtYPCqp0CnYSsucQC597'
      )
    );
    try {
      $proj = new ProjectService($config);

      $prjs = $proj->getAllProjects();
      dump($prjs);

      /*foreach ($prjs as $p) {
                echo sprintf("Project Key:%s, Id:%s, Name:%s, projectCategory: %s\n",
                    $p->key, $p->id, $p->name, $p->projectCategory['name']
                );			
            }*/
    } catch (JiraException $e) {
      print("Error Occured! " . $e->getMessage());
    }
  }
}

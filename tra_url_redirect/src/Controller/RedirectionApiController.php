<?php

namespace Drupal\tra_url_redirect\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\node\NodeInterface;
use Drupal\node\NodeStorageInterface;
use Drupal\tra_url_redirect\Entity\RedirectionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Utility\Xss;

/**
 * Class RedirectionApiController
 * @package Drupal\tra_url_redirect\Controller
 */
class RedirectionApiController extends ControllerBase {


  /**
   * @var ContentEntityStorageInterface
   */
  protected $redirectStorage;
  /**
   * @var NodeStorageInterface
   */
  protected $nodeStorage;


  /**
   * RedirectionApiController constructor.
   * @param ContentEntityStorageInterface $redirect_storage
   * @param NodeStorageInterface $node_storage
   */
  public function __construct(ContentEntityStorageInterface $redirect_storage, NodeStorageInterface $node_storage) {
    $this->redirectStorage = $redirect_storage;
    $this->nodeStorage = $node_storage;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')->getStorage('redirection'),
      $container->get('entity_type.manager')->getStorage('node')
    );
  }

  /**
   * @param int|string $id
   * @return JsonResponse
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function list($id = null) {

    $response = new JsonResponse();

    /** @var RedirectionInterface[] $redirects */
    $redirects  = $this->redirectStorage->loadMultiple($id ? [Xss::filter($id)] :  NULL);

    if (empty($redirects)) {
      $response->setStatusCode(404);
      $response->setData([
        'error' => new TranslatableMarkup('The resource requested was not found')
      ]);

      return $response;
    }

    $results = [];

    foreach ($redirects as $redirect){

      $source_params =  $redirect->getSource(TRUE)->isExternal() || !$redirect->getSource(TRUE)->isRouted() ? [] : $redirect->getSource(TRUE)->getRouteParameters();
      /** @var NodeInterface|null $source */
      if(!isset($source_params['node']) || !($source = $this->nodeStorage->load($source_params['node']))){
        continue;
      }

      if ($redirect->isExternal()){
        foreach ($source->getTranslationLanguages() as $language){
          $current_source = $source->getTranslation($language->getId());
          $results[] = [
            'pathid' => sprintf('%s_%s', $redirect->id(), strtoupper($language->getId())),
            'source' => $current_source->toUrl('canonical', ['absolute' => FALSE])->toString(),
            'destination' => $redirect->getDestination(TRUE)->toString(),
            'type' => 'external'
          ];
        }
      }
      else {
        $destination_params =  $redirect->getDestination(TRUE)->isExternal() || !$redirect->getDestination(TRUE)->isRouted() ? [] : $redirect->getDestination(TRUE)->getRouteParameters();
        /** @var NodeInterface|null $destination */
        if(!isset($destination_params['node']) || !($destination = $this->nodeStorage->load($destination_params['node']))){
          if ($redirect->getDestination(TRUE)->isExternal() || $redirect->getDestination(TRUE)->isRouted()){
            continue;
          }
          $destination = $redirect->getDestination(TRUE);
        }

        foreach ($source->getTranslationLanguages() as $language){
          if($destination instanceof NodeInterface && !$destination->hasTranslation($language->getId())){
            continue;
          }

          $current_destination =  $destination instanceof NodeInterface  ? $destination->getTranslation($language->getId())->toUrl('canonical', ['absolute' => FALSE]) : $destination;

          $current_source = $source->getTranslation($language->getId());
          $results[] = [
            'pathid' => sprintf('%s_%s', $redirect->id(), strtoupper($language->getId())),
            'source' => $current_source->toUrl('canonical', ['absolute' => FALSE])->toString(),
            'destination' => $current_destination->toString(),
            'type' => 'internal'
          ];
        }
      }
    }

    $response->setData(['redirections' => $results]);
    return $response;
  }

}
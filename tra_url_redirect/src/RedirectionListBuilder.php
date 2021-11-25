<?php

namespace Drupal\tra_url_redirect;

use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\node\NodeInterface;
use Drupal\node\NodeStorageInterface;
use Drupal\tra_url_redirect\Entity\RedirectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * EntityListBuilderInterface implementation responsible for the Redirection entities. */
class RedirectionListBuilder extends EntityListBuilder {
  /**
   * @var NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * RedirectionListBuilder constructor.
   * @param EntityTypeInterface $entity_type
   * @param EntityStorageInterface $storage
   * @param NodeStorageInterface $node_storage
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, NodeStorageInterface $node_storage) {
    $this->nodeStorage = $node_storage;
    parent::__construct($entity_type, $storage);
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('entity_type.manager')->getStorage('node')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    $build['table']['#attributes']['class'][] = 'redirects-list';
    $build['#attached']['library'][] = 'tra_url_redirect/admin';
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['type'] = $this->t('Type');
    $header['redirects'] = [
      'data' => [
        'source' => [
          '#markup' => $this->t('Source'),
          '#prefix' => '<div>',
          '#suffix' => '</div>'

        ],
        'destination' => [
          '#markup' => $this->t('Destination'),
          '#prefix' => '<div>',
          '#suffix' => '</div>'
        ],
        '#prefix' => '<div class="redirects-head">',
        '#suffix' => '</div>'

      ] ,
      'class' => 'redirects'
    ];
    return $header + parent::buildHeader();
  }

  /**
   * Builds a row for an entity in the entity listing.
   *
   * @param \Drupal\Core\Entity\EntityInterface|RedirectionInterface $entity
   *   The entity for this row of the list.
   *
   * @return array
   *   A render array structure of fields for this entity.
   *
   * @see \Drupal\Core\Entity\EntityListBuilder::render()
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function buildRow(EntityInterface $entity) {

    $redirects = [];
    $source_params =  $entity->getSource(TRUE)->isExternal() || !$entity->getSource(TRUE)->isRouted() ? [] : $entity->getSource(TRUE)->getRouteParameters();
    $destination_params =  $entity->getDestination(TRUE)->isExternal() || !$entity->getDestination(TRUE)->isRouted() ? [] : $entity->getDestination(TRUE)->getRouteParameters();

    /** @var NodeInterface|null $source */
    if(isset($source_params['node']) && ($source = $this->nodeStorage->load($source_params['node']))){
      if ($entity->isExternal()){
        foreach ($source->getTranslationLanguages() as $language){
          $redirects[] = [
            [
              'data' => $source->getTranslation($language->getId())->toUrl('canonical', ['absolute' => FALSE])->toString(),
              'class' => 'redirect-source'
            ],
            [
              'data' => $entity->getDestination(TRUE)->toString(),
              'class' => 'redirect-destination'
            ]
          ];
        }
      }
      /** @var NodeInterface|null $destination */
      elseif(isset($destination_params['node'])){
        $destination = $this->nodeStorage->load($destination_params['node']);
        foreach ($source->getTranslationLanguages() as $language){
          $destination_url = '-- NONE --';
          if($destination && $destination->hasTranslation($language->getId())){
            $destination_url = $destination->getTranslation($language->getId())->toUrl('canonical', ['absolute' => FALSE])->toString();
          }
          $redirects[] = [
            [
              'data' => $source->getTranslation($language->getId())->toUrl('canonical', ['absolute' => FALSE])->toString(),
              'class' => 'redirect-source'
            ],
            [
              'data' => $destination_url,
              'class' => 'redirect-destination'
            ]

          ];
        }
      }
      elseif (!$entity->getDestination(TRUE)->isExternal() && !$entity->getDestination(TRUE)->isRouted()) {
        foreach ($source->getTranslationLanguages() as $language){
          $redirects[] = [
            [
              'data' => $source->getTranslation($language->getId())->toUrl('canonical', ['absolute' => FALSE])->toString(),
              'class' => 'redirect-source'
            ],
            [
              'data' => $entity->getDestination(TRUE)->toString(),
              'class' => 'redirect-destination'
            ]

          ];
        }
      }

    }

    $row['id'] = [
      'data' => $entity->id(),
      'class' => 'id'
    ];
    $row['type'] = $entity->isExternal() ? t('External') : t('Internal');
    $row['redirects'] = [
      'data' => [
        '#theme' => 'table',
        '#attributes' => ['class' => 'redirects-data'],
        '#rows' => $redirects
      ],
      'class' => 'redirects'
    ];

    return $row + parent::buildRow($entity);
  }

}
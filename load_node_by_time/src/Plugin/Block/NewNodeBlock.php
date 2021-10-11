<?php

namespace Drupal\load_node_by_time\Plugin\Block;

use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;


/**
 * Display new materials
 *
 * @Block(
 *   id = "load_node_by_time",
 *   admin_label = @Translation("New nodes"),
 * )
 */
class NewNodeBlock extends BlockBase implements ContainerFactoryPluginInterface
{
  protected $_entityTypeManager;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $_entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->_entityTypeManager = $_entityTypeManager;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $result = [];
    $storage = $this->_entityTypeManager->getStorage('node');

    $query = $storage->getQuery()
      ->condition('status',1)
      ->condition('created', strtotime('-2hour'), '>=')
      ->sort('created',"DESC")
      ->range(0,5)
      ->execute();
    $nodes = $storage->loadMultiple($query);

    foreach ($nodes as $node){
      $result[] = [
        'id' => $node->id(),
        'title' => $node->getTitle(),
        'Created Date' =>$node->getCreatedTime()
      ];
    }

    return [
      '#theme' => 'block_new_nodes',
      '#nodes' => $result,
    ];
  }

  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), ['node_list']);
  }

}

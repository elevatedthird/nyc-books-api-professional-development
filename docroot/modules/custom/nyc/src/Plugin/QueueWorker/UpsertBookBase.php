<?php

namespace Drupal\nyc\Plugin\QueueWorker;

use Drupal;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\nyc\NYCClient;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides base functionality for the Upsert Queue Workers.
 */
/**
 * Retrieve and save job on cron.
 */
abstract class UpsertBookBase extends QueueWorkerBase implements ContainerFactoryPluginInterface {
  /**
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_storage
   * @throws Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(EntityTypeManagerInterface  $entity_storage) {
    $this->nodeStorage = $entity_storage->getStorage('node');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  // Function to process Queue Items : Upsert Queue

  public function processItem($data) {
    $nycClient = new NYCClient();
    $listname = key($data);
    $nid = array_values($data);
    $nid = reset($nid);
    $listname = $nycClient->getNameEncoded($listname);
    $final_data = $nycClient->getCompleteListData($listname);
    if($nid == NULL) {
      return $this->nodeCreate($final_data,$listname);
    }
    else {
      return $this->nodeUpdate($listname, $nid);
    }
  }

  // Function to Create the List Book nodes

  public function nodeCreate($final_data,$listname){
    $nycClient = new NYCClient();
    $book = $nycClient->getBookField($final_data,$listname);
    $node = [
      'type' => 'list',
      'uid' => 1,
      'field_publish_date' => $final_data[$listname]['publish_date'],
      'title' => $final_data[$listname]['list_name'],
      'status' => 1,
      'field_books' => $book,
    ];
    try {
      $node = Drupal::entityTypeManager()->getStorage('node')->create($node);
    } catch (Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException $e) {
    } catch (Drupal\Component\Plugin\Exception\PluginNotFoundException $e) {
    }
    if (!empty($node)) {
      try {
        $node->save();
      }
      catch (Drupal\Core\Entity\EntityStorageException $e) {
      }
    }
    return $node;
  }
// Function to Update the List book nodes

  public function nodeUpdate($listname,$nid){
    $node = Node::load($nid);
    $nycClient = new NYCClient();
    $final_data = $nycClient->getCompleteListData($listname);
    $book = $nycClient->getBookField($final_data,$listname);
    if (empty($node)) {
      $node = [
        'type' => 'list',
        'uid' => 1,
        'field_publish_date' => $final_data[$listname]['publish_date'],
        'title' => $final_data[$listname]['list_name'],
        'status' => 1,
        'field_books' => $book,
      ];
      try {
        $node = Drupal::entityTypeManager()->getStorage('node')->create($node);
      } catch (Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException $e) {
      } catch (Drupal\Component\Plugin\Exception\PluginNotFoundException $e) {
      }
      if (!empty($node)) {
        try {
          $node->save();
        } catch (Drupal\Core\Entity\EntityStorageException $e) {
        }
      }
      return $node;
    }
  }
}

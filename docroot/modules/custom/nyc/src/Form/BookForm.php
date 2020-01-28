<?php

namespace Drupal\nyc\Form;


use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\nyc\NYCClient;

/**
 * Class BookForm.
 */
class BookForm extends FormBase {

  /**
   * Drupal\nyc\NYCClient definition.
   *
   * @var \Drupal\nyc\NYCClient
   */
  protected $nyc_client;

  /**
   * Constructs a new NYCClient object.
   * @param NYCClient $nyc_client
   */
  public function __construct(
    NYCClient $nyc_client
  ) {
    $nycclient =[];
    $this->nyc_client = $nycclient;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('nyc.client')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'book_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {


    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $apiClient = new  NYCClient();
    $request = $apiClient->getList();
    $results = $request['results'];
    foreach($results as $r) {
      $list_names[] = $r['list_name'];
    }
    foreach ($list_names as $list) {
      try {
        $node = \Drupal::entityTypeManager()
          ->getStorage('node')
          ->loadByProperties(['title' => $list, 'type' => 'list']);
      } catch (InvalidPluginDefinitionException $e) {
      } catch (PluginNotFoundException $e) {
      }
      $node = reset($node);
      //Code to update or create job opening on cron run
      $queue = \Drupal::queue('nyc_upsert_book_manual');
      if ($node == false) {
        $queue->createItem([$list => NULL]);
      } else {
        $queue->createItem([$list => $node->id()]);
      }
    }
  }
}

<?php

declare(strict_types=1);

namespace Drupal\custom_content_tools\Plugin\Block;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides a latest content items block.
 */
#[Block(
  id: 'latest_content_items',
  admin_label: new TranslatableMarkup('Latest Content Items'),
  category: new TranslatableMarkup('Content Items'),
)]
final class LatestContentItemsBlock extends BlockBase implements ContainerFactoryPluginInterface
{

  /**
   * Constructs the plugin instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self
  {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array
  {
    return [
      'count' => 5,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state): array
  {
    $form['count'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of items to show'),
      '#default_value' => $this->configuration['count'],
      '#min' => 1,
      '#max' => 50,
      '#required' => TRUE,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state): void
  {
    $this->configuration['count'] = $form_state->getValue('count');
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array
  {
    $query = $this->entityTypeManager
      ->getStorage('content_item')
      ->getQuery()
      ->condition('status', 1)
      ->sort('created', 'DESC')
      ->range(0, $this->configuration['count']);

    $ids = $query->execute();

    if (empty($ids)) {
      return ['#markup' => $this->t('No content items found.')];
    }

    // Load entities.
    $entities = $this->entityTypeManager
      ->getStorage('content_item')
      ->loadMultiple($ids);

    $items = [];
    foreach ($entities as $entity) {
      $items[] = [
        '#theme' => 'item_list__latest_content_items',
        '#items' => [
          [
            'title' => $entity->label(),
            'date' => \Drupal::service('date.formatter')->format($entity->get('created')->value, 'short'),
          ],
        ],
      ];
    }
    return [
      '#theme' => 'latest_content_items_block',
      '#items' => $items,
      '#cache' => [
        'tags' => ['node_list', 'content_item_list', !empty($entities) ? $entities[0]->getCacheTags() : ''],
        'contexts' => ['url.path', 'user.roles'],
        'max-age' => 3600, // 1 hour
      ],
    ];
  }
}

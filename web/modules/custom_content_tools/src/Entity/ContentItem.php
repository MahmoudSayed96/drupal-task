<?php

declare(strict_types=1);

namespace Drupal\custom_content_tools\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\views\EntityViewsData;
use Drupal\Core\Entity\Form\DeleteMultipleForm;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\custom_content_tools\ContentItemListBuilder;
use Drupal\custom_content_tools\Form\ContentItemForm;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EditorialContentEntityBase;
use Drupal\Core\Entity\Form\RevisionDeleteForm;
use Drupal\Core\Entity\Form\RevisionRevertForm;
use Drupal\Core\Entity\Routing\RevisionHtmlRouteProvider;
use Drupal\custom_content_tools\ContentItemInterface;
use Drupal\user\EntityOwnerTrait;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;
use Drupal\custom_content_tools\ContentItemAccessControlHandler;

/**
 * Defines the content item entity class.
 */
#[ContentEntityType(
  id: 'content_item',
  label: new TranslatableMarkup('Content Item'),
  label_collection: new TranslatableMarkup('Content Items'),
  label_singular: new TranslatableMarkup('content item'),
  label_plural: new TranslatableMarkup('content items'),
  entity_keys: [
    'id' => 'id',
    'revision' => 'revision_id',
    'langcode' => 'langcode',
    'label' => 'label',
    'owner' => 'uid',
    'published' => 'status',
    'uuid' => 'uuid',
  ],
  handlers: [
    'list_builder' => ContentItemListBuilder::class,
    'views_data' => EntityViewsData::class,
    'access' => ContentItemAccessControlHandler::class,
    'form' => [
      'add' => ContentItemForm::class,
      'edit' => ContentItemForm::class,
      'delete' => ContentEntityDeleteForm::class,
      'delete-multiple-confirm' => DeleteMultipleForm::class,
      'revision-delete' => RevisionDeleteForm::class,
      'revision-revert' => RevisionRevertForm::class,
    ],
    'route_provider' => [
      'html' => AdminHtmlRouteProvider::class,
      'revision' => RevisionHtmlRouteProvider::class,
    ],
  ],
  links: [
    'collection' => '/admin/content/content-item',
    'add-form' => '/content-item/add',
    'canonical' => '/content-item/{content_item}',
    'edit-form' => '/content-item/{content_item}/edit',
    'delete-form' => '/content-item/{content_item}/delete',
    'delete-multiple-form' => '/admin/content/content-item/delete-multiple',
    'revision' => '/content-item/{content_item}/revision/{content_item_revision}/view',
    'revision-delete-form' => '/content-item/{content_item}/revision/{content_item_revision}/delete',
    'revision-revert-form' => '/content-item/{content_item}/revision/{content_item_revision}/revert',
    'version-history' => '/content-item/{content_item}/revisions',
  ],
  admin_permission: 'administer content_item',
  base_table: 'content_item',
  data_table: 'content_item_field_data',
  revision_table: 'content_item_revision',
  revision_data_table: 'content_item_field_revision',
  translatable: TRUE,
  show_revision_ui: TRUE,
  label_count: [
    'singular' => '@count content items',
    'plural' => '@count content items',
  ],
  field_ui_base_route: 'entity.content_item.settings',
  revision_metadata_keys: [
    'revision_user' => 'revision_uid',
    'revision_created' => 'revision_timestamp',
    'revision_log_message' => 'revision_log',
  ],
)]
class ContentItem extends EditorialContentEntityBase implements ContentItemInterface
{

  use EntityChangedTrait;
  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage): void
  {
    parent::preSave($storage);
    if (!$this->getOwnerId()) {
      // If no owner has been set explicitly, make the anonymous user the owner.
      $this->setOwnerId(0);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array
  {

    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['label'] = BaseFieldDefinition::create('string')
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setLabel(t('Title'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setRevisionable(TRUE)
      ->setLabel(t('Status'))
      ->setDefaultValue(TRUE)
      ->setSetting('on_label', 'Enabled')
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => FALSE,
        ],
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'boolean',
        'label' => 'above',
        'weight' => 0,
        'settings' => [
          'format' => 'enabled-disabled',
        ],
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['summary'] = BaseFieldDefinition::create('text_long')
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setLabel(t('Summary'))
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'text_default',
        'label' => 'above',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setLabel(t('Author'))
      ->setSetting('target_type', 'user')
      ->setDefaultValueCallback(self::class . '::getDefaultEntityOwner')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'placeholder' => '',
        ],
        'weight' => 15,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'author',
        'weight' => 15,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Publish Date'))
      ->setTranslatable(TRUE)
      ->setDescription(t('The time that the content item was created.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setTranslatable(TRUE)
      ->setDescription(t('The time that the content item was last edited.'));

    // Featured image
    $fields['featured_image'] = BaseFieldDefinition::create('image')
      ->setLabel(t('Featured Image'))
      ->setRequired(FALSE)
      ->setSettings([
        'file_extensions' => 'png jpg jpeg',
        'max_filesize' => '2 MB',
        'alt_field' => TRUE,
        'alt_field_required' => FALSE,
        'title_field' => FALSE,
      ])
      ->setDisplayOptions('form', [
        'type' => 'image_image',
        'weight' => 10,
      ])
      ->setDisplayOptions('view', [
        'type' => 'image',
        'weight' => 10,
        'settings' => [
          'image_style' => 'thumbnail',
          'image_link' => 'content',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);


    return $fields;
  }
}
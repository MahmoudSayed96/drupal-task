<?php

declare(strict_types=1);

namespace Drupal\custom_content_tools;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the content item entity type.
 *
 * phpcs:disable Drupal.Arrays.Array.LongLineDeclaration
 *
 * @see https://www.drupal.org/project/coder/issues/3185082
 */
final class ContentItemAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account): AccessResult {
    if ($account->hasPermission($this->entityType->getAdminPermission())) {
      return AccessResult::allowed()->cachePerPermissions();
    }

    return match($operation) {
      'view' => AccessResult::allowedIfHasPermission($account, 'view content_item'),
      'update' => AccessResult::allowedIfHasPermission($account, 'edit content_item'),
      'delete' => AccessResult::allowedIfHasPermission($account, 'delete content_item'),
      'delete revision' => AccessResult::allowedIfHasPermission($account, 'delete content_item revision'),
      'view all revisions', 'view revision' => AccessResult::allowedIfHasPermissions($account, ['view content_item revision', 'view content_item']),
      'revert' => AccessResult::allowedIfHasPermissions($account, ['revert content_item revision', 'edit content_item']),
      default => AccessResult::neutral(),
    };
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL): AccessResult {
    return AccessResult::allowedIfHasPermissions($account, ['create content_item', 'administer content_item'], 'OR');
  }

}

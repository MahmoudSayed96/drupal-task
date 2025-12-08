<?php

declare(strict_types=1);

namespace Drupal\custom_content_tools;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a content item entity type.
 */
interface ContentItemInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}

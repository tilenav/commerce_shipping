<?php

namespace Drupal\commerce_shipping;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;

/**
 * Provides routes for the ShippingMethod entity.
 */
class ShippingMethodRouteProvider extends AdminHtmlRouteProvider {

  /**
   * {@inheritdoc}
   */
  protected function getCanonicalRoute(EntityTypeInterface $entity_type) {
    // Shipping methods use the edit-form route as the canonical route.
    // @todo Remove this when #2479377 gets fixed.
    return $this->getEditFormRoute($entity_type);
  }

}

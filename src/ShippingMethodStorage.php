<?php

namespace Drupal\commerce_shipping;

use Drupal\commerce\CommerceContentEntityStorage;
use Drupal\commerce_shipping\Entity\ShipmentInterface;

/**
 * Defines the shipping method storage.
 */
class ShippingMethodStorage extends CommerceContentEntityStorage implements ShippingMethodStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function loadMultipleForShipment(ShipmentInterface $shipment) {
    $query = $this->getQuery();
    $query
      ->condition('status', TRUE)
      ->sort('weight', 'ASC');
    $result = $query->execute();

    return $result ? $this->loadMultiple($result) : [];
  }

}

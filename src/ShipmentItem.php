<?php

namespace Drupal\commerce_shipping;

/**
 * Represents a shipment item.
 */
final class ShipmentItem {

  /**
   * The purchased entity ID.
   *
   * @var string
   */
  protected $purchasedEntityId;

  /**
   * The purchased entity type ID.
   *
   * @var string
   */
  protected $purchasedEntityTypeId;

  /**
   * The quantity.
   *
   * @var float
   */
  protected $quantity;

  /**
   * Constructs a new ShipmentItem object.
   *
   * @param array $definition
   *   The definition.
   */
  public function __construct(array $definition) {
    foreach (['purchased_entity_id', 'purchased_entity_type', 'quantity'] as $required_property) {
      if (empty($definition[$required_property])) {
        throw new \InvalidArgumentException(sprintf('Missing required property %s.', $required_property));
      }
    }

    $this->purchasedEntityId = $definition['purchased_entity_id'];
    $this->purchasedEntityTypeId = $definition['purchased_entity_type'];
    $this->quantity = $definition['quantity'];
  }

  /**
   * Gets the purchased entity ID.
   *
   * @return string
   *   The purchased entity ID.
   */
  public function getPurchasedEntityId() {
    return $this->purchasedEntityId;
  }

  /**
   * Gets the purchased entity type ID.
   *
   * @return string
   *   The purchased entity type ID.
   */
  public function getPurchasedEntityTypeId() {
    return $this->purchasedEntityTypeId;
  }

  /**
   * Gets the quantity.
   *
   * @return float
   *   The quantity.
   */
  public function getQuantity() {
    return $this->quantity;
  }

}

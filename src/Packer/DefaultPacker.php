<?php

namespace Drupal\commerce_shipping\Packer;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_shipping\ProposedShipment;
use Drupal\commerce_shipping\ShipmentItem;
use Drupal\profile\Entity\ProfileInterface;

/**
 * Creates a single shipment per order.
 */
class DefaultPacker implements PackerInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(OrderInterface $order, ProfileInterface $shipping_profile) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function pack(OrderInterface $order, ProfileInterface $shipping_profile) {
    $items = [];
    foreach ($order->getItems() as $order_item) {
      $purchased_entity = $order_item->getPurchasedEntity();
      if (!$purchased_entity) {
        // The purchased entity holds its weight and dimensions.
        // Without that, it's not possible to ship an order item.
        continue;
      }

      // @todo Skip entities that don't have the shippable trait.
      $items[] = new ShipmentItem([
        'purchased_entity_id' => $purchased_entity->id(),
        'purchased_entity_type' => $purchased_entity->getEntityTypeId(),
        'quantity' => $order_item->getQuantity(),
        'order_item_id' => $order_item->id(),
      ]);
    }

    $proposed_shipments = [];
    if (!empty($items)) {
      $proposed_shipments[] = new ProposedShipment([
        'order_id' => $order->id(),
        'shipping_profile_id' => $shipping_profile->id(),
        'items' => $items,
      ]);
    }

    return $proposed_shipments;
  }

}

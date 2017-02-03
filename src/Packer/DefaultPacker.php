<?php

namespace Drupal\commerce_shipping\Packer;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_shipping\ProposedShipment;
use Drupal\commerce_shipping\ShipmentItem;
use Drupal\physical\Weight;
use Drupal\physical\WeightUnit;
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
      // Ship only shippable purchasable entity types.
      if (!$purchased_entity || !$purchased_entity->hasField('weight')) {
        continue;
      }
      // The weight will be empty if the shippable trait was added but the
      // existing entities were not updated.
      if ($purchased_entity->get('weight')->isEmpty()) {
        $purchased_entity->set('weight', new Weight(0, WeightUnit::GRAM));
      }

      $quantity = $order_item->getQuantity();
      /** @var \Drupal\physical\Weight $weight */
      $weight = $purchased_entity->get('weight')->first()->toMeasurement();
      $items[] = new ShipmentItem([
        'order_item_id' => $order_item->id(),
        'label' => $order_item->getTitle(),
        'quantity' => $quantity,
        'weight' => $weight->multiply($quantity),
        'declared_value' => $order_item->getUnitPrice()->multiply($quantity),
      ]);
    }

    $proposed_shipments = [];
    if (!empty($items)) {
      $proposed_shipments[] = new ProposedShipment([
        'order_id' => $order->id(),
        'items' => $items,
        'shipping_profile_id' => $shipping_profile->id(),
      ]);
    }

    return $proposed_shipments;
  }

}

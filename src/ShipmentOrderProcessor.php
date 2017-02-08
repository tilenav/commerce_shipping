<?php

namespace Drupal\commerce_shipping;

use Drupal\commerce_order\Adjustment;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\OrderProcessorInterface;

/**
 * Processes the order's shipments.
 */
class ShipmentOrderProcessor implements OrderProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function process(OrderInterface $order) {
    if (!$order->hasField('shipments') || $order->get('shipments')->isEmpty()) {
      return;
    }

    /** @var \Drupal\commerce_shipping\Entity\ShipmentInterface[] $shipments */
    $shipments = $order->get('shipments')->referencedEntities();
    $single_shipment = count($shipments) === 1;
    foreach ($shipments as $shipment) {
      $order->addAdjustment(new Adjustment([
        'type' => 'shipping',
        'label' => $single_shipment ? t('Shipping') : $shipment->getTitle(),
        'amount' => $shipment->getAmount(),
        'source_id' => $shipment->id(),
      ]));
    }
  }

}

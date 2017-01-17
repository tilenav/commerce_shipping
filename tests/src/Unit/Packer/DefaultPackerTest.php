<?php

namespace Drupal\Tests\commerce_shipping\Unit;

use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\Entity\OrderItemInterface;
use Drupal\commerce_shipping\Packer\PackerInterface;
use Drupal\commerce_shipping\Packer\DefaultPacker;
use Drupal\commerce_shipping\ProposedShipment;
use Drupal\commerce_shipping\ShipmentItem;
use Drupal\profile\Entity\ProfileInterface;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\commerce_shipping\Packer\DefaultPacker
 * @group commerce_shipping
 */
class DefaultPackerTest extends UnitTestCase {

  /**
   * The default packer.
   *
   * @var \Drupal\commerce_shipping\Packer\DefaultPacker
   */
  protected $packer;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->packer = new DefaultPacker();
  }

  /**
   * ::covers pack.
   */
  public function testPack() {
    $order_items = [];
    $first_order_item = $this->prophesize(OrderItemInterface::class);
    $first_order_item->id()->willReturn(2001);
    $first_order_item->getPurchasedEntity()->willReturn(NULL);
    $first_order_item->getQuantity()->willReturn(1);
    $order_items[] = $first_order_item->reveal(1);

    $purchased_entity = $this->prophesize(PurchasableEntityInterface::class);
    $purchased_entity->id()->willReturn(3001);
    $purchased_entity->getEntityTypeId()->willReturn('commerce_product_variation');
    $purchased_entity = $purchased_entity->reveal();
    $second_order_item = $this->prophesize(OrderItemInterface::class);
    $second_order_item->id()->willReturn(2002);
    $second_order_item->getPurchasedEntity()->willReturn($purchased_entity);
    $second_order_item->getQuantity()->willReturn(3);
    $order_items[] = $second_order_item->reveal();

    $order = $this->prophesize(OrderInterface::class);
    $order->id()->willReturn(2);
    $order->getItems()->willReturn($order_items);
    $order = $order->reveal();
    $shipping_profile = $this->prophesize(ProfileInterface::class);
    $shipping_profile->id()->willReturn(3);
    $shipping_profile = $shipping_profile->reveal();

    $expected_proposed_shipment = new ProposedShipment([
      'order_id' => 2,
      'shipping_profile_id' => 3,
      'items' => [
        new ShipmentItem([
          'purchased_entity_id' => 3001,
          'purchased_entity_type' => 'commerce_product_variation',
          'quantity' => 3,
        ]),
      ],
    ]);
    $result = $this->packer->pack($order, $shipping_profile);
    $this->assertEquals([$expected_proposed_shipment], $result);
  }

}

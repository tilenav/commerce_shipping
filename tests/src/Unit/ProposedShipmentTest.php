<?php

namespace Drupal\Tests\commerce_shipping\Unit;

use Drupal\commerce_shipping\ProposedShipment;
use Drupal\commerce_shipping\ShipmentItem;
use Drupal\physical\Weight;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\commerce_shipping\ProposedShipment
 * @group commerce_shipping
 */
class ProposedShipmentTest extends UnitTestCase {

  /**
   * The proposed shipment.
   *
   * @var \Drupal\commerce_shipping\ProposedShipment
   */
  protected $proposedShipment;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->proposedShipment = new ProposedShipment([
      'order_id' => 10,
      'shipping_profile_id' => 11,
      'items' => [
        new ShipmentItem([
          'purchased_entity_id' => 2,
          'purchased_entity_type' => 'commerce_product_variation',
          'quantity' => 1,
          'weight' => new Weight('10', 'kg'),
          'order_item_id' => 10,
        ]),
      ],
      'package_type_id' => 'default',
      'custom_fields' => [
        'field_test' => 'value',
      ],
    ]);
  }

  /**
   * @covers ::getOrderId
   */
  public function testGetOrderId() {
    $this->assertEquals(10, $this->proposedShipment->getOrderId());
  }

  /**
   * @covers ::getShippingProfileId
   */
  public function testGetShippingProfileId() {
    $this->assertEquals(11, $this->proposedShipment->getShippingProfileId());
  }

  /**
   * @covers ::getItems
   */
  public function testGetItems() {
    $expected_items = [];
    $expected_items[] = new ShipmentItem([
      'purchased_entity_id' => 2,
      'purchased_entity_type' => 'commerce_product_variation',
      'quantity' => 1,
      'weight' => new Weight('10', 'kg'),
      'order_item_id' => 10,
    ]);
    $items = $this->proposedShipment->getItems();
    $this->assertArrayEquals($expected_items, $items);
  }

  /**
   * @covers ::getPackageTypeId
   */
  public function testGetPackageTypeId() {
    $this->assertEquals('default', $this->proposedShipment->getPackageTypeId());
  }

  /**
   * @covers ::getCustomFields
   */
  public function testGetCustomFields() {
    $this->assertEquals(['field_test' => 'value'], $this->proposedShipment->getCustomFields());
  }

  /**
   * @covers ::__construct
   */
  public function testMissingProperties() {
    $this->setExpectedException(\InvalidArgumentException::class, 'Missing required property "items".');
    $proposed_shipment = new ProposedShipment([
      'order_id' => 10,
      'shipping_profile_id' => 11,
      'package_type_id' => 'default',
    ]);
  }

  /**
   * @covers ::__construct
   */
  public function testInvalidItems() {
    $this->setExpectedException(\InvalidArgumentException::class, 'Each shipment item under the "items" property must be an instance of ShipmentItem.');
    $proposed_shipment = new ProposedShipment([
      'order_id' => 10,
      'shipping_profile_id' => 11,
      'items' => ['invalid'],
      'package_type_id' => 'default',
    ]);
  }

}

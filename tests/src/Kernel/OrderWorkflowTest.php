<?php

namespace Drupal\Tests\commerce_shipping\Kernel;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderType;
use Drupal\commerce_price\Price;
use Drupal\commerce_shipping\Entity\Shipment;
use Drupal\commerce_shipping\ShipmentItem;
use Drupal\physical\Weight;
use Drupal\Tests\commerce\Kernel\CommerceKernelTestBase;

/**
 * Tests the interaction between order and shipping workflows.
 *
 * @group commerce_shipping
 */
class OrderWorkflowTest extends CommerceKernelTestBase {

  /**
   * A sample order.
   *
   * @var \Drupal\commerce_order\Entity\OrderInterface
   */
  protected $order;

  /**
   * A sample shipment.
   *
   * @var \Drupal\commerce_shipping\Entity\ShipmentInterface
   */
  protected $shipment;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'entity_reference_revisions',
    'physical',
    'profile',
    'state_machine',
    'commerce_order',
    'commerce_shipping',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('profile');
    $this->installEntitySchema('commerce_order');
    $this->installEntitySchema('commerce_shipment');
    $this->installConfig([
      'profile',
      'commerce_order',
      'commerce_shipping',
    ]);

    $user = $this->createUser(['mail' => $this->randomString() . '@example.com']);

    /** @var \Drupal\commerce_order\Entity\OrderTypeInterface $order_type */
    $order_type = OrderType::load('default');
    $order_type->setThirdPartySetting('commerce_shipping', 'shipment_type', 'default');
    $order_type->save();

    // Create the order field.
    $field_definition = commerce_shipping_build_shipment_field_definition($order_type->id());
    \Drupal::service('commerce.configurable_field_manager')->createField($field_definition);

    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = Order::create([
      'type' => $order_type->id(),
      'state' => 'draft',
      'mail' => $user->getEmail(),
      'uid' => $user->id(),
      'store_id' => $this->store->id(),
    ]);
    $order->save();
    $this->order = $this->reloadEntity($order);

    $shipment = Shipment::create([
      'type' => 'default',
      'order_id' => $this->order->id(),
      'items' => [
        new ShipmentItem([
          'order_item_id' => 10,
          'title' => 'T-shirt (red, large)',
          'quantity' => 2,
          'weight' => new Weight('40', 'kg'),
          'declared_value' => new Price('30', 'USD'),
        ]),
      ],
      'amount' => new Price('5', 'USD'),
      'state' => 'draft',
    ]);
    $shipment->save();
    $this->shipment = $this->reloadEntity($shipment);

    $this->order->set('shipments', [$shipment]);
    $this->order->save();
  }

  /**
   * Tests the order cancellation.
   */
  public function testOrderCancellation() {
    $transitions = $this->order->getState()->getTransitions();
    $this->order->getState()->applyTransition($transitions['cancel']);
    $this->order->save();

    $shipment = $this->reloadEntity($this->shipment);
    $this->assertEquals('canceled', $shipment->getState()->value, 'The shipment has been correctly canceled.');
  }

  /**
   * Tests the order fulfillment.
   */
  public function testOrderFulfillment() {
    $order_type = OrderType::load($this->order->bundle());
    $order_type->setWorkflowId('order_fulfillment');
    $order_type->save();

    $transitions = $this->order->getState()->getTransitions();
    $this->order->getState()->applyTransition($transitions['place']);
    $this->order->save();

    $shipment = $this->reloadEntity($this->shipment);
    $this->assertEquals('ready', $shipment->getState()->value, 'The shipment has been correctly finalized.');

    $transitions = $this->order->getState()->getTransitions();
    $this->order->getState()->applyTransition($transitions['fulfill']);
    $this->order->save();
    $shipment = $this->reloadEntity($this->shipment);
    $this->assertEquals('shipped', $shipment->getState()->value);
  }

  /**
   * Tests the order validation.
   */
  public function testOrderValidation() {
    $order_type = OrderType::load($this->order->bundle());
    $order_type->setWorkflowId('order_fulfillment_validation');
    $order_type->save();

    $transitions = $this->order->getState()->getTransitions();
    $this->order->getState()->applyTransition($transitions['place']);
    $this->order->save();
    $shipment = $this->reloadEntity($this->shipment);
    $this->assertEquals('draft', $shipment->getState()->value);

    $transitions = $this->order->getState()->getTransitions();
    $this->order->getState()->applyTransition($transitions['validate']);
    $this->order->save();
    $shipment = $this->reloadEntity($this->shipment);
    $this->assertEquals('ready', $shipment->getState()->value);

    $transitions = $this->order->getState()->getTransitions();
    $this->order->getState()->applyTransition($transitions['fulfill']);
    $this->order->save();
    $shipment = $this->reloadEntity($this->shipment);
    $this->assertEquals('shipped', $shipment->getState()->value);
  }

}

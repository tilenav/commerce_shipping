<?php

namespace Drupal\Tests\commerce_shipping\Unit;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_shipping\Packer\PackerInterface;
use Drupal\commerce_shipping\PackerManager;
use Drupal\commerce_shipping\ProposedShipment;
use Drupal\profile\Entity\ProfileInterface;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\commerce_shipping\PackerManager
 * @group commerce_shipping
 */
class PackerManagerTest extends UnitTestCase {

  /**
   * The packer manager.
   *
   * @var \Drupal\commerce_shipping\PackerManager
   */
  protected $packerManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->packerManager = new PackerManager();
  }

  /**
   * ::covers addPacker
   * ::covers getPackers
   * ::covers pack.
   */
  public function testPack() {
    $order = $this->prophesize(OrderInterface::class)->reveal();
    $shipping_profile = $this->prophesize(ProfileInterface::class)->reveal();

    $first_proposed_shipment = $this->prophesize(ProposedShipment::class)->reveal();
    $second_proposed_shipment = $this->prophesize(ProposedShipment::class)->reveal();
    $third_proposed_shipment = $this->prophesize(ProposedShipment::class)->reveal();

    $first_packer = $this->prophesize(PackerInterface::class);
    $first_packer->applies($order, $shipping_profile)->willReturn(FALSE);
    $first_packer->pack($order, $shipping_profile)->willReturn([$first_proposed_shipment]);
    $first_packer = $first_packer->reveal();

    $second_packer = $this->prophesize(PackerInterface::class);
    $second_packer->applies($order, $shipping_profile)->willReturn(TRUE);
    $second_packer->pack($order, $shipping_profile)->willReturn([$second_proposed_shipment]);
    $second_packer = $second_packer->reveal();

    $third_packer = $this->prophesize(PackerInterface::class);
    $third_packer->applies($order, $shipping_profile)->willReturn(TRUE);
    $third_packer->pack($order, $shipping_profile)->willReturn([$third_proposed_shipment]);
    $third_packer = $third_packer->reveal();

    $this->packerManager->addPacker($first_packer);
    $this->packerManager->addPacker($second_packer);
    $this->packerManager->addPacker($third_packer);
    $expected_packers = [$first_packer, $second_packer, $third_packer];
    $packers = $this->packerManager->getPackers();
    $this->assertEquals($expected_packers, $packers, 'The manager has the expected packers');

    // Confirm that the first packer was skipped due to applies(), and the third one
    // was not reached.
    $result = $this->packerManager->pack($order, $shipping_profile);
    $this->assertArrayEquals([$second_proposed_shipment], $result);
  }

}

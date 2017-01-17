<?php

namespace Drupal\commerce_shipping;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_shipping\Packer\PackerInterface;
use Drupal\profile\Entity\ProfileInterface;

class PackerManager implements PackerManagerInterface {

  /**
   * The packers.
   *
   * @var \Drupal\commerce_shipping\Packer\PackerInterface[]
   */
  protected $packers = [];

  /**
   * {@inheritdoc}
   */
  public function addPacker(PackerInterface $packer) {
    $this->packers[] = $packer;
  }

  /**
   * {@inheritdoc}
   */
  public function getPackers() {
    return $this->packers;
  }

  /**
   * {@inheritdoc}
   */
  public function pack(OrderInterface $order, ProfileInterface $shipping_profile) {
    $proposed_shipments = [];
    foreach ($this->packers as $packer) {
      if ($packer->applies($order, $shipping_profile)) {
        $proposed_shipments = $packer->pack($order, $shipping_profile);
        if (!is_null($proposed_shipments)) {
          break;
        }
      }
    }

    return $proposed_shipments;
  }

}

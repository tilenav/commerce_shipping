<?php

namespace Drupal\commerce_shipping\Plugin\Commerce\EntityTrait;

use Drupal\commerce\BundleFieldDefinition;
use Drupal\commerce\Plugin\Commerce\EntityTrait\EntityTraitBase;

/**
 * Provides the "order_shippable" trait.
 *
 * @CommerceEntityTrait(
 *   id = "order_shippable",
 *   label = @Translation("Shippable"),
 *   entity_types = {"commerce_order"}
 * )
 */
class OrderShippable extends EntityTraitBase {

  /**
   * {@inheritdoc}
   */
  public function buildFieldDefinitions() {
    $fields = [];
    $fields['shipments'] = BundleFieldDefinition::create('entity_reference')
      ->setLabel('Shipments')
      ->setCardinality(BundleFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSetting('target_type', 'commerce_shipment')
      ->setSetting('handler', 'default');

    return $fields;
  }

}

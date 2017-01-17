<?php

namespace Drupal\commerce_shipping\Entity;

use Drupal\commerce_shipping\Plugin\Commerce\PackageType\PackageTypeInterface as PackageTypePluginInterface;
use Drupal\commerce_shipping\ProposedShipment;
use Drupal\commerce_shipping\ShipmentItem;
use Drupal\commerce_order\Adjustment;
use Drupal\commerce_price\Price;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\physical\Weight;
use Drupal\profile\Entity\ProfileInterface;

/**
 * Defines the shipment entity class.
 *
 * @ContentEntityType(
 *   id = "commerce_shipment",
 *   label = @Translation("Shipment"),
 *   label_singular = @Translation("shipment"),
 *   label_plural = @Translation("shipments"),
 *   label_count = @PluralTranslation(
 *     singular = "@count shipment",
 *     plural = "@count shipments",
 *   ),
 *   handlers = {
 *     "storage" = "Drupal\commerce\CommerceContentEntityStorage",
 *     "access" = "Drupal\Core\Entity\EntityAccessControlHandler",
 *     "views_data" = "Drupal\views\EntityViewsData"
 *   },
 *   base_table = "commerce_shipment",
 *   admin_permission = "administer commerce_shipment",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id" = "shipment_id",
 *     "uuid" = "uuid",
 *   },
 *   field_ui_base_route = "entity.commerce_shipment.settings"
 * )
 */
class Shipment extends ContentEntityBase implements ShipmentInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public function populateFromProposedShipment(ProposedShipment $proposed_shipment) {
    $this->set('order_id', $proposed_shipment->getOrderId());
    $this->set('items', $proposed_shipment->getItems());
    $this->set('package_type', $proposed_shipment->getPackageTypeId());
    foreach ($proposed_shipment->getCustomFields() as $field_name => $value) {
      if ($this->hasField($field_name)) {
        $this->set($field_name, $value);
      }
    }
    // @todo
    // Remove this workaround when entity_reference_revisions gets
    // fixed to accept just the target_id.
    $profile_storage = $this->entityTypeManager()->getStorage('profile');
    $shipping_profile = $profile_storage->load($proposed_shipment->getShippingProfileId());
    $this->set('shipping_profile', $shipping_profile);
    // @todo Reset the shipping method/service/amount if the items changed.
  }

  /**
   * {@inheritdoc}
   */
  public function getOrder() {
    return $this->get('order_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOrderId() {
    return $this->get('order_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getPackageType() {
    if (!$this->get('package_type')->isEmpty()) {
      $package_type_id = $this->get('package_type')->value;
      $package_type_manager = \Drupal::service('plugin.manager.commerce_package_type');
      return $package_type_manager->createInstance($package_type_id);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setPackageType(PackageTypePluginInterface $package_type) {
    $this->set('package_type', $package_type->getId());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getShippingMethod() {
    return $this->get('shipping_method')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setShippingMethod(ShippingMethodInterface $shipping_method) {
    $this->set('shipping_method', $shipping_method);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getShippingMethodId() {
    return $this->get('shipping_method')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setShippingMethodId($shipping_method_id) {
    $this->set('shipping_method', $shipping_method_id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getShippingService() {
    return $this->get('shipping_service')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setShippingService($shipping_service) {
    $this->set('shipping_service', $shipping_service);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getShippingProfile() {
    return $this->get('shipping_profile')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setShippingProfile(ProfileInterface $profile) {
    $this->set('shipping_profile', $profile);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getItems() {
    return $this->get('items')->getShipmentItems();
  }

  /**
   * {@inheritdoc}
   */
  public function setItems(array $shipment_items) {
    $this->set('items', $shipment_items);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function addItem(ShipmentItem $shipment_item) {
    $this->get('items')->appendItem($shipment_item);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function removeItem(ShipmentItem $shipment_item) {
    $this->get('items')->removeShipmentItem($shipment_item);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    if (!$this->get('weight')->isEmpty()) {
      return $this->get('weight')->first()->toMeasurement();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight(Weight $weight) {
    $this->set('weight', $weight);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getAmount() {
    if (!$this->get('amount')->isEmpty()) {
      return $this->get('amount')->first()->toPrice();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setAmount(Price $amount) {
    $this->set('amount', $amount);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getAdjustments() {
    return $this->get('adjustments')->getAdjustments();
  }

  /**
   * {@inheritdoc}
   */
  public function setAdjustments(array $adjustments) {
    $this->set('adjustments', $adjustments);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function addAdjustment(Adjustment $adjustment) {
    $this->get('adjustments')->appendItem($adjustment);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function removeAdjustment(Adjustment $adjustment) {
    $this->get('adjustments')->removeAdjustment($adjustment);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getTrackingCode() {
    return $this->get('tracking_code')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTrackingCode($tracking_code) {
    $this->set('tracking_code', $tracking_code);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getState() {
    return $this->get('state')->first();
  }

  /**
   * {@inheritdoc}
   */
  public function getData($key, $default = NULL) {
    $data = [];
    if (!$this->get('data')->isEmpty()) {
      $data = $this->get('data')->first()->getValue();
    }
    return isset($data[$key]) ? $data[$key] : $default;
  }

  /**
   * {@inheritdoc}
   */
  public function setData($key, $value) {
    $this->get('data')->__set($key, $value);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getShippedTime() {
    return $this->get('shipped')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setShippedTime($timestamp) {
    $this->set('shipped', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // The order backreference.
    $fields['order_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Order'))
      ->setDescription(t('The parent order.'))
      ->setSetting('target_type', 'commerce_order')
      ->setReadOnly(TRUE);

    $fields['package_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Package type'))
      ->setDescription(t('The package type.'))
      ->setRequired(TRUE)
      ->setDefaultValue('')
      ->setSetting('max_length', 255)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['shipping_method'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Shipping method'))
      ->setDescription(t('The shipping method'))
      ->setSetting('target_type', 'commerce_shipping_method')
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => 0,
        'settings' => [],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['shipping_service'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Shipping service'))
      ->setDescription(t('The shipping service.'))
      ->setRequired(TRUE)
      ->setDefaultValue('')
      ->setSetting('max_length', 255)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['shipping_profile'] = BaseFieldDefinition::create('entity_reference_revisions')
      ->setLabel(t('Shipping profile'))
      ->setDescription(t('Shipping profile'))
      ->setSetting('target_type', 'profile')
      ->setSetting('handler', 'default')
      ->setSetting('handler_settings', ['target_bundles' => ['customer']])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => 0,
        'settings' => [],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['items'] = BaseFieldDefinition::create('commerce_shipment_item')
      ->setLabel(t('Items'))
      ->setRequired(FALSE)
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['weight'] = BaseFieldDefinition::create('physical_measurement')
      ->setLabel(t('Weight'))
      ->setSetting('measurement_type', 'weight')
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['amount'] = BaseFieldDefinition::create('commerce_price')
      ->setLabel(t('Amount'))
      ->setDescription(t('The shipment amount.'))
      ->setReadOnly(TRUE)
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['adjustments'] = BaseFieldDefinition::create('commerce_adjustment')
      ->setLabel(t('Adjustments'))
      ->setRequired(FALSE)
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['tracking_code'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Tracking code'))
      ->setDescription(t('The shipment tracking code.'))
      ->setRequired(TRUE)
      ->setDefaultValue('')
      ->setSetting('max_length', 255)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['state'] = BaseFieldDefinition::create('state')
      ->setLabel(t('State'))
      ->setDescription(t('The shipment state.'))
      ->setRequired(TRUE)
      ->setSetting('workflow', 'shipment_default')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'state_transition_form',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['data'] = BaseFieldDefinition::create('map')
      ->setLabel(t('Data'))
      ->setDescription(t('A serialized array of additional data.'));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time when the shipment was created.'))
      ->setRequired(TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time when the shipment was last updated.'))
      ->setRequired(TRUE);

    $fields['shipped'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Shipped'))
      ->setDescription(t('The time when the shipment was shipped.'));

    return $fields;
  }

}

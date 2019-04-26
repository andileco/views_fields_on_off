<?php

namespace Drupal\views_fields_on_off\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a handler that adds the form for Fields On/Off.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("views_fields_combined_on_off_form")
 */
class ViewsFieldsCombinedOnOffForm extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function canExpose() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function isExposed() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function buildExposedForm(&$form, FormStateInterface $form_state) {

    $field_id = $this->options['id'];
    $label = $this->options['label'];
    $combined_count = count($this->options['fieldset']);
    $combined_select_options = [];
    for ($i = 0; $i < $combined_count; $i++) {
      array_push($combined_select_options, $this->options['fieldset'][$i]['title']);
    }
    $all_fields = $this->displayHandler->getFieldLabels();

    $form[$field_id] = [
      '#type' => $this->options['exposed_select_type'],
      '#title' => $this->t('@value', [
        '@value' => $label,
      ]),
      '#options' => $combined_select_options,
    ];
    $form['fields_on_off_hidden_submitted'] = [
      '#type' => 'hidden',
      '#default_value' => 1,
    ];
    
    $selected_options = $this->options['fieldset'][0]['fields'];
    $options = array_filter($all_fields, function ($key) use ($selected_options) {
      return in_array($key, $selected_options, TRUE);
    }, ARRAY_FILTER_USE_KEY);
    foreach ($options as $key => $value) {
      $form['views_fields_combined_on_off_form'.'_'.$key] = [
        '#type' => 'hidden',
        '#default_value' => $key,
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['fieldset'][0]['fields'] = ['default' => []];
    $options['fieldset'][0]['title'] = ['default' => ''];
    $options['fieldset'][1]['fields'] = ['default' => []];
    $options['fieldset'][1]['title'] = ['default' => ''];
    $options['exposed_select_type'] = ['default' => 'checkboxes'];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $all_fields = $this->displayHandler->getFieldLabels();

    // Remove any field that have been excluded from the display from the list.
    foreach ($all_fields as $key => $field) {
      $exclude = $this->view->display_handler->handlers['field'][$key]->options['exclude'];
      if ($exclude) {
        unset($all_fields[$key]);
      }
    }

    // Offer to include only those fields that follow this one.
    $field_options = array_slice($all_fields, 0, array_search($this->options['id'], array_keys($all_fields)));

    $form['fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Fields'),
    ];

    $form['fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Fields'),
    ];

    $form['fieldset'][0]['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $this->options['fieldset'][0]['title'],
    ];

    $form['fieldset'][0]['fields'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Fields'),
      '#description' => t('Fields to be turned on and off.'),
      '#options' => $field_options,
      '#default_value' => $this->options['fieldset'][0]['fields'],
    ];

    $form['fieldset'][1]['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $this->options['fieldset'][1]['title'],
    ];

    $form['fieldset'][1]['fields'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Fields'),
      '#description' => t('Fields to be turned on and off.'),
      '#options' => $field_options,
      '#default_value' => $this->options['fieldset'][1]['fields'],
    ];

    $form['exposed_select_type'] = [
      '#type' => 'radios',
      '#title' => t('Exposed Selection Field Type'),
      '#description' => t('Fields to be turned on and off.'),
      '#options' => [
        'checkboxes' => $this->t('Checkboxes'),
        'radios' => $this->t('Radios'),
      ],
      '#default_value' => $this->options['exposed_select_type'],
    ];

  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // This is not a real field and it only affects the query by excluding
    // fields from the display. But Views won't render if the query()
    // method is not present. This doesn't do anything, but it has to be here.
    // This function is a void so it doesn't return anything.
  }

}
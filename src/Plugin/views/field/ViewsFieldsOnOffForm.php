<?php

namespace Drupal\views_fields_on_off\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a handler that adds the form for Fields On/Off.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("views_fields_on_off_form")
 */
class ViewsFieldsOnOffForm extends FieldPluginBase {

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
    $selected_options = $this->options['fields'];
    $all_fields = $this->displayHandler->getFieldLabels();
    $options = array_filter($all_fields, function ($key) use ($selected_options) {
      return in_array($key, $selected_options, TRUE);
    }, ARRAY_FILTER_USE_KEY);

    if (!empty($this->options['exposed_select_type']) && $this->options['exposed_select_type'] === 'radios') {
      $type = 'radios';
    }
    else {
      $type = 'checkboxes';
    }

    $form[$field_id] = [
      '#type' => $type,
      '#title' => $this->t('@value', [
        '@value' => $label,
      ]),
      '#options' => $options,
    ];

    if ($form[$field_id]['#type'] == 'checkboxes'
      && $this->view->getDisplay()
        ->getOption('exposed_form')['type'] !== 'input_required'
    ) {
      // If the form has been submitted, don't have all boxes checked.
      $params = \Drupal::request()->query->all();
      // This is for a GET request.
      // If the view is submitted through AJAX, like in view preview, it will be
      // a POST request. Merge the parameter arrays and we’ll get our values.
      $postParams = \Drupal::request()->request->all();
      $params = array_merge($params, $postParams);
      if ($params['fields_on_off_hidden_submitted'] != 1) {
        $form[$field_id]['#attributes'] = ['checked' => 'checked'];
      }
    }

    $form['fields_on_off_hidden_submitted'] = [
      '#type' => 'hidden',
      '#default_value' => 1,
    ];

  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['fields'] = ['default' => []];
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
    $form['fields'] = [
      '#type' => 'checkboxes',
      '#title' => t('Fields'),
      '#description' => t('Fields to be turned on and off.'),
      '#options' => $field_options,
      '#default_value' => $this->options['fields'],
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
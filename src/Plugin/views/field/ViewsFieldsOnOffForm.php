<?php

namespace Drupal\views_fields_on_off\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\Core\Form\FormStateInterface;


/**
 * Provides a handler that adds the form for Fields On/Off.
 *
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("views_fields_on_off_form")
 */
class ViewsFieldsOnOffForm extends FieldPluginBase {

  /**
   * @return bool
   */
  public function canExpose() {
    return TRUE;
  }

  /**
   * @return bool
   */
  public function isExposed() {
    return TRUE;
  }

  /**
   * @param $form
   * @param $form_state
   */
  public function buildExposedForm(&$form, FormStateInterface $form_state) {
    $fields = $this->options['fields'];
    $options = [];
    $checked = [];

    $all_fields = $this->displayHandler->getFieldLabels();

    // Grab the fields_on_off values that have been submitted already.
    $params = \Drupal::request()->query->all();

    $on_off_submitted = array_key_exists('fields_on_off_hidden_submitted', $params);
    $checked_fields = array_key_exists('fields_on_off', $params) ? $params['fields_on_off'] : [];

    // Now loop through the fields defined in the view.
    foreach ($fields as $field) {
      if ($field) {
        $id = $field;
        if (array_key_exists($id, $all_fields)) {
          $label = $all_fields[$id];
          $options[$id] = $label;

          // If the field is included on the querystring...
          $check_me = (!count($checked_fields) && !$on_off_submitted) || array_key_exists($id, $checked_fields);
          if ($check_me) {
            // Check it because it has already been selected
            $checked[$id] = TRUE;
          }
        }
      }
    }

    // Form API to build the checkboxes.
    $form['fields_on_off'] = [
      '#type' => 'checkboxes',
      '#title' => t('Show Fields'),
      '#description' => t('Select the fields you want to display'),
      '#options' => $options,
      '#value' => $options,
      // I don't know why we have to include #options and #value, but it
      // doesn't work if we don't...
    ];

    $form['fields_on_off_hidden_submitted'] = [
      '#type' => 'hidden',
      '#default_value' => 1,
    ];

    // Have to use $form_state['input'] because setting the default values on
    // the form field itself doesn't work.
    // Because of how Views handles the exposed filters,
    // this is how we set our values in the form.
   //
   // Not sure if this is needed for D8
   // $form_state['input']['fields_on_off'] = $checked;
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['fields'] = ['default' => []];

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

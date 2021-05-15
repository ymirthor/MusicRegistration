<?php

namespace Drupal\discogs_search\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configuration form for the discogs search module
 */
class DiscogsSearchForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['discogs_search.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'discogs_search_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config("discogs_search.settings");

    $form['discogs_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Discogs consumer key'),
      '#required' => TRUE,
      '#default_value' => $config->get('discogs_key'),
    ];

    $form['discogs_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Discogs consumer secret'),
      '#required' => TRUE,
      '#default_value' => $config->get('discogs_secret'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config("discogs_search.settings")
      ->set("discogs_key", $form_state->getValue("discogs_key"))
      ->set("discogs_secret", $form_state->getValue("discogs_secret"))
      ->save();

    parent::submitForm($form, $form_state);
  }
}

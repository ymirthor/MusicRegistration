<?php

namespace Drupal\spotify_search\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configuration form for the spotify search module
 */
class SpotifySearchForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['spotify_search.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'spotify_search_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = \Drupal::config('spotify_search.settings');

    $form['spotify_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Spotify client id'),
      '#required' => TRUE,
      '#default_value' => $config->get('spotify_id'),
    ];

    $form['spotify_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Spotify secret key'),
      '#required' => TRUE,
      '#default_value' => $config->get('spotify_secret'),
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
    $this->config("spotify_search.settings")
      ->set("spotify_id", $form_state->getValue("spotify_id"))
      ->set("spotify_secret", $form_state->getValue("spotify_secret"))
      ->save();

    parent::submitForm($form, $form_state);
  }
}

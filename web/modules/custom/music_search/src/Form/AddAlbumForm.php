<?php

namespace Drupal\music_search\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form definition for adding new albums.
 */
class AddAlbumForm extends FormBase {

  /**
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  private $tempStoreFactory;

  /**
   * AddAlbumForm constructor.
   * @param PrivateTempStoreFactory $tempStoreFactory
   */
  public function __construct(PrivateTempStoreFactory $tempStoreFactory) {
    $this->tempStoreFactory = $tempStoreFactory;
  }

  /**
   * @param ContainerInterface $container
   * @return AddAlbumForm|static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'add_album_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['album_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Album name'),
      '#required' => TRUE,
    ];

    $form['artist_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Artist name'),
      '#required' => TRUE,
      '#autocomplete_route_name' => 'music_search.autocomplete',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Check if that album with the same artist is in the database.

    // Gets all ids for albums with same title as album_name input.
    $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
    $query
      ->condition('type', 'album')
      ->condition('title', $form_state->getValue('album_name'));
    $ids = $query->execute();

    // Gets the nodes for the ids.
    $albums = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($ids);
    foreach ($albums as $album) {
      $artist_ids = $album->get('field_artist')->getValue()[0];
      $artists = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($artist_ids);
      foreach ($artists as $artist) {
        $artist_name = $artist->get('title')->getValue()[0]['value'];
        if ($artist_name === $form_state->getValue('artist_name')) {
          $form_state->setErrorByName('album_name', $this->t('That album is already in the database.'));
        }
      }
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Create PrivateTempStore for user input.
    $tempstore = $this->tempStoreFactory->get('input_data');

    // Store input data in tempstore.
    try {
      $tempstore->set('params', [
        'album_name' => $form_state->getValue('album_name'),
        'artist_name' => $form_state->getValue('artist_name'),
      ]);
      // Redirect to controller.
      $form_state->setRedirect('music_search.pick_data');
    }
    catch (\Exception $err) {
      $this->messenger()->addError($this->t('Something went wrong... please try again.'));
    }
  }
}

<?php

namespace Drupal\music_search\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\music_search\SearchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form definition for picking correct data from APIs.
 */
class PickDataForm extends FormBase {

  /**
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  private $tempStoreFactory;

  /**
   * @var \Drupal\music_search\SearchInterface
   */
  protected $search_api;

  /**
   * PickDataForm constructor.
   * @param PrivateTempStoreFactory $tempStoreFactory
   * @param array $search_api
   */
  public function __construct(PrivateTempStoreFactory $tempStoreFactory, array $search_api) {
    $this->tempStoreFactory = $tempStoreFactory;
    $this->search_api = $search_api;
  }

  /**
   * @param ContainerInterface $container
   * @return PickDataForm|static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private'),
      [
        $container->get('spotify_search.search'),
        $container->get('discogs_search.search'),
      ]
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'pick_data_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['spotify_options'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Spotify Results'),
      '#options' => $this->getData('spotify'),
    ];

    $form['discogs_options'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Discogs Results'),
      '#options' => $this->getData('discogs'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Continue'),
      '#button_type' => 'primary'
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state){
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Create PrivateTempStore for user input.
    $tempstore = $this->tempStoreFactory->get('picked_ids');
    // Store input data in tempstore.
    try {
      $spotify = $form_state->getValue('spotify_options');
      $discogs = $form_state->getValue('discogs_options');

      $tempstore->set('params', [
        'spotify_ids' => $form_state->getValue('spotify_options'),
        'discogs_ids' => $form_state->getValue('discogs_options'),
      ]);
      // Redirect to controller.
      $form_state->setRedirect('music_search.create_album');

    }
    catch (\Exception $err) {
      $this->messenger()->addError($this->t('Something went wrong... please try again.'));
    }
  }

  /**
   * Gets data from the relevant service and creates format for the form.
   * @param string $type where did the data come from? (spotify/discogs)
   * @return array
   */
  private function getData(string $type) {
    $tempstore = $this->tempStoreFactory->get('input_data');
    $params = $tempstore->get('params');
    $artist_name = $params['artist_name'];
    $album_name = $params['album_name'];


    $index = $type === 'spotify' ? 0 : 1;
    // Call API for data.
    $data = $this->search_api[$index]->getQuery($artist_name, $album_name);
    $albums = json_decode($data)->{'albums'}->{'items'};

    // Mapping function to create render array from json data.
    // Only works for spotify
    $process_item = function($item) {
      return sprintf(
        '<div>
                    <h3>%s</h3>
                    <h5>%s</h5>
                    <img src="%s" alt="Album image" width=250px>
                </div>',
        $item->{'name'},
        $item->{'artists'}[0]->{'name'},
        $item->{'images'}[0]->{'url'},
      );
    };

    $ret_arr = [];
    for ($i = 0; $i < count($albums); $i++) {
      $album_id = $albums[$i]->{'id'};
      if ($type === 'spotify') {
        $artist_id = reset($albums[$i]->{'artists'})->{'id'};
      } else {
        $search_res = json_decode($this->search_api[1]->getArtist(reset($albums[$i]->{'artists'})->{'name'}));
        $artist_id = reset($search_res->results)->id;
      }
      $ret_arr[$album_id . " " . $artist_id] = $process_item($albums[$i]);
    }

    return $ret_arr;
  }
}

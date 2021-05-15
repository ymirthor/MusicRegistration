<?php

namespace Drupal\music_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\music_search\SearchInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for MusicSearch.
 */
class MusicSearchController extends ControllerBase {

  /**
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * @var \Drupal\music_search\SearchInterface
   */
  protected $search_api;

  /**
   * MusicSearchController constructor.
   * @param PrivateTempStoreFactory $tempStoreFactory
   * @param array $search_api
   */
  public function __construct(PrivateTempStoreFactory $tempStoreFactory, array $search_api) {
    $this->tempStoreFactory = $tempStoreFactory;
    $this->search_api = $search_api;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private'),
      [
        $container->get('spotify_search.search'),
        $container->get('discogs_search.search'),
      ],
    );
  }

  /**
   * A function for giving autocomplete results
   * @param Request $request the request object to parse
   * @return JsonResponse the final response of artist suggestions
   */
  public function autoComplete(Request $request): JsonResponse
  {
    $json = [];
    $artist = $request->query->get('q');

    if (strlen($artist) < 3) {
      return new JsonResponse($json);
    }

    $spotify_suggestions = json_decode($this->search_api[0]->getArtist($artist));
    $discogs_suggestions = json_decode($this->search_api[1]->getArtist($artist));

    foreach (array_slice($spotify_suggestions->{"artists"}->{"items"}, 0, 5, true) as $suggestion) {
      $json[0][] = [
        'value' => $suggestion->{"name"},
        'label' => $suggestion->{"name"} . " - Spotify",
      ];
    }

    foreach (array_slice($discogs_suggestions->{"results"}, 0, 5, true) as $suggestion) {
      $json[1][] = [
        'value' => $suggestion->{"title"},
        'label' => $suggestion->{"title"} . " - Discogs",
      ];
    }

    $json = $this->shuffle($json);
    return new JsonResponse($json);
  }

  /**
   * Takes in an array of equally sized arrays. Shuffles them equally
   * so that the first elements in each array appear first in the new array
   * and the next values after in order.
   * @param array $values of arrays of equal length to shuffle
   * @return array of values from all arrays passed in shuffled
   */
  private function shuffle(array $values) {
    $len_array = count($values[0]);
    $nr_arrays = count($values);
    $ret = [];
    for ($i = 0; $i < $len_array * $nr_arrays; $i++) {
      $array_pick = $i % $nr_arrays;
      $value_pick = intdiv($i, $nr_arrays);
      $ret[$i] = $values[$array_pick][$value_pick];
    }
    return $ret;
  }
}

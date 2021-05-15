<?php

namespace Drupal\discogs_search;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\music_search\ApiSearch;
use Drupal\music_search\SearchInterface;
use Drupal\discogs_search\DiscogsParser;

/**
 * Discogs Search that allows for searching the discogs database
 * @package Drupal\discogs_search
 */
class DiscogsSearch implements SearchInterface {

  /**
   * The master search api that handles GET and POST requests
   *
   * @var \Drupal\music_search\ApiSearch
   */
  protected $search_api;

  /**
   * The config factory
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config_factory;

  /**
   * The parser used to change the discogs data to our data format (same as spotify returns)
   *
   * @var \Drupal\discogs_search\DiscogsParser
   */
  protected $parser;

  /**
   * DiscogsSearch constructor.
   * @param ApiSearch $search_api the master search api
   * @param ConfigFactoryInterface $config_factory for configuration access
   * @param \Drupal\discogs_search\DiscogsParser $parser to parse data from discogs into our preferred format
   */
  public function __construct(ApiSearch $search_api, ConfigFactoryInterface $config_factory, DiscogsParser $parser) {
    $this->search_api = $search_api;
    $this->config_factory = $config_factory;
    $this->parser = $parser;
  }

  /**
   * Returns the discogs authentication string for the header
   * @return string of discogs authentication header value
   */
  private function getAuthString() {
    $config = $this->config_factory->get('discogs_search.settings');
    return "Discogs key=" . $config->get('discogs_key') . ', secret=' . $config->get('discogs_secret');
  }

  /**
   * Searches for matching releases based on the artist and album names supplied
   * @param $artist string of the artist name
   * @param $album string of the album name
   * @return string json of the data
   */
  public function getQuery($artist, $album) {
    $auth_string = $this->getAuthString();
    $uri = "https://api.discogs.com/database/search?release_title=" . $album . "&artist=" . $artist . "&type=master&per_page=10&page=1";
    $data = $this->search_api->getQuery($uri, $auth_string);
    return $this->parser->toAlbums($data);
  }

  /**
   * Searches for an artist based on the name supplied
   * @param $artist string of the artist's name
   * @return string of type json
   */
  public function getArtist($artist) {
    $bearer_token = $this->getAuthString();
    $uri = "https://api.discogs.com/database/search?q=" . $artist . "&type=artist&per_page=10&page=1";
    $data = $this->search_api->getQuery($uri, $bearer_token);
    return $data;
  }

  /**
   * Gets a single artist from the artist id found by using the getArtist search method
   * @param $artist_id string of the artist id
   * @return string of json object
   */
  public function getArtistById($artist_id) {
    $bearer_token = $this->getAuthString();
    $uri = "https://api.discogs.com/artists/" . $artist_id;
    $data = $this->search_api->getQuery($uri, $bearer_token);
    return $data;
  }

  /**
   * Gets a single album based on the albums master id found with the search method
   * @param $album_id string of the album master id
   * @return string of json object
   */
  public function getAlbumById($album_id) {
    $bearer_token = $this->getAuthString();
    $uri = "https://api.discogs.com/masters/" . $album_id;
    $data = $this->search_api->getQuery($uri, $bearer_token);
    return $this->parser->toMasterAlbum($data);
  }
}

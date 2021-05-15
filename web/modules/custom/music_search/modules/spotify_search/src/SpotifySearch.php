<?php

namespace Drupal\spotify_search;

use DateTime;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\music_search\ApiSearch;
use Drupal\music_search\SearchInterface;

/**
 * Spotify Search that allows for searching the spotify database
 * @package Drupal\spotify_search
 */
class SpotifySearch implements SearchInterface {

  /**
   * The master search api for handling GET and POST requests
   * @var \Drupal\music_search\ApiSearch
   */
  protected $search_api;

  /**
   * The config factory
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config_factory;

  /**
   * The cache of the site for caching the authorization token
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * SpotifySearch constructor.
   * @param ApiSearch $search_api the master search api
   * @param ConfigFactoryInterface $config_factory the default config factory to access the config values
   * @param CacheBackendInterface $cache the sites cache where you want to store the auth token
   */
  public function __construct(ApiSearch $search_api, ConfigFactoryInterface $config_factory, CacheBackendInterface $cache) {
    $this->search_api = $search_api;
    $this->config_factory = $config_factory;
    $this->cache = $cache;
  }

  /**
   * Returns the auth token from spotify. If it is valid in the cache it uses that one otherwise
   * it sends a post request to spotify for a new one and stores that one in the cache.
   * @return string of the token needed for the get request
   */
  private function getToken() {
    // Check if the stored token exists or is valid
    if (!$this->cache->get("spotify-token")) {
      // Key and connection url definitions
      $conn_str = "https://accounts.spotify.com/api/token";
      $config = $this->config_factory->get('spotify_search.settings');
      $key = base64_encode($config->get('spotify_id') . ':' . $config->get('spotify_secret'));
      // Header setup
      $headers = array();
      $headers[] = "Authorization: Basic " . $key;
      $headers[] = "Content-Type: application/x-www-form-urlencoded";
      // Token api call
      $token = json_decode($this->search_api->getAuthToken($conn_str, $headers));
      $bearer_token = "Bearer " . $token->access_token;
      // Token expiration
      $date = new DateTime();
      $expires = $date->getTimestamp() + $token->expires_in;
      // Storing token in cache with spotify config as tag for invalidating
      $this->cache->set("spotify-token", $bearer_token, $expires, ["config:spotify_search.settings"]);
    }
    else {
      $bearer_token = $this->cache->get("spotify-token")->data;
    }
    return $bearer_token;
  }

  /**
   * Searches for album entries based on the supplied artist and album names
   * @param $artist string of the artist name
   * @param $album string of the album name
   * @return string of json object
   */
  public function getQuery($artist, $album) {
    $bearer_token = $this->getToken();
    $uri = "https://api.spotify.com/v1/search?q=album:" . $album . "%20artist:" . $artist . "&type=album";
    return $this->search_api->getQuery($uri, $bearer_token);
  }

  /**
   * Searches for an artist based on the supplied artist name
   * @param $artist string of the artist name
   * @return string of json object
   */
  public function getArtist($artist) {
    $bearer_token = $this->getToken();
    $uri = "https://api.spotify.com/v1/search?q=artist:" . $artist . "&type=artist";
    return $this->search_api->getQuery($uri, $bearer_token);
  }

  /**
   * Gets the data from a single artist based on his id found with the search method
   * @param $artist_id string of the artist id
   * @return string of json object
   */
  public function getArtistById($artist_id) {
    $bearer_token = $this->getToken();
    $uri = "https://api.spotify.com/v1/artists/" . $artist_id;
    return $this->search_api->getQuery($uri, $bearer_token);
  }

  /**
   * Gets the data for a single album based on the id found with the search method
   * @param $album_id string of the album id
   * @return string of json object
   */
  public function getAlbumById($album_id) {
    $bearer_token = $this->getToken();
    $uri = "https://api.spotify.com/v1/albums/" . $album_id;
    return $this->search_api->getQuery($uri, $bearer_token);
  }

}

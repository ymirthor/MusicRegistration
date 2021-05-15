<?php

namespace Drupal\music_search;

/**
 * Interface that individual search units should implement
 */
interface SearchInterface {
  public function getQuery($artist, $album);
  public function getArtist($artist);
  public function getArtistById($artist_id);
  public function getAlbumById($album_id);
}

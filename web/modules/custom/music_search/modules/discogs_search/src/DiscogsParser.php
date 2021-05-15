<?php

namespace Drupal\discogs_search;

/**
 * Parser class for the DiscogsSearch class to change the data
 * representation to that of spotify's to erase code duplication
 * @package Drupal\discogs_search
 */
class DiscogsParser {

  /**
   * Takes in a json string of releases retrieved from discogs search
   * api and turns it into a json string with the same parameter names
   * as spotify's search api
   *
   * @param string $albums_json json string from discogs search api
   * @return string in json format like spotify returns it
   */
  function toAlbums(string $albums_json) {
    $albums_decoded = json_decode($albums_json);
    $data = new \stdClass();
    $data->albums = new \stdClass();
    $data->albums->items = [];

    foreach ($albums_decoded->results as $album) {
      $name_arr = explode(" - ", $album->title);
      $ret_album = new \stdClass();
      $ret_album->name = $name_arr[1];
      $ret_album->artists = [["name"=>$name_arr[0]]];
      $ret_album->images = [["url"=>$album->cover_image]];
      $ret_album->id = $album->master_id;
      array_push($data->albums->items, $ret_album);
    }
    return json_encode($data);
  }

  /**
   * Takes in a json string retrieved from discogs master release api and turns
   * it into a json string with the same parameter names as spotify's album api
   *
   * @param string $album_json a json string of a single master album from discogs
   * @return string in json format like spotify returns it
   */
  function toMasterAlbum(string $album_json) {
    $album_decoded = json_decode($album_json);
    $album = new \stdClass();
    $album->name = $album_decoded->title;
    $album->artists = $album_decoded->artists;
    $album->release_date = $album_decoded->year;
    $album->label = "";
    $album->genres = $album_decoded->genres;
    $album->id = $album_decoded->id;
    $album->images = [];
    $album->songs = $album_decoded->tracklist;
    foreach ($album_decoded->images as $image) {
      array_push($album->images, ["url" => $image->uri]);
    }
    return json_encode($album);
  }

  /**
   * Not currently in use and does nothing
   * @param string $artists_json json of artists from discogs
   * @return string json of artists like spotify returns it
   */
  function toArtists(string $artists_json) {
    return $artists_json;
  }

  /**
   * Not currently in use and does nothing
   * @param string $artist_json json of an artist from discogs
   * @return string json of an artist like spotify returns it
   */
  function toMasterArtist(string $artist_json) {
    return $artist_json;
  }
}

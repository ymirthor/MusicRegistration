<?php

namespace Drupal\music_search;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * A service class with function to save data to the database
 * @package Drupal\music_search
 */
class DataService {

  /**
   * @var ConfigFactoryInterface
   */
  protected $config_factory;

  /**
   * @var EntityTypeManagerInterface
   */
  protected $typeManager;

  /**
   * DataService constructor.
   * @param ConfigFactoryInterface $config_factory
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $typeManager) {
    $this->config_factory = $config_factory;
    $this->typeManager = $typeManager;
  }

  /**
   * Saves a file, based on it's type
   *
   * @param $url
   *   Full path to the image on the internet
   * @param $folder
   *   The folder where the image is stored on your hard drive
   * @param $type
   *   Type should be 'image' at all time for images.
   * @param $title
   *   The title of the image (like ALBUM_NAME - Cover), as it will appear in the Media management system
   * @param $basename
   *   The name of the file, as it will be saved on your hard drive
   *
   * @return int|null|string
   * @throws EntityStorageException
   */
  public function save_file($url, $folder, $type, $title, $basename, $uid = 1) {
    $directory = $this->config_factory->get("system.file")->get("default_scheme") . "://" . $folder;
    if (!is_dir($directory)) {
      return null;
    }

    $destination = $directory . "/" . basename($basename);
    if (!file_exists($destination)) {
      $file = file_get_contents($url);
      $file = file_save_data($file, $destination);
    }
    else {
      $file = \Drupal\file\Entity\File::create([
        'uri' => $destination,
        'uid' => $uid,
        'status' => FILE_STATUS_PERMANENT
      ]);

      $file->save();
    }
    $file->status = 1;

    $media_type_field_name = "field_media_image";
    $media_array = [
      $media_type_field_name => $file->id(),
      "name" => $title,
      "bundle" => $type,
    ];
    if ($type == "image") {
      $media_array["alt"] = $title;
    }

    $media_object = \Drupal\media\Entity\Media::create($media_array);
    $media_object->save();
    return $media_object->id();
  }

  /**
   * Takes in a vocabulary and a new term name and returns an id to that term
   * @param $vocabulary string of the vocabulary name
   * @param $title string of the new term name
   * @return string of id to the term
   */
  public function save_taxonomy($vocabulary, $title) {
    $taxonomy = $this->typeManager->getStorage('taxonomy_term');
    $taxQuery = $taxonomy->getQuery();

    $taxQuery
      ->condition('vid', $vocabulary)
      ->condition('name', $title)
      ->range(0, 1)
      ->sort('changed', 'DESC');
    $exists = $taxQuery->execute();

    if ($exists) {
      foreach ($exists as $data) {
        return $data;
      }
    }
    else {
      $new_term = $taxonomy->create(["vid" => $vocabulary, "name" => $title]);
      $new_term->save();
      return $new_term->id();
    }
  }

  /**
   * Takes in a node's type and title and returns it's id, if it does not exist,
   * returns FALSE.
   * @param string $type
   * @param string $title
   * @return false|string
   */
  public function get_node_id(string $type, string $title) {
    $node = $this->typeManager->getStorage('node');
    $nodeQuery = $node->getQuery();

    $nodeQuery
      ->condition('type', $type)
      ->condition('title', $title)
      ->range(0, 1)
      ->sort('changed', 'DESC');
    $ids = $nodeQuery->execute();
    if ($ids) {
      return array_pop($ids);
    } else {
      return FALSE;
    }
  }

  /**
   * Take in a node's type and data and returns an id to that node.
   * @param string $type of the new node type
   * @param array $data of the new node data
   * @return \Drupal\Core\Entity\EntityInterface the new node
   */
  public function save_node(string $type, array $data) {
    // Check if node already exists.
    $id = $this->get_node_id($type, $data['title']);

    // If so, return it's id.
    if ($id) {
      return array_pop($id);
    }

    $node = $this->typeManager->getStorage('node');
    // Set the values for the new node.
    $vals = [
      'type' => $type,
      'title' => $data['title'],
    ];
    if ($type === 'album') {
      $vals['field_artist'] = [
        ['target_id' => $data['artist_id']]
      ];
      $vals['field_album_cover'] = [
        ['target_id' => $data['cover_id']]
      ];
      $vals['field_genre'] = [
        ['target_id' => $data['genre_id']]
      ];
      $vals['field_released'] = date('Y-m-d', strtotime($data['release_date']));
      $vals['body'] = $data['description'];
    } elseif ($type === 'artist') {
      $vals['status'] = 1;
    }

    // Save new node and return it's id.
    $new_node = $node->create($vals);
    $new_node->save();
    return $new_node;
  }
}

<?php

namespace Drupal\music_search;

class ApiSearch {

  public function getQuery(string $uri, $token) {
    $res = null;

    $options = array(
      'method' => 'GET',
      'timeout' => 3,
      'headers' => array(
        'Accept' => 'application/json',
        'Authorization' => $token,
      )
    );

    $res = \Drupal::httpClient()->get($uri, $options);
    return (string) $res->getBody();
  }

  public function getAuthToken(string $conn_str, array $headers) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $conn_str);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
    curl_setopt($ch, CURLOPT_POST, 1);

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $res = curl_exec($ch);

    curl_close($ch);
    return $res;
  }
}

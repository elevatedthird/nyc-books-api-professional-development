<?php

namespace Drupal\nyc;
use Drupal\Component\Serialization\Json;
use Exception;
use Drupal;


/**
 * Class NYCClient.
 */
class NYCClient {

  const PKMN_SRC = '/names.json';
  const PKMN_DATE = '/current';

  /**
   * @return array of list of names
   */
  public function getNames() {
    $api_url = self::PKMN_SRC .'?api-key=I9czxtzYsym0bYDHVdV5201NhmfvgCv1';
    try {
      $api_url = $this->apiRequestList($api_url);
      $request = $this->getApiRequestList($api_url);
    }
    catch (Exception $e) {
      \Drupal::logger('nyc')->error($e->getMessage());
    }
    $results = $request['results'];
    foreach($results as $r) {
      $list_names[] = $r['list_name'];
    }
    return $list_names;
  }
  /**
   * @return array with details of list
   */
  public function getList() {
    $api_url = self::PKMN_SRC .'?api-key=I9czxtzYsym0bYDHVdV5201NhmfvgCv1';
    try {
      $api_url = $this->apiRequestList($api_url);
      $request = $this->getApiRequestList($api_url);
    }
    catch (Exception $e) {
      \Drupal::logger('nyc')->error($e->getMessage());
    }
    return $request;
  }


  public function getNameEncoded($data) {
    $api_url = self::PKMN_SRC .'?api-key=I9czxtzYsym0bYDHVdV5201NhmfvgCv1';
    try {
      $api_url = $this->apiRequestList($api_url);
      $request = $this->getApiRequestList($api_url);
    }
    catch (Exception $e) {
      \Drupal::logger('nyc')->error($e->getMessage());
    }
    $results = $request['results'];
    foreach($results as $r){
      $list_names_encoded[$r['list_name']]= $r['list_name_encoded'];
    }
    if(array_key_exists($data, $list_names_encoded)){
      $name_encoded = $list_names_encoded[$data];
    }
    return $name_encoded;
  }
  /**
   * @param $url
   * @return string
   */

  public function apiRequestList($url) {
    $url = $this->getUrl($url);
    return $url;
  }
  /**
   * {@inheritdoc}
   */
  public function getApiRequestList($url) {
    $client = \Drupal::httpClient();
    $request = $client->get($url, [
      'auth' => ['api-key','I9czxtzYsym0bYDHVdV5201NhmfvgCv1']
    ]);
    try {
      $response = $client->request('GET', $url);
      $request = $response->getBody()->getContents();
    }
    catch (Exception $e) {
      \Drupal::logger('nyc')->error($e->getMessage());
    }
    return Json::decode($request);
  }

  /**
   * @param $listName
   * @return mixed
   */

  public function getListData($listName) {
    $api_url = self::PKMN_DATE;
    try{
      $api_url = $this->apiRequestList($api_url . '/'.$listName .'.json?api-key=I9czxtzYsym0bYDHVdV5201NhmfvgCv1');
      $request = $this->getApiRequestList($api_url);
    }
    catch (Exception $e) {
      \Drupal::logger('nyc')->error($e->getMessage());
    }
    return $request;
  }

  /**
   * @param $url
   * @return string
   */

  public function getUrl($url) {
    return 'https://api.nytimes.com/svc/books/v3/lists' . $url;
  }

  /**
   * @param $encoded_name
   * @return mixed
   */

  public function getBookDetails($encoded_name) {
    $listdata = $this->getListData($encoded_name);
    $books = $listdata['results']['books'];
    for($i=0; $i < 6;$i++){
      $title = $books[$i]['title'];
      $author = $books[$i]['author'];
      $description = $books[$i]['description'];
      $book[$encoded_name][]= array('title'=>$title, 'author'=>$author, 'description'=>$description);
    }
    return $book;
  }

  /**
   * @param $listname
   * @return mixed
   */

  public function getListDetails($listname){
    $api_url = self::PKMN_DATE;
    $api_url = $this->apiRequestList($api_url . '/'.$listname .'.json?api-key=I9czxtzYsym0bYDHVdV5201NhmfvgCv1');
    $request = $this->getApiRequestList($api_url);
    $results = $request['results'];
    $list[$results['list_name_encoded']] = array('list'=>$results['list_name'], 'publish_date'=> $results['published_date']);
    return $list;
  }

  /**
   * @param $listname
   * @return mixed
   */

  public function getCompleteListData($listname){
    $list = $this->getListDetails($listname);
    $book = $this->getBookDetails($listname);
    $final_data[$listname] = array('list_name'=> $list[$listname]['list'],'publish_date'=> $list[$listname]['publish_date'],'Top_5_books'=> $book);
    return $final_data;
  }

  /**
   * @param $final_data
   * @param $listname
   * @return mixed
   */

  public function getBookField($final_data,$listname) {
    $a = $final_data[$listname]['Top_5_books'];
    for( $i=0;$i<6;$i++){
      $book[$i]= 'Title : '. t($a[$listname][$i]['title']).', Author : '.t($a[$listname][$i]['author'].', Description : '.t($a[$listname][$i]['description']));
    }
    return($book);
  }
}

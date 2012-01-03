<?php
/**
 * @file
 *
 * This file provides the ObjectStorage class, which is the primary
 * representation of the ObjectStorage system.
 *
 * ObjectStorage (aka Swift) is the OpenStack service for providing
 * storage of complete and discrete pieces of data (e.g. an image file,
 * a text document, a binary).
 */

namespace HPCloud\Storage;

use HPCloud\Storage\ObjectStorage\Container;

/**
 * Access to ObjectStorage (Swift).
 *
 * This is the primary piece of the Object Oriented representation of
 * the Object Storage service. Developers wishing to work at a low level
 * should use this API.
 *
 * There is also a stream wrapper interface that exposes ObjectStorage
 * to PHP's streams system. For common use of an object store, you may
 * prefer to use that system. (See \HPCloud\Bootstrap).
 *
 * When constructing a new ObjectStorage object, you will need to know
 * what kind of authentication you are going to perform. Older
 * implementations of OpenStack provide a separate authentication
 * mechanism for Swift. You can use ObjectStorage::newFromSwiftAuth() to
 * perform this type of authentication.
 *
 * Newer versions use the Control Services authentication mechanism (see
 * \HPCloud\Services\ControlServices). That method is the preferred
 * method.
 *
 * Common Tasks
 *
 * - Create a new container with addContainer().
 * - List containers with containers().
 * - Remove a container with deleteContainer().
 *
 */
class ObjectStorage {


  /**
   * Create a new instance after getting an authenitcation token.
   *
   * This uses the legacy Swift authentication facility to authenticate
   * to swift, get a new token, and then create a new ObjectStorage 
   * instance with that token.
   *
   * To use the legacy Object Storage authentication mechanism, you will
   * need the follwing pieces of information:
   *
   * - Account ID: Your account username or ID. For HP Cloud customers,
   *   this is typically a long string of numbers and letters.
   * - Key: Your secret key. For HP Customers, this is a string of
   *   random letters and numbers.
   * - Endpoint URL: The URL given to you by your service provider.
   *
   * HP Cloud users can find all of this information on your Object
   * Storage account dashboard.
   *
   * @param string $account
   *   Your account name.
   * @param string $key
   *   Your secret key.
   * @param string $url
   *   The URL to the object storage endpoint.
   *
   * @throws \HPCloud\Transport\AuthorizationException if the 
   *   authentication failed.
   * @throws \HPCloud\Transport\FileNotFoundException if the URL is
   *   wrong.
   * @throws \HPCloud\Exception if some other exception occurs.
   */
  public static function newFromSwiftAuth($account, $key, $url) {
    $headers = array(
      'X-Auth-User' => $account,
      'X-Auth-Key' => $key,
    );

    $client = \HPCloud\Transport::instance();

    // This will throw an exception if it cannot connect or
    // authenticate.
    $res = $client->doRequest($url, 'GET', $headers);


    // Headers that come back:
    // X-Storage-Url: https://region-a.geo-1.objects.hpcloudsvc.com:443/v1/AUTH_d8e28d35-3324-44d7-a625-4e6450dc1683
    // X-Storage-Token: AUTH_tkd2ffb4dac4534c43afbe532ca41bcdba
    // X-Auth-Token: AUTH_tkd2ffb4dac4534c43afbe532ca41bcdba
    // X-Trans-Id: tx33f1257e09f64bc58f28e66e0577268a


    $token = $res->header('X-Auth-Token');
    $newUrl = $res->header('X-Storage-Url');


    $store = new ObjectStorage($token, $newUrl);

    return $store;
  }

  /**
   * The authorization token.
   */
  protected $token = NULL;
  /**
   * The URL to the Swift endpoint.
   */
  protected $url = NULL;

  /**
   * Construct a new ObjectStorage object.
   *
   * @param string $authToken
   *   A token that will be included in subsequent requests to validate
   *   that this client has authenticated correctly.
   */
  public function __construct($authToken, $url) {
    $this->token = $authToken;
    $this->url = $url;
  }

  /**
   * Get the authentication token.
   *
   * @return string
   *   The authentication token.
   */
  public function token() {
    return $this->token;
  }

  /**
   * Get the URL endpoint.
   *
   * @return string
   *   The URL that is the endpoint for this service.
   */
  public function url() {
    return $this->url;
  }

  /**
   * Fetch a list of containers for this account.
   *
   * By default, this fetches the entire list of containers for the
   * given account. If you have more than 10,000 containers (who
   * wouldn't?), you will need to use $marker for paging.
   *
   * If you want more controlled paging, you can use $limit to indicate
   * the number of containers returned per page, and $marker to indicate
   * the last container retrieved.
   *
   * Containers are ordered. That is, they will always come back in the
   * same order. For that reason, the pager takes $marker (the name of
   * the last container) as a paging parameter, rather than an offset
   * number.
   *
   * @param int $limit
   *   The maximum number to return at a time. The default is -- brace
   *   yourself -- 10,000 (as determined by OpenStack. Implementations
   *   may vary).
   * @param string $marker
   *   The name of the last object seen. Used when paging.
   *
   * @return array
   *   An associative array of containers, where the key is the
   *   container's name and the value is an
   *   \HPCloud\Storage\ObjectStorage\Container object. Results are
   *   ordered in server order (the order that the remote host puts them
   *   in).
   */
  public function containers($limit = 0, $marker = NULL) {

    $url = $this->url() . '?format=json';

    if ($limit > 0) {
      $url .= sprintf('&limit=%d', $limit);
    }
    if (!empty($marker)) {
      $url .= sprintf('&marker=%d', $marker);
    }

    $containers = $this->get($url);

    $containerList = array();
    foreach ($containers as $container) {
      $containerList[$container['name']] = Container::newFromJSON($container);
    }

    return $containerList;
  }

  /**
   * Check to see if this container name exists.
   *
   * Unless you are working with a huge list of containers, this
   * operation is as slow as simply fetching the entire container list.
   */
  public function hasContainer($name) {
    $containers = $this->containers();
    return isset($containers[$name]);
  }

  /**
   * Create a container with the given name.
   *
   * This creates a new container on the ObjectStorage
   * server with the name provided in $name.
   *
   * A boolean is returned when the operation did not generate an error
   * condition.
   *
   * - TRUE means that the container was created.
   * - FALSE means that the container was not created because it already
   * exists.
   *
   * Any actual error will cause an exception to be thrown. These will
   * be the HTTP-level exceptions.
   *
   * @param string $name
   *   The name of the container.
   * @return boolean
   *   TRUE if the container was created, FALSE if the container was not
   *   created because it already exists.
   */
  public function createContainer($name) {
    $url = $this->url() . '/' . urlencode($name);
    $data = $this->req($url, 'PUT', FALSE);

    $status = $data->status();

    if ($status == 201) {
      return TRUE;
    } elseif ($status == 202) {
      return FALSE;
    }
    // According to the OpenStack docs, there are no other return codes.
    else {
      throw new \HPCloud\Exception('Server returned unexpected code: ' . $status);
    }
  }

  /**
   * Delete an empty container.
   *
   * Given a container name, this attempts to delete the container in
   * the object storage.
   *
   * The container MUST be empty before it can be deleted. If it is not, 
   * an \HPCloud\Storage\ObjectStorage\ContainerNotEmptyException will 
   * be thrown.
   *
   * @param string $name
   *   The name of the container.
   * @return boolean
   *   TRUE if the container was deleted, FALSE if the container was not
   *   found (and hence, was not deleted).
   * @throws \HPCloud\Storage\ObjectStorage\ContainerNotEmptyException
   *   if the container is not empty.
   * @throws \HPCloud\Exception if an unexpected response code is returned.
   *   While this should never happen on HPCloud servers, forks of
   *   OpenStack may choose to extend object storage in a way that
   *   results in a non-standard code.
   */
  public function deleteContainer($name) {
    $url = $this->url() . '/' . urlencode($name);
    $data = $this->req($url, 'DELETE', FALSE);

    $status = $data->status();

    // 204 indicates that the container has been deleted.
    if ($status == 204) {
      return TRUE;
    }

    // Container not found. We return false, as this isn't really an
    // error condition.
    elseif ($status == 404) {
      return FALSE;
    }

    // The container must be empty before it can be deleted. This is an
    // actual failure, so we throw an exception.
    elseif ($status == 409) {
      throw new ObjectStorage\ContainerNotEmptyException("Non-empty container cannot be deleted.");
    }

    // OpenStacks documentation doesn't suggest any other return
    // codes.
    else {
      throw new \HPCloud\Exception('Server returned unexpected code: ' . $status);
    }
  }

  /**
   * Do a GET on Swift.
   *
   * This is a convenience method that handles the
   * most common case of Swift requests.
   */
  protected function get($url, $jsonDecode = TRUE) {
    return $this->req($url, 'GET', $jsonDecode);
  }

  protected function req($url, $method = 'GET', $jsonDecode = TRUE, $body = '') {
    $client = \HPCloud\Transport::instance();
    $headers = array(
      'X-Auth-Token' => $this->token(),
    );

    $raw = $client->doRequest($url, $method, $headers, $body);
    if (!$jsonDecode) {
      return $raw;
    }
    return json_decode($raw->content(), TRUE);

  }
}
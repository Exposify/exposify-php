<?php

/**
 * Blueprint class to bundle API functionality.
 */
abstract class ApiBlueprint {
	/**
	 * API JSON result converted to an array.
	 * @var Array
	 */
	protected $result = [];

	/**
	 * API JSON error converted to an array.
	 * @var Array
	 */
	protected $error = [];

	/**
	 * The URL to connect with Exposify API.
	 * @var string
	 */
	protected $apiUrl = '';

	/**
	 * The secret key to connect with Exposify API.
	 * @var string
	 */
	protected $apiKey = '';

	/**
	 * Request and store data from a specific URL.
	 * @param  String $url
	 * @return Void
	 */
	protected function requestData($url)
	{
		$curl = curl_init();
		curl_setopt_array($curl, [
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL            => $url,
			CURLOPT_TIMEOUT        => 5
		]);
		$json = json_decode(curl_exec($curl), true);

		if (isset($json['error'])) {
			$this->error = $json['error'];
		} else {
			$this->result = $json;
		}

		curl_close($curl);
	}

	/**
	 * Request all properties.
	 * @param  String $searchQuery
	 * @return Void
	 */
	public function requestAllProperties($searchQuery)
	{
		$url = $this->apiUrl . '?api_token=' . $this->apiKey . '&query=' . $searchQuery;
		$this->requestData($url);
	}

	/**
	 * Request a single property.
	 * @param  String $slug
	 * @return Void
	 */
	public function requestSingleProperty($slug)
	{
		$url = $this->apiUrl . '/' . $slug . '?api_token=' . $this->apiKey;
		$this->requestData($url);
	}

	/**
	 * Return the result of the finished request.
	 * @return Array
	 */
	public function getResult()
	{
		return $this->result;
	}

	/**
	 * Return the error of the finished request.
	 * @return Array
	 */
	public function getError()
	{
		return $this->error;
	}
}

/**
 * Class to allow access to the HTML API.
 */
class HtmlHandler extends ApiBlueprint {
	/**
	 * Construct the class.
	 * @param String $apiUrl
	 * @param String $apiKey
	 */
	public function __construct($apiUrl, $apiKey)
	{
		$this->apiUrl = $apiUrl;
		$this->apiKey = $apiKey;
	}

	/**
	 * Output the result of the HTML API request.
	 * @return void
	 */
	public function getContent()
	{
		if (!empty($this->error)) {
			http_response_code($this->error['status'] ?: 404);
			echo htmlspecialchars_decode($this->error['html']);
		} else {
			echo htmlspecialchars_decode($this->result['html']);
		}
	}

	/**
	 * Output the title of the requested property.
	 * @return void
	 */
	public function getTitle()
	{
		if (isset($this->result['title'])) {
			echo $this->result['title'];
		}
	}

	/**
	 * Output the description of the requested property.
	 * @return void
	 */
	public function getDescription()
	{
		if (isset($this->result['description'])) {
			echo $this->result['description'];
		}
	}

	/**
	 * Output all head tags needed for the requested ressources.
	 * @return void
	 */
	public function getMeta()
	{
		if (isset($this->error['css']))  { $css = $this->error['css']; }
		if (isset($this->result['css'])) { $css = $this->result['css']; }

		if (isset($css) && is_array($css)) {
			foreach ($css as $css_src) {
				echo '<link rel="stylesheet" href="' . $css_src . '">';
			}
		}
	}

	/**
	 * Output all footer tags needed for the requested ressources.
	 * @return void
	 */
	public function getScripts()
	{
		if (isset($this->error['js']))  { $js = $this->error['js']; }
		if (isset($this->result['js'])) { $js = $this->result['js']; }

		if (isset($js) && is_array($js)) {
			foreach ($js as $js_src) {
				echo '<script src="' . $js_src . '"></script>';
			}
		}
	}
}

/**
 * Class to handle the JSON API and allow access to the HTML API.
 */
class Exposify extends ApiBlueprint {
	/**
	 * The HtmlHandler Instance
	 * @var HtmlHandler
	 */
	public $html = null;

	/**
	 * Construct the class and instantiate the HtmlHandler.
	 * @param String $apiKey
	 */
	public function __construct($apiKey, $apiBaseUrl = 'https://app.exposify.de')
	{
		$this->apiUrl = $apiBaseUrl . '/api/beta/';
		$this->apiKey = $apiKey;
		$this->html   = new HtmlHandler($apiBaseUrl . '/html-api', $apiKey);
	}
}

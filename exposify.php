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
		$this->result = json_decode(curl_exec($curl), true);
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
		if (empty($this->result)) {
			http_response_code(404);
			echo '<h1>404 :(</h1>' .
			     '<p>Wir können diese Immobilie leider nicht finden.</p>';
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
		if (isset($this->result['css']) && is_array($this->result['css'])) {
			foreach ($this->result['css'] as $css_src) {
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
		if (isset($this->result['js']) && is_array($this->result['js'])) {
			foreach ($this->result['js'] as $js_src) {
				echo '<script src="' . $js_src . '">';
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
	public function __construct($apiKey)
	{
		$this->apiUrl = 'https://app.exposify.de/api/beta/';
		$this->apiKey = $apiKey;
		$this->html   = new HtmlHandler('https://app.exposify.de/html-api', $apiKey);
	}
}

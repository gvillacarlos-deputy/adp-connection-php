<?php

/*
*/

class adpapiConnectionFactory {

	/**
	* Grant type map
	* @var array $grant_types
	*/
	 private static $grant_types = array (
        'ClientCredentials' 			=> 'ClientConnection',
        'AuthorizationCode' 			=> 'AuthorizedConnection'
    );

    /**
     * Creates a connection object
     *
     * The factory worker - decides via grant type which connection object to give
     *                      to the user.
     *
     * @param array $config - Associative array of configuration options
     * @return class adpapiConnection based on grant type
     */
	public static function create($config) {

		$grant_type = $config['grantType'];

		if (empty(self::$grant_types[$grant_type])) {
			// @codeCoverageIgnoreStart
			throw new adpException("Invalid Grant Type.", 0 , null, "");
			return;
			// @codeCoverageIgnoreEnd
		}

		$classname = "adpapi" . self::$grant_types[$grant_type];

		if(class_exists($classname)) {
			return new $classname($config);
		}

		// @codeCoverageIgnoreStart
		throw new adpException("Invalid Grant Type.", 0 , null, "");
		return;
		// @codeCoverageIgnoreEnd
	}

}

/**
 *
 *  This is an abstract class that contains shared functionality among all grant types.
 * @codeCoverageIgnore
 */
abstract class adpapiConnection {

	/**
	* Grant Type
	* @var string
	*/
	public $grant_type;

	/**
	* Logger instance
	* @var object
	*/
	public $logger;

	/**
	* The local file location of the server certificate
	* @var string
	*/
	public $certfile;

	/**
	* The local file location of the server key
	* @var string
	*/
	public $keyfile;

	/**
	* The Client ID
	* @var string
	*/
	public $client_id;

	/**
	* The Client Secret
	* @var string
	*/
	public $client_secret;

	/**
	* The root URL for all calls
	* @var string
	*/
	public $apiRoot;

	protected $accessToken;
	protected $tokenExpiration = 0;
	protected $status;
	protected $decoded;
	protected $jsondata;

	/**
	* Constructor
	* @codeCoverageIgnore
	* @param array $config - Configuration Options
	*/
	public function __construct($config) {

		$this->logger = adpapiUtilityFactory::getObject("logger");

	}

	/**
	 *
	 * Creates a random uuid to utilize as the connection state.
	 *
	 * @return string uuid value
	 **/
	public function create_uuid() {

		return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0x0fff ) | 0x4000,
			mt_rand( 0, 0x3fff ) | 0x8000,
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
	}

	/**
	* Returns true if you've got a token and have not hit the expiration time yet.
	*
	* @return boolean
	*/
	public function isConnectedIndicator() {

		if (empty($this->accessToken)) {
			return false;
		}

		if ($this->tokenExpiration < time()) {
			return false;
		}

		return true;

	}

	/**
	* Returns the access token.
	*
	* @return string
	*/
	public function getAccessToken() {

		return $this->accessToken;

	}

	/**
	* Returns the time of expiration, in seconds.
	*
	* @return integer
	*/
	public function getExpiration() {

		return $this->tokenExpiration;

	}

}


/**
 *
 *  Handles the logic required to connect and disconnect using client credentials.
 *
 */

class adpapiClientConnection extends adpapiConnection {


    /**
     *
     * Construct - Initializes the object using an associative array
     *
     * @param array $config - Associative array
     * @return void
     */
	public function __construct($config) {

		parent::__construct($config);

		$this->grant_type = "Client Credentials";

		$this->apiRoot			= $config['tokenServerURL'];
		$this->client_id 		= $config['clientID'];
		$this->client_secret	= $config['clientSecret'];
		$this->certfile 		= $config['sslCertPath'];
		$this->keyfile			= $config['sslKeyPath'];

	}

    /**
     * Connect - Connects to the ADP Endpoint and retrieves an access token / Client Credentials
     *
     * @return void
     */
	public function connect() {

		$this->logger->write("CC Connect");

		if (strlen($this->apiRoot) < 10) {
			// @codeCoverageIgnoreStart
			throw new adpException("Missing token server url.", 0 , null, "");
			return;
			// @codeCoverageIgnoreEnd
		}

		$this->logger->write("Creating Parameters");

		$endpoint = $this->apiRoot . "auth/oauth/v2/token";

		$pemf = $this->certfile;
		$keyf = $this->keyfile;

		$id 	= $this->client_id;
		$secret = $this->client_secret;

		$params = array(
		  "grant_type" => "client_credentials",
		  );

		$postdata = http_build_query($params, '', '&');

		$curl = curl_init($endpoint);

		curl_setopt($curl, CURLOPT_SSLCERT,			$pemf);
		curl_setopt($curl, CURLOPT_SSLKEY, 			$keyf);
		curl_setopt($curl, CURLOPT_USERPWD, 		"$id:$secret");
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 	true);
		curl_setopt($curl, CURLOPT_POST, 			true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, 		$postdata);

		$this->logger->write("Making Call");

		$this->jsondata = curl_exec($curl);
		$this->status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$this->decoded = json_decode($this->jsondata);

		$this->token = "";

		$this->logger->write("Checking Status");

		// evaluate for success response
		if ($this->status != 200) {

			if (curl_errno($curl) <> 0) {
				// @codeCoverageIgnoreStart
				throw new adpException("Communication Error", curl_errno($curl) , null, curl_error($curl));
				return;
				// @codeCoverageIgnoreEnd
			}
			else {

				// @codeCoverageIgnoreStart
				throw new adpException($this->decoded->error, $this->status , null, $this->jsondata);
				return;
				// @codeCoverageIgnoreEnd
			}
		}
		else {

			// Fill in the access token property
			$this->accessToken = $this->decoded->access_token;
			$this->tokenExpiration = $this->decoded->expires_in + time();

		}

		curl_close($curl);

		$this->logger->write("CC Complete");

		return $this->decoded;

	}

    /**
     * Disconnect - Terminates the life of the token
     *
     * @return void
     */
	public function disconnect() {

		return true;

	}

}




/**
 *
 *  This class handles the logic required to connect and disconnect using client credentials.
 *
 */
class adpapiAuthorizedConnection extends adpapiConnection {


	/**
	* The response type.  MUST BE 'code'.
	* @var string
	*/
	public $responseType;

	/**
	* The URL to redirect to, in order to continue authentication.
	* @var string
	*/
	public $redirectUri;

	/**
	* The scope of the call.  MUST BE 'openid'.
	* @var string
	*/
	public $scope;

	/**
	* The state of the connection.  A uuid will be generated upon creation.)
	* @var string
	*/
	public $state;

	/**
	* The auth code, returned from the gateway.  Won't have at first.
	* @var string
	*/
	public $auth_code;

	/**
	* The refresh token.
	* @var string
	*/
	public $refreshToken;


    /**
     * Construct - Initializes the object
     *
     * @param array $config - Associative array of configuration options
     */
	public function __construct($config) {

		parent::__construct($config);

		$this->grant_type 		= "Authorization Code";

		$this->client_id 		= $config['clientID'];
		$this->client_secret 	= $config['clientSecret'];
		$this->certfile 		= $config['sslCertPath'];
		$this->keyfile 			= $config['sslKeyPath'];
		$this->apiRoot 			= $config['tokenServerURL'];
		$this->redirectUri 		= $config['redirectURL'];
		$this->scope 			= $config['scope'];
		$this->responseType 	= $config['responseType'];

		$this->state 			= $this->create_uuid();

	}

    /**
     * getAuthRequest
     *
     * Creates the authorization URL to call the gateway
     *
     * @return string url
     */
	public function getAuthRequest() {

		$this->logger->write("::AC building Request URI");

		if (strlen($this->apiRoot) < 10) {
			// @codeCoverageIgnoreStart
			throw new adpException("Missing token server url.", 0 , null, "");
			return;
			// @codeCoverageIgnoreEnd
		}

		$endpoint = $this->apiRoot . "auth/oauth/v2/authorize";

		$params = array(
		   "response_type" => $this->responseType,
		   "client_id" => $this->client_id,
		   "redirect_uri" => $this->redirectUri,
		   "scope" => $this->scope,
		   "state" => $this->state
		);

		$postdata = http_build_query($params, '', '&');

		$url = $endpoint . "?" . $postdata;

		return $url;

	}

    /**
     * Connect - Connects to the ADP Endpoint and retrieves an access token / Client Credentials
     * @codeCoverageIgnore
     * @return void
     */
	public function connect() {

		$this->logger->write("::AC Connect");

		if (strlen($this->apiRoot) < 10) {
			// @codeCoverageIgnoreStart
			throw new adpException("Missing token server url.", 0 , null, "");
			return;
			// @codeCoverageIgnoreEnd
		}

		$this->logger->write("::Building Parameters");

		$endpoint = $this->apiRoot . "auth/oauth/v2/token";

		// Use one of the parameter configurations listed at the top of the post
		//$params = array(...);

		$pemf = $this->certfile;
		$keyf = $this->keyfile;

		$id 	= $this->client_id;
		$secret = $this->client_secret;

		$params = array(
		  "grant_type" => "authorization_code",
		  "code" => $this->auth_code,
		  "redirect_uri" => $this->redirectUri,

		  );

		$postdata = http_build_query($params, '', '&');

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL,			$endpoint);
		curl_setopt($curl, CURLOPT_SSLCERT,		$pemf);
		curl_setopt($curl, CURLOPT_SSLKEY, 		$keyf);
		curl_setopt($curl, CURLOPT_USERPWD, 	"$id:$secret");
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		curl_setopt($curl, CURLOPT_POSTFIELDS, 	$postdata);

		$this->logger->write("::Making the call");

		$this->jsondata = curl_exec($curl);
		$this->status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$this->decoded = json_decode($this->jsondata);

		$this->token = "";

		$this->logger->write("::Checking status");

		// evaluate for success response
		if ($this->status != 200) {

			if (curl_errno($curl) <> 0) {
				// @codeCoverageIgnoreStart
				throw new adpException("Communication Error", curl_errno($curl) , null, curl_error($curl));
				return;
				// @codeCoverageIgnoreEnd
			}
			else {
				// @codeCoverageIgnoreStart
				throw new adpException($this->decoded->error, $this->status , null, $this->jsondata);
				return;
				// @codeCoverageIgnoreEnd
			}
		}
		else {
			// Fill in the access token property
			$this->accessToken = $this->decoded->access_token;
			$this->tokenExpiration = $this->decoded->expires_in + time();

		}

		curl_close($curl);

		$this->logger->write("::Complete");

		return $this->decoded;

	}

    /**
     * Terminates the life of the token
     *
     * @return void
     */
	public function disconnect() {

		return true;

	}

    /**
     * refreshToken
     *
     * @return void
     */
	public function refreshToken() {

		return true;

	}

    /**
     * Returns the refresh token
     *
     * @return string refreshToken
     */

	public function getRefreshToken() {

		return $this->refreshToken;

	}

    /**
     * getState
     *
     * @return string state
     */

	public function getState() {

		return $this->state;

	}

}

//-----------------------------------------------------------------------------
// Automatically load the UTILITY class when loading the connection classes.
//-----------------------------------------------------------------------------

require_once($libroot . "connection/adpapiUtility.class.php");


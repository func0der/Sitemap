<?php
/**
 * Sitemaps.org sitemap file.
 * 
 * Contains the definition of a Sitemaps.org Sitemap.
 *
 * @author Lars Lenecke <incode@func0der.de>
 * @version 0.5
 * @package Sitemap
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . '../SitemapInterface.php';

class Sitemap_SitemapsOrg implements SitemapInterface {
/**
 * XML Version holder
 *
 * @var string
 */
 	protected $_xmlVersion = '1.0';

 /**
  * Encoding of the sitemap.
  *
  * @var string
  */
 	protected $_encoding = 'utf-8';

 /**
  * Include xml styling.
  *
  * @var boolean
  */
 	protected $_styleSheet = FALSE;

/**
 * Namespaces to apply on <urlset> tag.
 *
 * Structure of the array and its consequences:
 *
 *	array(
 *		[PREFIX] => [SCHEMAURL],
 *	)
 *
 * Will be included in the <urlset> tag as the following:
 *
 *	<urlset xmlns[:[PREFIX]]="[SCHEMAURL]">
 *
 * The prefix it optional.
 * If left empty this entry becomes the root namespace.
 *
 * @var array
 */
	protected $_namespaces = array(
		'' => 'http://www.sitemaps.org/schemas/sitemap/0.9',
	);

/**
 * Holds the DomDocument object.
 *
 * @var DomDocument
 */
 	protected $_domDocument;

/**
 * Root node name.
 *
 * {@link http://www.sitemaps.org/protocol.html#mainContent Definition}
 *
 * @var string
 */
	protected $_rootNodeName = 'urlset';

/**
 * Entry root node name.
 *
 * {@link http://www.sitemaps.org/protocol.html#mainContent Definition}
 *
 * @var string
 */
	protected $_entryRootNodeName = 'url';

/**
 * Allowed entry nodes.
 *
 * {@link http://www.sitemaps.org/protocol.html#xmlTagDefinitions Definition}
 * Structure: 
 *
 *	array(
 *		[NODENAME] => array(
 *			self::ALLOWED_ENTRY_CHILDREN_INDEX => array(
 *				[NODENAME] => ...
 *			),
 *			'contentCallback' => 'nameOfContentManipulateMethod',
 *			'fallbackValue' => ('someValue' || self::VALIDATION_EXCEPTION),
 *			'required' => TRUE|FALSE,
 *			'validationCallback' => 'nameOfValidationMethod',
 *		),
 *	)
 *
 * Where:
 *	self::ALLOWED_ENTRY_CHILDREN_INDEX (optional)
 *		Child nodes to be accepted under this node.
 *		The same options are available for child nodes
 *		as for parent nodes.
 *		Additional there is an index called "prefix". If set to
 *		"TRUE" or to a string, node names get prefixed by parent node name
 *		or the given string. If not set or "FALSE", node names are
 *		not prefixed.
 *		If not empty, validation and content callbacks do not
 *		apply, nor is any content of the node rendered.
 *		If not set, possible children will not be rendered.
 *
 *	contentCallback (optional)
 *		Is called to manipulate the content of the given value. For example
 *		to encode a url.
 *		If the callback is class internal, it should be prefixed with
 *		"content_".
 *		The callback needs to be specified as an array with the following structure:
 *			array(
 *				'functionName' | array('className'|$classInstance, 'methodName'),
 *				'parameterForCallback_1',
 *				'parameterForCallback_2',
 *				...
 *			)
 *		If not set, content is taken as is.
 *
 *	fallbackValue (conditional)
 *		The here given value is applied if the validation for the field
 *		fails. In case the value is the value of self::VALIDATION_EXCEPTION
 *		the class throws a SitemapValidationException.
 *		Needs to be set if validationCallback is set.
 *
 *	required (optional)
 *		If set to TRUE the node is required.
 *		If not set, FALSE is used.
 *
 *	validationCallback (optional)
 *		The callback to use for validation.
 *		If the callback is class internal, it should be prefixed with
 *		"validation_".
 *		The callback needs to be specified as an array with the following structure:
 *			array(
 *				'functionName' | array('className'|$classInstance, 'methodName'),
 *				'parameterForCallback_1',
 *				'parameterForCallback_2',
 *				...
 *			)
 *		If validation fails, either "fallbackValue" is used or an exception is thrown.
 *
 * @var array
 */
	protected $_allowedEntryNodes = array(
		'loc' => array(
			'contentCallback' => array('content_url'),
			'fallbackValue' => self::VALIDATION_EXCEPTION,
			'required' => TRUE,
			'validationCallback' => array('validation_url'),
		),
		'lastmod' => array(
			'contentCallback' => array('content_date'),
			'fallbackValue' => self::VALIDATION_EXCEPTION,
			'validationCallback' => array('validation_date'),
		),
		'changefreq' => array(
			'fallbackValue' => self::VALIDATION_EXCEPTION,
			'validationCallback' => array('validation_changeFrequency'),
		),
		'priority' => array(
			'fallbackValue' => 0.5,
			'validationCallback' => array('validation_priority'),
		),
	);

/**
 * Allowed entry nodes child index.
 *
 * @const string
 */
	const ALLOWED_ENTRY_CHILDREN_INDEX = '_children';

/**
 * Holding the entries.
 *
 * @var array
 */
 	protected $_entries = array();

/**
 * Holding the node names used for node name generation.
 *
 * @var array
 */
	protected $_parentNodeNames = array();

 /**
  * Change frequency: always
  *
  * @const string
  */
 	const CHANGE_FREQUENCY_ALWAYS = 'always';

 /**
  * Change frequency: hourly
  *
  * @const string
  */
 	const CHANGE_FREQUENCY_HOURLY = 'hourly';

 /**
  * Change frequency: daily
  *
  * @const string
  */
 	const CHANGE_FREQUENCY_DAILY = 'daily';

 /**
  * Change frequency: weekly
  *
  * @const string
  */
 	const CHANGE_FREQUENCY_WEEKLY = 'weekly';

 /**
  * Change frequency: montly
  *
  * @const string
  */
 	const CHANGE_FREQUENCY_MONTHLY = 'monthly';

 /**
  * Change frequency: yearly
  *
  * @const string
  */
 	const CHANGE_FREQUENCY_YEARLY = 'yearly';

 /**
  * Change frequency: never
  *
  * @const string
  */
 	const CHANGE_FREQUENCY_NEVER = 'never';

 /** 
  * Allowed change frequencies in a stirng array.
  *
  * @var array
  */
 	protected $_allowedChangeFrequencies = array(
 		'CHANGE_FREQUENCY_ALWAYS',
 		'CHANGE_FREQUENCY_HOURLY',
 		'CHANGE_FREQUENCY_DAILY',
 		'CHANGE_FREQUENCY_WEEKLY',
 		'CHANGE_FREQUENCY_MONTHLY',
 		'CHANGE_FREQUENCY_YEARLY',
 		'CHANGE_FREQUENCY_NEVER',
 	);

 /**
  * Maximum entries for a sitemap.
  *
  * @const int
  */
 	const MAXIMUM_ENTRIES = 50000;

 /**
  * Maximum filesize for a sitemap in bytes.
  *
  * @const int
  */
 	const MAXIMUM_FILESIZE = 10485760;

 /**
  * Datetime format.
  *
  * @const int
  */
 	const DATETIME_FORMAT = '%Y-%m-%dT%H:%M:%S+00:00';

 /**
  * Ping urls.
  *
  * @var array
  */
 	protected $pingUrl = array();

 /**
  * Validation trigger.
  *
  * @var boolean
  */
 	protected $_useValidation = TRUE;

 /**
  * Content callback trigger.
  *
  * @var boolean
  */
	protected $_useContentCallbacks = TRUE;

 /**
  * Trigger for debug mode.
  *
  *	Enables for example formatted output.
  *
  * @var boolean
  */
 	protected $_debug = false;


/********************************************
 |
 ############# General Methods ##############
 											|
 *******************************************/

 /**
  * Constructor.
  *
  * Basically just calls the self::init() method.
  */
 	public function __construct() {
 	}

 /**
  * Resets the class to start from the beginning with new entries.
  *
  * @return SitemapsOrg
  */
 	public function reset() {
 		// Unset the entries holding url.
 		$this->_entries = array();
 		// Unset dom document
 		unset($this->_domDocument);

 		return $this;
 	}


/********************************************
 |
 ############## Inserting Data ##############
 											|
 *******************************************/

/**
 * Add an entry to entries holder.
 *
 * @param array $entry
 *	An array of the following struture:
 *		array(
 *			'loc' => http://example.com,
 *			'lastmod' => [DATETIME string usable by strtotime],
 *			'changefreq' => 'never',
 *			'priority' => 0.5,
 *		)
 *
 * @return SitemapsOrg
 */
	public function addEntry($entry) {
		if (!is_array($entry)) {
			throw new SitemapInvalidParameterException('Given entry is missformed.');
		}

		if (count($this->_entries) === self::MAXIMUM_ENTRIES) {
			// @XXX: Maybe we should just create another new sitemap here.
			throw new SitemapMaximumEntriesException('A maximum of ' . self::MAXIMUM_ENTRIES . ' entries per sitemap are allowed.');
		}

		// Progress entry data.
		$entry = $this->_progressEntryData($entry);

		// @TODO: Calculate estimated file size
		// @XXX: Just add up byte amount for each node.

		// Add entry.
		// @XXX:	Maybe hash the url with md5 and check if entry already exists.
		//			Overwrite it only if second parameter for this method is set.
		$this->_entries[] = $entry;

		return $this;
	}

/**
 * Adds multiple entries at once.
 *
 * @param array $entries
 *
 * @return SitemapsOrg
 */
	public function addEntries($entries) {
		foreach ($entries as $entry) {
			$this->addEntry($entry);
		}

		return $this;
	}

/**
 * Validates and manipulates entry data, if needed.
 *
 * @param array $entry
 *	The entry to progress.
 * @param string $runTimeConfiguration
 *	If filled, this configuration is used instead of 
 *	class configuration. Used for recursion.
 *
 * @return string
 *	The (manipulated) entry.
 */
	protected function _progressEntryData($entry, $runTimeConfiguration = NULL) {
		$result = array();

		// Set allowed entry nodes configuration if run time configuration is given.
		if (!is_null($runTimeConfiguration)) {
			$oldAllowedEntryNodes = $this->getAllowedEntryNodes();
			$this->setAllowedEntryNodes($runTimeConfiguration);
		}

		// Get allowed entry nodes.
		$allowedEntryNodes = $this->getAllowedEntryNodes();

		// Check for missing required data.
		foreach ($allowedEntryNodes as $node => $configuration) {
			
			if (
				// Configuration is present.
				isset($configuration['required']) &&
				$configuration['required'] === TRUE &&
				// Node is not found in entry data or empty.
				(!isset($entry[$node]) || empty($entry[$node]))
			) {
				throw new SitemapInvalidParameterException('Required node ' . $node . ' is missing in your entry.');
			}
		}

		foreach ($entry as $node => $data) {
			// Check if we have a configuration.
			if ($this->hasAllowedEntryConfig($node)) {
				// Add node to parent node names.
				$this->_addParentNodeName($node);

				$nodeConfiguration = $this->getAllowedEntryConfig($node);

				// Check for children.
				if (is_array($data)) {
					// We need to recreated the whole array to catch sub nodes and sub node collections.
					$sub_node_collections = array();
					foreach ($data as $index => $data_items) {
						// If this is a collection of sub nodes it is something like images or videos.
						// Since XML nodes can not be an integer, this should do it.
						if (is_int($index)) {
							$sub_node_collections[$index] = $this->_progressEntryData($data[$index], $nodeConfiguration[self::ALLOWED_ENTRY_CHILDREN_INDEX]);
							// Unset this index, so we can process $data later on.
							unset($data[$index]);
						}
					}

					// Process left over data, if any.
					if (!empty($data)) {
						$data = $this->_progressEntryData($data, $nodeConfiguration[self::ALLOWED_ENTRY_CHILDREN_INDEX]);
					}

					// Merge data back together.
					$data = array_merge($sub_node_collections, $data);
				}
				// Normal string.
				else {
					// Validation.
					if ($this->getUseValidation() && isset($nodeConfiguration['validationCallback'])) {
						$validationCallback = $nodeConfiguration['validationCallback'];

						// Define parameters for validationCallback.
						$parameters = array(
							$data,
						);

						// Check if the given validation callback is an array
						// with additional parameters to be passed to validation callback.
						if (count($validationCallback) > 1) {
							// Get the additional parameters for the validation callback.
							$additionalParameters = array_splice($validationCallback, 1);
							// Reset validation callback.
							$validationCallback = $validationCallback[0];
							// Merge with already existing parameters.
							$parameters = array_merge(
								$parameters,
								$additionalParameters
							);
						}

						$validationResult = $this->_executeCallback(
							$validationCallback,
							$parameters,
							array(
								'prefix' => 'validation_',
								'requirePrefixFor' => 'internal',
							)
						);

						// If validation failed...
						if (!$validationResult) {
							// ...check the fallback value...
							// A special case for throwing an exception.
							if ($nodeConfiguration['fallbackValue'] === self::VALIDATION_EXCEPTION) {
								// Throw exception.
								throw new SitemapValidationException('Invalid value for node "' . $node . '"');
							}
							else {
								// Use fallback value as is.
								$data = $nodeConfiguration['fallbackValue'];
							}
						}
					}

					// Manipulate content.
					if ($this->getUseContentCallbacks() && isset($nodeConfiguration['contentCallback'])) {
						$contentCallback = $nodeConfiguration['contentCallback'];

						// Define parameters for contentCallback.
						$parameters = array(
							$data,
						);

						// Check if the given validation callback is an array
						// with additional parameters to be passed to validation callback.
						if (count($contentCallback) > 1) {
							// Get the additional parameters for the validation callback.
							$additionalParameters = array_splice($contentCallback, 1);
							// Reset content callback.
							$contentCallback = $contentCallback[0];
							// Merge with already existing parameters.
							$parameters = array_merge(
								$parameters,
								$additionalParameters
							);
						}

						$data = $this->_executeCallback(
							$contentCallback,
							$parameters,
							array(
								'prefix' => 'content_',
								'requirePrefixFor' => 'internal',
							)
						);
					}
				}

				// Generate node name.
				$nodeName = $this->_generateNodeName($node, $nodeConfiguration);

				// Save the result.
				$result[$nodeName] = $data;

				// Remove last entry from last parent nodes.
				$this->_removeLastParentNodeName();
			}
		}

		// Reset allowed entry configuration.
		if (!is_null($runTimeConfiguration) && isset($oldAllowedEntryNodes)) {
			$this->setAllowedEntryNodes($oldAllowedEntryNodes);
		}

		return $result;
	}


/**
 * Execute a given callback.
 *
 * @param mixed (array|string) $function
 *	The function or method or an array of an object
 *	and a string to execute.
 * @param array $parameters
 *	The parameters to passed to the $function.
 * @param array $options
 *	Options for the callback.
 *	Allowed options:
 *		'prefix'			=>	If a prefix is needed for the 
 *								callback function/method.
 *		'requirePrefixFor'	=>	For what functions/methods
 *								should the prefix be required.
 *								Possible options: internal, external, both
 *
 * @return mixed
 * 	Unpredictable mixed results.
 */
	protected function _executeCallback($function, $parameters, $options) {
		$options += array(
			'prefix' => '',
			'requirePrefixFor' => 'internal'
		);
		// Predefine class value.
		$class = NULL;
		// Predefine call value.
		$call = FALSE;
		// Predefine $result.
		$result = NULL;

		// Check function.
		if(is_array($function)) {
			// Split function for easier access.
			if (count($function) > 1) {
				$class = $function[0];
				$function = $function[1];
			}
			else {
				$function = $function[0];
			}
		}

		// Speed up this a little for a specific configuration.
		if (
			!empty($options['prefix']) &&
			$options['requirePrefixFor'] == 'both' &&
			strpos($function, $options['prefix']) !== 0
		) {
			throw new SitemapInvalidCallbackException('The prefix ' . $options['prefix'] . ' is missing in the callback.');
		}

		// Quick access to prefix combinations.
		$requireInternalPrefix = false;
		$requireExternalPrefix = false;
		if (!empty($options['prefix'])) {
			switch ($options['requirePrefixFor']) {
				case 'both':
					$requireInternalPrefix = true;
					$requireExternalPrefix = true;
					break;
					
				case 'internal':
					$requireInternalPrefix = true;
					break;
					
				case 'external':
					$requireExternalPrefix = true;
					break;
			}
		}
		if ($requireInternalPrefix || $requireExternalPrefix) {
			$hasPrefix = (strpos($function, $options['prefix']) === 0);
		}

		// Check if the callback is a simple string with no class given.
		// This means it is either internal or
		// a simple function call.
		if (!isset($class) && isset($function)){
			// Internal method call.
			if (
				(
					// We need no internal prefix.
					!$requireInternalPrefix ||
					// We need an internal prefix an it is present.
					(
						$requireInternalPrefix &&
						$hasPrefix
					)
				) &&
				// The internal method is callable.
				method_exists($this, $function)
			) {
				// Manipulate $class to call the right method.
				$class = $this;
				$call = true;
			}
			// External function call.
			elseif (
				// We need no external prefix.
				!$requireExternalPrefix ||
				// We need an external prefix an it is present.
				(
					$requireExternalPrefix &&
					$hasPrefix
				)
			) {
				$call = true;
			}
		}
		// Class calls with an array.
		elseif (
			// If class and function are both set.
			isset($class) && isset($function)
		) {
			// Internal call.
			if(
				$class === $this ||
				is_subclass_of($class, 'Sitemap')
			){
				if (
					(
						// We need no internal prefix.
						!$requireInternalPrefix ||
						// We need an internal prefix an it is present.
						(
							$requireInternalPrefix &&
							$hasPrefix
						)
					) &&
					// The internal method is callable.
					method_exists($class, $function)
				){
					$call = true;
				}
			}
			// External call.
			else {
				if (
					(
						// We need no external prefix.
						!$requireExternalPrefix ||
						// We need an external prefix an it is present.
						(
							$requireExternalPrefix &&
							$hasPrefix
						)
					) &&
					// The internal method is callable.
					method_exists($class, $function)
				){
					$call = true;
				}
			}
		}

		// Aggregate callback for is_callable and call_user_func_array.
		$callback = (isset($class)) ? array($class, $function) : $function;

		if ($call && is_callable($callback)) {
			$result = call_user_func_array(
				$callback,
				$parameters
			);
		}

		return $result;
	}

/**
 * Generate a node name.
 *
 * Generates a node name based on the given configuration and
 * the $this->_parentNodeName array.
 *
 * @param string $nodeName
 *	The name of the node to generade a name for.
 * @param array $configuration
 *	The allowed entry configuration of the current node.
 *
 * @return string
 *	The generated node name.
 */
	protected function _generateNodeName($nodeName, $nodeConfiguration) {
		// Initiate result.
		$result = $nodeName;

		if (isset($nodeConfiguration['prefix'])) {
			$prefix = $nodeConfiguration['prefix'];

			if (is_bool($prefix) && $prefix === TRUE) {
				$result = implode(':', $this->_getParentNodeNames());
			}
			elseif (is_string($prefix)) {
				$result = $prefix . ':' . $result;
			}
		}

		return $result;
	}

/********************************************
 |
 ############# Outputting Data ##############
 											|
 *******************************************/

/**
 * Creates the sitemap from saved entries.
 */
	public function render() {
		// Initiate render progress.
		$this->_initiateRender();

		// Get root node.
		$rootNode = $this->_getRootNode();

		// Get entries.
		$entries = $this->getEntries();

		foreach($entries as $entry) {
			// Create a root node for the entry.
			$entryNode = $this->_domDocument->createElement($this->_entryRootNodeName);

			// Create all the nodes for the entry.
			$this->_renderNode($entryNode, $entry);

			// Append entry node to root node.
			$rootNode->appendChild($entryNode);
		}

		return $this->_domDocument->saveXML();
	}

/**
 * Initiated render progress.
 */
	protected function _initiateRender() {
		// Initiate DomDocument.
		$this->_domDocument = new DomDocument($this->getXmlVersion(), $this->getEncoding());

		// Remove unnecessary whitespaces.
		$this->_domDocument->preserveWhiteSpace = false;

		if ($this->getDebug()) {
			$this->_domDocument->formatOutput = true;
		}

		// Include stylesheet.
		if($this->getStyleSheet()) {
			$styleSheetNode = $this->_domDocument->createProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="' . $this->getStyleSheet() . '"');

			$this->_domDocument->appendChild($styleSheetNode);
		}

		// Add root node.
		$rootNode = $this->_domDocument->createElement($this->_rootNodeName);

		// Apply additional namespaces if needed.
		$this->_applyNamespaces($rootNode);

		// Append root node to main document.
		$this->_domDocument->appendChild($rootNode);
	}

/**
 * Renders nodes.
 *
 * @param DomElement $rootElement
 *	The element to render nodes into.
 * @param array $node
 *	The nodes to create.
 */
	protected function _renderNode ($rootNode, $nodes) {
		// Render nodes.
		foreach($nodes as $nodeName => $nodeValue) {
			// Check for child nodes.
			$hasChildNodes = is_array($nodeValue);

			// This is a sub node collection.
			// This means, we create multiple nodes of the $rootNode
			// in the $rootNode->parentNode.
			if (is_int($nodeName) && $hasChildNodes) {
				// Create new node from the root node name.
				$node = $this->_domDocument->createElement($rootNode->nodeName);

				// Append node to root parent node.
				$rootNode->parentNode->appendChild($node);
			}
			// We have a normale sub node situation here.
			else {
				// Create node for data.
				$node = $this->_domDocument->createElement($nodeName);

				// Append node to root node.
				$rootNode->appendChild($node);
			}

			// This one has child nodes and needs to append those.
			if ($hasChildNodes) {
				$this->_renderNode($node, $nodeValue);
			}
			// ... or it just has a value for it.
			else {
				$node->nodeValue = $nodeValue;
			}
		}
	}

/**
 * Applies namespaces saved in $this->_namespaces.
 *
 * @param DomElement $node
 *	The node to attach the namespaces to.
 */
	protected function _applyNamespaces($node) {
		$namespaces = $this->getNamespaces();

		foreach($namespaces as $prefix => $schema) {
			// Set default qualifiedName.
			$qualifiedName = 'xmlns';

			// Append prefix if needed.
			if(!empty($prefix)) {
				$qualifiedName .= ':' . $prefix;
			}

			$node->setAttributeNS('http://www.w3.org/2000/xmlns/', $qualifiedName, $schema);
		}
	}

/**
 * Get root node.
 *
 * @return DomNode
 */
	protected function _getRootNode(){
		$result = $this->_domDocument->getElementsByTagName($this->_rootNodeName);

		$result = $result->item(0);

		return $result;
	}



/********************************************
 |
 ############ Submitting Sitemap ############
 											|
 *******************************************/

/**
 * Submit sitemaps to all ping urls.
 *
 * @param array $sitemaps
 * 	The sitemap locations to submit.
 *
 * @return SitemapsOrg
 */
	public function sendPings($sitemaps) {
		if (empty($sitemaps)) {
			$pingUrls = $this->getPingUrls();

			if (!empty($pingUrls)) {
				foreach ($pingUrls as $identifier => $url) {
					$this->pingUrl($identifier);
				}
			}
			else {
				throw new SitemapException('There are no ping urls specified.');
			}
		}

		return $this;
	}

/**
 * Submit sitemap to a specific ping url.
 *
 * @param string $identifier
 * 	The identifier for the ping url.
 * @param array $sitemaps
 * 	The sitemap locations to submit.
 *
 * @return SitemapsOrg
 */
	public function sendPing($identifier, $sitemaps) {
		if (empty($sitemaps)) {
			throw new SitemapInvalidParameterException('No sitemaps for submit specified.');
		}

		if ($this->hasPingUrl($identifier)) {
			$pingUrl = $this->getPingUrl($identifier);

			foreach ($sitemaps as $sitemap) {
				if ($this->_submitSitemapToPingUrl($pingUrl, $sitemap));
			}
		}
		else {
			throw new SitemapInvalidParameterException('Ping url for "' . $identifier . '" not found.');
		}
	}

/**
 * Submits sitemap to given ping url.
 *
 * @param string $pingUrl
 * 	The url the sitemap should be submitted to.
 * @param string $sitemapUrl
 * 	The url of the sitemap to be submitted.
 *
 * @return boolean
 * 	Returns TRUE if sitemap was successfully submitted.
 */
	protected function _submitSitemapToPingUrl($pingUrl, $sitemapUrl) {
		if (!$this->validation_url($sitemapUrl)) {
			throw new SitemapInvalidParameterException('Sitemap url is not a valid url.');
		}

		// Initiate result.
		$result = FALSE;

		// Prepare url to be called.
		$callUrl = $pingUrl . urlencode($sitemapUrl);

		// Send ping via curl.
		if (function_exists('curl_init')) {
			$result = $this->_callWithCurl($callUrl);
		}
		else {
			$result = $this->_callWithFSockOpen($callUrl);
		}

		// Check for submit success.
		if ($result && $result === 200) {
			$result = TRUE;
		}
		else {
			throw new Exception('Sitemap could not be submitted to given ping url.');
		}

		return $result;
	}

/**
 * Call url with curl and return the response status.
 *
 * @param string $url
 * 	The url to call.
 *
 * @return mixed(boolean|int)
 * 	The http status code of the response. FALSE if something went wrong.
 */
	protected function _callWithCurl($url) {
		$result = FALSE;

		// Initiate curl.
		$c = curl_init();

		// Set timeout.
		$timeout = 10;
		curl_setopt($c, CURLOPT_CONNECTTIMEOUT, $timeout);

		// Set url for call.
		curl_setopt($c, CURLOPT_URL, $url);
		
		// Execute curl call.
		$success = curl_exec($ch);

		// Only on success, get the status.
		if ($success) {
			// Get info about last curl call.
			$result = curl_getinfo($c, CURLINFO_HTTP_CODE);
		}

		// Close curl.
		curl_close($ch);

		return (int) $result;
	}

/**
 * Call url with fsockopen and return the response status.
 *
 * @param string $url
 * 	The url to call.
 *
 * @return mixed(boolean|int)
 * 	The http status code of the response. FALSE if something went wrong.
 */
	protected function _callWithFSockOpen($url) {
		$result = FALSE;

		// Parse url.
		$url = parse_url($url);
		// Append query to path.
		$url['path'] .= '?'.$url['query'];

		// Setup fsockopen.
		$port = 80;
		$timeout = 10;
		$fso = fsockopen($url['host'], $port, $errno, $errstr, $timeout);
		
		// Proceed if connection was successfully opened.
		if ($fso) {
			// Create headers.
			$headers = 'GET ' . $url['path'] . 'HTTP/1.0' . "\r\n";
			$headers .= 'Host: ' . $url['host'] . "\r\n";
			$headers .= 'Connection: closed' . "\r\n";
			$headers .= "\r\n";

			// Write headers to socket.
			fwrite($fso, $headers);
		
			// Set timeout for stream read/write.
			stream_set_timeout($fso, $timeout);

			// Use a loop in case something unexpected happens.
			// I do not know what, but that why it is unexpected.			
			while (!feof($fso)){
				// 128 bytes is getting use the header with the http response code in it.				
				$buffer = fread($fso, 128);

				// Filter only the http status line (first line) and break loop on success.
				if(!empty($buffer) && ($buffer = substr($buffer, 0, strpos($buffer, "\r\n")))){
					break;
				}
			}

			// Match status.
			preg_match('/^HTTP.+\s(\d{3})/', $buffer, $match);
			// Extract status.
			list(, $status) = $match;

			$result = $status;
		}
		else {
			// @XXX: Throw exception here??
		}

		return (int) $result;
	}


/********************************************
 |
 ############ Validation Methods ############
 											|
 *******************************************/

/**
 * Checks if the given string is a valid url.
 *
 * @param string $url
 *	The url to be checked.
 *
 * @return boolean
 */
	public function validation_url($url) {
		return filter_var($url, FILTER_VALIDATE_URL);
	}

/**
 * Checks if the given string is a valid date for
 * a sitemap.
 *
 * Matches the following dates, which are all valid.
 *	1997
 *	1997-07
 *	1997-07-16
 *	1997-07-16T19:20+01:00
 *	1997-07-16T19:20:30+01:00
 *	1997-07-16T19:20:30.45+01:00
 *
 * @param string $date
 *	The date to be checked.
 *
 * @return boolean
 */
	public function validation_date($date) {
		$result = (
			// String date values.
			strtotime($date) !== FALSE ||
			// Timestamp values.
			strftime(
				self::DATETIME_FORMAT,
				$date
			) !== FALSE
		);

		return $result;
	}

/**
 * Checks if the given string is a valid change frequency for
 * a sitemap.
 *
 * @param string $changeFrequency
 *	The change frequency to be checked.
 *
 * @return boolean
 */
	public function validation_changeFrequency($changeFrequency) {
		// We use this, becasue it is easier to provde that this $changeFrequency valid than invalid.
		$result = FALSE;

		foreach ($this->_allowedChangeFrequencies as $frequency) {
			if ($changeFrequency === constant("self::$frequency")) {
				$result = TRUE;
				break;
			}
		}

		return $result;
	}

/**
 * Checks if the given string is a valid priority for
 * a sitemap.
 *
 * @param string $priority
 *	The priority to be checked.
 *
 * @return boolean
 */
	public function validation_priority($priority) {
		// Invalid until otherwise proven.
		$result = FALSE;

		if ($priority >= 0.0 && $priority <= 1.0) {
			$result = TRUE;
		}

		return $result;
	}

/**
 * Check if the given string is within the given max
 * max length.
 *
 * @param string $string
 *	The string to be checked.
 * @param int $maxLength
 *	Max length for $string.
 *
 * @return boolean
 */
	public function validation_maxLength($string, $maxLength) {
		// Invalid until otherwise proven.
		$result = FALSE;

		$result = (mb_strlen($string, $this->getEncoding()) <= $maxLength);

		return $result;
	}

/********************************************
 |
 ############ Content Callbacks #############
 											|
 *******************************************/

/**
 * Manipulates the given url.
 *
 * Replaces special characters in the url
 * and encodes the url.
 *
 * @param string $url
 *	The URL to manipulate.
 *
 * @return string
 *	The manipulated url.
 */
	public function content_url($url) {
		$result = $url;

		// Replace special characters.
		$searchReplace = array(
			'&' => '&amp;',
			"'" => '&apos;',
			'"' => '&quot;',
			'>' => '&gt;',
			'<' => '&lt;',
		);

		$result = str_replace(
			array_keys($searchReplace),
			array_values($searchReplace),
			$result
		);

		return $result;

		// @XXX:	This part is currently not in use, because
		// 			filter_var() would not let urls with e.g. 'ä' pass. 
		// 			So if the urls do not come valid in here, they will not
		//			come in here.
		//			Suggestions appreciated.

		// Parse url.
		$parsedUrl = parse_url($result);

		// Process path.
		$path = explode('/', $parsedUrl['path']);

		foreach ($path as $index => $value) {
			$path[$index] = $this->_encodeUrl($value);
		}

		$parsedUrl['path'] = implode('/', $path);

		// Process query.
		$query = explode('&', $parsedUrl['query']);

		foreach($query as $index => $value) {
			// Only encode value of GET parameters.
			$value = explode('=', $value);
			$value[1] = $this->_encodeUrl($value[1]);
			$value = implode('=', $value);

			$query[$index] = $value;
		}

		$parsedUrl['query'] = implode('&amp;', $query);

		// Process fragment.
		if (isset($parsedUrl['fragment'])) {
			$parsedUrl['fragment'] = $this->_encodeUrl($parsedUrl['fragment']);
		}

		// Use PECL_HTTP if available.
		if (function_exists('http_build_url')) {
			$result = http_build_url(null, $parsedUrl);
		}
		else {
			$result = $parsedUrl['scheme'] . '://';

			if (isset($parsedUrl['user'])){
				$result .= $parsedUrl['user'];

				if (isset($parsedUrl['pass'])) {
					$result .= ':' . $parsedUrl['user'];
				}

				$result .= '@';
			}

			$result .= $parsedUrl['host'];

			if (isset($parsedUrl['path'])){
				$result .= $parsedUrl['path'];
			}

			if (isset($parsedUrl['query'])){
				$result .= '?' . $parsedUrl['query'];
			}

			if (isset($parsedUrl['fragment'])){
				$result .= '#' . $parsedUrl['fragment'];
			}
		}

		return $result;
	}

/**
 * Encodes urls by RFC3986 standard.
 *
 * @param string $url
 *	The url to encode.
 */
	protected function _encodeUrl($url) {
		// Encode url after RFC3986 (as of PHP 5.3).
		$result = rawurlencode($url);

		// Since PHP 5.3 rawurlencode is completly RFC3986, we need
		// anohter replacement for php versions before 5.3.
		if(version_compare(PHP_VERSION, '5.3.0', '<')) {
			// Restore tildes.
			$result = str_replace('%E7', '~', $result);
		}

		return $result;
	}

/**
 * Manipulates the given date.
 *
 * The date will be converted to the appropriate format.
 *
 * @param string $datetime
 *	The date to manipulate.
 *
 * @return string
 *	The manipulated date.
 */
	public function content_date($datetime) {
		$result = $datetime;
		// @XXX:	This would validate a W3C date time, which is always valid for
		// 			sitemaps. But it would also lead to lastmod dates like "2009"
		//			without any time and date, if used.
		//			Any thoughts on this are appreciated.
		/*
		if (
			preg_match(
				'/^\d+(-\d{2}(-\d{2}(T\d{2}:\d{2}(:\d{2}(\.\d{2})?)?\+\d{2}:\d{2})?)?)?$/',
				$datetime,
				$match
			) === 1
		) {
			$result = $datetime;
		}
		else {
		*/
		// Get the unix timestamp.
		$result = strtotime($result);

		// We have already a timestamp in $datetime.
		if($result === FALSE) {
			$result = '@' . $datetime;
		}

		$result = new DateTime(
			$result,
			new DateTimeZone('Europe/London')
		);
		
		$result = $result->format(DateTime::W3C);
		/*
		} // Belongs to commented if conditional above.
		*/

		return $result;
	}

/********************************************
 |
 ####### Getter / Setters / Checkers ########
 											|
 *******************************************/

/**
 * Sets xml version.
 *
 * @param string $xmlVersion
 *
 * @return SitemapsOrg
 */
	public function setXmlVersion($xmlVersion) {
		$this->_xmlVersion = $xmlVersion;

		return $this;
	}

/**
 * Gets the xml version.
 *
 * @return string
 */
	public function getXmlVersion() {
		return $this->_xmlVersion;
	}

/**
 * Sets encoding.
 *
 * Since the encoding of the sitemaps needs to be "utf-8"
 * ({@link http://www.sitemaps.org/protocol.html#escaping Definition}) there is no setter method.
 *
 * @param string $encoding
 *
 * @return SitemapsOrg
 */
/*
	public function setEncoding($encoding) {
		$this->_encoding = $encoding;

		return $this;
	}
*/

/**
 * Gets the encoding.
 *
 * @return string
 */
	public function getEncoding() {
		return $this->_encoding;
	}

/**
 * Sets current style sheet.
 *
 * @param mixed(string|boolean[FALSE])
 *	Set to url or to false if not used anymore.
 *
 * @return SitemapsOrg
 */
	public function setStyleSheet($styleSheet) {
		$this->_styleSheet = $styleSheet;

		return $this;
	}

/**
 * Gets current style sheet.
 *
 * @return mixed(string|boolean)
 */
	public function getStyleSheet() {
		return $this->_styleSheet;
	}

/**
 * Gets dom document.
 *
 * @return DomDocument
 */
	public function getDomDocument() {
		return $this->_domDocument;
	}

/**
 * Sets namespaces.
 *
 * @param string $namespaces
 *
 * @return SitemapsOrg
 */
	public function setNamespaces($namespaces) {
		if (is_array($namespaces)) {
			// We do not need to empty namespaces here, because equal
			// prefixes will be overwritten and new ones added.
			foreach ($namespaces as $prefix => $url) {
				$this->addNamespace($url, $prefix);
			}
		}
		else {
			throw new SitemapInvalidParameterException(
				'An array of namespaces is needed.'
			);
		}

		return $this;
	}

/**
 * Gets namespaces.
 *
 * @return array
 */
	public function getNamespaces() {
		return $this->_namespaces;
	}

/**
 * Adds namespace.
 *
 * @param string $namespaceUrl
 *	The url for the namespace schema.
 * @param string $prefix
 *	The prefix to use in the sitemap.
 *
 * @return SitemapsOrg
 */
	public function addNamespace($namespaceUrl, $prefix = '') {
		$this->_namespaces[$prefix] = $namespaceUrl;

		return $this;
	}

/**
 * Gets specific namespace schema.
 *
 * @param string $prefix
 *	The prefix to search for.
 *
 * @return mixed(string|NULL)
 */
	public function getNamespaceSchema($prefix) {
		$result = NULL;

		if ($this->hasNamespaceSchema($prefix)) {
			$result = $this->getNamespaces();
			$result = $result[$prefix];
		}

		return $result;
	}

/**
 * Checks if schema for the given prefix is
 * set.
 *
 * @param string $prefix
 *	The prefix to search for.
 *
 * @return boolean
 */
	public function hasNamespaceSchema($prefix) {
		if (isset($this->_namespaces[$prefix])) {
			return TRUE;
		}

		return FALSE;
	}

/**
 * Sets allowed entry nodes.
 *
 * @param string $entryNodes
 *	The nodes to set.
 *
 * @return SitemapsOrg
 */
	public function setAllowedEntryNodes($entryNodes) {
		if (is_array($entryNodes)) {
			// Reset allowed entry nodes.
			$this->_allowedEntryNodes = null;

			foreach ($entryNodes as $name => $config) {
				$this->addAllowedEntryNode($name, $config);
			}
		}
		else {
			throw new SitemapInvalidParameterException(
				'An array of allowed entry nodes is needed.'
			);
		}

		return $this;
	}

/**
 * Gets allowed entry nodes.
 *
 * @return array
 */
	public function getAllowedEntryNodes() {
		return $this->_allowedEntryNodes;
	}

/**
 * Adds allowed entry node.
 *
 * This function should be used to extend the existing
 * allowed entry nodes in the init function of every
 * extending class, if it has additional allowed nodes.
 *
 * @param string $name
 *	The name of the entry node.
 * @param array $config
 *	The configuration for the entry node.
 *	@see self::$_allowedEntryNodes for documentation.
 *
 * @return SitemapsOrg
 */
	public function addAllowedEntryNode($name, $config) {
		if (isset($config['validationCallback']) && !isset($config['fallbackValue'])) {
			throw new SitemapInvalidParameterException(
				'A default value is needed for allowed entry node validation callbacks.'
			);
		}

		// Add allowed entry node, while overwriting old ones.
		if (is_string($name)) {
			$this->_allowedEntryNodes[$name] = $config;
		}
		elseif (is_array($name)) {
			// Prepare the merge array.
			$mergeArray = array();
			// We need to set a reference to access the deepest level.
			$deepestLayer = &$mergeArray;
			// Deepest level of existing config.
			$deepestExistingLayer = &$this->_allowedEntryNodes;
			// Common level refrence in new config.
			$deepestCommonLevel = null;
			// First run of loop indicator
			$firstRun = TRUE;

			foreach ($name as $sub) {
				// Create sub array.
				$deepestLayer[$sub] = array();
				$deepestLayer = &$deepestLayer[$sub];

				// Check if the given index exists in existing config, else we set the common level.
				if (is_null($deepestCommonLevel)) {
					// Root level.
					if ($firstRun && isset($deepestExistingLayer[$sub])) {
						// Set deepest existing layer.
						$deepestExistingLayer = &$deepestExistingLayer[$sub];
					}
					// Children of root nodes.
					elseif (
						isset($deepestExistingLayer[self::ALLOWED_ENTRY_CHILDREN_INDEX]) &&
						isset($deepestExistingLayer[self::ALLOWED_ENTRY_CHILDREN_INDEX][$sub])
					) {
						// Set deepest existing layer.
						$deepestExistingLayer = &$deepestExistingLayer[self::ALLOWED_ENTRY_CHILDREN_INDEX][$sub];
					}
					elseif (!isset($deepestExistingLayer[self::ALLOWED_ENTRY_CHILDREN_INDEX][$sub])) {
						$deepestCommonLevel = &$mergeArray[$sub];
					}
				}
			}

			// Set deepest common level if not already set.
			if (is_null($deepestCommonLevel)) {
				$deepestCommonLevel = &$deepestLayer;
			}
			$deepestLayer = $config;			

			$deepestExistingLayer = array_merge(
				$deepestExistingLayer,
				$deepestCommonLevel
			);

			// Set first run indicator to FALSE.
			// An additional IF would just blow this up.
			$firstRun = FALSE;
		}

		return $this;
	}

/**
 * Adds allowed entry nodes.
 *
 * This function should be used to extend the existing
 * allowed entry nodes in the init function of every
 * extending class, if it has additional allowed nodes.
 *
 * @param string $entryNodes
 *	The nodes to set.
 *
 * @return SitemapsOrg
 */
	public function addAllowedEntryNodes($nodes) {
		if (is_array($nodes)) {
			foreach ($nodes as $name => $config) {
				$this->addAllowedEntryNode($name, $config);
			}
		}
		else {
			throw new SitemapInvalidParameterException(
				'An array of allowed entry nodes is needed.'
			);
		}

		return $this;
	}

/**
 * Gets specific allowed entry config.
 *
 * @param string $entryNodeName
 *	The node name to search for.
 *
 * @return mixed(string|NULL)
 */
	public function getAllowedEntryConfig($entryNodeName) {
		$result = NULL;

		if ($this->hasAllowedEntryConfig($entryNodeName)) {
			$result = $this->getAllowedEntryNodes();

			// If node name is a string, just return the result.
			if (is_string($entryNodeName)) {
				$result = $result[$entryNodeName];
			}
			else {
				// First run indicator.
				$firstRun = TRUE;

				// Run through node names and reset $result each time.
				foreach ($entryNodeName as $nodeName) {
					// Root level. Only on first run.
					if ($firstRun && isset($result[$nodeName])) {
						$result = $result[$nodeName];	
					}
					elseif(
						isset($result[self::ALLOWED_ENTRY_CHILDREN_INDEX]) &&
						isset($result[self::ALLOWED_ENTRY_CHILDREN_INDEX][$nodeName])
					) {
						$result = $result[self::ALLOWED_ENTRY_CHILDREN_INDEX][$nodeName];
					}					

					// Set first run indicator to FALSE.
					// An additional IF would just blow this up.
					$firstRun = FALSE;
				}
			}
		}

		return $result;
	}

/**
 * Checks if config for the given allowed entry
 * node has already been set.
 *
 * @param string $entryNodeName
 *	The node name to search for.
 *
 * @return boolean
 */
	public function hasAllowedEntryConfig($entryNodeName) {
		// Initiate result.
		$result = FALSE;

		// If not an array, but a simple string, its pretty easy.
		if(is_string($entryNodeName)) {
			$result = isset($this->_allowedEntryNodes[$entryNodeName]);
		}
		else{
			// Get $entryNodeName length for loop.
			$entryNodeNameLength = count($entryNodeName);
			$allowedEntryNodes = $this->_allowedEntryNodes;

			for ($i = 0; $i < $entryNodeNameLength; $i++){
				$nodeName = $entryNodeName[$i];

				// Check if node name is in the currenty allowed entry nodes.
				if ($i === 0 && isset($allowedEntryNodes[$nodeName])) {
					// Reset $allowedEntryNodes for next loop run.
					$allowedEntryNodes = $allowedEntryNodes[$nodeName];
					// Set the result to true.
					$result = TRUE;
				}
				elseif (
					$i > 0 &&
					isset($allowedEntryNodes[self::ALLOWED_ENTRY_CHILDREN_INDEX]) &&
					isset($allowedEntryNodes[self::ALLOWED_ENTRY_CHILDREN_INDEX][$nodeName])
				) {
					// Reset $allowedEntryNodes for next loop run.
					$allowedEntryNodes = $allowedEntryNodes[self::ALLOWED_ENTRY_CHILDREN_INDEX][$nodeName];
					// Set the result to true.
					$result = TRUE;
				}
				else{
					// We can not find the wanted node name so the result is
					// false and the loops gets aborted.
					$result = FALSE;
					break;
				}
			}
		}

		return $result;
	}

/**
 * Gets the currently saved entries.
 *
 * @return array
 */
	public function getEntries() {
		return $this->_entries;
	}

/**
 * Gets the currently saved parent node names.
 *
 * @return array
 */
	protected function _getParentNodeNames() {
		return $this->_parentNodeNames;
	}

/**
 * Adds an entry to the current parent node names.
 *
 * @param string $nodeName
 *	The nodename to be added to parent node names.
 *
 * @return SitemapsOrg
 */
	public function _addParentNodeName($nodeName) {
		array_push(
			$this->_parentNodeNames,
			$nodeName
		);

		return $this;
	}

/**
 * Removes the last entry from the current parent node names.
 *
 * @return SitemapsOrg
 */
	public function _removeLastParentNodeName() {
		array_pop($this->_parentNodeNames);

		return $this;
	}

/**
 * Resets the parent node names array.
 *
 * @return SitemapsOrg
 */
	public function _resetParentNodeNames() {
		$this->_parentNodeNames = array();

		return $this;
	}

/**
 * Sets ping urls.
 *
 * @param string $pingUrls
 *
 * @return SitemapsOrg
 */
	public function setPingUrls($pingUrls) {
		if (is_array($pingUrls)) {
			// We do not need to empty namespaces here, because equal
			// prefixes will be overwritten and new ones added.
			foreach ($pingUrls as $identifier => $url) {
				$this->addPingUrl($identifier, $url);
			}
		}
		else {
			throw new SitemapInvalidParameterException(
				'An array of ping urls is needed.'
			);
		}

		return $this;
	}

/**
 * Gets ping urls.
 *
 * @return array
 */
	public function getPingUrls() {
		return $this->_namespaces;
	}

/**
 * Adds ping url.
 *
 * @param string $identifier
 *	The identifier used for the ping url.
 * @param string $pingUrl
 *	The ping url.
 *
 * @return SitemapsOrg
 */
	public function addPingUrl($identifier, $pingUrl) {
		if (!$this->validation_url($pingUrl)) {
			throw new SitemapInvalidParameterException('The given ping url is not a valid url.');
		}

		$this->_pingUrls[$identifier] = $pingUrl;

		return $this;
	}

/**
 * Gets specific ping url.
 *
 * @param string $identifier
 *	The identifier to search for.
 *
 * @return mixed(string|NULL)
 */
	public function getPingUrl($identifier) {
		$result = NULL;

		if ($this->hasPingUrl($identifier)) {
			$result = $this->getPingUrls();
			$result = $result[$identifier];
		}

		return $result;
	}

/**
 * Checks if the ping url for the given identifier is
 * set.
 *
 * @param string $identifier
 *	The identifier to search for.
 *
 * @return boolean
 */
	public function hasPingUrl($identifier) {
		if (isset($this->_pingUrls[$identifier])) {
			return TRUE;
		}

		return FALSE;
	}

/**
 * Sets trigger for validation.
 *
 * @param boolean $useValidation
 *	The trigger value.
 *
 * @return SitemapsOrg
 */
	public function setUseValidation($useValidation) {
		$this->_useValidation = $useValidation;

		return $this;
	}

/**
 * Get trigger for validation.
 *
 * @return boolean
 */
	public function getUseValidation() {
		return $this->_useValidation;
	}

/**
 * Enables validation.
 *
 * @return SitemapsOrg
 */
	public function enableValidation() {
		$this->setUseValidation(TRUE);

		return $this;
	}

/**
 * Disables validation.
 *
 * @return boolean
 */
	public function disableValidation() {
		$this->setUseValidation(FALSE);

		return $this;
	}

/**
 * Sets trigger for content callbacks.
 *
 * @param boolean $useContentCallbacks
 *	The trigger value.
 *
 * @return SitemapsOrg
 */
	public function setUseContentCallbacks($useContentCallbacks) {
		$this->_useContentCallbacks = $useContentCallbacks;

		return $this;
	}

/**
 * Get trigger for content callbacks.
 *
 * @return boolean
 */
	public function getUseContentCallbacks() {
		return $this->_useContentCallbacks;
	}

/**
 * Enables content callback.
 *
 * @return SitemapsOrg
 */
	public function enableContentCallbacks() {
		$this->setUseContentCallbacks(TRUE);

		return $this;
	}

/**
 * Disables content callbacks.
 *
 * @return boolean
 */
	public function disableContentCallbacks() {
		$this->setUseContentCallbacks(FALSE);

		return $this;
	}

/**
 * Sets debug mode.
 *
 * @param boolean $debug
 *	Either true or false, to turn debug on or off.
 *
 * @return SitemapsOrg
 */
	public function setDebug($debug) {
		$this->_debug = (bool) $debug;

		return $this;
	}

/**
 * Gets debug.
 *
 * @return boolean
 */
	public function getDebug() {
		return $this->_debug;
	}

}

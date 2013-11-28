<?php
/**
 * Google News Sitemap file.
 * 
 * Contains the definition of a Google News Sitemap.
 *
 * @author Lars Lenecke <incode@func0der.de>
 * @version 0.5
 * @package Sitemap
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . '../Google.php';

class Sitemap_SitemapsOrg_Google_News extends Sitemap_SitemapsOrg_Google{
 /**
  * Maximum entries for a sitemap.
  *
  * @const int
  */
 	const MAXIMUM_ENTRIES = 1000;

/**
 * Constructor.
 */
	public function __construct() {
		// Call parent.
		parent::__construct();

		// Use images.
		self::useImages();

		// Add namespace.
		$this->addNamespace(
			'http://www.google.com/schemas/sitemap-news/0.9',
			'news'
		);

		// Add allowed entry nodes.
		$newAllowedEntryNodes = array(
			'news' => array(
				self::ALLOWED_ENTRY_CHILDREN_INDEX => array(
					'publication' => array(
						self::ALLOWED_ENTRY_CHILDREN_INDEX => array(
							'name' => array(
								'fallbackValue' => self::VALIDATION_EXCEPTION,
								'prefix' => 'news',
								'required' => TRUE,
								'validationCallback' => 'validation_publicationName',
							),
							'language' => array(
								'fallbackValue' => self::VALIDATION_EXCEPTION,
								'prefix' => 'news',
								'required' => TRUE,
								'validationCallback' => 'validation_ISO639',
							),
						),
						'prefix' => TRUE,
					),
					'access' => array(
						'fallbackValue' => self::VALIDATION_EXCEPTION,
						'prefix' => TRUE,
						'validationCallback' => 'validation_accessCondition',
					),
					'genres' => array(
						'fallbackValue' => self::VALIDATION_EXCEPTION,
						'prefix' => TRUE,
						'validationCallback' => 'validation_genres',
					),
					'publication_date' => array(
						'contentCallback' => array('content_date'),
						'fallbackValue' => self::VALIDATION_EXCEPTION,
						'prefix' => TRUE,
						'required' => TRUE,
						'validationCallback' => array('validation_date'),
					),
					'title' => array(
						'prefix' => TRUE,
					),
					'geo_location' => array(
						'prefix' => TRUE,
					),
					'keywords' => array(
						'prefix' => TRUE,
					),
					'stock_tickers' => array(
						'prefix' => TRUE,
					),
				),
				'prefix' => 'news',
			),
		);

		$this->addAllowedEntryNodes($newAllowedEntryNodes);
	}


/********************************************
 |
 ############ Validation Methods ############
 											|
 *******************************************/

/**
 * Checks string for trailing parentheticals.
 *
 * @param string $name
 *	The name to check.
 *
 * @return boolean
 */
	public function validation_publicationName($name) {
		// Name is valid until otherwise proven.
		$result = TRUE;

		// We need the last sign in the string.
		if (substr($name, -1) === ')') {
			$result = FALSE;
		}

		return $result;
	}

/**
 * Checks for ISO 639 language codes.
 *
 * This method only check for valid length and structure of language codes.
 * It does not check if a language code does really exist.
 * Language codes considered as valid either:
 * 	- have 2 letters
 * 	- have 3 letters
 * 	- are exactly zh-cw (for Simplified Chinese)
 * 	- are exactly zh-tw (for Traditional Chinese)
 *
 * @param string $code
 *	The code to check.
 *
 * @return boolean
 */
	public function validation_ISO639($code) {
		// Code is invalid until otherwise proven.
		$result = FALSE;

		if (preg_match('/^([a-z]{2}|[a-z]{3}|zh-(cw|tw))$/i', $code) === 1) {
			$result = TRUE;
		}

		return $result;
	}

/**
 * Checks news access condition.
 *
 * Valid entries are:
 * 	- Registration
 * 	- Subscription
 *
 * @param string $accessCondition
 *	The access condition to check.
 *
 * @return boolean
 */
	public function validation_accessCondition($accessCondition) {
		// Code is invalid until otherwise proven.
		$result = FALSE;

		if (preg_match('/^(a-z{2}|a-z{3}|zh-(cw|tw))$/i', $code) === 1) {
			$result = TRUE;
		}

		return $result;
	}

/**
 * Checks if the given value contains only valid genres.
 *
 * @param string $value
 *	A list of genres seperated by commas.
 *
 * @return boolean
 */
	public function validation_genres($value) {
		// We set this to true at first, because it easier to use in loops.
		$result = TRUE;

		$allowedGenres = array(
			'PressRelease',
			'Satire',
			'Blog',
			'OpEd',
			'Opinion',
			'UserGenerated',
		);

		$value = implode(', ', $value);

		foreach ($value as $code) {
			if (!in_array($code, $allowedGenres)) {
				$result = FALSE;
				break;
			}
		}

		return $result;
	}
}

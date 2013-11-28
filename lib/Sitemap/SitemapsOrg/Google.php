<?php
/**
 * Google Sitemap file.
 * 
 * Contains the definition of a Google Sitemap.
 *
 * @author Lars Lenecke <incode@func0der.de>
 * @version 0.5
 * @package Sitemap
 */
require_once __DIR__ . DIRECTORY_SEPARATOR . '../SitemapsOrg.php';

// @TODO: Currenty "attributes" for nodes like "restriction" or "gallery_loc" are NOT supported. We need support for this.
class Sitemap_SitemapsOrg_Google extends Sitemap_SitemapsOrg{
 /**
  * Maximum filesize for a sitemap in bytes.
  *
  * @const int
  */
	const MAXIMUM_FILESIZE = 52428800;

/**
 * Constructor.
 */
	public function __construct() {
		// Call parent constructor.
		parent::__construct();

		$this->addPingUrl(
			'google',
			'http://www.google.com/webmasters/tools/ping?sitemap='
		);
	}

/**
 * Alters class for images in sitemap.
 *
 * @return Sitemap_SitemapsOrg_Google
 */
	public function useImages() {
		// Add namespace.
		$this->addNamespace(
			'http://www.google.com/schemas/sitemap-image/1.1',
			'image'
		);

		// Add allowed entry nodes.
		$newAllowedEntryNodes = array(
			'image' => array(
				self::ALLOWED_ENTRY_CHILDREN_INDEX => array(
					'loc' => array(
						'contentCallback' => 'content_url',
						'fallbackValue' => self::VALIDATION_EXCEPTION,
						'prefix' => 'image',
						'required' => TRUE,
						'validationCallback' => 'validation_url',
					),
					'caption' => array(
						'prefix' => TRUE,
					),
					'geo_location' => array(
						'prefix' => TRUE,
					),
					'title' => array(
						'prefix' => TRUE,
					),
					'license' => array(
						'prefix' => TRUE,
					),
				),
				'prefix' => 'image',
			),
		);

		$this->addAllowedEntryNodes($newAllowedEntryNodes);

		return $this;
	}

/**
 * Alters class for video in sitemap.
 *
 * @return Sitemap_SitemapsOrg_Google
 */
	public function useVideos() {
		// Add namespace.
		$this->addNamespace(
			'http://www.google.com/schemas/sitemap-video/1.1',
			'image'
		);

		// Add allowed entry nodes.
		$newAllowedEntryNodes = array(
			'video' => array(
				self::ALLOWED_ENTRY_CHILDREN_INDEX => array(
					'thumbnail_loc' => array(
						'contentCallback' => array('content_url'),
						'fallbackValue' => self::VALIDATION_EXCEPTION,
						'prefix' => TRUE,
						'required' => TRUE,
						'validationCallback' => array('validation_video_thumbnail'),
					),
					'title' => array(
						'contentCallback' => array('content_htmlEncodedText'),
						'fallbackValue' => self::VALIDATION_EXCEPTION,
						'prefix' => TRUE,
						'required' => TRUE,
						'validationCallback' => array('validation_maxLength', 50),
					),
					'description' => array(
						'contentCallback' => array('content_htmlEncodedText'),
						'fallbackValue' => self::VALIDATION_EXCEPTION,
						'prefix' => TRUE,
						'required' => TRUE,
						'validationCallback' => array('validation_maxLength', 2048),
					),
					// @XXX:	Since it is currently not possible to validate depending on 
					//			other values, use either self::useContentLoc() or
					//			self::usePlayerLoc() to "unrequire" the other one.
					'content_loc' => array(
						'contentCallback' => array('content_url'),
						'fallbackValue' => self::VALIDATION_EXCEPTION,
						'prefix' => TRUE,
						'required' => TRUE,
						'validationCallback' => array('validate_url'),
					),
					// @XXX:	Since it is currently not possible to validate depending on 
					//			other values, use either self::useContentLoc() or
					//			self::usePlayerLoc() to "unrequire" the other one.
					'player_loc' => array(
						'contentCallback' => array('content_url'),
						'fallbackValue' => self::VALIDATION_EXCEPTION,
						'prefix' => TRUE,
						'required' => TRUE,
						'validationCallback' => array('validate_url'),
					),
					'duration' => array(
						'fallbackValue' => self::VALIDATION_EXCEPTION,
						'prefix' => TRUE,
						'validationCallback' => array('validation_between', 0, 28800, TRUE),
					),
					'expiration_date' => array(
						'contentCallback' => array('content_date'),
						'fallbackValue' => self::VALIDATION_EXCEPTION,
						'prefix' => TRUE,
						'validationCallback' => array('validation_date'),
					),
					'rating' => array(
						'fallbackValue' => self::VALIDATION_EXCEPTION,
						'prefix' => TRUE,
						'validationCallback' => array('validation_between', 0, 5, TRUE),
					),
					'view_count' => array(
						'prefix' => TRUE,
					),
					'publication_date' => array(
						'contentCallback' => array('content_date'),
						'fallbackValue' => self::VALIDATION_EXCEPTION,
						'prefix' => TRUE,
						'validationCallback' => array('validation_date'),
					),
					'family_friendly' => array(
						'contentCallback' => array('content_boolean'),
						'fallbackValue' => self::VALIDATION_EXCEPTION,
						'prefix' => TRUE,
						'validationCallback' => array('validation_boolean'),
					),
					// @XXX:	Similar problem as with player/content_loc.
					//			A maximum of 32 tags is allowed per video, but we
					//			can not access other than the current entry while validation.
					// @XXX:	Idea to solve the above problem:
					//				Put all tags in an array, because they would be overwritten by each other
					//				in the current configuration anyway. So we can output at least several ones
					//				and also validate their number.
					'tag' => array(
						'prefix' => TRUE,
					),
					'category' => array(
						'fallbackValue' => self::VALIDATION_EXCEPTION,
						'prefix' => TRUE,
						'validationCallback' => array('validation_maxLength', 256),
					),
					// @XXX:	It should be considered accepting arrays here, instead of only a list
					//			seperated by spaces.
					'restriction' => array(
						'fallbackValue' => self::VALIDATION_EXCEPTION,
						'prefix' => TRUE,
						'validationCallback' => array('validation_ISO3166'),
					),
					'gallery_loc' => array(
						'contentCallback' => array('content_url'),
						'fallbackValue' => self::VALIDATION_EXCEPTION,
						'prefix' => TRUE,
						'validationCallback' => array('validate_url'),
					),
					// @XXX:	Currently NOT! supported, due to the lack of attribute support.
					/*
					'price' => array(
						'contentCallback' => array('content_url'),
						'fallbackValue' => self::VALIDATION_EXCEPTION,
						'validationCallback' => array('is_numeric'),
					),*/
					'requires_subscription' => array(
						'contentCallback' => array('content_boolean'),
						'fallbackValue' => self::VALIDATION_EXCEPTION,
						'prefix' => TRUE,
						'validationCallback' => array('validation_boolean'),
					),
					'uploader' => array(
						'prefix' => TRUE,
					),
					'platform' => array(
						'fallbackValue' => self::VALIDATION_EXCEPTION,
						'prefix' => TRUE,
						'validationCallback' => array('validation_platform'),
					),
					'live' => array(
						'contentCallback' => array('content_boolean'),
						'fallbackValue' => self::VALIDATION_EXCEPTION,
						'prefix' => TRUE,
						'validationCallback' => array('validation_boolean'),
					),
				),
				'prefix' => 'video',
			),
		);

		$this->addAllowedEntryNodes($newAllowedEntryNodes);

		return $this;
	}

/**
 * Unrequires player_loc in self::$allowedEntryNodes.
 *
 * @return Sitemap_SitemapsOrg_Google
 */
	public function useContentLoc() {
		$playerLoc = $this->getAllowedEntryConfig(array('video', 'player_loc'));

		$playerLoc['required'] = FALSE;

		$this->addAllowedEntryNode(array('video', 'player_loc'), $playerLoc);

		return $this;
	}

/**
 * Unrequires content_loc in self::$allowedEntryNodes.
 *
 * @return Sitemap_SitemapsOrg_Google
 */
	public function usePlayerLoc() {
		$playerLoc = $this->getAllowedEntryConfig(array('video', 'content_loc'));

		$playerLoc['required'] = FALSE;

		$this->addAllowedEntryNode(array('video', 'content_loc'), $playerLoc);

		return $this;
	}


/********************************************
 |
 ############ Validation Methods ############
 											|
 *******************************************/

/**
 * Checks if the image has the right dimensions.
 *
 * @param string $url
 *	The thumbnail to check for sizes.
 *
 * @return boolean
 */
	public function validation_video_thumbnail($url) {
		$result = TRUE;

		// Check for a valid url.
		$result = $this->validation_url($url);

		if ($result && ini_get('allow_url_fopen') === "1") {
			$imageSize = getimagesize($url);

			list($width, $height) = $imageSize;

			if (
				// Invalid width.
				($width < 160 || $width > 1920) ||
				($height < 90 || $height > 1080)
			)
			{
				$result = TRUE;
			}
		}
		else {
			// @XXX: I do not know, what to do here. Suggestions?
		}

		return $result;
	}

/**
 * Checks if the given integer is between two numbers.
 *
 * Please not, that if $include is not set to TRUE, $min and $max
 * are not valid values for $number.
 *
 * @param int $number
 *	The number to check.
 * @param int $min
 *	The lower number.
 * @param int $max
 *	The higher number.
 * @param boolean $include
 *	If set to TRUE, $min and $max are valid values for $number.
 *
 * @return boolean
 */
	public function validation_between($number, $min, $max, $include = FALSE) {
		// Number is valid until otherwise proved.
		$result = TRUE;

		// Cast number. Just in case.
		$number = intval($number);

		// Determine if $min and $max are valid values for $number.
		if ($include === TRUE) {
			$result = ($number >= $min && $number <= $max);
		}
		else {
			$result = ($number > $min && $number < $max);
		}

		return $result;
	}

/**
 * Checks if the given value is a valid boolean.
 *
 * Please not, that if "yes" and "no" are considered to be valid
 * booleans as well in default configuration.
 * Set $alternative to FALSE to turn this behavior off.
 *
 * @param mixed $value
 *	The value to check.
 * @param boolean $alternative
 *	If set to TRUE, "yes" and "no" are considered to be valid booleans.
 *
 * @return boolean
 */
	public function validation_boolean($value, $alternative = TRUE) {
		$result = TRUE;

		$result = ($value === TRUE || $value === FALSE);

		if (!$result && $alternative === TRUE) {
			$result = ($value === 'yes' && $value === 'no');
		}

		return $result;
	}

/**
 * Checks if the given value has only valid ISO 3166 country codes.
 *
 * This method only check for valid length and structure of country codes.
 * It does not check if a country code does really exist.
 *
 * @param string $value
 *	A list of ISO 3166 country codes seperated by spaces.
 *
 * @return boolean
 */
	public function validation_ISO3166($value) {
		// We set this to true at first, because it easier to use in loops.
		$result = TRUE;

		$value = implode(' ', $value);

		foreach ($value AS $code) {
			if (preg_match('/^(a-z{2}|a-z{3}|\d{3})$/i', $code) !== 1) {
				$result = FALSE;
				break;
			}
		}

		return $result;
	}

/**
 * Checks if the given value contains only valid platforms.
 *
 * @param string $value
 *	A list of platforms seperated by spaces.
 *
 * @return boolean
 */
	public function validation_platform($value) {
		// We set this to true at first, because it easier to use in loops.
		$result = TRUE;

		$allowedPlatforms = array(
			'web',
			'mobile',
			'tv',
		);

		$value = implode(' ', $value);

		foreach ($value AS $code) {
			if (!in_array($code, $allowedPlatforms)) {
				$result = FALSE;
				break;
			}
		}

		return $result;
	}

/********************************************
 |
 ############ Content Callbacks #############
 											|
 *******************************************/

/**
 * Converts string to html encoded text.
 *
 * Removes html tags and encodes all specials
 * chars.
 *
 * @param string $string
 *	The string to be cleaned up.
 * @param boolean $double_encode
 *	Used for htmlentities() function.
 *
 * @param string
 *	Cleaned up string.
 */
	public function content_htmlEncodedText($string, $double_encode = true) {
		$result = $string;

		// Remove html tags.
		$result = strip_tags($result);

		// Encode special chars.
		$result = $this->content_encodedText($result, $double_encode);

		return $result;
	}

/**
 * Converts booleans to "yes" and "no".
 *
 * @param mixed $value
 *	The value to check.
 *
 * @return string
 *	The manipulated string.
 */
	public function content_boolean($value) {
		$result = $value;

		if ($result === TRUE) {
			$result = 'yes';
		}
		elseif ($result === FALSE) {
			$result = 'no';
		}

		return $result;
	}

}
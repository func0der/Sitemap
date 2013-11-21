<?php
/**
 * Google Image Sitemap file.
 * 
 * Contains the definition of a Google Image Sitemap.
 *
 * @author Lars Lenecke <incode@func0der.de>
 * @version 0.5
 * @package Sitemap
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . '../Google.php';

class Sitemap_SitemapsOrg_Google_Image extends Sitemap_SitemapsOrg_Google{
/**
 * Constructor.
 */
	public function __construct() {
		// Call parent.
		parent::__construct();

		// Add image definitions.
		$this->useImages();
	}
}
<?php
/**
 * Sitemap interface file.
 * 
 * @author Lars Lenecke <incode@func0der.de>
 * @version 0.5
 * @package Sitemap
 */

// @TODO: Write tests for all of this.

// Require exceptions.
require 'SitemapException' . DIRECTORY_SEPARATOR . 'SitemapValidationException.php';
require 'SitemapException' . DIRECTORY_SEPARATOR . 'SitemapInvalidParameterException.php';
require 'SitemapException' . DIRECTORY_SEPARATOR . 'SitemapInvalidCallbackException.php';
require 'SitemapException' . DIRECTORY_SEPARATOR . 'SitemapMaximumEntriesException.php';

interface SitemapInterface {
/**
 * A flag for the validation fallback value.
 *
 * @const string
 */
	const VALIDATION_EXCEPTION = 'VALIDATION_EXCEPTION';
}

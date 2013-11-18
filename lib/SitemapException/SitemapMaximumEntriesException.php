<?php
/**
 * SitemapMaximumEntriesException
 * 
 * Contains the definition of a Sitemap maximum entries exception.
 *
 * @author Lars Lenecke <incode@func0der.de>
 * @version 0.5
 * @package Sitemap
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . '../SitemapException.php';

class SitemapMaximumEntriesException extends Exception {}

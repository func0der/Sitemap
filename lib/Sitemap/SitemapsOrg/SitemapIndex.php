<?php
/**
 * Sitemap interface file.
 * 
 * Contains the definition of a Sitemap Index Sitemap.
 *
 * @author Lars Lenecke <incode@func0der.de>
 * @version 0.5
 * @package Sitemap
 */
require_once __DIR__ . DIRECTORY_SEPARATOR . '../SitemapsOrg.php';

class Sitemap_SitemapsOrg_SitemapIndex extends Sitemap_SitemapsOrg {
/**
 * Root node name.
 *
 * {@link http://www.sitemaps.org/protocol.html#sitemapIndexTagDefinitions Definition}
 *
 * @var string
 */
	protected $_rootNodeName = 'sitemapindex';

/**
 * Entry root node name.
 *
 * {@link http://www.sitemaps.org/protocol.html#sitemapIndexTagDefinitions Definition}
 *
 * @var string
 */
	protected $_entryRootNodeName = 'sitemap';

/**
 * Constructor.
 */
	public function __construct() {
		// Call parent constructor.
		parent::__construct();

		// Get allowed entry nodes.
		$allowedEntryNodes = $this->getAllowedEntryNodes();

		// Unset change frequency and priority.
		unset($allowedEntryNodes['changefreq'], $allowedEntryNodes['priority']);

		// Re-set allowed entry nodes.
		$this->setAllowedEntryNodes($allowedEntryNodes);
	}
}
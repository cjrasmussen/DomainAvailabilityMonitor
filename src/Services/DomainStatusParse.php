<?php

namespace DAM\Services;

use DOMDocument;
use DOMXPath;

class DomainStatusParse
{
	public function isDomainTaken(string $html, string $domain): bool
	{
		$doc = new DOMDocument();
		libxml_use_internal_errors(true);
		$doc->loadHTML($html);

		$xpath = new DOMXpath($doc);

		$priceElementId = 'searchResultRowPrice_' . str_replace('.', '_', $domain);
		$priceElementQuery = '//*/div[@id="' . $priceElementId . '"]/span[@class="childContent"]/small';
		$priceElement = $xpath->query($priceElementQuery);

		return (($priceElement) && ($priceElement->item(0)->textContent === 'unavailable'));
	}
}
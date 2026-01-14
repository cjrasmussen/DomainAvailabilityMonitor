<?php

namespace DAM\Services;

use DOMDocument;
use DOMXPath;

class DomainStatusParse
{
	private array $unavailableTextOptions = [
		'registered', // used as of 1/9/2026
		'unavailble', // used as of 1/13/2026 (typo intended)
		'unavailable', // assume they'll fix that typo eventually
	];

	public function isDomainTaken(string $html, string $domain): bool
	{
		$doc = new DOMDocument();
		libxml_use_internal_errors(true);
		$doc->loadHTML($html);

		$xpath = new DOMXpath($doc);

		$priceElementId = 'searchResultRowPrice_' . str_replace('.', '_', $domain);
		$priceElementQuery = '//*/div[@id="' . $priceElementId . '"]/span[@class="childContent"]/small';
		$priceElement = $xpath->query($priceElementQuery);

		if (!$priceElement) {
			return false;
		}

		if (in_array($priceElement->item(0)->textContent, $this->unavailableTextOptions, true)) {
			return true;
		}

		return false;
	}
}
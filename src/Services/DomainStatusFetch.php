<?php

namespace DAM\Services;

use Exception;
use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Exception\CommunicationException;
use HeadlessChromium\Exception\CommunicationException\CannotReadResponse;
use HeadlessChromium\Exception\CommunicationException\InvalidResponse;
use HeadlessChromium\Exception\CommunicationException\ResponseHasError;
use HeadlessChromium\Exception\ElementNotFoundException;
use HeadlessChromium\Exception\EvaluationFailed;
use HeadlessChromium\Exception\NavigationExpired;
use HeadlessChromium\Exception\NoResponseAvailable;
use HeadlessChromium\Exception\OperationTimedOut;
use Random\RandomException;
use RuntimeException;

class DomainStatusFetch
{
	private BrowserFactory $browserFactory;

	public function __construct(BrowserFactory $browserFactory)
	{
		$this->browserFactory = $browserFactory;
	}

	/**
	 * Get the HTML of the GoDaddy search results page for a domain
	 *
	 * @param string $domain
	 * @return string
	 * @throws CannotReadResponse
	 * @throws CommunicationException
	 * @throws ElementNotFoundException
	 * @throws EvaluationFailed
	 * @throws InvalidResponse
	 * @throws NavigationExpired
	 * @throws NoResponseAvailable
	 * @throws OperationTimedOut
	 * @throws RandomException
	 * @throws ResponseHasError
	 */
	public function fetchDomainAvailabilityData(string $domain): string
	{
		$homeUrl = 'https://porkbun.com/';

		$windowWidth = random_int(1900, 2000);
		$windowHeight = random_int(950, 1050);
		$keyInterval = random_int(7, 12);

		$browser = $this->browserFactory->createBrowser([
			'windowSize' => [$windowWidth, $windowHeight],
			'userAgent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.150 Safari/537.36',
			'enableImages' => true,
		]);

		$page = $browser->createPage();
		$page->navigate($homeUrl)->waitForNavigation();

		$page->waitUntilContainsElement('#domainSearchInput');

		$mouse = $page->mouse();
		$keyboard = $page->keyboard();

		if (!(($mouse) && ($keyboard))) {
			$msg = 'Mouse or keyboard could not be found';
			throw new RuntimeException($msg);
		}

		$mouse->find('#domainSearchInput')->click();
		$keyboard->setKeyInterval($keyInterval)->typeText($domain);

		sleep(round(($keyInterval / 1000) * 5.2));
		$mouse->find('#domainSearchButton')->click();

		$page->waitUntilContainsElement('#searchResultsSectionContainer_exact');

		do {
			$html = $page->getHtml();
			sleep(1);
		} while (str_contains($html, ' pendingDomain '));

		$browser->close();

		return $html;
	}
}
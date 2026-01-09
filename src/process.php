<?php

use cjrasmussen\SlackApi\SlackApi;
use DAM\Services\DomainStatusFetch;
use DAM\Services\DomainStatusParse;
use HeadlessChromium\BrowserFactory;

require_once __DIR__ . '/../bootstrap.php';
/** @var object{slackEndpoint: string, slackChannel: ?string} $config */

$time_limit = 900;
$pause = random_int(30, 300);

set_time_limit($time_limit);

$slackApi = new SlackApi($config->slackEndpoint);

// DEFINE WHAT WE'RE CHECKING FOR
$check = [
	'icethetics.com',
	'uni-watch.com',
	'fhlsim.com',
	'letsgowings.com',
];

$delay_max = round((($time_limit - $pause) * .8) / count($check));
$delay_min = round($delay_max / 2);

sleep($pause);

$alerts = [];

shuffle($check);

$domainStatusFetch = new DomainStatusFetch(new BrowserFactory());
$domainStatusParse = new DomainStatusParse();

foreach ($check AS $domain) {
	try {
		$html = $domainStatusFetch->fetchDomainAvailabilityData($domain);
	} catch (Exception) {
		continue;
	}

	$domainTaken = $domainStatusParse->isDomainTaken($html, $domain);

	if (!$domainTaken) {
		$alerts[] = $domain . ' may be available.';
	}

	sleep(random_int($delay_min, $delay_max));
}
var_dump($alerts);
if (!count($alerts)) {
	// WE DIDN'T FIND ANYTHING, CAN EXIT
	exit;
}

// BUILD THE HEADER
$blocks = [];
$blocks[] = [
	'type' => 'header',
	'text' => [
		'type' => 'plain_text',
		'text' => 'Domain Availability Check',
	],
];
$blocks[] = [
	'type' => 'divider',
];

// BUILD THE CONTENT
$elements = [];
foreach ($alerts AS $alert) {
	$elements[] = [
		'type' => 'rich_text_section',
		'elements' => [
			[
				'type' => 'text',
				'text' => $alert,
			],
		],
	];
}

if (count($elements)) {
	$blocks[] = [
		'type' => 'rich_text',
		'elements' => [
			[
				'type' => 'rich_text_list',
				'style' => 'bullet',
				'elements' => $elements,
			],
		],
	];
}

// SEND THE MESSAGE
$msg = [
	'blocks' => $blocks,
];

if ($config->slackChannel) {
	$channel = $config->slackChannel;
	if (!str_starts_with($channel, '#')) {
		$channel = '#' . $channel;
	}

	$msg['channel'] = $channel;
}

$slackApi->sendMessage($msg);

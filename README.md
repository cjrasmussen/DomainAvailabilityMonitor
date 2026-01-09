# DomainAvailabilityMonitor

Tool for automated monitoring of domain availability via Porkbun. Sends a notification via Slack when domains
are found to possibly be available.

## Installation

1. Pull code from repo.
2. Run `composer update`.
3. Copy `config.dist.php` to `config.php` and update as necessary.

## Execution
`php src/process.php`
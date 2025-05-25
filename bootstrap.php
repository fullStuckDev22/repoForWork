<?php
use AmoCRM\Client\AmoCRMApiClient;

$clientId = '';
$clientSecret = '';
$redirectUri = '';
$baseDomain = '';

$apiClient = new AmoCRMApiClient($clientId, $clientSecret, $redirectUri);


include_once __DIR__ . '/error_printer.php';

<?php
use AmoCRM\Client\AmoCRMApiClient;

$clientId = '2c3e5ffb-71f6-4363-8f38-e7fc5e589b47';
$clientSecret = '3zxdVXzNzV3VoWsHAk9uZqxFbaYNqDFP78WqPrHznP40jYccgQK5Pyl4afStIcpm';
$redirectUri = 'https://testamo.ru/amo/amo.php';
$baseDomain = 'novikovalexei22.amocrm.ru';

$apiClient = new AmoCRMApiClient($clientId, $clientSecret, $redirectUri);


include_once __DIR__ . '/error_printer.php';

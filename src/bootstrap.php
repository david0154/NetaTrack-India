<?php
define('ROOT', dirname(__DIR__));
define('SRC', __DIR__);

session_start();

require_once SRC . '/Database.php';
require_once SRC . '/Auth.php';
require_once SRC . '/AiClient.php';
require_once SRC . '/Collector.php';
require_once SRC . '/LeaderScorer.php';
require_once SRC . '/VerificationEngine.php';
require_once SRC . '/Mailer.php';
require_once SRC . '/SettingsManager.php';

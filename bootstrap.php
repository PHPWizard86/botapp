<?php

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

define('DB_PATH', __DIR__ . '/db/');
define('TEMP_PATH', __DIR__ . '/tmp/');

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Configuration;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;

$_ENV['BOT_API'] = $_ENV['BOT_API'] ?? 'https://tapi.bale.ai';

date_default_timezone_set($_ENV['TIMEZONE']);

$psr6Cache = new FilesystemAdapter(
    namespace: 'nutgram',
    defaultLifetime: 0,
    directory: __DIR__ . '/cache/'
);

$psr16Cache = new Psr16Cache($psr6Cache);

$config = new Configuration(
    cache: $psr16Cache,
    clientTimeout: 10,
    apiUrl: $_ENV['BOT_API'],
    conversationTtl: 86400 * 100000
);

$bot = new Nutgram($_ENV['BOT_TOKEN'], $config);

return $bot;
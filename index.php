<?php

use SergiX44\Nutgram\RunningMode\Webhook;
use Library\Helper;

$bot = require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/handler.php';

$webhook = new Webhook( secretToken: $_ENV[ 'WEBHOOK_SECRET_TOKEN' ] );
$webhook->setSafeMode(true);
$bot->setRunningMode(Webhook::class);

if( Helper::isRepeatedUpdate( $webhook ) ) {
    
    exit;
    
}

$bot->run();
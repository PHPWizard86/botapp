<?php

if( empty( $_SERVER[ 'QUERY_STRING' ] ) ) {
    
    exit('Invalid request!');
    
}

if( ! isset( $_GET[ 'wpttb_site_id' ] ) || ! is_numeric( $_GET[ 'wpttb_site_id' ] ) ) {
    
    exit('Invalid request!');
    
}

$site_id = abs( (int) $_GET[ 'wpttb_site_id' ] );

define('DB_PATH', __DIR__ . '/db/');

require_once __DIR__ . '/vendor/autoload.php';

use SleekDB\Store;

$siteStore = new Store('site', DB_PATH, ['timeout' => false]);

$site = $siteStore->findById( $site_id );

if( is_null( $site ) ) {
    
    exit('Invalid request!');
    
}

$api = $site[ 'api' ];

unset( $_GET[ 'wpttb_site_id' ] );

$_GET[ 'action' ] = 'wpttb_login';

$query = http_build_query( $_GET );

$url = "{$api}?{$query}";

header("Location: {$url}", true, 302);

exit;
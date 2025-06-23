<?php

namespace App\Middleware;

use SergiX44\Nutgram\Nutgram;
use SleekDB\Store;

class IsUser {

	function __invoke(Nutgram $bot, $next) {
        
        $siteStore = new Store('site', DB_PATH, ['timeout' => false]);
        
        $sites = $siteStore->findBy(
            [
                [ 'accepted', '=', true ],
                [ 'user_ids', 'CONTAINS', $bot->userId() ?? 0 ]
            ]
        );
        
        if( $bot->isCallbackQuery() ) {
            
            if( !empty( $sites ) ) {
                
                $next($bot);
                
            } else {
            
                $bot->answerCallbackQuery(text: '⚠️ درخواست غیر مجاز');
            
            }
            
        } else {
            
            if( !empty( $sites ) ) {
                
                $next($bot);
                
            } else {
            
                $bot->sendMessage('⚠️ درخواست غیر مجاز');
            
            }
            
        }
        
	}
    
}
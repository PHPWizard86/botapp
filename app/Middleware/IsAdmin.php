<?php

namespace App\Middleware;

use SergiX44\Nutgram\Nutgram;

class IsAdmin {

	function __invoke(Nutgram $bot, $next) {
        
        if( $bot->isCallbackQuery() ) {
            
            if($bot->userId() == $_ENV['ADMIN_ID']) {
                
                $next($bot);
                
            } else {
            
                $bot->answerCallbackQuery(text: '⚠️ درخواست غیر مجاز');
            
            }
            
        } else {
            
            if($bot->userId() == $_ENV['ADMIN_ID']) {
                
                $next($bot);
                
            } else {
            
                $bot->sendMessage('⚠️ درخواست غیر مجاز');
            
            }
            
        }
        
	}
    
}
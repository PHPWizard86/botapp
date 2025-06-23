<?php

namespace Library;

use SergiX44\Nutgram\RunningMode\Webhook;

class Helper {
    
    public static function getUpdate() {
        
        return file_get_contents('php://input') ?: null;
        
    }
    
    public static function getLastUpdateId() {
        
        if( file_exists( DB_PATH . 'last_update_id' ) ) {
            
            $update_id = PHP_INT_MAX;
            $fp = fopen( DB_PATH . 'last_update_id', 'rb' );

            if( flock( $fp, LOCK_SH ) ) {

                $update_id = stream_get_contents( $fp );

            }

            flock( $fp, LOCK_UN );
            fclose( $fp );

            return (int) $update_id;
            
        } else {
            
            return 0;
            
        }
        
    }
    
    public static function setLastUpdateId( $update_id ) {
        
        if( !file_exists( DB_PATH ) ) mkdir( DB_PATH, 0755, true );
        
        return file_put_contents( DB_PATH . 'last_update_id', $update_id, LOCK_EX );
        
    }
    
    public static function resolveSecretToken() {
        
        return $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ?? null;
        
    }
    
    public static function isRepeatedUpdate( Webhook $webhook ) {
        
        $input = self::getUpdate();
        
        if(
            $input === null ||
            (
                $webhook->isSafeMode() &&
                self::resolveSecretToken() !== $_ENV[ 'WEBHOOK_SECRET_TOKEN' ]
            )
        ) {
            
            return;
            
        }
        
        $update = json_decode( $input );
        
        if( $update->update_id > self::getLastUpdateId() ) {
            
            self::setLastUpdateId( $update->update_id );
            
            return false;
            
        } else {
            
            return true;
            
        }
        
    }
    
}
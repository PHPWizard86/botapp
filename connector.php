<?php

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Message\LinkPreviewOptions;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SleekDB\Store;
use Ahc\Jwt\JWT;
use Ahc\Jwt\JWTException;

$bot = require_once __DIR__ . '/bootstrap.php';

$data = @file_get_contents('php://input');

if( empty( $data ) ) {
	
	header('Content-Type: application/json; charset=UTF-8');
	$res = [
		'success' => false,
		'msg' => 'درخواست ارسالی معتبر نیست'
	];
	exit(json_encode($res));
	
}

$jwt = new JWT($_ENV['BOT_TOKEN']);

try {

    $payload = (object) $jwt->decode( $data );
    
    switch( $payload->type ) {
        
        case 'user_update':
        
            $url = trim( $payload->url );
            $wp_id = $payload->id;
            $user_login = trim( $payload->user_login );
            $display_name = trim( $payload->display_name );
        
            $site = new Store('site', DB_PATH, ['timeout' => false]); 
            $site_data = $site->findOneBy( [ 'url', '=', $url ] );
            
            if( !is_null( $site_data ) ) {
                
                foreach( $site_data[ 'users' ] as &$user ) {
                    
                    if( $user[ 'wp_id' ] == $wp_id ) {
                        
                        $user[ 'user_login' ] = $user_login;
                        $user[ 'display_name' ] = $display_name;
                        
                    }
                    
                }
                
                $site->updateOrInsert($site_data);
                
            }
            
            header('Content-Type: application/json; charset=UTF-8');
            $res = [
                'success' => true
            ];
            exit(json_encode($res));
        
        break;
        
        case 'delete_user':
        
            $url = trim( $payload->url );
            $wp_id = $payload->id;
        
            $site = new Store('site', DB_PATH, ['timeout' => false]); 
            $site_data = $site->findOneBy( [ 'url', '=', $url ] );
            
            if( !is_null( $site_data ) ) {
                
                $users = [];
                $user_data = [];
                
                foreach( $site_data[ 'users' ] as $user ) {
                    
                    if( $user[ 'wp_id' ] == $wp_id ) {
                        
                        $user_data = $user;
                        
                    } else {
                        
                        $users[] = $user;
                        
                    }
                    
                }
                
                $site_data[ 'users' ] = array_values( $users );
                
                if( !empty( $user_data ) ) {
                    
                    $site_data[ 'user_ids' ] = array_filter( $site_data[ 'user_ids' ], function( $tele_id ) use ( $user_data ) {
                        
                        if( $tele_id == $user_data[ 'tele_id' ] ) {
                            
                            return false;
                            
                        }
                        
                        return true;
                        
                    } );
                    
                    $conversation = $bot->currentConversation( (int) $user_data[ 'tele_id' ], (int) $user_data[ 'tele_id' ] );
                    
                    if( $conversation !== null ) {
                    
                        $conversation->killIt( $bot, (int) $user_data[ 'tele_id' ], (int) $user_data[ 'tele_id' ] );
                    
                    }
                    
                }
                
                $site->updateOrInsert($site_data);
                
            }
            
            header('Content-Type: application/json; charset=UTF-8');
            $res = [
                'success' => true
            ];
            exit(json_encode($res));
        
        break;
        
        case 'site':
        
            $name = trim( $payload->name );
            $url = trim( $payload->url );
            $api = trim( $payload->api );
            $ip = $_SERVER[ 'REMOTE_ADDR' ];
            $user_ids = $payload->user_ids;
            $users = $payload->users;
            $taxonomies = $payload->taxonomies;
            $post_types = $payload->post_types;
            $accepted = false;
        
            $site = new Store('site', DB_PATH, ['timeout' => false]); 
            $site_data = $site->findOneBy( [ 'url', '=', $url ] );
        
            if( $payload->admin_id != $_ENV['ADMIN_ID'] ) {
                
                if( ! is_null( $site_data ) ) {
                    
                    $site_data[ 'accepted' ] = false;
                    
                    $site->update($site_data);
                    
                }
                
                header('Content-Type: application/json; charset=UTF-8');
                $res = [
                    'success' => false,
                    'msg' => 'آیدی تلگرام ادمین ربات با آیدی تلگرام ادمین سایت یکسان نیست'
                ];
                exit(json_encode($res));
                
            }
            
            if( is_null( $site_data ) ) {
                
                $site_data = compact( "name", "url", "api", "ip", "user_ids", "users", "taxonomies", "post_types", "accepted" );
                
                $site_data = $site->updateOrInsert($site_data);

                $bot->sendMessage(
                    text: "🛡 وب سایت زیر تقاضای اتصال به ربات را دارد:\n\n🌐 نام وب سایت: <b>$name</b>\n🔗 آدرس وب سایت: $url\n🕸 آیپی خروجی وب سایت: <code>$ip</code>\n\n📌 لطفا در صورتی که میخواهید وب سایت فوق به لیست سایت های ربات اضافه شود گزینه \" <b>✅ متصلش کن</b> \" را انتخاب کنید و در غیر این صورت از گزینه \" <b>🚫 نادیده بگیرش</b> \" استفاده کنید.",
                    chat_id: $_ENV['ADMIN_ID'],
                    parse_mode: ParseMode::HTML,
                    link_preview_options: LinkPreviewOptions::make( true ),
                    reply_markup: InlineKeyboardMarkup::make()
                    ->addRow(
                        InlineKeyboardButton::make('🚫 نادیده بگیرش', callback_data: "siteaction ignore_{$site_data['_id']}"),
                        InlineKeyboardButton::make('✅ متصلش کن', callback_data: "siteaction connect_{$site_data['_id']}")
                    )
                );
            
            } else {
                
                $site_data[ 'name' ] = $name;
                $site_data[ 'api' ] = $api;
                $site_data[ 'ip' ] = $ip;
                $site_data[ 'user_ids' ] = $user_ids;
                $site_data[ 'users' ] = $users;
                $site_data[ 'taxonomies' ] = $taxonomies;
                $site_data[ 'post_types' ] = $post_types;
                $site_data[ 'accepted' ] = true;
                
                $site->update($site_data);
                
            }
            
            header('Content-Type: application/json; charset=UTF-8');
            $res = [
                'success' => true
            ];
            exit(json_encode($res));
        
        break;
        
    }

} catch( JWTException $e ) {
    
    header('Content-Type: application/json; charset=UTF-8');
	$res = [
		'success' => false,
		'msg' => 'درخواست ارسالی معتبر نیست'
	];
	exit(json_encode($res));
    
}
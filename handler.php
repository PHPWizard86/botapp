<?php

use SergiX44\Nutgram\Nutgram;
use SleekDB\Store;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use lucadevelop\TelegramEntitiesDecoder\EntityDecoder;
use SergiX44\Nutgram\Telegram\Types\Message\LinkPreviewOptions;
use App\InlineMenu\AdminMenu;
use App\Middleware\IsAdmin;
use App\InlineMenu\UserMenu;
use App\Middleware\IsUser;

$bot->middleware(function (Nutgram $bot, $next) {
    
    if( $bot->isCallbackQuery() ) {
        
        $bot->answerCallbackQuery(text: '🛠️ ربات در دست تعمیر است.');
        
    } else {
        
        $bot->sendMessage('🛠️ ربات در دست تعمیر است.');
        
    }
    
});

$bot->onCallbackQueryData('siteaction {data}', function (Nutgram $bot, $data) {
    
    list($action, $id) = explode('_', $data);
    
    $site = new Store('site', DB_PATH, ['timeout' => false]);

    $site_data = $site->findById($id);
    
    if( $site_data === null ) {
        
        $bot->answerCallbackQuery(
            text: "⚠️ سایت مورد نظر یافت نشد."
        );
        
        $bot->message()->delete();
        
    } else {
    
        switch($action) {
            
            case 'connect':
            
                $site_data['accepted'] = true;
            
                $site->update($site_data);
                
                $bot->answerCallbackQuery(
                    text: "✅ سایت مورد نظر متصل شد."
                );
                
                $entity_decoder = new EntityDecoder('HTML');
                $text = $entity_decoder->decode(json_decode(json_encode($bot->message()->toArray())));
                $text_parts = explode("\n\n", $text);
                $text_parts[array_key_last($text_parts)] = "✅ سایت فوق با موفقیت متصل شد.";
                array_shift($text_parts);
                $text = implode("\n\n", $text_parts);
                
                $bot->editMessageText(
                    text: $text,
                    parse_mode: ParseMode::HTML,
                    link_preview_options: LinkPreviewOptions::make( true ),
                );
            
            break;
            
            case 'ignore':
            
                $bot->answerCallbackQuery(
                    text: "🚫 سایت مورد نظر نادیده گرفته شد."
                );
                
                $entity_decoder = new EntityDecoder('HTML');
                $text = $entity_decoder->decode(json_decode(json_encode($bot->message()->toArray())));
                $text_parts = explode("\n\n", $text);
                $text_parts[array_key_last($text_parts)] = "🚫 سایت فوق نادیده گرفته شد.";
                array_shift($text_parts);
                $text = implode("\n\n", $text_parts);
                
                $bot->editMessageText(
                    text: $text,
                    parse_mode: ParseMode::HTML,
                    link_preview_options: LinkPreviewOptions::make( true ),
                );
            
            break;
            
        }
    
    }
    
})->middleware(IsAdmin::class);

AdminMenu::refreshOnDeserialize();
UserMenu::refreshOnDeserialize();

$bot->onCommand('start', function (Nutgram $bot) {
    
    if( $bot->userId() == $_ENV[ 'ADMIN_ID' ]  ) {
        
        AdminMenu::begin( $bot );
        
    } else {
        
        UserMenu::begin( $bot );
        
    }
    
})->middleware(IsUser::class);

$bot->fallback(fn(Nutgram $bot) => exit)->middleware(IsUser::class);
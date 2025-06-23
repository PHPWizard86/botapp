<?php

namespace App\InlineMenu;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Conversations\InlineMenu;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Message\LinkPreviewOptions;
use SergiX44\Nutgram\Telegram\Types\Common\LoginUrl;
use SergiX44\Nutgram\Telegram\Properties\MessageType;
use function SergiX44\Nutgram\Support\array_filter_null;
use SleekDB\Store;
use Respect\Validation\Validator as v;
use App\Fields;
use GuzzleHttp\Psr7;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Handler\CurlMultiHandler;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\ResponseInterface;
use fivefilters\Readability\Readability;
use fivefilters\Readability\Configuration;
use fivefilters\Readability\ParseException;
use DOMWrap\Document;
use DOMWrap\Text;
use Kiwilan\Audio\Audio;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Carbon\Carbon;
use pira\YTDL;
use Mimey\MimeTypes;
use Intervention\Image\ImageManager;
use Ahc\Jwt\JWT;
use Lazzard\FtpClient\Connection\FtpConnection;
use Lazzard\FtpClient\Config\FtpConfig;
use Lazzard\FtpClient\FtpClient;
use Lazzard\FtpClient\FtpWrapper;
use \Morilog\Jalali\Jalalian;
use Library\DomainChecker;
use League\Uri\Uri;
use League\Uri\Modifier;
use lucadevelop\TelegramEntitiesDecoder\EntityDecoder;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Audio\Mp3;

class AdminMenu extends InlineMenu {
    
    protected ?string $step = 'mainMenu';
    protected ?string $callback_data = null;
    
    private array $menu_text = [];
    private ?string $menu_title = null;
    private Store $siteStore;
    private Store $sampleStore;
    private Guzzle $curl;
    private Readability $readability;
    private Document $doc;
    private DomainChecker $checker;
    
    public array $prev_menu = [];
    public array $next_data = [];
    public bool $need_disable = false;
    
    public function __construct() {
        
        $this->siteStore = new Store('site', DB_PATH, ['timeout' => false]);
        $this->sampleStore = new Store('sample', DB_PATH, ['timeout' => false]);
        $this->curl = new Guzzle;
        $this->readability = new Readability(new Configuration());
        $this->doc = new Document();
        $this->checker = new DomainChecker();
        
    }
    
    protected function fixTempPath( $path ) {
        
        if( str_starts_with( $path, TEMP_PATH ) ) {
            
            return $path;
            
        }
        
        list( , $path ) = explode( '/tmp/', $path, 2 );
        
        return TEMP_PATH . $path;
        
    }
    
    protected function beforeStep( Nutgram $bot ) {
        
        parent::beforeStep( $bot );
        
        if(
            !empty( $this->next_data ) &&
            isset( $this->next_data[ 'post_data' ][ 'tmp' ] )
        ) {
            
            $this->next_data[ 'post_data' ][ 'tmp' ] = $this->fixTempPath( $this->next_data[ 'post_data' ][ 'tmp' ] );
            
        }
        
    }
    
    protected function isLocalSite( $url ) {
        
        $sites = $this->siteStore->findBy(
            [
                [ 'accepted', '=', true ]
            ]
        );
        
        if( empty( $sites ) ) {
            
            return false;
            
        }
        
        $url_host = Uri::new( $url )->getHost();
        
        $sites_host = [];
        
        foreach( $sites as $site ) {
            
            $sites_host[] = Uri::new( $site[ 'url' ] )->getHost();
            
        }
        
        if( in_array( $url_host, $sites_host ) ) {
            
            return true;
            
        }
        
        return false;
        
    }
    
    protected function isLocalUrl( $site, $url ) {
        
        if( !isset( $site[ 'dl_host' ][ 'url' ] ) ) {
            
            return false;
            
        }
        
        $url = strtolower( trim( $url ) );
        
        $dl_host = Uri::new( $site[ 'dl_host' ][ 'url' ] )->getHost();
        $url_host = Uri::new( $url )->getHost();
        
        if( $dl_host == $url_host ) {
            
            return true;
            
        }
        
        return false;
        
    }
    
    public function fileUpload(Nutgram $bot, string $data) {
        
        list( , $id, $type, $error ) = array_pad( explode( '_', $data ), 4, null );
        
        $site = $this->siteStore->findById( $id );
        
        if( is_null( $site ) || empty( $site[ 'accepted' ] ) || !in_array( $_ENV[ 'ADMIN_ID' ], $site[ 'user_ids' ] ) ) {
            
            $this->setTitle("🌐 سایت شماره {$id}")
                ->addText('⚠️ سایت مورد نظر یافت نشد.')
                ->setMenuText()
                ->clearButtons()
                ->addPrevMenuBtn(3)
                ->setPrevMenu(__FUNCTION__, 3)
                ->showMenu();
            
        } else {
                
            if( isset( $type ) ) {
                
                $type_name = $type == 'a' ? 'صوتی' : 'تصویری';
                
                $this->setTitle( "📝 نام فایل {$type_name}" );
                
                if(
                    isset( $error ) && 
                    isset( $this->next_data[ 'post_data' ][ 'error' ] )
                ) {
                    
                    $error = $this->next_data[ 'post_data' ][ 'error' ];
                    
                    $this->addText("⚠️ {$error}")->addText("");
                    
                }
                
                $this->next_data[ 'post_data' ] = [
                    'site_data' => $site,
                    'type' => $type
                ];
                
                $this->addText('✍️ لطفا نام فایلی که قصد آپلود آن را دارید وارد کنید.')
                    ->addText('📌 نام فایل می تواند شامل حروف انگلیسی، اعداد، فاصله و خط فاصله باشد.')
                    ->setMenuText()
                    ->clearButtons()
                    ->addPrevMenuBtn(4)
                    ->setPrevMenu(__FUNCTION__, 4)
                    ->orNext( 'fileUploadName' )
                    ->showMenu();
                
            } else {
                
                $this->setTitle("📤 آپلود فایل")
                    ->addText('👇 لطفا نوع فایلی که قصد آپلود آن را دارید انتخاب کنید.')
                    ->addText('📌 فرمت فایل صوتی <b>mp3</b> و فرمت فایل تصویری <b>mp4</b> می باشد.')
                    ->setMenuText()
                    ->clearButtons()
                    ->addButtonRow(
                        InlineKeyboardButton::make("🎞 تصویری", callback_data: "id_{$site['_id']}_v@fileUpload"),
                        InlineKeyboardButton::make("🎧 صوتی", callback_data: "id_{$site['_id']}_a@fileUpload")
                    )
                    ->addPrevMenuBtn(3)
                    ->setPrevMenu(__FUNCTION__, 3)
                    ->showMenu();
                
            }
            
        }
        
    }
    
    public function fileUploadName(Nutgram $bot) {
        
        $next_data =& $this->next_data[ 'post_data' ];
        $site = $next_data[ 'site_data' ];
        $type = $next_data[ 'type' ];
        
        if(
            $bot->message()->getType() === MessageType::TEXT ||
            isset( $next_data[ 'fileupname' ] )
        ) {
            
            $text = $next_data[ 'fileupname' ] ?? $bot->message()->getText();
            
            if( v::alnum( ' ', '-' )->validate( $text ) ) {
                
                if( !isset( $next_data[ 'fileupname' ] ) ) {
                    
                    $next_data[ 'fileupname' ] = trim( $text );
                    
                    $temp_path = TEMP_PATH . $this->generateRandomString();
                    if( !file_exists( $temp_path ) ) mkdir( $temp_path, 0755, true );
                    
                    $next_data[ 'tmp' ] = $temp_path;
                    
                }
                
                if( isset( $next_data[ 'disabled' ] ) ) {
                    
                    if( $next_data[ 'disabled' ] === false ) {
                        
                        $this->disableMenu();
                        
                        unset( $this->next_data[ 'post_data' ][ 'disabled' ] );
                        
                    }
                    
                } else {
                    
                    $this->disableMenu();
                    
                }
                
                $type_name = $type == 'a' ? 'صوتی' : 'تصویری';
                
                $this->setTitle( "📤 آپلود فایل {$type_name}" );
                    
                if( isset( $next_data[ 'error' ] ) ) {
                    
                    $error = $next_data[ 'error' ];
                    
                    $this->addText("⚠️ {$error}")->addText("");
                    
                    unset( $this->next_data[ 'post_data' ][ 'error' ] );
                    
                }
                    
                $this->addText('📥 لطفا فایلی که قصد آپلود آن را دارید ارسال کنید.')
                    ->addText("");
                
                if( $type == 'a' ) {
                    
                    $this->addText('📌 لینک و فایل <b>mp3</b> قابل قبول است.')
                        ->addText('📌 در صورت ارسال لینک ویدئو از <b>اینستاگرام</b>، صوت ویدئو استخراج شده و قرار میگیرد.');
                    
                } else {
                    
                    $this->addText('📌 لینک و فایل <b>mp4</b> قابل قبول است.')
                        ->addText('📌 لینک ویدئو از <b>اینستاگرام</b> قابل قبول است.');
                    
                }
                    
                $this->setMenuText()
                    ->clearButtons()
                    ->addPrevMenuBtn(5, null, 'post_data')
                    ->setPrevMenu(__FUNCTION__, 5)
                    ->orNext( 'fileUploadFile' )
                    ->showMenu();
                
            } else {
                
                $next_data[ 'error' ] = 'نام وارد شده معتبر نیست.';
                $this->disableMenu()
                    ->loadMenu( 'fileUpload', "id_{$site['_id']}_{$type}_1@fileUpload" );
                
            }
            
        } else {
            
            $next_data[ 'error' ] = 'پیام ارسال شده متنی نمی باشد.';
            $this->disableMenu()
                ->loadMenu( 'fileUpload', "id_{$site['_id']}_{$type}_1@fileUpload" );
            
        }
        
    }
    

            public function fileUploadFile(Nutgram $bot) {
                $next_data =& $this->next_data['post_data'];
                $next_data['dl'] = 0;
                $next_data['up'] = 0;
                $site = $next_data['site_data'];
                $type = $next_data['type'];
                $ext = $type == 'a' ? 'mp3' : 'mp4';
                $filename = $next_data['fileupname'];
                $next_data['disabled'] = false;
                $disabled =& $next_data['disabled'];
                $start = time();
            
                $update_menu = function() use (&$disabled, &$next_data) {
                    if (!$disabled) {
                        $disabled = true;
                        $this->disableMenu();
                    }
            
                    $dl = $next_data['dl'];
                    $up = $next_data['up'];
            
                    if (is_int($dl)) {
                        $dl_text = $dl === 0 ? 'در انتظار دانلود ...' : "{$dl}% دانلود شده ...";
                    } elseif (is_string($dl)) {
                        $dl_text = "{$dl} دانلود شده ...";
                    } else {
                        $dl_text = $dl === true ? '✅ دانلود شده' : '❌ خطا در دانلود';
                    }
            
                    if (is_int($up)) {
                        $up_text = $up === 0 ? 'در انتظار آپلود ...' : "{$up}% آپلود شده ...";
                    } else {
                        $up_text = $up === true ? '✅ آپلود شده' : '❌ خطا در آپلود';
                    }
            
                    $date = Jalalian::now()->format('l d F Y ساعت H:i:s');
            
                    $this->setTitle("📥 دانلود و آپلود فایل 📤")
                        ->addText("🔄 این پیام هر 5 ثانیه بروز می شود.")
                        ->addText("")
                        ->addText("📥 وضعیت دانلود: {$dl_text}")
                        ->addText("📤 وضعیت آپلود: {$up_text}")
                        ->addText("")
                        ->addText("🔄 آخرین بروزرسانی: <code>{$date}</code>")
                        ->setMenuText()
                        ->clearButtons()
                        ->addButtonRow(
                            InlineKeyboardButton::make("⌛️ در حال دانلود و آپلود ...", callback_data: "pass")
                        )
                        ->showMenu();
                };
            
                $dl_fn = function($downloadTotal, $downloadedBytes, $uploadTotal, $uploadedBytes) use (&$start, &$next_data, $update_menu) {
                    static $first_call = true;
            
                    if ($downloadTotal > 0) {
                        $next_data['dl'] = (int)(($downloadedBytes * 100) / $downloadTotal);
                    } else {
                        $next_data['dl'] = $downloadedBytes < 1048576
                            ? number_format($downloadedBytes / 1024, 2, '.', '') . ' کیلوبایت'
                            : number_format($downloadedBytes / 1048576, 2, '.', '') . ' مگابایت';
                    }
            
                    if ($first_call === true) {
                        $first_call = false;
                        $start = time();
                        $update_menu();
                    }
            
                    if (time() - $start >= 5) {
                        $start = time();
                        $update_menu();
                    }
                };
            
                $ftp = $this->getFtpClient($site);
            
                if ($ftp === false) {
                    $next_data['error'] = 'خطا در اتصال به سرور <b>FTP</b> سایت.';
                    $this->fileUploadName($bot);
                    exit;
                }
            
                $this->closeConnection();
                $ftp->getWrapper()->set_option(FTP_TIMEOUT_SEC, 300);
            
                if ($bot->message()->getType() === MessageType::TEXT && $this->isUrlMessage()) {
                    $text = $bot->message()->getText();
            
                    if ($this->isSocialLink($text)) {
                        $bot->sendMessage('⌛️ کمی صبر ...');
                        $dl_link = $this->get {}
            SocialDirectLink($text, $type == 'a' ? true : false);
            
                        if ($dl_link) {
                            $ok = $this->fileDownload(
                                $dl_link,
                                $next_data['tmp'] . "/file.{$ext}",
                                300,
                                $dl_fn,
                                $type == 'a' ? "file.{$ext}" : null
                            );
            
                            if ($ok) {
                                $next_data['dl'] = true;
                            } else {
                                $next_data['error'] = 'خطایی در دانلود فایل رخ داده است.';
                                $this->fileUploadName($bot);
                                exit;
                            }
                        } else {
                            $next_data['error'] = 'خطایی در تبدیل لینک سوشیال مدیا به لینک مستقیم رخ داده است.';
                            $this->fileUploadName($bot);
                            exit;
                        }
                    } else {
                        $urlWOQ = strtolower(current(explode("?", $text)));
                        if (str_ends_with($urlWOQ, ".{$ext}")) {
                            $size = $this->getRemoteFileSize($text);
                            if ($size > 0) {
                                if ($this->isLocalUrl($site, $text)) {
                                    if (!$disabled) {
                                        $disabled = true;
                                        $this->disableMenu();
                                    }
            
                                    $url = $this->urlEncode($text);
                                    $this->setTitle("📥 دانلود و آپلود فایل 📤")
                                        ->addText("✅ فایل با موفقیت آپلود شد.\n")
                                        ->addText("🔗 لینک دانلود فایل:")
                                        ->addText("<pre>{$text}</pre>")
                                        ->setMenuText()
                                        ->clearButtons()
                                        ->addButtonRow(
                                            InlineKeyboardButton::make('📥 دانلود فایل', url: $url)
                                        )
                                        ->addPrevMenuBtn(4, null, 'post_data')
                                        ->showMenu();
                                        
                                    exit;
                                } else {
                                    $ok = $this->fileDownload(
                                        $text,
                                        $next_data['tmp'] . "/file.{$ext}",
                                        300,
                                        $dl_fn
                                    );
            
                                    if ($ok) {
                                        $next_data['dl'] = true;
                                    } else {
                                        $next_data['error'] = 'خطایی در دانلود فایل رخ داده است.';
                                        $this->fileUploadName($bot);
                                        exit;
                                    }
                                }
                            } else {
                                $next_data['error'] = 'لینک ارسالی نامعتبر است.';
                                $this->fileUploadName($bot);
                                exit;
                            }
                        } else {
                            $next_data['error'] = 'لینک ارسالی نامعتبر است.';
                            $this->fileUploadName($bot);
                            exit;
                        }
                    }
                } else {
                    $msg_type = $type == 'a' ? MessageType::AUDIO : MessageType::VIDEO;
                    if ($this->bot->message()->getType() === $msg_type) {
                        if ($type == 'a') {
                            $file = $this->bot->message()->audio;
                        } else {
                            $file = $this->bot->message()->video;
                        }
            
                        $file_name = $file->file_name ?? '';
                        $mime_type = $file->mime_type ?? '';
                        $mimes = new MimeTypes;
            
                        if (str_ends_with($file_name, ".{$ext}") || in_array($ext, $mimes->getAllExtensions($mime_type))) {
                            try {
                                $ok = $file->download($next_data['tmp'] . "/file.{$ext}", ['progress' => $dl_fn, 'timeout' => 300]);
                            } catch (\Exception $e) {
                                $ok = false;
                            }
            
                            if ($ok) {
                                $next_data['dl'] = true;
                            } else {
                                $next_data['error'] = 'خطایی در دانلود فایل رخ داده است.';
                                $this->fileUploadName($bot);
                                exit;
                            }
                        } else {
                            $next_data['error'] = 'فایل ارسالی نامعتبر است.';
                            $this->fileUploadName($bot);
                            exit;
                        }
                    } else {
                        $next_data['error'] = $type == 'a' ? 'نوع پیام ارسالی باید صوت باشد.' : 'نوع پیام ارسالی باید ویدئو باشد.';
                        $this->fileUploadName($bot);
                        exit;
                    }
                }
            
                // تگ‌گذاری فایل‌های صوتی
                if ($type == 'a') {
                    try {
                        $audio = Audio::read($next_data['tmp'] . "/file.{$ext}");
                        $audio->write()
                            ->setTitle($filename) // تنظیم عنوان بر اساس نام فایل
                            ->setArtist('Unknown Artist') // قابل تغییر با داده‌های دیگر
                            ->setAlbum('Unknown Album') // قابل تغییر با داده‌های دیگر
                            ->removeOtherTags()
                            ->removeCover()
                            ->skipErrors()
                            ->save();
            
                        // لاگ‌گیری برای دیباگ
                        $newTags = $audio->getTags();
                        \Log::info('Audio Tags After Save: ' . json_encode($newTags));
                    } catch (\Exception $e) {
                        \Log::error('Error in audio tagging: ' . $e->getMessage());
                        $next_data['error'] = 'خطایی در تگ‌گذاری فایل صوتی رخ داده است.';
                        $this->fileUploadName($bot);
                        exit;
                    }
                }
            
                // آماده‌سازی مسیر FTP و بررسی وجود فایل
                $ftp_base = $this->trailingslashit($site['dl_host']['ftp_path']);
                $date_dir = Jalalian::now()->format('Y/m');
                $dir = $ftp_base . $date_dir;
                $original_filename = $filename;
                $ftp_path = "{$dir}/{$filename}.{$ext}";
                $url_path = "{$date_dir}/{$filename}.{$ext}";
            
                // بررسی وجود فایل و تغییر نام در صورت نیاز
                try {
                    $counter = 1;
                    while ($ftp->isFile($ftp_path)) {
                        $filename = $original_filename . '_' . $counter;
                        $ftp_path = "{$dir}/{$filename}.{$ext}";
                        $url_path = "{$date_dir}/{$filename}.{$ext}";
                        $counter++;
                    }
                } catch (\Exception $e) {
                    \Log::error('Error checking file existence on FTP: ' . $e->getMessage());
                    $next_data['error'] = 'خطایی در بررسی وجود فایل رخ داده است.';
                    $this->fileUploadName($bot);
                    exit;
                }
            
                // آپلود فایل
                try {
                    if (!$ftp->isDir($dir)) {
                        $ftp->createDir($dir);
                    }
            
                    $ok = $ftp->asyncUpload(
                        $next_data['tmp'] . "/file.{$ext}",
                        $ftp_path,
                        function($speed, $percentage, $transferred, $seconds) use ($update_menu) {
                            $this->next_data['post_data']['up'] = $percentage;
                            $update_menu();
                        },
                        false,
                        5
                    );
            
                    if ($ok === false) {
                        throw new \Exception('آپلود فایل ناموفق بود.');
                    }
                } catch (\Exception $e) {
                    \Log::error('Error uploading file to FTP: ' . $e->getMessage());
                    $next_data['error'] = 'خطایی در آپلود فایل رخ داده است: ' . $e->getMessage();
                    $this->fileUploadName($bot);
                    exit;
                }
            
                $url = $this->createFtpLink($site['dl_host'], $url_path);
            
                if (!$disabled) {
                    $disabled = true;
                    $this->disableMenu();
                }
            
                $this->setTitle("📥 دانلود و آپلود فایل 📤")
                    ->addText("✅ فایل با موفقیت آپلود شد.\n")
                    ->addText("🔗 لینک دانلود فایل:")
                    ->addText("<pre>{$url}</pre>")
                    ->setMenuText()
                    ->clearButtons()
                    ->addButtonRow(
                        InlineKeyboardButton::make('📥 دانلود فایل', url: $url)
                    )
                    ->addPrevMenuBtn(4, null, 'post_data')
                    ->showMenu();
            }
                            exit;
                            
                        } else {
                            
                            $ok = $this->fileDownload(
                                $text,
                                $next_data[ 'tmp' ] . "/file.{$ext}",
                                300,
                                $dl_fn
                            );
                            
                            if( $ok ) {
                            
                                $next_data[ 'dl' ] = true;
                            
                            } else {
                                
                                $next_data[ 'error' ] = 'خطایی در دانلود فایل رخ داده است.';
                                $this->fileUploadName( $bot );
                                exit;
                                
                            }
                            
                        }
                        
                    } else {
                        
                        $next_data[ 'error' ] = 'لینک ارسالی نامعتبر است.';
                        $this->fileUploadName( $bot );
                        exit;
                        
                    }
                    
                } else {
                    
                    $next_data[ 'error' ] = 'لینک ارسالی نامعتبر است.';
                    $this->fileUploadName( $bot );
                    exit;
                    
                }
                
            }
            
        } else {
            
            $msg_type = $type == 'a' ? MessageType::AUDIO : MessageType::VIDEO;
            
            if( $this->bot->message()->getType() === $msg_type ) {
                
                if( $type == 'a' ) {
                    
                    $file = $this->bot->message()->audio;
                    
                } else {
                    
                    $file = $this->bot->message()->video;
                    
                }
                
                $file_name = $file->file_name ?? '';
                $mime_type = $file->mime_type ?? '';
                $mimes = new MimeTypes;
                
                if( str_ends_with( $file_name, ".{$ext}" ) || in_array( $ext, $mimes->getAllExtensions( $mime_type ) ) ) {
                    
                    try {
                        
                        $ok = $file->download( $next_data[ 'tmp' ] . "/file.{$ext}", [ 'progress' => $dl_fn, 'timeout' => 300 ] );
                        
                    } catch( \Exception $e ) {
                        
                        $ok = false;
                        
                    }
                    
                    if( $ok ) {
                        
                        $next_data[ 'dl' ] = true;
                        
                    } else {
                        
                        $next_data[ 'error' ] = 'خطایی در دانلود فایل رخ داده است.';
                        $this->fileUploadName( $bot );
                        exit;
                        
                    }
                    
                } else {
                    
                    $next_data[ 'error' ] = 'فایل ارسالی نامعتبر است.';
                    $this->fileUploadName( $bot );
                    exit;
                    
                }
                
            } else {
                
                if( $type == 'a' ) {
                    
                    $next_data[ 'error' ] = 'نوع پیام ارسالی باید صوت باشد.';
                    
                } else {
                    
                    $next_data[ 'error' ] = 'نوع پیام ارسالی باید ویدئو باشد.';
                    
                }
                
                $this->fileUploadName( $bot );
                exit;
                
            }
            
        }
        
        if( $type == 'a' ) {
        
            $audio = Audio::read( $next_data[ 'tmp' ] . "/file.{$ext}" );
            
            $audio->write()
                ->removeOtherTags()
                ->removeCover()
                ->skipErrors()
                ->save();
            
        }
        
        $ftp_base = $this->trailingslashit( $site[ 'dl_host' ][ 'ftp_path' ] );
        $date_dir = Jalalian::now()->format( 'Y/m' );
        $dir = $ftp_base . $date_dir;
        $ftp_path = "{$dir}/{$filename}.{$ext}";
        $url_path = "{$date_dir}/{$filename}.{$ext}";
        
        try {
            
            if( !$ftp->isDir( $dir ) ) {
                
                $ftp->createDir( $dir );
                
            }
        
            $ok = $ftp->asyncUpload(
                $next_data[ 'tmp' ] . "/file.{$ext}",
                $ftp_path,
                function( $speed, $percentage, $transferred, $seconds ) use ( $update_menu ) {
                    
                    $this->next_data[ 'post_data' ][ 'up' ] = $percentage;
                    
                    $update_menu();
                    
                },
                false,
                5
            );
        
        } catch ( \Exception $e ) {
            
            $ok = false;
            
        }
        
        if( $ok === false ) {
            
            $next_data[ 'error' ] = 'خطایی در آپلود فایل رخ داده است.';
            $this->fileUploadName( $bot );
            exit;
            
        } else {
            
            $url = $this->createFtpLink(
                $site[ 'dl_host' ],
                $url_path
            );
            
            if( !$disabled ) {
                
                $disabled = true;
                
                $this->disableMenu();
                
            }
            
            $this->setTitle("📥 دانلود و آپلود فایل 📤")
                ->addText("✅ فایل با موفقیت آپلود شد.\n")
                ->addText("🔗 لینک دانلود فایل:")
                ->addText("<pre>{$url}</pre>")
                ->setMenuText()
                ->clearButtons()
                ->addButtonRow(
                    InlineKeyboardButton::make('📥 دانلود فایل', url: $url)
                )
                ->addPrevMenuBtn(4, null, 'post_data')
                ->showMenu();
            
        }
        
    }
    
    public function mainMenu(Nutgram $bot) {
        
        $this->setTitle("📋 منوی اصلی")
            ->setMenuText()
            ->clearButtons()
            ->addButtonRow(
                InlineKeyboardButton::make('🌐 سایت ها', callback_data: "p_1@sitesList")
            )
            ->addButtonRow(
                InlineKeyboardButton::make('🧩 الگو ساز', login_url: LoginUrl::make($this->getSampleUrl()))
            )
            ->setPrevMenu(__FUNCTION__, 0)
            ->showMenu();
        
    }
    
    public function sitesList(Nutgram $bot, string $data) {
        
        list( , $p ) = explode( '_', $data );
        
        $sites = $this->siteStore->findBy(
            [
                [ 'accepted', '=', true ],
                [ 'user_ids', 'CONTAINS', (int) $_ENV[ 'ADMIN_ID' ] ]
            ],
            [ '_id' => 'asc' ]
        );
        
        if( empty( $sites ) ) {
            
            $this->setTitle("🌐 سایت ها")
                ->addText('❌ در حال حاضر سایتی به ربات متصل نیست.')
                ->setMenuText()
                ->clearButtons()
                ->addPrevMenuBtn(1)
                ->setPrevMenu(__FUNCTION__, 1)
                ->showMenu();
            
        } else {
            
            $buttons = [];
            
            foreach( $sites as $site ) {
                
                $buttons[] = InlineKeyboardButton::make($site['name'], callback_data: "id_{$site['_id']}@site");
                
            }
            
            $this->setTitle("🌐 سایت ها")
                ->setMenuText()
                ->setMenuButtons($buttons, 20, $p, 2, true)
                ->addPrevMenuBtn(1)
                ->setPrevMenu(__FUNCTION__, 1)
                ->showMenu();
            
        }
        
    }
    
    public function site(Nutgram $bot, string $data) {
        
        list( , $id, $action ) = array_pad( explode( '_', $data ), 3, null );
        
        $site = $this->siteStore
            ->createQueryBuilder()
            ->disableCache()
            ->where( [ "_id", "==", $id ] )
            ->where( [ 'accepted', '=', true ] )
            ->where( [ 'user_ids', 'CONTAINS', (int) $_ENV[ 'ADMIN_ID' ] ] )
            ->join( function( $site ) {
                
                $samples = [];
                
                foreach( Fields::GROUPS as $group ) {
                    
                    $samples[ $group ] = $this->sampleStore->findBy( [
                        [ "site_id", "=", $site[ '_id' ] ],
                        [ "group", "=", $group ]
                    ], [ '_id' => 'asc' ] );
                    
                }
                
                return empty( array_filter( $samples ) ) ? [] : $samples;
                
            }, "samples" )
            ->getQuery()
            ->first();
        
        if( is_null( $site ) ) {
        
            $this->setTitle("🌐 سایت شماره {$id}")
                ->addText('⚠️ سایت مورد نظر یافت نشد.')
                ->setMenuText()
                ->clearButtons()
                ->addPrevMenuBtn(2)
                ->setPrevMenu(__FUNCTION__, 2)
                ->showMenu();
            
        } else {
            
            if( isset( $action ) ) {
                
                switch( $action ) {
                    
                    case 'aion':
                    
                        $site[ 'ai' ] = true;
                    
                    break;
                    
                    case 'aioff':
                    
                        $site[ 'ai' ] = false;
                    
                    break;
                    
                }
                
                $site = $this->siteStore->updateOrInsert( $site );
                
            }
            
            extract( $site );
            
            /*if( !empty( $ai ) ) {
                
                $ai_btn = InlineKeyboardButton::make('🟢 هوش مصنوعی', callback_data: "id_{$id}_aioff@site");
                $ai_text = "🟢 هوش مصنوعی برای این سایت فعال است.";
                
            } else {
                
                $ai_btn = InlineKeyboardButton::make('🔴 هوش مصنوعی', callback_data: "id_{$id}_aion@site");
                $ai_text = "🔴 هوش مصنوعی برای این سایت غیرفعال است.";
                
            }*/
            
            $this->setTitle("🌐 {$name}")
                ->addText("🆔 شناسه سایت: <code>{$id}</code>")
                ->addText("🕸 آیپی خروجی وب سایت: <code>{$ip}</code>")
                ->addText("");
                
                if( isset( $site[ 'dl_host' ] ) ) {
                    
                    $this->addText("📥 تنظیمات هاست دانلود")
                        ->addText("")
                        ->addText("🖥️ FTP هاست نیم: <code>{$site[ 'dl_host' ][ 'ftp_host' ]}</code>")
                        ->addText("👤 FTP یوزر نیم: <code>{$site[ 'dl_host' ][ 'ftp_username' ]}</code>")
                        ->addText("🔒 FTP پسورد: <tg-spoiler>{$site[ 'dl_host' ][ 'ftp_password' ]}</tg-spoiler>")
                        ->addText("🔢 FTP پورت: <code>{$site[ 'dl_host' ][ 'ftp_port' ]}</code>")
                        ->addText("🛣 FTP مسیر پایه: <code>{$site[ 'dl_host' ][ 'ftp_path' ]}</code>")
                        ->addText("🔗 نشانی هاست دانلود: {$site[ 'dl_host' ][ 'url' ]}");
                    
                } else {
                    
                    $this->addText("⚠️ تنظیمات مربوط به <b>📥 هاست دانلود</b> تکمیل نشده است.");
                    
                }
                
            $this->addText("");
            
            if( empty( $samples ) ) {
                
                $this->addText("⚠️ تنظیمات مربوط به <b>📝 الگوها</b> تکمیل نشده است.");
                
            } else {
                
                $this->addText("📝 الگوها")->addText("");
                
                foreach( $samples as $group => $sample ) {
                    
                    $count = count( $sample );
                    
                    $this->addText("گروه <b>" . Fields::getGroupName( $group ) . "</b>: <code>{$count}</code> الگو");
                    
                }
                
            }
            
            /*$this->addText("")
                ->addText( $ai_text )*/
            $this->setMenuText()
                ->clearButtons();
                
            if( !empty( $samples ) && isset( $site[ 'dl_host' ] ) ) {
                
                $this->addButtonRow(
                    InlineKeyboardButton::make('📝 ویرایش پست', callback_data: "id_{$id}_0@editPost"),
                    InlineKeyboardButton::make('🚀 ارسال پست', callback_data: "id_{$id}@sendPost")
                )->addButtonRow(
                    InlineKeyboardButton::make('📤 آپلود فایل', callback_data: "id_{$id}@fileUpload")
                );
                
            }
                
            $this->addButtonRow(
                InlineKeyboardButton::make('📊 ورود به پیشخوان', login_url: LoginUrl::make($this->getLoginUrl($id))),
                InlineKeyboardButton::make('👀 مشاهده وب سایت', url: $url)
            )
            ->addButtonRow(
                InlineKeyboardButton::make('📝 الگوها', login_url: LoginUrl::make($this->getSampleUrl($id))),
                InlineKeyboardButton::make('📥 هاست دانلود', callback_data: "id_{$id}@dlHost")
            )
            /*->addButtonRow(
                $ai_btn
            )*/
            ->addPrevMenuBtn(2)
            ->setPrevMenu(__FUNCTION__, 2)
            ->showMenu();
            
        }
        
    }
    
    public function dlHost(Nutgram $bot, string $data) {
        
        list( , $id ) = explode( '_', $data );
        
        $site = $this->siteStore->findById( $id );
        
        if( is_null( $site ) || empty( $site[ 'accepted' ] ) || !in_array( $_ENV[ 'ADMIN_ID' ], $site[ 'user_ids' ] ) ) {
            
            $this->setTitle("🌐 سایت شماره {$id}")
                ->addText('⚠️ سایت مورد نظر یافت نشد.')
                ->setMenuText()
                ->clearButtons()
                ->addPrevMenuBtn(3)
                ->setPrevMenu(__FUNCTION__, 3)
                ->showMenu();
            
        } else {
            
            extract( $site );
            
            $this->next_data[ '_' . __FUNCTION__ ] = [
                'title' => "📥 تنظیمات هاست دانلود",
                'site' => $site,
                'prev_data' => isset( $dl_host ) ? $dl_host : [],
                'step' => 'ftp_host',
                'steps' => [
                    'ftp_host' => ["1️⃣ لطفا هاست نیم FTP سایت <b>{$name}</b> را وارد کنید."],
                    'ftp_username' => ["2️⃣ لطفا یوزر نیم FTP سایت <b>{$name}</b> را وارد کنید."],
                    'ftp_password' => ["3️⃣ لطفا پسورد FTP سایت <b>{$name}</b> را وارد کنید."],
                    'ftp_port' => ["4️⃣ لطفا پورت FTP سایت <b>{$name}</b> را وارد کنید."],
                    'ftp_path' => ["5️⃣ لطفا مسیر پایه FTP سایت <b>{$name}</b> را وارد کنید."],
                    'url' => ["6️⃣ لطفا نشانی هاست دانلود سایت <b>{$name}</b> را وارد کنید."]
                ],
                'data' => []
            ];
            
            $text = $this->handleSubStep( $bot, '_' . __FUNCTION__, true );
            
            $this->setTitle("📥 تنظیمات هاست دانلود")
                ->addText($text)
                ->setMenuText()
                ->clearButtons()
                ->addPrevMenuBtn(3, null, '_' . __FUNCTION__)
                ->orNext('_dlHost')
                ->showMenu();
            
        }
        
    }
    
    public function editPost(Nutgram $bot, string $data) {
        
        list( , $id, $error ) = explode( '_', $data );
        
        $site = $this->siteStore->findById( $id );
        
        if( is_null( $site ) || empty( $site[ 'accepted' ] ) || !in_array( $_ENV[ 'ADMIN_ID' ], $site[ 'user_ids' ] ) ) {
            
            $this->setTitle("🌐 سایت شماره {$id}")
                ->addText('⚠️ سایت مورد نظر یافت نشد.')
                ->setMenuText()
                ->clearButtons()
                ->addPrevMenuBtn(3)
                ->setPrevMenu(__FUNCTION__, 3)
                ->showMenu();
            
        } else {
            
            $this->setTitle("📝 ویرایش پست");
            
            if( $error == 1 && isset( $this->next_data[ 'site_edit' ][ 'error' ] ) ) {
                
                $error_text = $this->next_data[ 'site_edit' ][ 'error' ];
                
                $this->addText("⚠️ {$error_text}")->addText('');
                
            }
            
            $this->next_data[ 'site_edit' ] = $site;
            
            $this->addText("✍️ لطفا لینک یا آیدی پستی که قصد ویرایش آن را دارید وارد کنید.")
                ->setMenuText()
                ->clearButtons()
                ->addPrevMenuBtn(3, null, 'site_edit')
                ->setPrevMenu(__FUNCTION__, 3)
                ->orNext('checkEditInput')
                ->showMenu();
            
        }
        
    }
    
    public function checkEditInput(Nutgram $bot) {
        
        $text = $bot->message()->getText();
        $site =& $this->next_data[ 'site_edit' ];
        
        if( $this->isUrlMessage() ) {
            
            $site_host = Uri::new( $site[ 'url' ] )->getHost();
            $url_host = Uri::new( $text )->getHost();
            
            if( $site_host == $url_host ) {
                
                $ok = $this->sendPostRequest(
                    "{$site[ 'api' ]}?action=wpttb_post_data",
                    [
                        'input' => $text,
                        'user' => $this->bot->userId()
                    ],
                    true
                );
                
                if( $ok !== false && json_decode( $ok )->success ) {
                    
                    $data = json_decode( $ok, true )[ 'data' ];
                    
                    $fields = new Fields( $data[ 'wpttb' ][ 'sample_group' ] );
                    
                    $temp_path = TEMP_PATH . $this->generateRandomString();
                    
                    if( !file_exists( $temp_path ) ) mkdir( $temp_path, 0755, true );
                    
                    $sample = $this->sampleStore->findById( $data[ 'wpttb' ][ 'sample_id' ] );
                    
                    if( $sample === null ) {
                        
                        $samples = $this->sampleStore->findBy(
                            [
                                [ 'site_id', '==', $site[ '_id' ] ],
                                [ 'group', '=', $data[ 'wpttb' ][ 'sample_group' ] ]
                            ]
                        );
                        
                        $sample = $samples[ array_rand( $samples ) ];
                        
                    }
                    
                    if(
                        !empty( $data[ 'wpttb' ][ 'fields' ][ 'lyric' ] ) &&
                        is_array( $data[ 'wpttb' ][ 'fields' ][ 'lyric' ] )
                    ) {
                        
                        $data[ 'wpttb' ][ 'fields' ][ 'lyric' ] = implode( "\n", $data[ 'wpttb' ][ 'fields' ][ 'lyric' ] );
                        
                    }
                    
                    $fields_value = array_filter( $data[ 'wpttb' ][ 'fields' ] );
                    
                    $this->next_data[ 'post_data' ] = [
                        'site_id' => $site[ '_id' ],
                        'tmp' => $temp_path,
                        'group' => $data[ 'wpttb' ][ 'sample_group' ],
                        'sample' => $sample,
                        'group_fields' => $fields->getFields(),
                        'fields' => $fields_value,
                        'post' => [
                            'id' => $data[ 'id' ],
                            'fields' => $fields_value,
                            'type' => $data[ 'wpttb' ][ 'extra' ][ 'type' ],
                            'base_type' => $data[ 'wpttb' ][ 'extra' ][ 'type' ],
                            'flop' => $data[ 'wpttb' ][ 'extra' ][ 'flop' ],
                            'status' => $data[ 'status' ],
                            'base_status' => $data[ 'status' ]
                        ]
                    ];
                    
                    if( isset( $data[ 'date' ] ) ) {
                        
                        $this->next_data[ 'post_data' ][ 'post' ][ 'date' ] = $data[ 'date' ];
                        
                    }
                    
                    unset( $this->next_data[ 'site_edit' ] );
                    
                    $this->disableMenu()
                        ->loadMenu( 'postView', "id_{$site[ '_id' ]}" );
                    
                } else {
                    
                    if( $ok === false ) {
                        
                        $site[ 'error' ] = 'سرور سایت در دسترس نیست!';
                        $this->disableMenu()
                            ->loadMenu( 'editPost', "id_{$site[ '_id' ]}_1" );
                        
                    } else {
                        
                        $site[ 'error' ] = json_decode( $ok )->data->msg;
                        $this->disableMenu()
                            ->loadMenu( 'editPost', "id_{$site[ '_id' ]}_1" );
                        
                    }
                    
                }
                
            } else {
                
                $site[ 'error' ] = 'لینک وارد شده مربوط به این سایت نیست.';
                $this->disableMenu()
                    ->loadMenu( 'editPost', "id_{$site[ '_id' ]}_1" );
                
            }
            
        } else {
            
            if(
                is_numeric( $text ) &&
                $text > 0 &&
                $text == ( int ) $text
            ) {
                
                $ok = $this->sendPostRequest(
                    "{$site[ 'api' ]}?action=wpttb_post_data",
                    [
                        'input' => (int) $text,
                        'user' => $this->bot->userId()
                    ],
                    true
                );
                
                if( $ok !== false && json_decode( $ok )->success ) {
                    
                    $data = json_decode( $ok, true )[ 'data' ];
                    
                    $fields = new Fields( $data[ 'wpttb' ][ 'sample_group' ] );
                    
                    $temp_path = TEMP_PATH . $this->generateRandomString();
                    
                    if( !file_exists( $temp_path ) ) mkdir( $temp_path, 0755, true );
                    
                    $sample = $this->sampleStore->findById( $data[ 'wpttb' ][ 'sample_id' ] );
                    
                    if( $sample === null ) {
                        
                        $samples = $this->sampleStore->findBy(
                            [
                                [ 'site_id', '==', $site[ '_id' ] ],
                                [ 'group', '=', $data[ 'wpttb' ][ 'sample_group' ] ]
                            ]
                        );
                        
                        $sample = $samples[ array_rand( $samples ) ];
                        
                    }
                    
                    if(
                        !empty( $data[ 'wpttb' ][ 'fields' ][ 'lyric' ] ) &&
                        is_array( $data[ 'wpttb' ][ 'fields' ][ 'lyric' ] )
                    ) {
                        
                        $data[ 'wpttb' ][ 'fields' ][ 'lyric' ] = implode( "\n", $data[ 'wpttb' ][ 'fields' ][ 'lyric' ] );
                        
                    }
                    
                    $fields_value = array_filter( $data[ 'wpttb' ][ 'fields' ] );
                    
                    $this->next_data[ 'post_data' ] = [
                        'site_id' => $site[ '_id' ],
                        'tmp' => $temp_path,
                        'group' => $data[ 'wpttb' ][ 'sample_group' ],
                        'sample' => $sample,
                        'group_fields' => $fields->getFields(),
                        'fields' => $fields_value,
                        'post' => [
                            'id' => $data[ 'id' ],
                            'fields' => $fields_value,
                            'type' => $data[ 'wpttb' ][ 'extra' ][ 'type' ],
                            'base_type' => $data[ 'wpttb' ][ 'extra' ][ 'type' ],
                            'flop' => $data[ 'wpttb' ][ 'extra' ][ 'flop' ],
                            'status' => $data[ 'status' ],
                            'base_status' => $data[ 'status' ]
                        ]
                    ];
                    
                    if( isset( $data[ 'date' ] ) ) {
                        
                        $this->next_data[ 'post_data' ][ 'post' ][ 'date' ] = $data[ 'date' ];
                        
                    }
                    
                    unset( $this->next_data[ 'site_edit' ] );
                    
                    $this->disableMenu()
                        ->loadMenu( 'postView', "id_{$site[ '_id' ]}" );
                    
                } else {
                    
                    if( $ok === false ) {
                        
                        $site[ 'error' ] = 'سرور سایت در دسترس نیست!';
                        $this->disableMenu()
                            ->loadMenu( 'editPost', "id_{$site[ '_id' ]}_1" );
                        
                    } else {
                        
                        $site[ 'error' ] = json_decode( $ok )->data->msg;
                        $this->disableMenu()
                            ->loadMenu( 'editPost', "id_{$site[ '_id' ]}_1" );
                        
                    }
                    
                }
                
            } else {
                
                $site[ 'error' ] = 'مقدار وارد شده معتبر نیست.';
                $this->disableMenu()
                    ->loadMenu( 'editPost', "id_{$site[ '_id' ]}_1" );
                
            }
            
        }
        
    }
    
    public function sendPost(Nutgram $bot, string $data) {
        
        list( , $id ) = explode( '_', $data );
        
        $site = $this->siteStore
            ->createQueryBuilder()
            ->disableCache()
            ->where( [ "_id", "==", $id ] )
            ->where( [ 'accepted', '=', true ] )
            ->where( [ 'user_ids', 'CONTAINS', (int) $_ENV[ 'ADMIN_ID' ] ] )
            ->join( function( $site ) {
                
                $samples = [];
                
                foreach( Fields::GROUPS as $group ) {
                    
                    $samples[ $group ] = $this->sampleStore->findBy( [
                        [ "site_id", "=", $site[ '_id' ] ],
                        [ "group", "=", $group ]
                    ], [ '_id' => 'asc' ] );
                    
                }
                
                return $samples;
                
            }, "samples" )
            ->getQuery()
            ->first();
        
        if( is_null( $site ) || !isset( $site[ 'samples' ] ) ) {
        
            $this->setTitle("🌐 سایت شماره {$id}")
                ->addText('⚠️ سایت مورد نظر یافت نشد.')
                ->setMenuText()
                ->clearButtons()
                ->addPrevMenuBtn(3)
                ->setPrevMenu(__FUNCTION__, 3)
                ->showMenu();
                
        } else {
            
            $buttons = [];
            
            foreach( Fields::GROUPS as $group ) {
                
                if( !empty( $site[ 'samples' ][ $group ] ) ) {
                    
                    $buttons[] = InlineKeyboardButton::make(Fields::getGroupName( $group ), callback_data: "id_{$site['_id']}_{$group}@source");
                    
                }
                
            }
            
            $this->setTitle("🧩 انتخاب الگو")
                ->addText('👇 لطفا گروه الگویی پست را انتخاب کنید.')
                ->setMenuText()
                ->setMenuButtons($buttons, 20, 1, 3, true)
                ->addPrevMenuBtn(3)
                ->setPrevMenu(__FUNCTION__, 3)
                ->showMenu();
            
        }
        
    }
    
    public function source(Nutgram $bot, string $data) {
        
        list( , $id, $group ) = explode( '_', $data );
        
        $site = $this->siteStore->findById( $id );
        
        if( is_null( $site ) || empty( $site[ 'accepted' ] ) || !in_array( $_ENV[ 'ADMIN_ID' ], $site[ 'user_ids' ] ) ) {
            
            $this->setTitle("🌐 سایت شماره {$id}")
                ->addText('⚠️ سایت مورد نظر یافت نشد.')
                ->setMenuText()
                ->clearButtons()
                ->addPrevMenuBtn(4)
                ->setPrevMenu(__FUNCTION__, 4)
                ->showMenu();
            
        } else {
            
            $samples = $this->sampleStore->findBy(
                [
                    [ 'site_id', '==', $id ],
                    [ 'group', '=', $group ]
                ]
            );
            
            if( is_null( $samples ) ) {
                
                $this->setTitle("🧩 گروه الگویی {$group}")
                    ->addText('⚠️ گروه الگویی مورد نظر یافت نشد.')
                    ->setMenuText()
                    ->clearButtons()
                    ->addPrevMenuBtn(4)
                    ->setPrevMenu(__FUNCTION__, 4)
                    ->showMenu();
                
            } else {
                
                $fields = new Fields( $group );
                
                $temp_path = TEMP_PATH . $this->generateRandomString();
                
                if( !file_exists( $temp_path ) ) mkdir( $temp_path, 0755, true );
                
                $this->next_data[ 'post_data' ] = [
                    'site_id' => $site[ '_id' ],
                    'tmp' => $temp_path,
                    'group' => $group,
                    'sample' => $samples[ array_rand( $samples ) ],
                    'group_fields' => $fields->getFields(),
                    'post' => [
                        'id' => 0
                    ]
                ];
            
                $this->setTitle("🔗 لینک منبع پست")
                    ->addText("✍️ لطفا لینک منبع را وارد کنید.")
                    ->addText("")
                    ->addText("👇 از لینک منبع جهت استخراج اتوماتیک موارد زیر در صورت وجود استفاده می شود:")
                    ->addText("");
                    
                foreach( $fields->getFields() as $field ) {
                    
                    if( /*!empty( $field[ 'ai' ] ) ||*/ !empty( $field[ 'source' ] ) ) {
                        
                        $this->addText( "- <b>{$field[ 'name' ]}</b>" );
                        
                    }
                    
                }
                
                $key = array_key_first( $this->next_data[ 'post_data' ][ 'group_fields' ] );
                
                $this->addText("")
                    ->addText("📌 در صورتی که تمایلی به وارد کردن لینک منبع ندارید دکمه <b>❌ منبع ندارد</b> را انتخاب کنید.")
                    ->setMenuText()
                    ->clearButtons()
                    ->addButtonRow(
                        InlineKeyboardButton::make('❌ منبع ندارد', callback_data: "{$key}|1@editField")
                    )
                    ->addPrevMenuBtn(4, null, 'post_data')
                    ->setPrevMenu(__FUNCTION__, 4)
                    ->orNext('checkSourceInput')
                    ->showMenu();
            
            }
            
        }
        
    }
    
    public function checkSourceInput(Nutgram $bot) {
        
        $text = $bot->message()->getText();
        
        if( $this->isUrlMessage() ) {
            
            if( $this->isLocalSite( $text ) ) {
                
                $site = $this->siteStore->findById( $this->next_data[ 'post_data' ][ 'site_id' ] );
                
                $ok = $this->sendPostRequest(
                    "{$site[ 'api' ]}?action=wpttb_post_data",
                    [
                        'input' => $text,
                        'user' => 0
                    ],
                    true
                );
                
                if( $ok !== false && json_decode( $ok )->success ) {
                    
                    $data = json_decode( $ok, true )[ 'data' ];
                    
                    if(
                        !empty( $data[ 'wpttb' ][ 'fields' ][ 'lyric' ] ) &&
                        is_array( $data[ 'wpttb' ][ 'fields' ][ 'lyric' ] )
                    ) {
                        
                        $data[ 'wpttb' ][ 'fields' ][ 'lyric' ] = implode( "\n", $data[ 'wpttb' ][ 'fields' ][ 'lyric' ] );
                        
                    }
                    
                    $this->next_data[ 'post_data' ][ 'fields' ] = [];
                    
                    foreach( $this->next_data[ 'post_data' ][ 'group_fields' ] as $key => $field ) {
                        
                        if( $key == 'cover_url' ) {
                            
                            continue;
                            
                        }
                        
                        if( !empty( $data[ 'wpttb' ][ 'fields' ][ $key ] ) ) {
                            
                            $this->next_data[ 'post_data' ][ 'fields' ][ $key ] = $data[ 'wpttb' ][ 'fields' ][ $key ];
                            
                        }
                        
                    }
                    
                    $next_key = null;
                    
                    foreach( $this->next_data[ 'post_data' ][ 'group_fields' ] as $key => $field ) {
                        
                        if( !isset( $this->next_data[ 'post_data' ][ 'fields' ][ $key ] ) ) {
                            
                            $next_key = $key;
                            
                            break;
                            
                        }
                        
                    }
                    
                    if( isset( $next_key ) ) {
                        
                        $this->disableMenu()
                            ->loadMenu( 'editField', "{$next_key}|1" );
                        
                    } else {
                        
                        $this->disableMenu()
                            ->loadMenu( 'postView', "id_{$site[ '_id' ]}" );
                        
                    }
                    
                    exit;
                    
                }
                
            }
            
            $response = $this->getPage( $text );
            
            if( $response !== false ) {
                    
                $this->doc->html( $response );
                $readability = $this->getReadability( $response );
                
                if( $readability !== false && $this->isMusicArticle( $readability->getTitle() ) ) {
                    
                    $this->next_data[ 'post_data' ][ 'fields' ] = [];
                    
                    foreach( $this->next_data[ 'post_data' ][ 'group_fields' ] as $key => $field ) {
                        
                        if( empty( $field[ 'source' ] ) ) continue;
                        
                        switch( $key ) {
                            
                            case 'url_128':
                            
                                $mp3_nodes = $this->doc->find( 'a[href*=".mp3"]' );
                                
                                if( $mp3_nodes->count() > 0 && $mp3_nodes->count() <= 2 ) {
                                    
                                    $mp3_links = [];
                                
                                    $mp3_nodes->each( function( $node ) use ( &$mp3_links ) {
                                        
                                        $url = trim( $node->attr( 'href' ) );
                                        $size = $this->getRemoteFileSize( $url );
                                        
                                        if( $size > 0 ) {
                                            
                                            $mp3_links[] = [
                                                'url' => $url,
                                                'size' => $size
                                            ];
                                            
                                        }
                                        
                                    } );
                                    
                                    if( !empty( $mp3_links ) ) {
                                        
                                        $size_arr = array_unique( array_column( $mp3_links, 'size' ) );
                                        $mp3_links = array_intersect_key( $mp3_links, $size_arr );
                                        
                                    }
                                    
                                    if( count( $mp3_links ) == 2 ) {
                                        
                                        usort( $mp3_links, function( $a, $b ) {
                                            
                                            return $a[ 'size' ] <=> $b[ 'size' ];
                                            
                                        } );
                                        
                                        $this->next_data[ 'post_data' ][ 'fields' ][ 'url_128' ] = $mp3_links[ 0 ][ 'url' ];
                                        $this->next_data[ 'post_data' ][ 'fields' ][ 'url_320' ] = $mp3_links[ 1 ][ 'url' ];
                                        
                                    } else {
                                        
                                        if( count( $mp3_links ) == 1 ) {
                                            
                                            $file_path = $this->next_data[ 'post_data' ][ 'tmp' ] . '/mp3.mp3';
                                            
                                            $ok = $this->filePartialDownload( $mp3_links[ 0 ][ 'url' ], $file_path );
                                            
                                            if( $ok ) {
                                                
                                                $audio = Audio::read( $file_path );
                                                $bitrate = $audio->getMetadata()->getBitrate();
                                                
                                                if( !is_null( $bitrate ) ) {
                                                
                                                    $bitrate = $this->nearestBitrate( $bitrate );
                                                    $key = $bitrate == '128kbps' ? 'url_128' : 'url_320';
                                                    
                                                    $this->next_data[ 'post_data' ][ 'fields' ][ $key ] = $mp3_links[ 0 ][ 'url' ];
                                                
                                                }
                                                
                                                @unlink( $file_path );
                                                
                                            }
                                            
                                        }
                                        
                                    }
                                
                                }
                            
                            break;
                            
                            case 'teaser_url':
                            
                                $mp4_nodes = $this->doc->find( 'a[href*=".mp4"]' );
                                
                                $mp4_links = [];
                                
                                $mp4_nodes->each( function( $node ) use ( &$mp4_links ) {
                                    
                                    $url = trim( $node->attr( 'href' ) );
                                    $path = parse_url( $url, PHP_URL_PATH );
                                    
                                    $mp4_links[] = [
                                        'url' => $url,
                                        'path' => $path ?? '',
                                        'text' => $node->text(),
                                        'title' => $node->attr( 'title' )
                                    ];
                                    
                                } );
                                
                                foreach( $mp4_links as $mp4_data ) {
                                    
                                    if(
                                        str_contains( $mp4_data[ 'text' ], 'تیزر' ) ||
                                        str_contains( $mp4_data[ 'text' ], 'دمو' ) ||
                                        str_contains( $mp4_data[ 'title' ] ?? '', 'تیزر' ) ||
                                        str_contains( $mp4_data[ 'title' ] ?? '', 'دمو' ) ||
                                        str_contains( strtolower( $mp4_data[ 'path' ] ), 'teaser' ) ||
                                        str_contains( strtolower( $mp4_data[ 'path' ] ), 'demo' )
                                    ) {
                                        
                                        $this->next_data[ 'post_data' ][ 'fields' ][ 'teaser_url' ] = $mp4_data[ 'url' ];
                                        
                                        break;
                                        
                                    }
                                    
                                }
                            
                            break;
                            
                            /*case 'cover_url':
                            
                                if( $readability->getImage() ) {
                                        
                                    $ok = $this->fileDownload( $readability->getImage(), $this->next_data[ 'post_data' ][ 'tmp' ] . "/{$key}.jpg" );
                                    
                                    if( $ok ) {
                                        
                                        $this->applyImageEffects( $this->next_data[ 'post_data' ][ 'tmp' ] . "/{$key}.jpg" );
                                        
                                        $this->next_data[ 'post_data' ][ 'fields' ][ $key ] = $this->tempPathToUrl( $this->next_data[ 'post_data' ][ 'tmp' ] . "/{$key}.jpg" );
                                        
                                    }
                                
                                }
                            
                            break;*/
                            
                        }
                        
                    }
                    
                    $this->next_data[ 'post_data' ][ 'fields' ] = array_filter_null( $this->next_data[ 'post_data' ][ 'fields' ] );
                    
                    $key = array_key_first( $this->next_data[ 'post_data' ][ 'group_fields' ] );
                    
                    $this->disableMenu()
                        ->loadMenu( 'editField', "{$key}|1" );
                    
                } else {
                    
                    $this->disableMenu()
                        ->setTitle("🔗 لینک منبع پست")
                        ->addText('⚠️ خطا در پردازش لینک منبع')
                        ->setMenuText()
                        ->showMenu();
                    
                }
                
            } else {
                
                $this->disableMenu()
                    ->setTitle("🔗 لینک منبع پست")
                    ->addText('⚠️ خطا در بارگیری لینک منبع')
                    ->setMenuText()
                    ->showMenu();
                
            }
            
        } else {
        
            $this->disableMenu()
                ->setTitle("🔗 لینک منبع پست")
                ->addText('⚠️ لینک منبع معتبر نیست.')
                ->setMenuText()
                ->showMenu();
            
        }
        
    }
    
    public function postView(Nutgram $bot, string $data) {
        
        list( , $id, $action, $action_value ) = array_pad( explode( '_', $data ), 4, null );
        
        $site = $this->siteStore->findById( $id );
        
        if( is_null( $site ) || empty( $site[ 'accepted' ] ) || !in_array( $_ENV[ 'ADMIN_ID' ], $site[ 'user_ids' ] ) ) {
            
            $this->setTitle("🌐 سایت شماره {$id}")
                ->addText('⚠️ سایت مورد نظر یافت نشد.')
                ->setMenuText()
                ->clearButtons()
                ->addPrevMenuBtn(5, null, 'post_data')
                ->setPrevMenu(__FUNCTION__, 5)
                ->showMenu();
            
        } else {
            
            if( isset( $this->next_data[ 'post_data' ][ 'post' ][ 'id' ] ) ) {
                
                $post_id = $this->next_data[ 'post_data' ][ 'post' ][ 'id' ];
                
            } else {
                
                $post_id = $this->next_data[ 'post_data' ][ 'post' ][ 'id' ] = 0;
                
            }
            
            if( isset( $action ) ) {
            
                switch( $action ) {
                    
                    case 'draft':
                    
                        $this->next_data[ 'post_data' ][ 'post' ][ 'status' ] = 'draft';
                    
                    break;
                    
                    case 'publish':
                    
                        $this->next_data[ 'post_data' ][ 'post' ][ 'status' ] = 'publish';
                    
                    break;
                    
                    case 'com':
                    
                        $this->next_data[ 'post_data' ][ 'post' ][ 'type' ] = 'com';
                    
                    break;
                    
                    case 'pub':
                    
                        $this->next_data[ 'post_data' ][ 'post' ][ 'type' ] = 'pub';
                    
                    break;
                    
                    case 'nor':
                    
                        $this->next_data[ 'post_data' ][ 'post' ][ 'type' ] = 'nor';
                    
                    break;
                    
                    case 'now':
                    
                        unset( $this->next_data[ 'post_data' ][ 'post' ][ 'time' ] );
                        unset( $this->next_data[ 'post_data' ][ 'post' ][ 'status' ] );
                    
                    break;
                    
                    case 'td':
                    case 'tu':
                    
                        if( isset( $action_value ) ) {
                    
                            $time = $this->next_data[ 'post_data' ][ 'post' ][ 'time' ] ?? 0;
                            $time = $action == 'tu' ? $time + $action_value : $time - $action_value;
                            $time = $time < 0 ? 0 : $time;
                            $this->next_data[ 'post_data' ][ 'post' ][ 'time' ] = $time;
                            if( $time == 0 ) {
                                
                                unset( $this->next_data[ 'post_data' ][ 'post' ][ 'status' ] );
                                
                            } else {
                                
                                $this->next_data[ 'post_data' ][ 'post' ][ 'status' ] = 'publish';
                                
                            }
                        
                        }
                    
                    break;
                    
                    case 'cf':
                    
                        if( isset( $action_value ) ) {
                            
                            $flop = $this->next_data[ 'post_data' ][ 'post' ][ 'flop' ] ?? 'no';
                            
                            if( $flop != $action_value ) {
                                
                                $this->flopImage( $this->next_data[ 'post_data' ][ 'tmp' ] . '/cover_url.jpg' );
                                
                                $this->next_data[ 'post_data' ][ 'post' ][ 'flop' ] = $action_value;
                                
                            }
                            
                        }
                    
                    break;
                    
                }
            
            }
            
            if(
                isset( $this->next_data[ 'post_data' ][ 'post' ][ 'time' ] ) &&
                $this->next_data[ 'post_data' ][ 'post' ][ 'time' ] != 0
            ) {
                
                $time = $this->next_data[ 'post_data' ][ 'post' ][ 'time' ];
                $parts = $time > 60 ? 2 : 1;
                $diff = Carbon::now()->locale( 'fa_IR' )
                    ->addMinutes( $time )
                    ->diffForHumans( [
                    'parts' => $parts,
                    'join' => ' و ',
                    'options' => Carbon::CEIL
                ] );
                
                $time_btn_text = "🕗 زمان انتشار پست: {$diff}";
                $now_btn_text = "اکنون";
                
            } else {
                
                $time_btn_text = "🕗 زمان انتشار پست";
                $now_btn_text = "✅ اکنون";
                
            }
            
            if( isset( $this->next_data[ 'post_data' ][ 'post' ][ 'status' ] ) ) {
                
                $sv = $this->next_data[ 'post_data' ][ 'post' ][ 'status' ];
                
                $draft_btn_text = $sv == 'draft' ? '✅ پیش نویس' : 'پیش نویس';
                $publish_btn_text = $sv == 'publish' ? '✅ انتشار' : 'انتشار';
                
            } else {
                
                $draft_btn_text = 'پیش نویس';
                $publish_btn_text = '✅ انتشار';
                
            }
            
            if( isset( $this->next_data[ 'post_data' ][ 'post' ][ 'flop' ] ) ) {
                
                $cf = $this->next_data[ 'post_data' ][ 'post' ][ 'flop' ];
                
                $fyes_btn_text = $cf == 'yes' ? '✅ بله' : 'بله';
                $fno_btn_text = $cf == 'no' ? '✅ خیر' : 'خیر';
                
            } else {
                
                $fyes_btn_text = 'بله';
                $fno_btn_text = '✅ خیر';
                
            }
            
            if( isset( $this->next_data[ 'post_data' ][ 'post' ][ 'type' ] ) ) {
                
                $tv = $this->next_data[ 'post_data' ][ 'post' ][ 'type' ];
                
                switch( $tv ) {
                    
                    case 'pub':
                    
                        $com_btn_text = 'بزودی';
                        $pub_btn_text = '✅ منتشر';
                        $nor_btn_text = 'عادی';
                    
                    break;
                    
                    case 'com':
                    
                        $com_btn_text = '✅ بزودی';
                        $pub_btn_text = 'منتشر';
                        $nor_btn_text = 'عادی';
                    
                    break;
                    
                    case 'nor':
                    
                        $com_btn_text = 'بزودی';
                        $pub_btn_text = 'منتشر';
                        $nor_btn_text = '✅ عادی';
                    
                    break;
                    
                }
                
            } else {
                
                $com_btn_text = 'بزودی';
                $pub_btn_text = 'منتشر';
                $nor_btn_text = '✅ عادی';
                
            }
            
            $this->setTitle("👁 مشاهده پست");
                
            if( isset( $this->next_data[ 'post_data' ][ 'fields' ][ 'cover_url' ] ) ) {
                
                $this->attachImage( $this->next_data[ 'post_data' ][ 'fields' ][ 'cover_url' ] );
                
            } else {
                
                $this->attachImage( "{$_ENV[ 'BASE_URL' ]}wpttb_cover.png" );
                
            }
            
            $buttons = [];
            $required_value = [];
            $first_empty_key = null;
            
            foreach( $this->next_data[ 'post_data' ][ 'group_fields' ] as $key => $field ) {
                
                $required = $field[ 'required' ] ? '*' : '-';
                
                $text = "{$required} {$field[ 'name' ]}: ";
                
                if( isset( $this->next_data[ 'post_data' ][ 'fields' ][ $key ] ) ) {
                    
                    $fbtn_prefix = "🟢 ";
                    
                    if( $required == '*' ) {
                        
                        $required_value[] = true;
                        
                    }
                    
                    if( str_contains( $key, 'url' ) || $key == 'lyric' ) {
                        
                        if( $key == 'cover_url' ) {
                            
                            $text .= "تصویر بالا ☝️";
                            
                        } else {
                            
                            if( isset( $this->next_data[ 'post_data' ][ 'fields_menu' ][ $key ] ) ) {
                                
                                $text .= "<pre>{$this->next_data[ 'post_data' ][ 'fields_menu' ][ $key ]}</pre>";
                                
                            } else {
                                
                                if( $key == 'lyric' ) {
                                    
                                    $lyric = $this->prepareMulti( $this->next_data[ 'post_data' ][ 'fields' ][ $key ] );
                                    
                                    $text .= "{$lyric}";
                                    
                                } else {
                                    
                                    if( $this->next_data[ 'post_data' ][ 'fields' ][ $key ] != 'local' ) {
                                        
                                        $text .= "<pre>{$this->next_data[ 'post_data' ][ 'fields' ][ $key ]}</pre>";
                                        
                                    }
                                    
                                }
                                
                            }
                            
                        }
                        
                    } else {
                        
                        if( is_array( $this->next_data[ 'post_data' ][ 'fields' ][ $key ] ) ) {
                            
                            $text .= implode( $field[ 'seprator' ], array_map( function( $val ) {
                                
                                return "<b>" . $val . "</b>";
                                
                            }, $this->next_data[ 'post_data' ][ 'fields' ][ $key ] ) );
                            
                        } else {
                            
                            $text .= "<b>" . $this->next_data[ 'post_data' ][ 'fields' ][ $key ] . "</b>";
                            
                        }
                        
                    }
                    
                } else {
                    
                    if( !isset( $first_empty_key ) ) {
                        
                        $first_empty_key = $key;
                        
                    }
                    
                    $fbtn_prefix = "🔴 ";
                    
                    if( $required == '*' ) {
                        
                        $required_value[] = false;
                        
                    }
                    
                    $text .= "🚫 تعریف نشده";
                    
                }
                
                $this->addText($text);
                
                if( $post_id == 0 || $required != '*' ) {
                
                    $buttons[] = InlineKeyboardButton::make($fbtn_prefix . $field[ 'name' ], callback_data: "{$key}@editField");
                
                }
                
            }
                
            $this->addText("")
                ->addText("📌 برای ویرایش هر یک از آیتم های بالا ☝️ از دکمه مربوط به آن آیتم در منوی پایین 👇 استفاده کنید.");
                
            if( $post_id == 0 ) {
                
                $this->addText("📌 پر کردن آیتم های * دار اجباری است.");
                
            } else {
                
                $this->addText("📌 امکان ویرایش آیتم های * دار نیست.");
                
            }
            
            $this->setMenuText(false, true)
                ->clearButtons();
                
            if( isset( $first_empty_key ) ) {
                
                $this->addButtonRow(
                    InlineKeyboardButton::make("🔢 ویرایش متوالی فیلدهای خالی", callback_data: "{$first_empty_key}|2@editField")
                );
                
            }
                
            $this->setMenuButtons($buttons, 20, 1, 3, false);
                
            if( $post_id == 0 && count( $required_value ) === count( array_filter( $required_value ) ) ) {
                
                $this->addButtonRow(
                    InlineKeyboardButton::make("🚀 ارسال پست", callback_data: "id_{$id}@dupCheck")
                );
                
            }
            
            $status = $this->next_data[ 'post_data' ][ 'post' ][ 'status' ] ?? 'publish';
            $time = $this->next_data[ 'post_data' ][ 'post' ][ 'time' ] ?? 0;
            
            if(
                $post_id != 0 &&
                (
                    $this->next_data[ 'post_data' ][ 'fields' ] != $this->next_data[ 'post_data' ][ 'post' ][ 'fields' ] ||
                    $this->next_data[ 'post_data' ][ 'post' ][ 'base_status' ] != $status ||
                    $time != 0 ||
                    $this->next_data[ 'post_data' ][ 'post' ][ 'base_type' ] != $this->next_data[ 'post_data' ][ 'post' ][ 'type' ]
                )
            ) {
                
                $this->addButtonRow(
                    InlineKeyboardButton::make("📝 ویرایش پست", callback_data: "id_{$id}@dupCheck")
                );
                
            }
                
            $this->addButtonRow(
                InlineKeyboardButton::make($com_btn_text, callback_data: "id_{$id}_com@postView"),
                InlineKeyboardButton::make($pub_btn_text, callback_data: "id_{$id}_pub@postView"),
                InlineKeyboardButton::make($nor_btn_text, callback_data: "id_{$id}_nor@postView"),
                InlineKeyboardButton::make("🗂 نوع پست:", callback_data: "pass")
            );
            
            if( $post_id == 0 && $time == 0 ) {
            
                $this->addButtonRow(
                    InlineKeyboardButton::make($draft_btn_text, callback_data: "id_{$id}_draft@postView"),
                    InlineKeyboardButton::make($publish_btn_text, callback_data: "id_{$id}_publish@postView"),
                    InlineKeyboardButton::make("📝 وضعیت پست:", callback_data: "pass")
                );
            
            }
            
            if( $post_id != 0 && $this->next_data[ 'post_data' ][ 'post' ][ 'base_status' ] == 'draft' ) {
                
                $this->addButtonRow(
                    InlineKeyboardButton::make($draft_btn_text, callback_data: "id_{$id}_draft@postView"),
                    InlineKeyboardButton::make($publish_btn_text, callback_data: "id_{$id}_publish@postView"),
                    InlineKeyboardButton::make("📝 وضعیت پست:", callback_data: "pass")
                );
                
            }
                
            if(
                isset( $this->next_data[ 'post_data' ][ 'fields' ][ 'cover_url' ] ) &&
                $post_id == 0
            ) {
                
                $this->addButtonRow(
                    InlineKeyboardButton::make($fyes_btn_text, callback_data: "id_{$id}_cf_yes@postView"),
                    InlineKeyboardButton::make($fno_btn_text, callback_data: "id_{$id}_cf_no@postView"),
                    InlineKeyboardButton::make("🏞 کاور آینه ای:", callback_data: "pass")
                );
                
            }
            
            if(
                ( $status != 'draft' && $post_id == 0 ) ||
                ( $post_id != 0 && $this->next_data[ 'post_data' ][ 'post' ][ 'base_status' ] == 'draft' && $status != 'draft' )
            ) {
                
                $this->addButtonRow(
                    InlineKeyboardButton::make($time_btn_text, callback_data: "pass")
                )
                ->addButtonRow(
                    InlineKeyboardButton::make($now_btn_text, callback_data: "id_{$id}_now@postView")
                )
                ->addButtonRow(
                    InlineKeyboardButton::make("-10", callback_data: "id_{$id}_td_10@postView"),
                    InlineKeyboardButton::make("-5", callback_data: "id_{$id}_td_5@postView"),
                    InlineKeyboardButton::make("-1", callback_data: "id_{$id}_td_1@postView"),
                    InlineKeyboardButton::make("دقیقه", callback_data: "pass"),
                    InlineKeyboardButton::make("+1", callback_data: "id_{$id}_tu_1@postView"),
                    InlineKeyboardButton::make("+5", callback_data: "id_{$id}_tu_5@postView"),
                    InlineKeyboardButton::make("+10", callback_data: "id_{$id}_tu_10@postView")
                )
                ->addButtonRow(
                    InlineKeyboardButton::make("-10", callback_data: "id_{$id}_td_600@postView"),
                    InlineKeyboardButton::make("-5", callback_data: "id_{$id}_td_300@postView"),
                    InlineKeyboardButton::make("-1", callback_data: "id_{$id}_td_60@postView"),
                    InlineKeyboardButton::make("ساعت", callback_data: "pass"),
                    InlineKeyboardButton::make("+1", callback_data: "id_{$id}_tu_60@postView"),
                    InlineKeyboardButton::make("+5", callback_data: "id_{$id}_tu_300@postView"),
                    InlineKeyboardButton::make("+10", callback_data: "id_{$id}_tu_600@postView")
                )
                ->addButtonRow(
                    InlineKeyboardButton::make("-10", callback_data: "id_{$id}_td_14400@postView"),
                    InlineKeyboardButton::make("-5", callback_data: "id_{$id}_td_7200@postView"),
                    InlineKeyboardButton::make("-1", callback_data: "id_{$id}_td_1440@postView"),
                    InlineKeyboardButton::make("روز", callback_data: "pass"),
                    InlineKeyboardButton::make("+1", callback_data: "id_{$id}_tu_1440@postView"),
                    InlineKeyboardButton::make("+5", callback_data: "id_{$id}_tu_7200@postView"),
                    InlineKeyboardButton::make("+10", callback_data: "id_{$id}_tu_14400@postView")
                );
            
            }
                
            if( $post_id == 0 ) {
                
                $this->addPrevMenuBtn(5, null, 'post_data')
                    ->setPrevMenu(__FUNCTION__, 5);
                
            } else {
                
                $this->addPrevMenuBtn(4, null, 'post_data')
                    ->setPrevMenu(__FUNCTION__, 4);
                
            }
                
            $this->showMenu();
            
        }
        
    }
    
    public function editField(Nutgram $bot, string $data) {
        
        list( $key, $sequential, $error ) = array_pad( explode( '|', $data ), 3, null );
        
        $fields = new Fields( null );
        $field = $fields->getField( $key );
        $site_id = $this->next_data[ 'post_data' ][ 'site_id' ];
        
        $is_edit = $this->next_data[ 'post_data' ][ 'post' ][ 'id' ] != 0;
        
        if( !isset( $field ) ) {
            
            $this->setTitle("📝 ویرایش فیلد")
                ->addText('⚠️ فیلد مورد نظر یافت نشد!')
                ->setMenuText()
                ->clearButtons()
                ->addPrevMenuBtn($is_edit ? 5 : 6)
                ->setPrevMenu(__FUNCTION__, $is_edit ? 5 : 6)
                ->showMenu();
            
        } else {
            
            $name = isset( $field[ 'fullname' ] ) ? $field[ 'fullname' ] : $field[ 'name' ];
            
            if( isset( $sequential ) && $sequential != 0 ) {
                
                $this->next_data[ 'post_data' ][ 'saveFieldExtra' ] = $sequential;
                
            } else {
                
                $this->next_data[ 'post_data' ][ 'saveFieldExtra' ] = 0;
                
            }
            
            if( isset( $error ) && $error != 0 ) {
                
                $this->setTitle("⚠️ مقدار وارد شده معتبر نیست.");
                
            } else {
                
                $this->setTitle("📝 ویرایش فیلد <b>{$name}</b>");
                
            }
                
            if( isset( $this->next_data[ 'post_data' ][ 'fields' ][ $key ] ) ) {
                
                if( $key == 'cover_url' ) {
                    
                    $this->attachImage( $this->next_data[ 'post_data' ][ 'fields' ][ $key ] );
                    
                } else {
                    
                    $text = "📝 مقدار فعلی: ";
                    
                    if( str_contains( $key, 'url' ) || $key == 'lyric' ) {
                        
                        if( isset( $this->next_data[ 'post_data' ][ 'fields_menu' ][ $key ] ) ) {
                            
                            $text .= "<pre>{$this->next_data[ 'post_data' ][ 'fields_menu' ][ $key ]}</pre>";
                            
                        } else {
                            
                            if( $key == 'lyric' ) {
                                
                                $lyric = $this->prepareMulti( $this->next_data[ 'post_data' ][ 'fields' ][ $key ] );
                                
                                $text .= "{$lyric}";
                                
                            } else {
                                
                                if( $this->next_data[ 'post_data' ][ 'fields' ][ $key ] != 'local' ) {
                                    
                                    $text .= "<pre>{$this->next_data[ 'post_data' ][ 'fields' ][ $key ]}</pre>";
                                    
                                }
                                
                            }
                            
                        }
                        
                    } else {
                        
                        if( is_array( $this->next_data[ 'post_data' ][ 'fields' ][ $key ] ) ) {
                            
                            $text .= implode( $field[ 'seprator' ], array_map( function( $val ) {
                                
                                return "<b>" . $val . "</b>";
                                
                            }, $this->next_data[ 'post_data' ][ 'fields' ][ $key ] ) );
                            
                        } else {
                            
                            $text .= "<b>" . $this->next_data[ 'post_data' ][ 'fields' ][ $key ] . "</b>";
                            
                        }
                        
                    }
                    
                    $this->addText( $text );
                    
                }
                
            }
            
            $this->addText("✍️ لطفا مقدار جدید <b>{$name}</b> را وارد کنید.");
                
            if( isset( $field[ 'edit' ][ 'text' ] ) ) {
                
                $this->addText('');
                
                foreach( $field[ 'edit' ][ 'text' ] as $text ) {
                    
                    $this->addText( "📌 " . $text );
                    
                }
                
            }
            
            $this->next_data[ 'post_data' ][ 'saveField' ] = $key;
                
            $this->setMenuText(false, true)
                ->clearButtons();
                
            if( $sequential == 0 ) {
                
                $this->addPrevMenuBtn($is_edit ? 5 : 6)
                    ->setPrevMenu(__FUNCTION__, $is_edit ? 5 : 6);
                
            } else {
                
                if( $field[ 'required' ] === false ) {
                    
                    $keys_arr = array_keys( $this->next_data[ 'post_data' ][ 'group_fields' ] );
                    $key_index = array_search( $key, $keys_arr, true );
                    $key_index += 1;
                    $next_key = $keys_arr[ $key_index ] ?? false;
                    
                    while(
                        $next_key !== false &&
                        isset( $this->next_data[ 'post_data' ][ 'fields' ][ $next_key ] )
                    ) {
                        
                        $key_index += 1;
                        $next_key = $keys_arr[ $key_index ] ?? false;
                        
                    }
                    
                    if( $next_key === false ) {
                        
                        $this->addButtonRow(
                            InlineKeyboardButton::make("➡️ برو بعدی", callback_data: "id_{$site_id}@postView")
                        );
                        
                    } else {
                        
                        $this->addButtonRow(
                            InlineKeyboardButton::make("➡️ برو بعدی", callback_data: "{$next_key}|{$sequential}@editField")
                        );
                        
                    }
                    
                }
                    
                if( $sequential == 2 ) {
                    
                    $this->addPrevMenuBtn($is_edit ? 5 : 6)
                        ->setPrevMenu(__FUNCTION__, $is_edit ? 5 : 6);
                    
                } else {
                    
                    $this->addPrevMenuBtn($is_edit ? 4 : 5, null, 'post_data')
                        ->setPrevMenu(__FUNCTION__, $is_edit ? 4 : 5);
                    
                }
                
            }
                
            $this->orNext( 'saveField' )
                ->showMenu();
            
        }
        
    }
    
    protected function prepareMulti( $value ) {
        
        /*$new_value = preg_replace( "/[\r\n]+/", "\n", $value );
        $val_arr = explode( "\n", $new_value );
        
        if( count( $val_arr ) > 4 ) {
            
            $new_val = [];
            
            $new_val = array_merge( $new_val, array_slice( $val_arr, 0, 2 ) );
            $new_val = array_merge( $new_val, [ '‏.', '‏.', '‏.' ] );
            $new_val = array_merge( $new_val, array_slice( $val_arr, -2, 2 ) );
            
            return implode( "\n", $new_val );
            
        } else {
            
            return $value;
            
        }*/
        
        $value = preg_replace( "/[\r\n]+/", "\n", $value );
        
        return "\n‏----------\n{$value}\n‏----------";
        
    }
    
    public function saveField() {
        
        $key = $this->next_data[ 'post_data' ][ __FUNCTION__ ];
        $sequential = $this->next_data[ 'post_data' ][ 'saveFieldExtra' ];
        
        $fields = new Fields( null );
        $field = $fields->getField( $key );
        $site = $this->siteStore->findById( $this->next_data[ 'post_data' ][ 'site_id' ] );
        
        if( !isset( $field ) || is_null( $site ) || empty( $site[ 'accepted' ] ) || !in_array( $_ENV[ 'ADMIN_ID' ], $site[ 'user_ids' ] ) ) {
                
            $this->disableMenu()
                ->loadMenu( 'editField', "{$key}|{$sequential}" );
            
        } else {
            
            $site_id = $this->next_data[ 'post_data' ][ 'site_id' ];
            $error = false;
            $start = time();
            $disabled = false;
            
            $dl_fn = function( $downloadTotal, $downloadedBytes, $uploadTotal, $uploadedBytes ) use ( &$start, &$disabled ) {
                
                if( $downloadTotal > 0 ) {
                        
                    $status = (int) ( ( $downloadedBytes * 100 ) / $downloadTotal );
                    $status = "<b>{$status}%</b> دانلود شده ...";
                    
                } else {
                    
                    if( $downloadedBytes < 1048576 ) {
                        
                        $kb = number_format( $downloadedBytes / 1024, 2, '.', '' );
                        $status = "<b>{$kb}</b> کیلوبایت دانلود شده ...";
                        
                    } else {
                        
                        $mb = number_format( $downloadedBytes / 1048576, 2, '.', '' );
                        $status = "<b>{$mb}</b> مگابایت دانلود شده ...";
                        
                    }
                    
                }
                
                if( !$disabled ) {
                    
                    $disabled = true;
                    
                    $date = Jalalian::now()->format( 'l d F Y ساعت H:i:s' );
                    
                    $start = time();
                    
                    $this->disableMenu()
                        ->setTitle("📥 دانلود فایل")
                        ->addText("🔄 این پیام هر 5 ثانیه بروز می شود.")
                        ->addText("")
                        ->addText("📊 وضعیت دانلود: {$status}")
                        ->addText("")
                        ->addText("🔄 آخرین بروزرسانی: <code>{$date}</code>")
                        ->setMenuText()
                        ->clearButtons()
                        ->addButtonRow(
                            InlineKeyboardButton::make("⌛️ در حال دانلود فایل ...", callback_data: "pass")
                        )
                        ->showMenu();
                    
                }
                
                if( time() - $start >= 5 ) {
                    
                    $start = time();
                    
                    $date = Jalalian::now()->format( 'l d F Y ساعت H:i:s' );
                    
                    $this->setTitle("📥 دانلود فایل")
                        ->addText("🔄 این پیام هر 5 ثانیه بروز می شود.")
                        ->addText("")
                        ->addText("📊 وضعیت دانلود: {$status}")
                        ->addText("")
                        ->addText("🔄 آخرین بروزرسانی: <code>{$date}</code>")
                        ->setMenuText()
                        ->showMenu();
                    
                }
                
            };
            
            switch( $key ) {
                
                case 'url_128':
                case 'url_320':
                
                    if( 
                        $this->bot->message()->getType() === MessageType::TEXT &&
                        $this->isUrlMessage()
                    ) {
                        
                        $text = $this->bot->message()->getText();
                        
                        if( $this->isSocialLink( $text ) ) {
                            
                            $this->bot->sendMessage( '⌛️ کمی صبر ...' );
                            
                            $dl_link = $this->getSocialDirectLink( $text, true );
                            
                            if( $dl_link ) {
                                
                                $this->closeConnection();
                                
                                $ok = $this->fileDownload(
                                    $dl_link,
                                    $this->next_data[ 'post_data' ][ 'tmp' ] . "/{$key}.mp3",
                                    300,
                                    $dl_fn,
                                    "{$key}.mp3"
                                );
                                
                                if( $ok ) {
                                    
                                    $this->next_data[ 'post_data' ][ 'fields' ][ $key ] = 'local';
                                    $this->next_data[ 'post_data' ][ 'fields_menu' ][ $key ] = $text;
                                    
                                } else {
                                    
                                    $error = true;
                                    
                                }
                                
                            } else {
                                
                                $error = true;
                                
                            }
                            
                        } else {
                            
                            $urlWOQ = strtolower( current( explode( "?", $text ) ) );
                            
                            if( str_ends_with( $urlWOQ, '.mp3' ) ) {
                                
                                $size = $this->getRemoteFileSize( $text );
                                
                                if( $size > 0 ) {
                                    
                                    if( $this->isLocalUrl( $site, $text ) ) {
                                        
                                        $this->next_data[ 'post_data' ][ 'fields' ][ $key ] = $text;
                                        
                                    } else {
                                        
                                        $this->closeConnection();
                                        
                                        $ok = $this->fileDownload(
                                            $text,
                                            $this->next_data[ 'post_data' ][ 'tmp' ] . "/{$key}.mp3",
                                            300,
                                            $dl_fn
                                        );
                                        
                                        if( $ok ) {
                                        
                                            $this->next_data[ 'post_data' ][ 'fields' ][ $key ] = 'local';
                                            $this->next_data[ 'post_data' ][ 'fields_menu' ][ $key ] = $text;
                                        
                                        } else {
                                            
                                            $error = true;
                                            
                                        }
                                        
                                    }
                                    
                                } else {
                                    
                                    $error = true;
                                    
                                }
                                
                            } else {
                                
                                $error = true;
                                
                            }
                            
                        }
                        
                    } else {
                        
                        if( $this->bot->message()->getType() === MessageType::AUDIO ) {
                            
                            $audio = $this->bot->message()->audio;
                            
                            $file_name = $audio->file_name ?? '';
                            $mime_type = $audio->mime_type ?? '';
                            $mimes = new MimeTypes;
                            
                            if( str_ends_with( $file_name, '.mp3' ) || in_array( 'mp3', $mimes->getAllExtensions( $mime_type ) ) ) {
                                
                                $this->closeConnection();
                                
                                try {
                                    
                                    $ok = $audio->download( $this->next_data[ 'post_data' ][ 'tmp' ] . "/{$key}.mp3", [ 'progress' => $dl_fn, 'timeout' => 300 ] );
                                    
                                } catch( \Exception $e ) {
                                    
                                    $ok = false;
                                    
                                }
                                
                                if( $ok ) {
                                    
                                    $this->next_data[ 'post_data' ][ 'fields' ][ $key ] = 'local';
                                    $this->next_data[ 'post_data' ][ 'fields_menu' ][ $key ] = "Telegram Audio File";
                                    
                                } else {
                                    
                                    $error = true;
                                    
                                }
                                
                            } else {
                                
                                $error = true;
                                
                            }
                            
                        } else {
                            
                            $error = true;
                            
                        }
                        
                    }
                
                break;
                
                case 'teaser_url':
                
                    if( 
                        $this->bot->message()->getType() === MessageType::TEXT &&
                        $this->isUrlMessage()
                    ) {
                        
                        $text = $this->bot->message()->getText();
                        
                        if( $this->isSocialLink( $text ) ) {
                            
                            $this->bot->sendMessage( '⌛️ کمی صبر ...' );
                            
                            $dl_link = $this->getSocialDirectLink( $text );
                            
                            if( $dl_link ) {
                                
                                $this->closeConnection();
                                
                                $ok = $this->fileDownload(
                                    $dl_link,
                                    $this->next_data[ 'post_data' ][ 'tmp' ] . "/{$key}.mp4",
                                    300,
                                    $dl_fn
                                );
                                
                                if( $ok ) {
                                    
                                    $this->next_data[ 'post_data' ][ 'fields' ][ $key ] = 'local';
                                    $this->next_data[ 'post_data' ][ 'fields_menu' ][ $key ] = $text;
                                    
                                } else {
                                    
                                    $error = true;
                                    
                                }
                                
                            } else {
                                
                                $error = true;
                                
                            }
                            
                        } else {
                            
                            $urlWOQ = strtolower( current( explode( "?", $text ) ) );
                            
                            if( str_ends_with( $urlWOQ, '.mp4' ) ) {
                                
                                $size = $this->getRemoteFileSize( $text );
                                
                                if( $size > 0 ) {
                                    
                                    if( $this->isLocalUrl( $site, $text ) ) {
                                        
                                        $this->next_data[ 'post_data' ][ 'fields' ][ $key ] = $text;
                                        
                                    } else {
                                        
                                        $this->closeConnection();
                                        
                                        $ok = $this->fileDownload(
                                            $text,
                                            $this->next_data[ 'post_data' ][ 'tmp' ] . "/{$key}.mp4",
                                            300,
                                            $dl_fn
                                        );
                                        
                                        if( $ok ) {
                                        
                                            $this->next_data[ 'post_data' ][ 'fields' ][ $key ] = 'local';
                                            $this->next_data[ 'post_data' ][ 'fields_menu' ][ $key ] = $text;
                                        
                                        } else {
                                            
                                            $error = true;
                                            
                                        }
                                        
                                    }
                                    
                                } else {
                                    
                                    $error = true;
                                    
                                }
                                
                            } else {
                                
                                $error = true;
                                
                            }
                            
                        }
                        
                    } else {
                        
                        if( $this->bot->message()->getType() === MessageType::VIDEO ) {
                            
                            $video = $this->bot->message()->video;
                            
                            $file_name = $video->file_name ?? '';
                            $mime_type = $video->mime_type ?? '';
                            $mimes = new MimeTypes;
                            
                            if( str_ends_with( $file_name, '.mp4' ) || in_array( 'mp4', $mimes->getAllExtensions( $mime_type ) ) ) {
                                
                                $this->closeConnection();
                                
                                try {
                                    
                                    $ok = $video->download( $this->next_data[ 'post_data' ][ 'tmp' ] . "/{$key}.mp4", [ 'progress' => $dl_fn, 'timeout' => 300 ] );
                                    
                                } catch( \Exception $e ) {
                                    
                                    $ok = false;
                                    
                                }
                                
                                if( $ok ) {
                                    
                                    $this->next_data[ 'post_data' ][ 'fields' ][ $key ] = 'local';
                                    $this->next_data[ 'post_data' ][ 'fields_menu' ][ $key ] = "Telegram Video File";
                                    
                                } else {
                                    
                                    $error = true;
                                    
                                }
                                
                            } else {
                                
                                $error = true;
                                
                            }
                            
                        } else {
                            
                            $error = true;
                            
                        }
                        
                    }
                
                break;
                
                case 'cover_url':
                
                    if( 
                        $this->bot->message()->getType() === MessageType::TEXT &&
                        $this->isUrlMessage()
                    ) {
                        
                        $text = $this->bot->message()->getText();
                        
                        $urlWOQ = strtolower( current( explode( "?", $text ) ) );
                        
                        if(
                            str_ends_with( $urlWOQ, '.jpg' ) ||
                            str_ends_with( $urlWOQ, '.jpeg' ) ||
                            str_ends_with( $urlWOQ, '.png' ) ||
                            str_ends_with( $urlWOQ, '.bmp' ) ||
                            str_ends_with( $urlWOQ, '.webp' )
                        ) {
                            
                            $this->closeConnection();
                            
                            $ok = $this->fileDownload(
                                $text,
                                $this->next_data[ 'post_data' ][ 'tmp' ] . "/{$key}.jpg",
                                300,
                                $dl_fn
                            );
                            
                            if( $ok ) {
                                
                                $this->applyImageEffects( $this->next_data[ 'post_data' ][ 'tmp' ] . "/{$key}.jpg" );
                                
                                $this->next_data[ 'post_data' ][ 'fields' ][ $key ] = $this->tempPathToUrl( $this->next_data[ 'post_data' ][ 'tmp' ] . "/{$key}.jpg" );
                                
                            } else {
                                
                                $error = true;
                                
                            }
                            
                        } else {
                            
                            $error = true;
                            
                        }
                        
                    } else {
                        
                        if( $this->bot->message()->getType() === MessageType::PHOTO ) {
                            
                            $photo = end( $this->bot->message()->photo );
                            
                            $this->closeConnection();
                            
                            try {
                                
                                $ok = $photo->download( $this->next_data[ 'post_data' ][ 'tmp' ] . "/{$key}.jpg", [ 'progress' => $dl_fn, 'timeout' => 300 ] );
                                
                            } catch( \Exception $e ) {
                                
                                $ok = false;
                                
                            }
                            
                            if( $ok ) {
                                
                                $this->applyImageEffects( $this->next_data[ 'post_data' ][ 'tmp' ] . "/{$key}.jpg" );
                                
                                $this->next_data[ 'post_data' ][ 'fields' ][ $key ] = $this->tempPathToUrl( $this->next_data[ 'post_data' ][ 'tmp' ] . "/{$key}.jpg" );
                                
                            } else {
                                
                                $error = true;
                                
                            }
                            
                        } else {
                            
                            $error = true;
                            
                        }
                        
                    }
                
                break;
                
                default:
                
                    if( $this->bot->message()->getType() === MessageType::TEXT ) {
                        
                        $text = $this->bot->message()->getText();
                        
                        if( !empty( $field[ 'multi' ] ) ) {
                            
                            $entity_decoder = new EntityDecoder('HTML');
                            $text = $entity_decoder->decode(json_decode(json_encode($this->bot->message()->toArray())));
                            
                            $this->next_data[ 'post_data' ][ 'fields' ][ $key ] = strip_tags( trim( $text ), '<blockquote>' );
                            
                        } else {
                            
                            if( !str_contains( $text, "\n" ) ) {
                                
                                if( !empty( $field[ 'seprator' ] ) ) {
                                    
                                    $text_arr = explode( $field[ 'seprator' ], $text );
                                    
                                    if( count( $text_arr ) > 1 ) {
                                        
                                        $this->next_data[ 'post_data' ][ 'fields' ][ $key ] = array_map( function( $value ) {
                                            return strip_tags( trim( $value ) );
                                        }, $text_arr );
                                        
                                    } else {
                                        
                                        $this->next_data[ 'post_data' ][ 'fields' ][ $key ] = strip_tags( trim( $text ) );
                                        
                                    }
                                    
                                } else {
                                    
                                    $this->next_data[ 'post_data' ][ 'fields' ][ $key ] = strip_tags( trim( $text ) );
                                    
                                }
                                
                            } else {
                                
                                $error = true;
                                
                            }
                            
                        }
                        
                    } else {
                        
                        $error = true;
                        
                    }
                
                break;
                
            }
            
            if( !$disabled ) {
                
                $this->disableMenu();
                
            }
            
            if( $sequential == 0 ) {
                
                if( $error ) {
                    
                    $this->loadMenu( 'editField', "{$key}|0|1" );
                    
                } else {
                    
                    $this->loadMenu( 'postView', "id_{$site_id}" );
                    
                }
                
            } else {
                
                if( $error ) {
                    
                    $this->loadMenu( 'editField', "{$key}|{$sequential}|1" );
                    
                } else {
                    
                    $keys_arr = array_keys( $this->next_data[ 'post_data' ][ 'group_fields' ] );
                    $key_index = array_search( $key, $keys_arr, true );
                    $key_index += 1;
                    $next_key = $keys_arr[ $key_index ] ?? false;
                    
                    while(
                        $next_key !== false &&
                        isset( $this->next_data[ 'post_data' ][ 'fields' ][ $next_key ] )
                    ) {
                        
                        $key_index += 1;
                        $next_key = $keys_arr[ $key_index ] ?? false;
                        
                    }
                    
                    if( $next_key === false ) {
                        
                        $this->loadMenu( 'postView', "id_{$site_id}" );
                        
                    } else {
                        
                        $this->loadMenu( 'editField', "{$next_key}|{$sequential}" );
                        
                    }
                    
                }
                
            }
            
        }
        
    }
    
    public function dupCheck(Nutgram $bot, string $data) {
        
        list( , $id ) = explode( '_', $data );
        
        $site = $this->siteStore->findById( $id );
        
        if( is_null( $site ) || empty( $site[ 'accepted' ] ) || !in_array( $_ENV[ 'ADMIN_ID' ], $site[ 'user_ids' ] ) ) {
            
            $this->setTitle("🌐 سایت شماره {$id}")
                ->addText('⚠️ سایت مورد نظر یافت نشد.')
                ->setMenuText()
                ->clearButtons()
                ->addPrevMenuBtn(6)
                ->setPrevMenu(__FUNCTION__, 6)
                ->showMenu();
                
        } else {
            
            if( $this->next_data[ 'post_data' ][ 'post' ][ 'id' ] == 0 ) {
                
                $dup_fields = array_filter( $this->next_data[ 'post_data' ][ 'group_fields' ], fn( $value ) => !empty( $value[ 'duplicate' ] ) );
                array_walk( $dup_fields, function( &$value, $key ) {
                    
                    if( is_array( $this->next_data[ 'post_data' ][ 'fields' ][ $key ] ) ) {
                        
                        $value = implode( $value[ 'seprator' ], $this->next_data[ 'post_data' ][ 'fields' ][ $key ]);
                        
                    } else {
                        
                        $value = $this->next_data[ 'post_data' ][ 'fields' ][ $key ];
                        
                    }
                    
                } );
                
                $dup_data = array_values( $dup_fields );
                $data = [ 'is_remix' => $this->next_data[ 'post_data' ][ 'group' ] == 'remix' ? true : false, 'check_data' => $dup_data ];
                $check = $this->isDuplicate( $site, $dup_data );
                
            } else {
                
                $check = false;
                
            }
            
            if( $check ) {
                
                $this->setTitle("📰 پست تکراری")
                    ->addText('⚠️ این پست قبلا در سایت ثبت شده است.')
                    ->setMenuText()
                    ->clearButtons()
                    ->addPrevMenuBtn(6)
                    ->setPrevMenu(__FUNCTION__, 6)
                    ->showMenu();
                
            } else {
                
                if( $check === false ) {
                
                    if( $this->bot->userId() == $_ENV[ 'ADMIN_ID' ] ) {
                        
                        $this->addButtonRow(
                            InlineKeyboardButton::make("🚀 ارسال پست", callback_data: "id_{$id}@selectAuthor")
                        );
                        
                        $this->bot->callbackQuery()->data = array_key_last( $this->getSerializableAttributes()[ 'callbacks' ] );
                        
                    } else {
                        
                        $this->addButtonRow(
                            InlineKeyboardButton::make("🚀 ارسال پست", callback_data: "id_{$id}@postSend")
                        );
                        
                        $this->bot->callbackQuery()->data = array_key_last( $this->getSerializableAttributes()[ 'callbacks' ] );
                        
                    }
                    
                    $this->next( 'handleStep' );
                    
                    $this( $this->bot );
                
                } else {
                    
                    $date = Jalalian::now()->format( 'l d F Y ساعت H:i:s' );
                    
                    $this->setTitle("📰 پست تکراری")
                        ->addText('⚠️ پاسخ مناسبی از سرور سایت دریافت نشد.')
                        ->addText("🔄 آخرین بررسی: <code>{$date}</code>")
                        ->setMenuText()
                        ->clearButtons()
                        ->addButtonRow(
                            InlineKeyboardButton::make("🔄 بررسی مجدد", callback_data: "id_{$id}@dupCheck")
                        )
                        ->addPrevMenuBtn(6)
                        ->setPrevMenu(__FUNCTION__, 6)
                        ->showMenu();
                    
                }
                
            }
            
        }
        
    }
    
    public function selectAuthor(Nutgram $bot, string $data) {
        
        list( , $id ) = explode( '_', $data );
        
        $site = $this->siteStore->findById( $id );
        
        $is_edit = $this->next_data[ 'post_data' ][ 'post' ][ 'id' ] != 0;
        
        if( is_null( $site ) || empty( $site[ 'accepted' ] ) || !in_array( $_ENV[ 'ADMIN_ID' ], $site[ 'user_ids' ] ) ) {
            
            $this->setTitle("🌐 سایت شماره {$id}")
                ->addText('⚠️ سایت مورد نظر یافت نشد.')
                ->setMenuText()
                ->clearButtons()
                ->addPrevMenuBtn($is_edit ? 5 : 6)
                ->setPrevMenu(__FUNCTION__, $is_edit ? 5 : 6)
                ->showMenu();
                
        } else {
            
            $buttons = [];
            
            foreach( $site[ 'users' ] as $user ) {
                
                if( $user[ 'tele_id' ] == $_ENV[ 'ADMIN_ID' ] ) continue;
                
                $name = $user[ 'display_name' ] ?: $user[ 'user_login' ];
                
                $buttons[] = InlineKeyboardButton::make($name, callback_data: "id_{$id}_{$user[ 'wp_id' ]}@postSend");
                
            }
            
            $this->setTitle("👤 انتخاب نویسنده")
                ->addText("👇 لطفا نویسنده این پست را از بین موارد زیر انتخاب کنید.")
                ->setMenuText()
                ->clearButtons()
                ->addButtonRow(
                    InlineKeyboardButton::make("خودم", callback_data: "id_{$id}@postSend")
                );
            
            if( !empty( $buttons ) ) {
            
                $this->setMenuButtons($buttons, 20, 1, 2, false);
            
            }
            
            $this->addPrevMenuBtn($is_edit ? 5 : 6)
                ->setPrevMenu(__FUNCTION__, $is_edit ? 5 : 6)
                ->showMenu();
                
        }
        
    }
    
    public function postSend(Nutgram $bot, string $data) {
        
        list( , $id, $user_id, $retry ) = array_pad( explode( '_', $data ), 4, null );
        
        $order = $this->bot->userId() == $_ENV[ 'ADMIN_ID' ] ? 7 : 6;
        
        $site = $this->siteStore->findById( $id );
        
        $is_edit = $this->next_data[ 'post_data' ][ 'post' ][ 'id' ] != 0;
        
        if( $is_edit ) {
            
            $order -= 1;
            
        }
        
        $title = $is_edit ? '📝 ویرایش پست' : '🚀 ارسال پست';
        
        if( is_null( $site ) || empty( $site[ 'accepted' ] ) || !in_array( $_ENV[ 'ADMIN_ID' ], $site[ 'user_ids' ] ) ) {
            
            $this->setTitle("🌐 سایت شماره {$id}")
                ->addText('⚠️ سایت مورد نظر یافت نشد.')
                ->setMenuText()
                ->clearButtons()
                ->addPrevMenuBtn($order)
                ->setPrevMenu(__FUNCTION__, $order)
                ->showMenu();
                
        } else {
            
            if( $user_id === null ) {
                
                $user_id = $this->next_data[ 'post_data' ][ 'post' ][ 'author' ] = (int) $site[ 'users' ][ array_search( $this->bot->userId(), array_column( $site[ 'users' ], 'tele_id' ) ) ][ 'wp_id' ];
                
            }
            
            $ftp = $this->getFtpClient( $site );
            
            if( $ftp !== false ) {
                
                $this->closeConnection();
                
                $ftp->getWrapper()->set_option( FTP_TIMEOUT_SEC, 300 );
                
                if( $retry === null ) {
                
                    $this->next_data[ 'post_data' ][ 'pf' ] = $this->prepareUrlFields(
                        $site,
                        $this->next_data[ 'post_data' ][ 'group_fields' ],
                        $this->next_data[ 'post_data' ][ 'fields' ],
                        $this->next_data[ 'post_data' ][ 'group' ],
                        $is_edit
                    );
                
                } else {
                    
                    array_walk( $this->next_data[ 'post_data' ][ 'pf' ], function( &$field, $key ) {
                        
                        if( $field[ 'dl' ] === false ) $field[ 'dl' ] = 0;
                        if( $field[ 'up' ] === false ) $field[ 'up' ] = 0;
                        
                    } );
                    
                }
                
                $this->setPrevMenu(__FUNCTION__, $order);
                
                [ $dl_texts, $up_texts, $last_update ] = $this->postSendTextGenerator( $this->next_data[ 'post_data' ][ 'pf' ] );
                
                $this->setTitle($title)
                    ->addText("🔄 این پیام هر 5 ثانیه بروز می شود.\n")
                    ->addText($dl_texts)
                    ->addText($up_texts)
                    ->addText($last_update)
                    ->setMenuText()
                    ->clearButtons();
                
                if( $is_edit ) {
                    
                    $this->addButtonRow(
                        InlineKeyboardButton::make("✏️ در حال ویرایش پست ...", callback_data: "pass")
                    );
                    
                } else {
                    
                    $this->addButtonRow(
                        InlineKeyboardButton::make("📨 در حال ارسال پست ...", callback_data: "pass")
                    );
                    
                }
                
                $this->showMenu();
                    
                $this->answerCallbackQuery();
                
                $start = time();
                
                $dl_needs = array_filter( $this->next_data[ 'post_data' ][ 'pf' ], function( $value ) {
                    
                    return $value[ 'dl' ] !== true ? true : false;
                    
                } );
                
                foreach( $dl_needs as $key => $field ) {
                    
                    $ext = $key == 'teaser_url' ? 'mp4' : 'mp3';
                    $ext = $key == 'cover_url' ? 'jpg' : $ext;
                    
                    $ok = $this->fileDownload(
                        $field[ 'value' ],
                        $this->next_data[ 'post_data' ][ 'tmp' ] . "/{$key}.{$ext}",
                        300,
                        function( $downloadTotal, $downloadedBytes, $uploadTotal, $uploadedBytes ) use ( $key, &$start, $title ) {
                            
                            if( $downloadTotal > 0 ) {
                                    
                                $this->next_data[ 'post_data' ][ 'pf' ][ $key ][ 'dl' ] = (int) ( ( $downloadedBytes * 100 ) / $downloadTotal );
                                
                            }
                            
                            if( time() - $start >= 5 ) {
                                
                                $start = time();
                                
                                [ $dl_texts, $up_texts, $last_update ] = $this->postSendTextGenerator( $this->next_data[ 'post_data' ][ 'pf' ] );
                                
                                $this->setTitle($title)
                                    ->addText("🔄 این پیام هر 5 ثانیه بروز می شود.\n")
                                    ->addText($dl_texts)
                                    ->addText($up_texts)
                                    ->addText($last_update)
                                    ->setMenuText()
                                    ->showMenu();
                                
                            }
                            
                        },
                        $ext == 'mp3' ? "{$key}.{$ext}" : null
                    );
                    
                    $this->next_data[ 'post_data' ][ 'pf' ][ $key ][ 'dl' ] = $ok;
                    
                    if( $ok === false ) {
                        
                        [ $dl_texts, $up_texts, $last_update ] = $this->postSendTextGenerator( $this->next_data[ 'post_data' ][ 'pf' ] );
                        
                        $this->setTitle($title)
                            ->addText($dl_texts)
                            ->addText($up_texts)
                            ->addText($last_update)
                            ->setMenuText()
                            ->clearButtons()
                            ->addButtonRow(
                                InlineKeyboardButton::make("🔄 تلاش مجدد", callback_data: "id_{$id}_{$user_id}_1@postSend")
                            )
                            ->addPrevMenuBtn($order)
                            ->showMenu();
                            
                        exit;
                        
                    }
                    
                }
                
                foreach( $this->next_data[ 'post_data' ][ 'pf' ] as $key => $field ) {
                    
                    if( in_array( $key, [ 'url_128', 'url_320' ] ) ) {
                        
                        if(
                            isset( $field[ 'dl' ] ) &&
                            $field[ 'dl' ] === true &&
                            file_exists( $this->next_data[ 'post_data' ][ 'tmp' ] . "/{$key}.mp3" )
                        ) {
                            
                            $audio = Audio::read( $this->next_data[ 'post_data' ][ 'tmp' ] . "/{$key}.mp3" );
                            
                            $audio->write()
                                ->removeOtherTags()
                                ->tags( $field[ 'metadata' ][ $this->next_data[ 'post_data' ][ 'group' ] ] )
                                ->cover( $this->next_data[ 'post_data' ][ 'tmp' ] . "/cover_url.jpg" )
                                ->skipErrors()
                                ->save();
                            
                        }
                        
                    }
                    
                }
                
                $up_needs = array_filter( $this->next_data[ 'post_data' ][ 'pf' ], function( $value ) {
                    
                    return $value[ 'up' ] !== true ? true : false;
                    
                } );
                
                foreach( $up_needs as $key => $field ) {
                    
                    if( $key == 'cover_url' ) {
                        
                        $ok = $this->coverUpload(
                            "{$site[ 'api' ]}?action=wpttb_cover_upload",
                            [
                                'filename' => $field[ 'filename' ][ $this->next_data[ 'post_data' ][ 'group' ] ],
                                'author' => $user_id,
                                'path' => $this->next_data[ 'post_data' ][ 'tmp' ] . "/{$key}.jpg"
                            ],
                            300,
                            function( $downloadTotal, $downloadedBytes, $uploadTotal, $uploadedBytes ) use ( $key, &$start, $title ) {
                                
                                if( $uploadTotal > 0 ) {
                                        
                                    $this->next_data[ 'post_data' ][ 'pf' ][ $key ][ 'up' ] = (int) ( ( $uploadedBytes * 100 ) / $uploadTotal );
                                    
                                }
                                
                                if( time() - $start >= 5 ) {
                                    
                                    $start = time();
                                    
                                    [ $dl_texts, $up_texts, $last_update ] = $this->postSendTextGenerator( $this->next_data[ 'post_data' ][ 'pf' ] );
                                    
                                    $this->setTitle($title)
                                        ->addText("🔄 این پیام هر 5 ثانیه بروز می شود.\n")
                                        ->addText($dl_texts)
                                        ->addText($up_texts)
                                        ->addText($last_update)
                                        ->setMenuText()
                                        ->showMenu();
                                    
                                }
                                
                            }
                        );
                        
                        if( $ok === false ) {
                            
                            $this->next_data[ 'post_data' ][ 'pf' ][ $key ][ 'up' ] = false;
                            
                            [ $dl_texts, $up_texts, $last_update ] = $this->postSendTextGenerator( $this->next_data[ 'post_data' ][ 'pf' ] );
                            
                            $this->setTitle($title)
                                ->addText($dl_texts)
                                ->addText($up_texts)
                                ->addText($last_update)
                                ->setMenuText()
                                ->clearButtons()
                                ->addButtonRow(
                                    InlineKeyboardButton::make("🔄 تلاش مجدد", callback_data: "id_{$id}_{$user_id}_1@postSend")
                                )
                                ->addPrevMenuBtn($order)
                                ->showMenu();
                                
                            exit;
                            
                        } else {
                            
                            $this->next_data[ 'post_data' ][ 'pf' ][ $key ][ 'up' ] = true;
                            $this->next_data[ 'post_data' ][ 'pf' ][ $key ][ 'upload_value' ] = $ok;
                            
                        }
                        
                    } else {
                        
                        $ext = $key == 'teaser_url' ? 'mp4' : 'mp3';
                        $ftp_base = $this->trailingslashit( $site[ 'dl_host' ][ 'ftp_path' ] );
                        $date_dir = Jalalian::now()->format( 'Y/m' );
                        $dir = $ftp_base . $date_dir;
                        $ftp_path = "{$dir}/" . $field[ 'filename' ][ $this->next_data[ 'post_data' ][ 'group' ] ];
                        $url_path = "{$date_dir}/" . $field[ 'filename' ][ $this->next_data[ 'post_data' ][ 'group' ] ];
                        
                        try {
                            
                            if( !$ftp->isDir( $dir ) ) {
                                
                                $ftp->createDir( $dir );
                                
                            }
                        
                            $ok = $ftp->asyncUpload(
                                $this->next_data[ 'post_data' ][ 'tmp' ] . "/{$key}.{$ext}",
                                $ftp_path,
                                function( $speed, $percentage, $transferred, $seconds ) use ( $key, $title ) {
                                    
                                    $this->next_data[ 'post_data' ][ 'pf' ][ $key ][ 'up' ] = $percentage;
                                    
                                    [ $dl_texts, $up_texts, $last_update ] = $this->postSendTextGenerator( $this->next_data[ 'post_data' ][ 'pf' ] );
                                    
                                    $this->setTitle($title)
                                        ->addText("🔄 این پیام هر 5 ثانیه بروز می شود.\n")
                                        ->addText($dl_texts)
                                        ->addText($up_texts)
                                        ->addText($last_update)
                                        ->setMenuText()
                                        ->showMenu();
                                    
                                },
                                false,
                                5
                            );
                        
                        } catch ( \Exception $e ) {
                            
                            $ok = false;
                            
                        }
                        
                        if( $ok === false ) {
                            
                            $this->next_data[ 'post_data' ][ 'pf' ][ $key ][ 'up' ] = false;
                            
                            [ $dl_texts, $up_texts, $last_update ] = $this->postSendTextGenerator( $this->next_data[ 'post_data' ][ 'pf' ] );
                            
                            $this->setTitle($title)
                                ->addText($dl_texts)
                                ->addText($up_texts)
                                ->addText($last_update)
                                ->setMenuText()
                                ->clearButtons()
                                ->addButtonRow(
                                    InlineKeyboardButton::make("🔄 تلاش مجدد", callback_data: "id_{$id}_{$user_id}_1@postSend")
                                )
                                ->addPrevMenuBtn($order)
                                ->showMenu();
                                
                            exit;
                            
                        } else {
                            
                            $this->next_data[ 'post_data' ][ 'pf' ][ $key ][ 'up' ] = true;
                            $this->next_data[ 'post_data' ][ 'pf' ][ $key ][ 'upload_value' ] = [
                                'url' => $this->createFtpLink(
                                    $site[ 'dl_host' ],
                                    $url_path
                                )
                            ];
                            
                        }
                        
                    }
                    
                }
                
                $final_fields = [];
                
                foreach( $this->next_data[ 'post_data' ][ 'group_fields' ] as $key => $field ) {
                    
                    $final_fields[ $key ] = $field;
                    
                    $value = '';
                    
                    if( isset( $this->next_data[ 'post_data' ][ 'fields' ][ $key ] ) ) {
                        
                        $value = $this->next_data[ 'post_data' ][ 'fields' ][ $key ];
                        
                    }
                    
                    if( isset( $this->next_data[ 'post_data' ][ 'pf' ][ $key ][ 'upload_value' ][ 'url' ] ) ) {
                        
                        $value = $this->next_data[ 'post_data' ][ 'pf' ][ $key ][ 'upload_value' ][ 'url' ];
                        
                    }
                    
                    if( isset( $field[ 'multi' ] ) ) {
                        
                        $value = explode( "\n", $value );
                        
                    }
                    
                    $final_fields[ $key ][ 'value' ] = $value;
                    
                }
                
                $sample = array_filter( $this->next_data[ 'post_data' ][ 'sample' ], function( $key ) {
                    
                    if( in_array( $key, [ 'title', 'slug', 'content', 'tax', 'cf' ] ) ) {
                        
                        return true;
                        
                    }
                    
                    return false;
                    
                }, ARRAY_FILTER_USE_KEY );
                
                $sample[ 'title' ] = $this->genericReplacer( $final_fields, $sample[ 'title' ] );
                $sample[ 'slug' ] = $this->genericReplacer( $final_fields, $sample[ 'slug' ] );
                
                $sample[ 'content' ] = $this->addOverCoverParagraph(
                    $this->next_data[ 'post_data' ][ 'post' ][ 'type' ] ?? 'nor',
                    $site[ 'post_types' ],
                    $sample[ 'content' ]
                );
                
                $sample[ 'content' ] = $this->htmlCoverReplacer(
                    $this->next_data[ 'post_data' ][ 'tmp' ] . '/cover_url.jpg',
                    $final_fields[ 'cover_url' ][ 'value' ],
                    $sample[ 'content' ]
                );
                
                foreach( $final_fields as $field ) {
                    
                    if( is_array( $field[ 'value' ] ) ) {

                        $sample[ 'content' ] = $this->htmlReplacer( $field, $sample[ 'content' ] );
                        
                    }
                    
                }
                
                $sample[ 'content' ] = $this->genericReplacer( $final_fields, $sample[ 'content' ] );
                
                if( isset( $sample[ 'tax' ] ) ) {
                    
                    foreach( $sample[ 'tax' ] as $key => $val ) {
                        
                        if( empty( trim( $val ) ) ) {
                            
                            unset( $sample[ 'tax' ][ $key ] );
                            
                            continue;
                            
                        }
                        
                        $sample[ 'tax' ][ $key ] = $this->genericReplacer( $final_fields, $val, '^' );
                        
                    }
                    
                }
                
                if( isset( $sample[ 'cf' ] ) ) {
                    
                    foreach( $sample[ 'cf' ] as $key => $val ) {
                        
                        $sample[ 'cf' ][ $key ] = $this->genericReplacer( $final_fields, $val );
                        
                        if( empty( $sample[ 'cf' ][ $key ] ) ) {
                            
                            unset( $sample[ 'cf' ][ $key ] );
                            
                        }
                        
                    }
                    
                }
                
                $sample[ 'cf' ][ '_wpttb' ] = [
                    'sample_group' => $this->next_data[ 'post_data' ][ 'group' ],
                    'sample_id' => $this->next_data[ 'post_data' ][ 'sample' ][ '_id' ],
                    'fields' => array_map( fn( $field ) => $field[ 'value' ], $final_fields ),
                    'extra' => [
                        'type' => $this->next_data[ 'post_data' ][ 'post' ][ 'type' ] ?? 'nor',
                        'flop' => $this->next_data[ 'post_data' ][ 'post' ][ 'flop' ] ?? 'no'
                    ]
                ];
                
                $sample[ 'author' ] = $user_id;
                $sample[ 'status' ] = $this->next_data[ 'post_data' ][ 'post' ][ 'status' ] ?? 'publish';
                
                if( isset( $this->next_data[ 'post_data' ][ 'post' ][ 'date' ] ) ) {
                    
                    $sample[ 'date' ] = $this->next_data[ 'post_data' ][ 'post' ][ 'date' ];
                    
                }
                
                if( !empty( $this->next_data[ 'post_data' ][ 'post' ][ 'time' ] ) ) {
                    
                    $time = $this->next_data[ 'post_data' ][ 'post' ][ 'time' ];
                    
                    $sample[ 'date' ] = Carbon::now( 'UTC' )->addMinutes( $time )->toDateTimeString();
                    
                }
                
                if( $is_edit ) {
                    
                    $sample[ 'id' ] = $this->next_data[ 'post_data' ][ 'post' ][ 'id' ];
                    
                } else {
                    
                    $sample[ 'thumbnail' ] = $this->next_data[ 'post_data' ][ 'pf' ][ 'cover_url' ][ 'upload_value' ][ 'id' ];
                    
                }
                
                $sample[ 'golchin_type' ] = $this->next_data[ 'post_data' ][ 'group' ] == 'nohe' ? 'maddah' : 'singer';
                
                $ok = $this->sendPostRequest(
                    "{$site[ 'api' ]}?action=wpttb_post",
                    $sample,
                    true
                );
                
                if( $ok === false || json_decode( $ok )->success === false ) {
                    
                    [ $dl_texts, $up_texts, $last_update ] = $this->postSendTextGenerator( $this->next_data[ 'post_data' ][ 'pf' ] );
                    
                    $this->setTitle($title);
                    
                    if( $is_edit ) {
                        
                        $this->addText("⚠️ خطایی در ویرایش پست رخ داده است.\n");
                        
                    } else {
                        
                        $this->addText("⚠️ خطایی در ارسال پست رخ داده است.\n");
                        
                    }
                    
                    $this->addText($dl_texts)
                        ->addText($up_texts)
                        ->addText($last_update)
                        ->setMenuText()
                        ->clearButtons()
                        ->addButtonRow(
                            InlineKeyboardButton::make("🔄 تلاش مجدد", callback_data: "id_{$id}_{$user_id}_1@postSend")
                        )
                        ->addPrevMenuBtn($order)
                        ->showMenu();
                        
                    exit;
                    
                }
                
                $res_post = json_decode( $ok )->data;
                
                [ $dl_texts, $up_texts, $last_update ] = $this->postSendTextGenerator( $this->next_data[ 'post_data' ][ 'pf' ] );
                
                $this->setTitle($title);
                    
                if( $is_edit ) {
                    
                    $this->addText("✅ پست با موفقیت ویرایش شد.\n");
                    
                } else {
                    
                    $this->addText("✅ پست با موفقیت ارسال شد.\n");
                    
                }
                
                $this->addText($dl_texts)
                    ->addText($up_texts)
                    ->addText($last_update)
                    ->setMenuText()
                    ->clearButtons()
                    ->addButtonRow(
                        InlineKeyboardButton::make("👀 مشاهده پست", url: $res_post->url)
                    )
                    ->addPrevMenuBtn(3, null, 'post_data')
                    ->showMenu();
                
            } else {
                
                $date = Jalalian::now()->format( 'l d F Y ساعت H:i:s' );
                
                $this->setTitle($title)
                    ->addText('⚠️ امکان اتصال به سرور FTP سایت مورد نظر نیست.')
                    ->addText("🔄 آخرین بررسی: <code>{$date}</code>")
                    ->setMenuText()
                    ->clearButtons()
                    ->addButtonRow(
                        InlineKeyboardButton::make("🔄 بررسی مجدد", callback_data: "id_{$id}_{$user_id}@postSend")
                    )
                    ->addPrevMenuBtn($order)
                    ->setPrevMenu(__FUNCTION__, $order)
                    ->showMenu();
                
            }
            
        }
        
    }
    
    protected function createFtpLink( $host_info, $path ) {
        
        return $this->urlEncode($this->trailingslashit( $host_info[ 'url' ] ) . $path);
        
    }
    
    protected function trailingslashit( $value ) {
        
        return $this->untrailingslashit( $value ) . '/';
        
    }
    
    protected function untrailingslashit( $value ) {

        return rtrim( $value, '/\\' );

    }
    
    public function killIt(Nutgram $bot, ?int $userId = null, ?int $chatId = null) {
        
        parent::terminate($bot, $userId, $chatId);
        
    }
    
    public function terminate(Nutgram $bot, ?int $userId = null, ?int $chatId = null): void {
        
        $text = $bot->message()?->text;
        
        if( $text == '/start' ) {
            
            parent::terminate($bot, $userId, $chatId);
            
        } else {
            
            $this->need_disable = (bool) $bot->messageId();
            
            $bot->stepConversation( $this, $userId, $chatId );
            
        }
        
    }
    
    public function handleStep(): mixed {
        
        $sa = $this->getSerializableAttributes();
        
        if ($this->bot->isCallbackQuery()) {
            $data = $this->bot->callbackQuery()?->data;

            $result = null;
            if (isset($sa['callbacks'][$data])) {
                if($this->need_disable) {
                    $this->need_disable = false;
                    $this->disableMenu();
                }
                $this->step = $sa['callbacks'][$data];
                $data = trim($data, '@');
                $this->bot->callbackQuery()->data = $data;
                $result = $this($this->bot, $data);
            }
            
            $this->answerCallbackQuery();
            return $result;
        }

        if (isset($sa['orNext'])) {
            $this->step = $sa['orNext'];
            return $this($this->bot);
        }

        $this->bot->message()?->delete();
        
        return null;
        
    }
    
    protected function answerCallbackQuery( $opt = [] ) {
        
        return;
        
        static $can_answer = true;
        
        if( $can_answer ) {
            
            $can_answer = false;
            $this->bot->answerCallbackQuery( ...$opt );
            
        }
        
    }
    
    protected function postSendTextGenerator( $pf ) {
        
        $dl_texts = $up_texts = $last_update = '';
        
        foreach( $pf as $key => $field ) {
            
            $dl_texts .= "- <b>{$field[ 'name' ]}</b>: ";
            $up_texts .= "- <b>{$field[ 'name' ]}</b>: ";
            
            if( $field[ 'value' ] == 'local' || $key == 'cover_url' ) {
                
                $dl_texts .= '✅ دانلود شده';
                
            } else {
                
                if( $field[ 'dl' ] === 0 ) {
                    
                    $dl_texts .= '⌛️ در انتظار دانلود ...';
                    
                } else {
                    
                    if( is_int( $field[ 'dl' ] ) ) {
                        
                        $dl_texts .= "🔄 {$field[ 'dl' ]}% دانلود شده ...";
                        
                    } else {
                        
                        if( $field[ 'dl' ] === true ) {
                            
                            $dl_texts .= '✅ دانلود شده';
                            
                        } else {
                            
                            $dl_texts .= '❌ خطا در دانلود';
                            
                        }
                        
                    }
                    
                }
                
            }
            
            if( $field[ 'up' ] === 0 ) {
                
                $up_texts .= '⌛️ در انتظار آپلود ...';
                
            } else {
                
                if( is_int( $field[ 'up' ] ) ) {
                    
                    $up_texts .= "🔄 {$field[ 'up' ]}% آپلود شده ...";
                    
                } else {
                    
                    if( $field[ 'up' ] === true ) {
                        
                        $up_texts .= '✅ آپلود شده';
                        
                    } else {
                        
                        $up_texts .= '❌ خطا در آپلود';
                        
                    }
                    
                }
                
            }
            
            $dl_texts .= "\n";
            $up_texts .= "\n";
            
        }
        
        $date = Jalalian::now()->format( 'l d F Y ساعت H:i:s' );
        
        $dl_texts = "📥 <b>دانلود ها</b>\n\n" . $dl_texts;
        $up_texts = "📤 <b>آپلود ها</b>\n\n" . $up_texts;
        $last_update = "🔄 آخرین بروزرسانی: <code>{$date}</code>";
        
        return [ $dl_texts, $up_texts, $last_update ];
        
    }
    
    public function _dlHost(Nutgram $bot, array $data = []) {
        
        if( isset( $this->next_data[ __FUNCTION__ ] ) ) {
            
            $step = $this->next_data[ __FUNCTION__ ][ 'step' ];
            $title = $this->next_data[ __FUNCTION__ ][ 'title' ];
            $message = $bot->message();
            $text = $message->getText();
            
            switch( $step ) {
                
                case 'ftp_host':
                    
                    $rules = [ v::domain(), v::ip('*', FILTER_FLAG_IPV4) ];
                    
                    if( isset( $this->next_data[ __FUNCTION__ ][ 'prev_data' ][ $step ] ) ) {
                        
                        $rules = array_merge( $rules, [ v::equals('0'), v::equals('۰') ] );
                        
                    }
                    
                    if( v::oneOf( ...$rules )->validate( $text ) ) {
                        
                        if( in_array( $text, [ '0', '۰' ] ) ) {
                            
                            $this->next_data[ __FUNCTION__ ][ 'data' ][ $step ] = $this->next_data[ __FUNCTION__ ][ 'prev_data' ][ $step ];
                            
                        } else {
                            
                            $this->next_data[ __FUNCTION__ ][ 'data' ][ $step ] = $text;
                            
                        }
                        
                        $this->setNextSubStep( __FUNCTION__, $step );
                        $this->handleSubStep( $bot, __FUNCTION__ );
                        
                    } else {
                        
                        $text = $this->handleSubStep( $bot, __FUNCTION__, true );
                        
                        $this->disableMenu()
                            ->setTitle($title)
                            ->addText("🚫 مقدار ارسال شده معتبر نیست.\n")
                            ->addText($text)
                            ->setMenuText()
                            ->showMenu();
                        
                    }
                
                break;
                
                case 'ftp_username':
                    
                    $rules = [ v::NotEmpty()->Not(v::equals('۰')) ];
                    
                    if( isset( $this->next_data[ __FUNCTION__ ][ 'prev_data' ][ $step ] ) ) {
                        
                        $rules = array_merge( $rules, [ v::equals('0'), v::equals('۰') ] );
                        
                    }
                    
                    if( v::oneOf( ...$rules )->validate( $text ) ) {
                        
                        if( in_array( $text, [ '0', '۰' ] ) ) {
                            
                            $this->next_data[ __FUNCTION__ ][ 'data' ][ $step ] = $this->next_data[ __FUNCTION__ ][ 'prev_data' ][ $step ];
                            
                        } else {
                            
                            $this->next_data[ __FUNCTION__ ][ 'data' ][ $step ] = $text;
                            
                        }
                        
                        $this->setNextSubStep( __FUNCTION__, $step );
                        $this->handleSubStep( $bot, __FUNCTION__ );
                        
                    } else {
                        
                        $text = $this->handleSubStep( $bot, __FUNCTION__, true );
                        
                        $this->disableMenu()
                            ->setTitle($title)
                            ->addText("🚫 مقدار ارسال شده معتبر نیست.\n")
                            ->addText($text)
                            ->setMenuText()
                            ->showMenu();
                        
                    }
                
                break;
                
                case 'ftp_password':
                
                    $rules = [ v::NotEmpty()->Not(v::equals('۰')) ];
                    
                    if( isset( $this->next_data[ __FUNCTION__ ][ 'prev_data' ][ $step ] ) ) {
                        
                        $rules = array_merge( $rules, [ v::equals('0'), v::equals('۰') ] );
                        
                    }
                    
                    if( v::oneOf( ...$rules )->validate( $text ) ) {
                        
                        if( in_array( $text, [ '0', '۰' ] ) ) {
                            
                            $this->next_data[ __FUNCTION__ ][ 'data' ][ $step ] = $this->next_data[ __FUNCTION__ ][ 'prev_data' ][ $step ];
                            
                        } else {
                            
                            $this->next_data[ __FUNCTION__ ][ 'data' ][ $step ] = $text;
                            
                        }
                        
                        $this->setNextSubStep( __FUNCTION__, $step );
                        $this->handleSubStep( $bot, __FUNCTION__ );
                        
                    } else {
                        
                        $text = $this->handleSubStep( $bot, __FUNCTION__, true );
                        
                        $this->disableMenu()
                            ->setTitle($title)
                            ->addText("🚫 مقدار ارسال شده معتبر نیست.\n")
                            ->addText($text)
                            ->setMenuText()
                            ->showMenu();
                        
                    }
                
                break;
                
                case 'ftp_port':
                
                    $rules = [ v::between( '1', '65535' ) ];
                    
                    if( isset( $this->next_data[ __FUNCTION__ ][ 'prev_data' ][ $step ] ) ) {
                        
                        $rules = array_merge( $rules, [ v::equals('0'), v::equals('۰') ] );
                        
                    }
                    
                    if( v::oneOf( ...$rules )->validate( $text ) ) {
                        
                        if( in_array( $text, [ '0', '۰' ] ) ) {
                            
                            $this->next_data[ __FUNCTION__ ][ 'data' ][ $step ] = $this->next_data[ __FUNCTION__ ][ 'prev_data' ][ $step ];
                            
                        } else {
                            
                            $this->next_data[ __FUNCTION__ ][ 'data' ][ $step ] = $text;
                            
                        }
                        
                        $this->setNextSubStep( __FUNCTION__, $step );
                        $this->handleSubStep( $bot, __FUNCTION__ );
                        
                    } else {
                        
                        $text = $this->handleSubStep( $bot, __FUNCTION__, true );
                        
                        $this->disableMenu()
                            ->setTitle($title)
                            ->addText("🚫 مقدار ارسال شده معتبر نیست.\n")
                            ->addText($text)
                            ->setMenuText()
                            ->showMenu();
                        
                    }
                
                break;
                
                case 'ftp_path':
                
                    $rules = [ v::startsWith('/') ];
                    
                    if( isset( $this->next_data[ __FUNCTION__ ][ 'prev_data' ][ $step ] ) ) {
                        
                        $rules = array_merge( $rules, [ v::equals('0'), v::equals('۰') ] );
                        
                    }
                    
                    if( v::oneOf( ...$rules )->validate( $text ) ) {
                        
                        if( in_array( $text, [ '0', '۰' ] ) ) {
                            
                            $this->next_data[ __FUNCTION__ ][ 'data' ][ $step ] = $this->next_data[ __FUNCTION__ ][ 'prev_data' ][ $step ];
                            
                        } else {
                            
                            $this->next_data[ __FUNCTION__ ][ 'data' ][ $step ] = $text;
                            
                        }
                        
                        $this->setNextSubStep( __FUNCTION__, $step );
                        $this->handleSubStep( $bot, __FUNCTION__ );
                        
                    } else {
                        
                        $text = $this->handleSubStep( $bot, __FUNCTION__, true );
                        
                        $this->disableMenu()
                            ->setTitle($title)
                            ->addText("🚫 مقدار ارسال شده معتبر نیست.\n")
                            ->addText($text)
                            ->setMenuText()
                            ->showMenu();
                        
                    }
                
                break;
                
                case 'url':
                
                    $rules = [ v::url()->oneOf( v::startsWith('http://'), v::startsWith('https://') ) ];
                    
                    if( isset( $this->next_data[ __FUNCTION__ ][ 'prev_data' ][ $step ] ) ) {
                        
                        $rules = array_merge( $rules, [ v::equals('0'), v::equals('۰') ] );
                        
                    }
                    
                    if( v::oneOf( ...$rules )->validate( $text ) ) {
                        
                        if( in_array( $text, [ '0', '۰' ] ) ) {
                            
                            $this->next_data[ __FUNCTION__ ][ 'data' ][ $step ] = $this->next_data[ __FUNCTION__ ][ 'prev_data' ][ $step ];
                            
                        } else {
                            
                            $this->next_data[ __FUNCTION__ ][ 'data' ][ $step ] = $text;
                            
                        }
                        
                        $site = $this->siteStore->findById( $this->next_data[ __FUNCTION__ ][ 'site' ][ '_id' ] );
                        
                        $site[ 'dl_host' ] = $this->next_data[ __FUNCTION__ ][ 'data' ];
                        
                        $this->siteStore->update( $site );
                        
                        $this->next_data = [];
                        
                        $this->setTitle("📥 تنظیمات هاست دانلود")
                            ->addText('✅ اطلاعات هاست دانلود با موفقیت ذخیره شد.')
                            ->setMenuText()
                            ->clearButtons()
                            ->addPrevMenuBtn(3)
                            ->disableMenu()
                            ->showMenu();
                        
                    } else {
                        
                        $text = $this->handleSubStep( $bot, __FUNCTION__, true );
                        
                        $this->disableMenu()
                            ->setTitle($title)
                            ->addText("🚫 مقدار ارسال شده معتبر نیست.\n")
                            ->addText($text)
                            ->setMenuText()
                            ->showMenu();
                        
                    }
                
                break;
                
            }
            
        }
        
    }
    
    public function cancelSubStep(Nutgram $bot, string $key) {
        
        extract( $this->next_data[ $key ][ 'cancel' ] );
        
        if( $key == 'post_data' ) {
            
            $this->removeDir( $this->next_data[ $key ][ 'tmp' ] );
            
        }
        
        $this->next_data = [];
        $this->step = $method;
        $this->bot->callbackQuery()->data = $data;
        
        if( is_null( $this->messageId ) || $this->bot->messageId() != $this->messageId ) {
            
            $this->closeMenu();
            
        }
        
        $this($this->bot, $data);
        
    }
    
    protected function loadMenu(string $step, string $data = '') {
        
        $this->callback_data = $data;
        
        $this->{$step}($this->bot, $data);
        
    }
    
    /* For Base */
    
    protected function addOverCoverParagraph( $type, $types, $html ) {
        
        if( $type == 'nor' ) {
            
            return $html;
            
        }
        
        $type_data = $types[ $type ];
        
        if( empty( $type_data[ 'text' ] ) ) {
            
            return $html;
            
        }
        
        $doc = new Document();
        $doc->html( $html );
        
        $element = $doc->find( 'img[src="wpttb_cover.png"]' )->first();
        
        if( $element === null ) {
            
            return $html;
            
        }
        
        while( $element->parent()->nodeName != 'body' ) {
            
            $element = $element->parent();
            
        }
        
        if( !empty( $type_data[ 'color' ] ) ) {
            
            $p = '<p style="text-align: center;"><span style="color: '.$type_data[ 'color' ].';"><strong>'.$type_data[ 'text' ].'</strong></span></p>';
            
        } else {
            
            $p = '<p style="text-align: center;"><strong>'.$type_data[ 'text' ].'</strong></p>';
            
        }
        
        $element->precede( $p );
        
        return $doc->find( 'body' )->html();
        
    }
    
    protected function htmlCoverReplacer( $cover_path, $cover_url, $html ) {
        
        $doc = new Document();
        $doc->html( $html );
        $imgs = $doc->find( 'img[src="wpttb_cover.png"]' );
        
        list( $width, $height ) = getimagesize( $cover_path );
        
        $imgs->each( function( $img ) use ( $cover_url, $width, $height ) {
            
            $img->attr( 'src', $cover_url );
            $img->attr( 'width', $width );
            $img->attr( 'height', $height );
            
        } );
        
        return $doc->find( 'body' )->html();
        
    }
    
    protected function htmlReplacer( $field, $html ) {
        
        $doc = new Document();
        $doc->html( $html );
        $nodes = $doc->find( "body *" );

        $nodes->each(function( $node ) use ( $doc, $field ) {
            
            if(
                trim( $node->text() ) == $field[ 'variable' ] &&
                (
                    $node->parent()->nodeName == 'body' ||
                    trim( $node->parent()->text() ) != $field[ 'variable' ]
                )
            ) {
                
                $div = $doc->create( '<div/>' );
                $quote = $doc->create( '<blockquote/>' );
                $quote_start = $quote_end = false;
                
                foreach( $field[ 'value' ] as $key => $value ) {
                    
                    $target = $node->clone();
                    
                    if( str_starts_with( trim( $value ), '<blockquote' ) ) {
                        
                        $quote_start = true;
                        
                    }
                    
                    if( str_ends_with( trim( $value ), '</blockquote>' ) ) {
                        
                        $quote_end = true;
                        
                    }
                    
                    $value = trim( strip_tags( $value ) );
                    
                    if( $target->children()->count() > 0 ) {
                        
                        $target->find( '*' )->each(function( $node2 ) use ( $field, $value ) {
                            
                            $node2->contents()->each(function( $node3 ) use ( $field, $value ) {
                                
                                if(
                                    $node3->nodeName == '#text' &&
                                    trim( $node3->text() ) == $field[ 'variable' ]
                                ) {
                                    
                                    $text = new Text( trim( $value ) );
                                    $node3->follow( $text )->destroy();
                                    
                                }
                                
                            });
                            
                        });
                        
                    } else {
                        
                        $target->contents()->each(function( $node2 ) use ( $field, $value ) {
                            
                            if(
                                $node2->nodeName == '#text' &&
                                trim( $node2->text() ) == $field[ 'variable' ]
                            ) {
                                
                                $text = new Text( trim( $value ) );
                                $node2->follow( $text )->destroy();
                                
                            }
                            
                        });
                        
                    }
                    
                    if( $quote_start ) {
                        
                        $target->appendTo( $quote );
                        
                    } else {
                        
                        $target->appendTo( $div );
                        
                    }
                    
                    if( isset( $field[ 'seprator' ] ) && array_key_last( $field[ 'value' ] ) != $key ) {
                        
                        $text = new Text( $field[ 'seprator' ] );
                        $target->follow( $text );
                        
                    }
                    
                    if( $quote_end ) {
                        
                        $quote->clone()->appendTo( $div );
                        $quote->empty();
                        $quote_start = $quote_end = false;
                        
                    }
                    
                }
                
                $node->follow( $div )->destroy();
                $div->contents()->first()->unwrap();
                
            }
            
        });
        
        return $doc->find( 'body' )->html();
        
    }
    
    protected function genericReplacer( $fields, $subject, $seprator = null ) {
        
        foreach( $fields as $key => $field ) {
            
            $value = $field[ 'value' ];
            
            if( isset( $field[ 'multi' ] ) ) {
                
                $value = '';
                
            }
            
            if( isset( $field[ 'seprator' ] ) ) {
                
                $value = implode( $seprator ?? $field[ 'seprator' ], (array) $value );
                
            }
            
            $subject = str_replace( $field[ 'variable' ], $value, $subject );
            
        }
        
        return $subject;
        
    }
    
    protected function generateFieldVarValPair( $fields, $values, $string_only = true ) {
        
        $result = [];
        
        array_walk( $fields, function( $field, $key ) use ( &$result, $values, $string_only ) {
            
            $result[ $field[ 'variable' ] ] = $values[ $key ] ?? '';
            
            if( $string_only ) {
                
                if( isset( $field[ 'seprator' ] ) ) {
                    
                    $result[ $field[ 'variable' ] ] = implode( $field[ 'seprator' ], (array)$result[ $field[ 'variable' ] ] );
                    
                }
                
            } else {
                
                if( isset( $field[ 'seprator' ] ) ) {
                    
                    $result[ $field[ 'variable' ] ] = (array) $result[ $field[ 'variable' ] ];
                    
                }
                
                if( isset( $field[ 'multi' ] ) ) {
                    
                    $result[ $field[ 'variable' ] ] = explode( "\n", $result[ $field[ 'variable' ] ] );
                    
                }
                
            }
            
        } );
        
        return $result;
        
    }
    
    protected function varValReplacer( $var_val_pair, $search ) {
        
        $vars = array_keys( $var_val_pair );
        $vals = array_values( $var_val_pair );
        
        return str_replace( $vars, $vals, $search );
        
    }
    
    protected function prepareUrlFields( $site, $fields, $values, $group, $is_edit = false ) {
        
        $result = [];
        $var_val_pair = $this->generateFieldVarValPair( $fields, $values );
        
        foreach( $fields as $key => &$field ) {
            
            if( !str_contains( $key, 'url' ) ) continue;
            if( !isset( $values[ $key ] ) ) continue;
            if(
                $is_edit &&
                $key != 'cover_url' &&
                $values[ $key ] == ( $this->next_data[ 'post_data' ][ 'post' ][ 'fields' ][ $key ] ?? '' )
            ) continue;
            
            $field[ 'value' ] = $values[ $key ];
            
            if( $is_edit ) {
                
                $field[ 'dl' ] = $field[ 'value' ] == 'local' ? true : 0;
                $field[ 'up' ] = $key == 'cover_url' ? true : 0;
                
            } else {
                
                $field[ 'dl' ] = ( $field[ 'value' ] == 'local' || $key == 'cover_url' ) ? true : 0;
                $field[ 'up' ] = 0;
                
            }
            
            if(
                in_array( $key, [ 'url_128', 'url_320', 'teaser_url' ] ) &&
                $this->isLocalUrl( $site, $field[ 'value' ] )
            ) {
                
                $field[ 'dl' ] = true;
                $field[ 'up' ] = true;
                
            }
            
            if( isset( $field[ 'filename' ][ $group ] ) ) {
                
                $field[ 'filename' ][ $group ] = $this->varValReplacer( $var_val_pair, $field[ 'filename' ][ $group ] );
                
            }
            
            if( isset( $field[ 'metadata' ][ $group ] ) ) {
                
                $field[ 'metadata' ][ $group ] = $this->varValReplacer( $var_val_pair, $field[ 'metadata' ][ $group ] );
                
            }
            
            $result[ $key ] = $field;
            
        }
        
        return $result;
        
    }
    
    protected function getFtpClient( $site ) {
        
        if(
            !isset( $site[ 'dl_host' ][ 'ftp_host' ] ) ||
            !isset( $site[ 'dl_host' ][ 'ftp_username' ] ) ||
            !isset( $site[ 'dl_host' ][ 'ftp_password' ] ) ||
            !isset( $site[ 'dl_host' ][ 'ftp_port' ] ) ||
            !isset( $site[ 'dl_host' ][ 'ftp_path' ] ) ||
            !isset( $site[ 'dl_host' ][ 'url' ] )
        ) {
            
            return false;
            
        }
        
        extract( $site[ 'dl_host' ] );
        
        try {
            
            $connection = new FtpConnection( $ftp_host, $ftp_username, $ftp_password, (int) $ftp_port, 5 );
            $connection->open();
            $config = new FtpConfig( $connection );
            $config->usePassiveAddress( false );
            $config->setPassive( true );
            $client = new FtpClient( $connection );
            
        } catch( \Exception $e ) {
            
            return false;
            
        }
        
        return $client;
        
    }
    
    protected function isDuplicate( $site, $data ) {
        
        $url = "{$site[ 'api' ]}?action=wpttb_dup_check";
        
        $body = $this->sendPostRequest( $url, $data, true );
        
        if( $body ) {
            
            $json = json_decode( $body );
            
            if( $json->success ) {
                
                return $json->data->duplicate;
                
            } else {
                
                return null;
                
            }
            
        } else {
            
            return null;
            
        }
        
    }
    
    protected function flopImage( $path ) {
        
        if( !extension_loaded( 'imagick' ) || !class_exists( 'Imagick' ) ) {
            
            $image = ImageManager::gd()->read( $path );
            
        } else {
            
            $image = ImageManager::imagick()->read( $path );
            
        }
        
        $image->flop();
        
        $jpg = $image->toJpg();
        
        $jpg->save( $path );
        
    }
    
    protected function changeSaturation( $path, $saturation ) {
        
        if( !file_exists( $path ) ) {
            
            return false;
            
        }

        $image = imagecreatefromstring( file_get_contents( $path ) );
        
        if( !$image ) {
            
            return false;
            
        }

        $saturation = max( 0, min( $saturation, 200 ) );

        $width = imagesx( $image );
        $height = imagesy( $image );

        for( $x = 0; $x < $width; $x++ ) {
            
            for( $y = 0; $y < $height; $y++ ) {
                
                $rgb = imagecolorat( $image, $x, $y );
                $colors = imagecolorsforindex( $image, $rgb );

                $red = $colors[ 'red' ];
                $green = $colors[ 'green' ];
                $blue = $colors[ 'blue' ];

                $gray = ( $red * 0.3 + $green * 0.59 + $blue * 0.11 );

                $red = $gray + ( $red - $gray ) * ( $saturation / 100 );
                $green = $gray + ( $green - $gray ) * ( $saturation / 100 );
                $blue = $gray + ( $blue - $gray ) * ( $saturation / 100 );

                $red = min( max( ( int ) $red, 0 ), 255 );
                $green = min( max( ( int ) $green, 0 ), 255 );
                $blue = min( max( ( int ) $blue, 0 ), 255 );

                $newColor = imagecolorallocate( $image, $red, $green, $blue );
                imagesetpixel( $image, $x, $y, $newColor );
                
            }
            
        }

        if( !imagejpeg( $image, $path ) ) {
            
            return false;
            
        }

        imagedestroy( $image );

        return true;
        
    }

    protected function applyImageEffects( $path ) {
        
        if( !extension_loaded( 'imagick' ) || !class_exists( 'Imagick' ) ) {
            
            $image = ImageManager::gd()->read( $path );
            
        } else {
            
            $image = ImageManager::imagick()->read( $path );
            
        }
            
        $image->cover( $_ENV[ 'IMAGE_WIDTH' ], $_ENV[ 'IMAGE_HEIGHT' ] );
        
        $ranges = array_merge( range( 20, 70 ), range( 120, 170 ) );
        
        $saturation = $ranges[ array_rand( $ranges ) ];
        
        if( $image->driver()->id() == 'Imagick' ) {
            
            $imagick = $image->core()->native();
            
            $imagick->stripImage();

            $imagick->modulateImage( 100, $saturation, 100 );
            
        }
            
        $jpg = $image->toJpg();
        
        $jpg->save( $path );
        
        if( $image->driver()->id() == 'GD' ) {
            
            $this->changeSaturation( $path, $saturation );
            
        }
        
    }
    
    protected function tempPathToUrl( $path ) {
        
        $tmp = implode( '/', array_slice( explode( '/', $path ), -3, 3 ) );
        
        return "{$_ENV[ 'BASE_URL' ]}{$tmp}";
        
    }
    
    protected function isSocialLink( $text_url, array $socials = [ 'instagram' ] ) {
        
        $text_url = trim( $text_url );
        
        $host = Uri::new( $text_url )->getHost();
        
        if( $host ) {
            
            $rules = [];
            
            if( in_array( 'youtube', $socials ) ) {
                
                $rules[] = v::endsWith('youtube.com');
                $rules[] = v::endsWith('youtu.be');
                
            }
            
            if( in_array( 'instagram', $socials ) ) {
                
                $rules[] = v::endsWith('instagram.com');
                
            }
            
            if(
                !empty( $rules ) &&
                v::oneOf( ...$rules )->validate( $host )
            ) {
                
                return true;
                
            }
            
        }
        
        return false;
        
    }
    
    protected function closing(Nutgram $bot) {
        
        if( !empty( $this->next_data ) && isset( $this->next_data[ 'post_data' ][ 'tmp' ] ) ) {
            
            $this->removeDir( $this->next_data[ 'post_data' ][ 'tmp' ] );
            
        }
        
        $this->disableMenu();
        
    }
    
    protected function getSocialDirectLink( $url, $audio_only = false ) {
        
        $url = $this->trailingslashit( $_ENV[ 'INSTA_DOWNLOADER_BASE' ] ) . "igdl?url={$url}";
        
        $response = $this->getPage( $url );
        
        if( $response === false ) {
            
            return $response;
            
        }
        
        $data = json_decode( $response, true );
        
        if( $data[ 'status' ] === false ) {
            
            return false;
            
        }
        
        return $data[ 'url' ];
        
    }
    
    protected function isUrlMessage() {
        
        $message = $this->bot->message();
        $text = $message->getText();
        $entities = $message->getEntities();
        
        if(
            v::oneOf( v::startsWith('http://'), v::startsWith('https://') )->validate( $text ) &&
            !empty( $entities ) &&
            count( $entities ) == 1 &&
            $entities[ 0 ]->type->value == 'url' &&
            $entities[ 0 ]->offset == 0 &&
            $entities[ 0 ]->length == mb_strlen( $text, 'utf8' )
        ) {
            
            return true;
            
        } else {
            
            return false;
            
        }   
        
    }
    
    protected function removeDir( $dir ) {
        
        $dir = $this->fixTempPath( $dir );
        
        if( !file_exists( $dir ) ) return false;
        
        $it = new RecursiveDirectoryIterator( $dir, RecursiveDirectoryIterator::SKIP_DOTS );
        $files = new RecursiveIteratorIterator( $it, RecursiveIteratorIterator::CHILD_FIRST );
        
        foreach( $files as $file ) {
            
            if ( $file->isDir() ){
                
                rmdir( $file->getPathname() );
                
            } else {
                
                unlink( $file->getPathname() );
                
            }
            
        }
        
        rmdir( $dir );
        
    }
    
    protected function urlEncode( $text_url ) {
        
        $text_url = trim( $text_url );
        
        $uri = Uri::new( $text_url );
        
        return $uri->toString();
        
    }
    
    protected function getReadability( $html ) {
        
        try {
            
            $this->readability->parse( $html );
            
            return $this->readability;
            
        } catch ( ParseException $e ) {
            
            return false;
            
        }
        
    }
    
    protected function isMusicArticle( $title ) {
        
        if( $this->doc->find( 'link[rel="next"], link[rel="prev"]' )->count() > 0 ) {
            
            return false;
            
        }
        
        $title = strtolower( $title );
        
        if(
            !(
                str_contains( $title, 'دانلود' ) ||
                str_contains( $title, 'download' )
            )
        ) {
            
            return false;
            
        }
        
        if(
            !(
                str_contains( $title, 'آهنگ' ) ||
                str_contains( $title, 'song' ) ||
                str_contains( $title, 'track' ) ||
                str_contains( $title, 'اهنگ' ) ||
                str_contains( $title, 'نوحه' ) ||
                str_contains( $title, 'مداحی' ) ||
                str_contains( $title, 'روضه' ) ||
                str_contains( $title, 'nohe' ) ||
                str_contains( $title, 'madahi' ) ||
                str_contains( $title, 'maddahi' ) ||
                str_contains( $title, 'remix' ) ||
                str_contains( $title, 'ریمیکس' )
            )
        ) {
            
            return false;
            
        }
        
        return true;
        
    }
    
    protected function coverUpload( $url, $data, $timeout = 30, $progress = null ) {
        
        try {
            
            $jwt = new JWT( $_ENV[ 'BOT_TOKEN' ] );
            $token = $jwt->encode( $data );
            
            $multipart = [
                [
                    'name' => 'data',
                    'contents' => $token
                ],
                [
                    'name' => 'cover',
                    'contents' => Psr7\Utils::tryFopen( $data[ 'path' ], 'r' ),
                    'filename' => $data[ 'filename' ]
                ]
            ];
            
            $response = $this->curl->post( $this->urlEncode( $url ), [
                'multipart' => $multipart,
                'timeout' => $timeout,
                'verify' => false,
                'progress' => $progress
            ] );
            
        } catch( \Exception $e ) {
            
            $response = false;
            
        }
        
        if( $response === false || $response->getStatusCode() != 200 ) {
            
            return false;
            
        } else {
            
            $body = (string) $response->getBody();
            
            $json = json_decode( $body, true );
            
            if( $json[ 'success' ] ) {
                
                return $json[ 'data' ];
                
            } else {
                
                return false;
                
            }
            
        }
        
    }
    
    protected function sendPostRequest( $url, $data, $jwt = false ) {
        
        try {
            
            if( $jwt ) {
                
                $jwt = new JWT( $_ENV[ 'BOT_TOKEN' ] );
                $token = $jwt->encode( [ 'data' =>  $data ] );
                
                $response = $this->curl->post( $this->urlEncode( $url ), [
                    'body' => $token,
                    'timeout' => 30,
                    'verify' => false
                ] );
                
            } else {
                
                $response = $this->curl->post( $this->urlEncode( $url ), [
                    'json' => $data,
                    'timeout' => 30,
                    'verify' => false
                ] );
                
            }
            
        } catch( \Exception $e ) {
            
            $response = false;
            
        }
        
        if( $response === false || $response->getStatusCode() != 200 ) {
            
            return false;
            
        } else {
            
            return (string) $response->getBody();
            
        }
        
    }
    
    protected function getPage( $url ) {
        
        try {
            
            $response = $this->curl->get( $this->urlEncode( $url ), [
                'timeout' => 30,
                'verify' => false
            ] );
            
        } catch( \Exception $e ) {
            
            $response = false;
            
        }
        
        if( $response === false || $response->getStatusCode() != 200 ) {
            
            return false;
            
        } else {
            
            return (string) $response->getBody();
            
        }
        
    }
    
    protected function getRemoteFileSize( $url ) {
        
        try {
            
            $response = $this->curl->head( $this->urlEncode( $url ), [
                'verify' => false
            ] );
            
        } catch( \Exception $e ) {
            
            $response = false;
            
        }
        
        if( $response === false || $response->getStatusCode() != 200 ) {
            
            return 0;
            
        } else {
            
            if( $response->hasHeader('Content-Length') ) {
                
                return (int) $response->getHeader('Content-Length')[0];
                
            } else {
            
                return 0;
            
            }
            
        }
        
    }
    
    protected function filePartialDownload( $url, $path, $limit = 1048576 ) {
        
        $writefn = function( $ch, $chunk ) use ( $limit, &$datadump ) {
            
            static $data = '';
            
            $len = strlen( $data ) + strlen( $chunk );
            
            if( $len >= $limit ) {
                
                $data .= substr( $chunk, 0, $limit - strlen( $data ) );
                $datadump = $data;
                
                return -1;
                
            }
            
            $data .= $chunk;
            $datadump = $data;
            
            return strlen( $chunk );
            
        };

        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $this->urlEncode( $url ) );
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt( $ch, CURLOPT_TIMEOUT, 30 );
        curl_setopt( $ch, CURLOPT_BINARYTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_WRITEFUNCTION, $writefn );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
        curl_exec( $ch );
        
        if( curl_getinfo( $ch, CURLINFO_HTTP_CODE ) != 200 ) {
            
            return false;
            
        }
        
        curl_close( $ch );
        
        return ( file_put_contents( $path, $datadump ) !== false );
        
    }
    
    protected function fileDownload( $url, $path, $timeout = 30, $progress = null, $filename = null ) {
        
        $path = is_null( $filename ) ? $path : dirname( $path ) . "/tmp_video.mp4";
        
        $options = [
            'timeout' => $timeout,
            'verify' => false,
            'sink' => $path,
            'progress' => $progress
        ];
        
        if( isset( $_ENV[ 'PROXY_AUTH' ], $_ENV[ 'PROXY_DOMAIN' ] ) ) {
            
            $blocked = $this->checker->acheck( $url );
            
            if( $blocked ) {
                
                $options[ 'headers' ] = [
                    'Proxy-Auth' => $_ENV[ 'PROXY_AUTH' ],
                    'Proxy-Target-URL' => $this->urlEncode( $url )
                ];
                
                $url = $_ENV[ 'PROXY_DOMAIN' ];
                
            } else {
                
                $url = $this->urlEncode( $url );
                
            }
            
        } else {
            
            $url = $this->urlEncode( $url );
            
        }
        
        try {
            
            $response = $this->curl->get( $url, $options );
            
        } catch( \Exception $e ) {
            
            $response = false;
            
        }
        
        if( $response === false || $response->getStatusCode() != 200 ) {
            
            @unlink( $path );
            
            return false;
            
        }
        
        if( !is_null( $filename ) ) {
            
            if( isset( $_ENV[ 'FFMPEG_PAEH' ], $_ENV[ 'FFPROBE_PAEH' ] ) ) {
                
                try {
                
                    $ffmpeg = FFMpeg::create( [
                        'ffmpeg.binaries'  => $_ENV[ 'FFMPEG_PAEH' ],
                        'ffprobe.binaries' => $_ENV[ 'FFPROBE_PAEH' ]
                    ] );
                    
                    $video = $ffmpeg->open( $path );
                    $video->filters()->resample( 44100 );
                    
                    $audioformat = new Mp3;
                    $audioformat->setAudioKiloBitrate( $filename == 'url_320.mp3' ? 320 : 128 );
                    
                    $video->save( $audioformat, dirname( $path ) . '/' . $filename );
                    
                    @unlink( $path );
                
                } catch( \Exception $e ) {
                    
                    @unlink( $path );
                    
                    return false;
                    
                }
                
            } else {
                
                $tmp = basename( dirname( $path ) );
                $bitrate = $filename == 'url_320.mp3' ? '320k' : '128k';
            
                $url = $this->trailingslashit( $_ENV[ 'INSTA_DOWNLOADER_BASE' ] ) . "mp3?tmp={$tmp}&filename={$filename}&bitrate={$bitrate}";
                
                $response = $this->getPage( $url );
                
                if( $response === false ) {
                    
                    @unlink( $path );
                    
                    return $response;
                    
                }
                
                $data = json_decode( $response, true );
                
                if( $data[ 'status' ] === false ) {
                    
                    @unlink( $path );
                    
                    return false;
                    
                }
                
                @unlink( $path );
                
            }
        
        }
        
        return true;
        
    }
    
    protected function nearestBitrate( $bitrate ) {
        
        $bits = [ 128000, 320000 ];
        $bit_distanse = array_map( function( $item ) use ( $bitrate ) {
            return abs( $bitrate - $item ); 
        } , $bits );
        $nearest_key = array_keys( $bit_distanse, min( $bit_distanse ) )[ 0 ];
        $nearest = $bits[ $nearest_key ];
        
        return $nearest / 1000 . 'kbps';
        
    }

    protected function generateRandomString( $length = 10 ) {
        
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen( $characters );
        $randomString = '';

        for( $i = 0; $i < $length; $i++ ) {
            
            $randomString .= $characters[ random_int( 0, $charactersLength - 1 ) ];
            
        }

        return $randomString;
        
    }
    
    protected function disableMenu() {
        
        try {
            $this->bot->editMessageReplyMarkup($this->chatId, $this->messageId);
            $this->messageId = null;
        } catch( \Exception $e ) {
        }
        
        return $this;
        
    }
    
    protected function setNextSubStep( $method, $step ) {
        
        $next_data = $this->next_data[ $method ];
        $steps = array_keys( $next_data[ 'steps' ] );
        $step_key = array_search( $step, $steps );
        $next_step = $steps[ $step_key + 1 ];
        
        $this->next_data[ $method ][ 'step' ] = $next_step;
        
    }
    
    protected function handleSubStep( Nutgram $bot, $method, $return = false ) {
        
        $next_data = $this->next_data[ $method ];
        $step = $next_data[ 'step' ];
        $step_texts = $next_data[ 'steps' ][ $step ];
        
        foreach( $step_texts as $step_text ) {
            
            $this->addText( $step_text );
            
        }
        
        if( isset( $this->next_data[ $method ][ 'prev_data' ][ $step ] ) ) {
            
            $prev_val = $this->next_data[ $method ][ 'prev_data' ][ $step ];
            
            $this->addText( "📝 مقدار قبلی: <code>{$prev_val}</code>" );
            $this->addText( "💡 جهت حفظ مقدار قبلی مقدار <code>0</code> را ارسال کنید." );
            
        }
        
        if( $return ) {
            
            $text = $this->getTexts();
            
            return $text;
            
        } else {
            
            $this->disableMenu()
                ->setTitle( $next_data[ 'title' ] )
                ->setMenuText()
                ->showMenu();
        
        }
        
    }
    
    protected function setTitle($title, $direction = 'rtl') {
        
        switch( $direction ) {
            
            case "rtl":
            
                $this->menu_title = "‏$title";
            
            break;
            
            case "ltr":
            
                $this->menu_title = "‎$title";
            
            break;
            
            default:
            
                $this->menu_title = $title;
            
        }
        
        return $this;
        
    }
    
    protected function attachImage( $text_url ) {
        
        $text_url = trim( $text_url );
        
        $random = $this->generateRandomString();
        
        $uri = Uri::new( $text_url );
        $newUri = Modifier::from( $uri )->appendQuery("wpttb={$random}");
        
        $this->menu_text['image'] = '<a href="'.$newUri.'">&#8205;</a>';
        
        return $this;
        
    }
    
    protected function addText($text, $direction = 'rtl') {
        
        switch( $direction ) {
            
            case "rtl":
            
                $this->menu_text[] = "‏$text";
            
            break;
            
            case "ltr":
            
                $this->menu_text[] = "‎$text";
            
            break;
            
            default:
            
                $this->menu_text[] = $text;
            
        }
        
        return $this;
        
    }
    
    protected function getTexts() {
        
        $text = '';
        
        if( isset( $this->menu_text[ 'image' ] ) ) {
            
            $text = $this->menu_text[ 'image' ];
            unset($this->menu_text[ 'image' ]);
            
        }
        
        $text .= implode("\n", $this->menu_text);
        
        $this->menu_text = [];
        
        return $text;
        
    }
    
    protected function setMenuText( $is_preview_disabled = true, $preview_above = false ) {
        
        if( empty( $this->menu_text ) ) {
            
            return $this->menuText($this->menu_title);
            
        } else {
            
            $text = '';
            
            if( isset( $this->menu_text[ 'image' ] ) ) {
                
                $text = $this->menu_text[ 'image' ];
                unset($this->menu_text[ 'image' ]);
                
            }
            
            $text .= implode("\n", $this->menu_text);
            
            $this->menu_text = [];
            
            return $this->menuText($this->menu_title . "\n\n" . $text, [
                'parse_mode' => ParseMode::HTML,
                'link_preview_options' => LinkPreviewOptions::make(
                    is_disabled: $is_preview_disabled,
                    show_above_text: $preview_above
                )
            ]);
            
        }
        
    }
    
    protected function setPrevMenu($method, $order) {
        
        if( $this->bot->isCallbackQuery() ) {
            
            $data = $this->bot->callbackQuery()?->data;
            
        } else {
            
            if( !empty( $this->callback_data ) ) {
                
                $data = $this->callback_data;
                
                $this->callback_data = null;
                
            } else {
                
                $data = null;
                
            }
            
        }
        
        if( isset( $data ) ) {
            
            $data_arr = explode( "_", $data );
            
            if( count( $data_arr ) > 3 ) {
                
                array_splice( $data_arr, 3 );
                $data = implode( "_", $data_arr );
                
            }
            
        }
        
        $this->prev_menu[ $order ] = [
            'title' => $this->menu_title,
            'data' => $data,
            'method' => $method
        ];
        
        return $this;
        
    }
    
    protected function getLoginUrl($id) {
        
        return "{$_ENV['BASE_URL']}wp-login.php?wpttb_site_id={$id}";
        
    }
    
    protected function getSampleUrl( $id = null ) {
        
        if( $id === null ) {
            
            return "{$_ENV['BASE_URL']}sample.php";
            
        } else {
            
            return "{$_ENV['BASE_URL']}sample.php?site_id={$id}";
            
        }
        
    }
    
    protected function setMenuButtons($buttons, $per_page = 20, $page = 1, $columns = 2, $clear = false) {
        
        if( $clear ) {
            
            $this->clearButtons();
            
        }
        
        $prev_next = [];
        
        $offset = $page == 1 ? 0 : $per_page * ($page - 1);
        
        if( $offset > 0 ) {
            
            $prev = $page - 1;
            
            $prev_next[] = InlineKeyboardButton::make('صفحه قبلی ⬅️', callback_data: "p_{$prev}@sitesList");
            
        }
        
        if( $page < ceil( count( $buttons ) / $per_page ) ) {
            
            $next = $page + 1;
            
            $prev_next[] = InlineKeyboardButton::make('➡️ صفحه بعدی', callback_data: "p_{$next}@sitesList");
            
        }
        
        $buttons = array_slice( $buttons, $offset, $per_page );
        
        if( $columns > 1 ) {
            
            $buttons = array_chunk( $buttons, $columns );
            
        }
        
        foreach( $buttons as $buttons_row ) {
            
            if( is_array( $buttons_row ) ) {
                
                $buttons_row = array_reverse( $buttons_row );
                
                $this->addButtonRow( ...$buttons_row );
                
            } else {
                
                $this->addButtonRow( $buttons_row );
                
            }
            
        }
        
        if( ! empty( $prev_next ) ) {
            
            $this->addButtonRow( ...$prev_next );
            
        }
        
        return $this;
        
    }
    
    protected function closeConnection() {
        
        set_time_limit(0);
        ignore_user_abort(true);
        ob_end_clean();
        ob_start();
        echo "OK";
        $size = ob_get_length();
        header("Connection: close");
        header("Content-Encoding: none");
        header("Content-Length: {$size}");
        http_response_code(200);
        ob_end_flush();
        @ob_flush();
        flush();
        
    }
    
    protected function addPrevMenuBtn($order, $btn_text = null, $cancelStepKey = false) {
        
        $order -= 1;
        
        if( ! empty( $this->prev_menu ) && array_key_exists( $order, $this->prev_menu ) ) {
            
            if( is_null( $btn_text ) ) {
                
                $text = "بازگشت به {$this->prev_menu[$order]['title']}";
                
            } else {
                
                $text = $btn_text;
                
            }
            
            $data = isset( $this->prev_menu[$order]['data'] ) ? $this->prev_menu[$order]['data'] : "";
            $data = str_starts_with( $data, "p_" ) ? "p_1" : $data;
            
            if( $cancelStepKey ) {
                
                $this->next_data[ $cancelStepKey ][ 'cancel' ] = [
                    'data' => $data,
                    'method' => $this->prev_menu[$order]['method']
                ];
                
                $data = "{$cancelStepKey}@cancelSubStep";
                
            } else {
            
                $data .= "@{$this->prev_menu[$order]['method']}";
            
            }
            
            return $this->addButtonRow(
                InlineKeyboardButton::make($text, callback_data: $data)
            );
            
        }
        
        return $this;
        
    }
    
}
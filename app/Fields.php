<?php

namespace App;

class Fields {
    
    public const GROUPS = [ 'song', 'nohe', 'remix', 'trend', 'trend-nohe', 'trend-remix' ];
    private array $fields = [];
    
    private function song_fa() {
        
        return [
            'fullname' => 'نام آهنگ به فارسی',
            'name' => 'آهنگ فارسی',
            'variable' => '%%آهنگ_فارسی%%',
            'ai' => true,
            'duplicate' => true,
            'required' => true
        ];
        
    }
    
    private function song_en() {
        
        return [
            'fullname' => 'نام آهنگ به انگلیسی',
            'name' => 'آهنگ انگلیسی',
            'variable' => '%%آهنگ_انگلیسی%%',
            'ai' => true,
            'required' => true
        ];
        
    }
    
    private function singer_fa() {
        
        return [
            'fullname' => 'نام خواننده به فارسی',
            'name' => 'خواننده فارسی',
            'variable' => '%%خواننده_فارسی%%',
            'ai' => true,
            'seprator' => ' و ',
            'duplicate' => true,
            'edit' => [
                'text' => [
                    'در صورتی که این پست بیش از یک خواننده دارد، نام خواننده ها را با <code> و </code> از یکدیگر جدا کنید.'
                ]
            ],
            'required' => true
        ];
        
    }
    
    private function singer_en() {
        
        return [
            'fullname' => 'نام خواننده به انگلیسی',
            'name' => 'خواننده انگلیسی',
            'variable' => '%%خواننده_انگلیسی%%',
            'ai' => true,
            'seprator' => ' & ',
            'edit' => [
                'text' => [
                    'در صورتی که این پست بیش از یک خواننده دارد، نام خواننده ها را با <code> & </code> از یکدیگر جدا کنید.'
                ]
            ],
            'required' => true
        ];
        
    }
    
    private function cover_url() {
        
        return [
            'fullname' => 'تصویر مطلب',
            'name' => 'کاور',
            'variable' => '%%کاور%%',
            'source' => true,
            'filename' => [
                'song' => '%%خواننده_انگلیسی%% - %%آهنگ_انگلیسی%%.jpg',
                'trend' => '%%ترند_انگلیسی%%.jpg',
                'trend-nohe' => '%%ترند_انگلیسی%%.jpg',
                'trend-remix' => '%%ترند_انگلیسی%%.jpg',
                'nohe' => '%%مداح_انگلیسی%% - %%نوحه_انگلیسی%%.jpg',
                'remix' => '%%خواننده_انگلیسی%% - %%آهنگ_انگلیسی%%.jpg'
            ],
            'edit' => [
                'text' => [
                    'لینک تصویر و فایل تصویر قابل قبول است.',
                    'برای ارسال تصویر میتوانید از ربات <code>@pic</code> استفاده کنید. به عنوان مثال برای جستجوی تصاویر <code>محسن یگانه</code> می توانید از کامند زیر استفاده کنید:<pre>@pic محسن یگانه</pre>'
                ]
            ],
            'required' => true
        ];
        
    }
    
    private function url_128() {
        
        return [
            'name' => 'لینک 128',
            'variable' => '%%لینک_128%%',
            'source' => true,
            'filename' => [
                'song' => '%%خواننده_انگلیسی%% - %%آهنگ_انگلیسی%% [128].mp3',
                'trend' => '%%ترند_انگلیسی%% [128].mp3',
                'trend-nohe' => '%%ترند_انگلیسی%% [128].mp3',
                'trend-remix' => '%%ترند_انگلیسی%% [128].mp3',
                'nohe' => '%%مداح_انگلیسی%% - %%نوحه_انگلیسی%% [128].mp3',
                'remix' => '%%خواننده_انگلیسی%% - %%آهنگ_انگلیسی%% [128].mp3'
            ],
            'metadata' => [
                'song' => [
                    'title' => '%%آهنگ_انگلیسی%%',
                    'artist' => '%%خواننده_انگلیسی%%'
                ],
                'trend' => [
                    'title' => '%%ترند_انگلیسی%%'
                ],
                'trend-nohe' => [
                    'title' => '%%ترند_انگلیسی%%'
                ],
                'trend-remix' => [
                    'title' => '%%ترند_انگلیسی%%'
                ],
                'nohe' => [
                    'title' => '%%نوحه_انگلیسی%%',
                    'artist' => '%%مداح_انگلیسی%%'
                ],
                'remix' => [
                    'title' => '%%آهنگ_انگلیسی%%',
                    'artist' => '%%خواننده_انگلیسی%%'
                ]
            ],
            'edit' => [
                'text' => [
                    'لینک و فایل <code>mp3</code> قابل قبول است.',
                    'در صورت ارسال لینک ویدئو از <b>اینستاگرام</b>، صوت ویدئو استخراج شده و قرار میگیرد.'
                ]
            ],
            'required' => false
        ];
        
    }
    
    private function url_320() {
        
        return [
            'name' => 'لینک 320',
            'variable' => '%%لینک_320%%',
            'source' => true,
            'filename' => [
                'song' => '%%خواننده_انگلیسی%% - %%آهنگ_انگلیسی%%.mp3',
                'trend' => '%%ترند_انگلیسی%%.mp3',
                'trend-nohe' => '%%ترند_انگلیسی%%.mp3',
                'trend-remix' => '%%ترند_انگلیسی%%.mp3',
                'nohe' => '%%مداح_انگلیسی%% - %%نوحه_انگلیسی%%.mp3',
                'remix' => '%%خواننده_انگلیسی%% - %%آهنگ_انگلیسی%%.mp3'
            ],
            'metadata' => [
                'song' => [
                    'title' => '%%آهنگ_انگلیسی%%',
                    'artist' => '%%خواننده_انگلیسی%%'
                ],
                'trend' => [
                    'title' => '%%ترند_انگلیسی%%'
                ],
                'trend-nohe' => [
                    'title' => '%%ترند_انگلیسی%%'
                ],
                'trend-remix' => [
                    'title' => '%%ترند_انگلیسی%%'
                ],
                'nohe' => [
                    'title' => '%%نوحه_انگلیسی%%',
                    'artist' => '%%مداح_انگلیسی%%'
                ],
                'remix' => [
                    'title' => '%%آهنگ_انگلیسی%%',
                    'artist' => '%%خواننده_انگلیسی%%'
                ]
            ],
            'edit' => [
                'text' => [
                    'لینک و فایل <code>mp3</code> قابل قبول است.',
                    'در صورت ارسال لینک ویدئو از <b>اینستاگرام</b>، صوت ویدئو استخراج شده و قرار میگیرد.'
                ]
            ],
            'required' => false
        ];
        
    }
    
    private function teaser_url() {
        
        return [
            'name' => 'لینک تیزر',
            'variable' => '%%لینک_تیزر%%',
            'source' => true,
            'filename' => [
                'song' => '%%خواننده_انگلیسی%% - %%آهنگ_انگلیسی%%.mp4',
                'trend' => '%%ترند_انگلیسی%%.mp4',
                'trend-nohe' => '%%ترند_انگلیسی%%.mp4',
                'trend-remix' => '%%ترند_انگلیسی%%.mp4',
                'nohe' => '%%مداح_انگلیسی%% - %%نوحه_انگلیسی%%.mp4',
                'remix' => '%%خواننده_انگلیسی%% - %%آهنگ_انگلیسی%%.mp4'
            ],
            'metadata' => [
                'song' => [
                    'title' => '%%آهنگ_انگلیسی%%',
                    'artist' => '%%خواننده_انگلیسی%%'
                ],
                'trend' => [
                    'title' => '%%ترند_انگلیسی%%'
                ],
                'trend-nohe' => [
                    'title' => '%%ترند_انگلیسی%%'
                ],
                'trend-remix' => [
                    'title' => '%%ترند_انگلیسی%%'
                ],
                'nohe' => [
                    'title' => '%%نوحه_انگلیسی%%',
                    'artist' => '%%مداح_انگلیسی%%'
                ],
                'remix' => [
                    'title' => '%%آهنگ_انگلیسی%%',
                    'artist' => '%%خواننده_انگلیسی%%'
                ]
            ],
            'edit' => [
                'text' => [
                    'لینک و فایل <code>mp4</code> قابل قبول است.',
                    'لینک ویدئو از <b>اینستاگرام</b> قابل قبول است.'
                ]
            ],
            'required' => false
        ];
        
    }
    
    private function lyric() {
        
        return [
            'name' => 'متن ترانه',
            'variable' => '%%متن_ترانه%%',
            'multi' => true,
            'edit' => [
                'text' => [
                    'امکان ارسال متن چند خطی وجود دارد.'
                ]
            ],
            'required' => false
        ];
        
    }
    
    private function trend_fa() {
        
        return [
            'fullname' => 'ترند به فارسی',
            'name' => 'ترند فارسی',
            'variable' => '%%ترند_فارسی%%',
            'duplicate' => true,
            'required' => true
        ];
        
    }
    
    private function trend_en() {
        
        return [
            'fullname' => 'ترند به انگلیسی',
            'name' => 'ترند انگلیسی',
            'variable' => '%%ترند_انگلیسی%%',
            'required' => true
        ];
        
    }
    
    private function nohe_fa() {
        
        return [
            'fullname' => 'نام نوحه به فارسی',
            'name' => 'نوحه فارسی',
            'variable' => '%%نوحه_فارسی%%',
            'ai' => true,
            'required' => true
        ];
        
    }
    
    private function nohe_en() {
        
        return [
            'fullname' => 'نام نوحه به انگلیسی',
            'name' => 'نوحه انگلیسی',
            'variable' => '%%نوحه_انگلیسی%%',
            'ai' => true,
            'required' => true
        ];
        
    }
    
    private function maddah_fa() {
        
        return [
            'fullname' => 'نام مداح به فارسی',
            'name' => 'مداح فارسی',
            'variable' => '%%مداح_فارسی%%',
            'ai' => true,
            'seprator' => ' و ',
            'edit' => [
                'text' => [
                    'در صورتی که این پست بیش از یک مداح دارد، نام مداحان را با <code> و </code> از یکدیگر جدا کنید.'
                ]
            ],
            'required' => true
        ];
        
    }
    
    private function maddah_en() {
        
        return [
            'fullname' => 'نام مداح به انگلیسی',
            'name' => 'مداح انگلیسی',
            'variable' => '%%مداح_انگلیسی%%',
            'ai' => true,
            'seprator' => ' & ',
            'edit' => [
                'text' => [
                    'در صورتی که این پست بیش از یک مداح دارد، نام مداحان را با <code> & </code> از یکدیگر جدا کنید.'
                ]
            ],
            'required' => true
        ];
        
    }
    
    private function pushFields( ...$ffns ) {
        
        $fields = [];
        
        foreach( $ffns as $ffn ) {
            
            if( method_exists( $this, $ffn ) ) {
                
                $fields[ $ffn ] = $this->$ffn();
                
            }
            
        }
        
        return $fields;
        
    }
    
    public function __construct( $group = 'all' ) {
        
        switch( $group ) {
            
            case 'all':
            
                foreach( self::GROUPS as $group ) {
                    
                    $this->fields[ $group ] = (new self( $group ))->getFields();
                    
                }
            
            break;
            
            case 'song':
            
                $this->fields = $this->pushFields(
                    'singer_fa',
                    'song_fa',
                    'singer_en',
                    'song_en',
                    'cover_url',
                    'url_128',
                    'url_320',
                    'teaser_url',
                    'lyric'
                );
            
            break;
            
            case 'nohe':
            
                $this->fields = $this->pushFields(
                    'maddah_fa',
                    'nohe_fa',
                    'maddah_en',
                    'nohe_en',
                    'cover_url',
                    'url_128',
                    'url_320',
                    'teaser_url',
                    'lyric'
                );
            
            break;
            
            case 'remix':
            
                $this->fields = $this->pushFields(
                    'singer_fa',
                    'song_fa',
                    'singer_en',
                    'song_en',
                    'cover_url',
                    'url_128',
                    'url_320',
                    'teaser_url',
                    'lyric'
                );
            
            break;
            
            case 'trend':
            case 'trend-nohe':
            case 'trend-remix':
            
                $this->fields = $this->pushFields(
                    'trend_fa',
                    'trend_en',
                    'cover_url',
                    'url_128',
                    'url_320',
                    'teaser_url',
                    'lyric'
                );
            
            break;
            
        }
        
    }
    
    public function getField( $ffn ) {
        
        if( method_exists( $this, $ffn ) ) {
            
            return $this->$ffn();
            
        }
        
        return null;
        
    }
    
    public function getFields() {
        
        return $this->fields;
        
    }
    
    public static function getGroupName( $group ) {
        
        if( ! in_array( $group, self::GROUPS ) ) {
            
            return '';
            
        }
        
        switch( $group ) {
            
            case 'song':
            
                return 'تک آهنگ';
            
            break;
            
            case 'trend':
            
                return 'ترند تک آهنگ';
            
            break;
            
            case 'trend-nohe':
            
                return 'ترند مداحی';
            
            break;
            
            case 'trend-remix':
            
                return 'ترند ریمیکس';
            
            break;
            
            case 'nohe':
            
                return 'مداحی';
            
            break;
            
            case 'remix':
            
                return 'ریمیکس';
            
            break;
            
        }
        
    }
    
}
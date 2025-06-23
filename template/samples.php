<!DOCTYPE html>
<html dir="rtl" lang="fa-IR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>الگوها &gt; <?php echo $site[ 'name' ]; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tailwindcss-cdn@3.4.3/tailwindcss.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: ['class', '[data-theme="dark"]']
        }
    </script>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@33.003/Round-Dots/misc/Farsi-Digits/Vazirmatn-RD-FD-font-face.css" rel="stylesheet">
    <style type="text/tailwindcss">
        @layer base {
            html {
                font-family: "Vazirmatn RD FD", sans-serif;
            }
        }
    </style>
</head>
<?php

    $samples = [];
    
    if( $site[ 'samples' ] !== null ) {
        
        foreach( $site[ 'samples' ] as $sample ) {
            
            $samples[ $sample[ 'group' ] ][] = $sample;
            
        }
        
    }
    
?>
<body class="flex flex-row justify-center align-center">
    <div class="my-20 shadow-2xl w-3/4 rounded-2xl px-3 py-3 bg-base-100 border">
        <div class="navbar bg-primary text-primary-content rounded-2xl">
            <div class="navbar-start flex flex-col items-start">
                <h1 class="text-3xl font-bold">الگوها</h1>
                <div class="breadcrumbs text-sm">
                    <ul>
                        <li>
                            <a class="inline-flex items-center gap-2" href="sample.php">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
                                    <path fill-rule="evenodd" d="M7.487 2.89a.75.75 0 1 0-1.474-.28l-.455 2.388H3.61a.75.75 0 0 0 0 1.5h1.663l-.571 2.998H2.75a.75.75 0 0 0 0 1.5h1.666l-.403 2.114a.75.75 0 0 0 1.474.28l.456-2.394h2.973l-.403 2.114a.75.75 0 0 0 1.474.28l.456-2.394h1.947a.75.75 0 0 0 0-1.5h-1.661l.57-2.998h1.95a.75.75 0 0 0 0-1.5h-1.664l.402-2.108a.75.75 0 0 0-1.474-.28l-.455 2.388H7.085l.402-2.108ZM6.8 6.498l-.571 2.998h2.973l.57-2.998H6.8Z" clip-rule="evenodd" />
                                </svg>
                                سایت ها
                            </a>
                        </li>
                        <li>
                            <span class="inline-flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
                                    <path fill-rule="evenodd" d="M3.757 4.5c.18.217.376.42.586.608.153-.61.354-1.175.596-1.678A5.53 5.53 0 0 0 3.757 4.5ZM8 1a6.994 6.994 0 0 0-7 7 7 7 0 1 0 7-7Zm0 1.5c-.476 0-1.091.386-1.633 1.427-.293.564-.531 1.267-.683 2.063A5.48 5.48 0 0 0 8 6.5a5.48 5.48 0 0 0 2.316-.51c-.152-.796-.39-1.499-.683-2.063C9.09 2.886 8.476 2.5 8 2.5Zm3.657 2.608a8.823 8.823 0 0 0-.596-1.678c.444.298.842.659 1.182 1.07-.18.217-.376.42-.586.608Zm-1.166 2.436A6.983 6.983 0 0 1 8 8a6.983 6.983 0 0 1-2.49-.456 10.703 10.703 0 0 0 .202 2.6c.72.231 1.49.356 2.288.356.798 0 1.568-.125 2.29-.356a10.705 10.705 0 0 0 .2-2.6Zm1.433 1.85a12.652 12.652 0 0 0 .018-2.609c.405-.276.78-.594 1.117-.947a5.48 5.48 0 0 1 .44 2.262 7.536 7.536 0 0 1-1.575 1.293Zm-2.172 2.435a9.046 9.046 0 0 1-3.504 0c.039.084.078.166.12.244C6.907 13.114 7.523 13.5 8 13.5s1.091-.386 1.633-1.427c.04-.078.08-.16.12-.244Zm1.31.74a8.5 8.5 0 0 0 .492-1.298c.457-.197.893-.43 1.307-.696a5.526 5.526 0 0 1-1.8 1.995Zm-6.123 0a8.507 8.507 0 0 1-.493-1.298 8.985 8.985 0 0 1-1.307-.696 5.526 5.526 0 0 0 1.8 1.995ZM2.5 8.1c.463.5.993.935 1.575 1.293a12.652 12.652 0 0 1-.018-2.608 7.037 7.037 0 0 1-1.117-.947 5.48 5.48 0 0 0-.44 2.262Z" clip-rule="evenodd" />
                                </svg>
                                <?php echo $site[ 'name' ]; ?>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="navbar-end">
                <label class="swap swap-rotate">
                    <input type="checkbox" class="theme-controller" value="dark">
                    <svg class="swap-off h-10 w-10 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path d="M5.64,17l-.71.71a1,1,0,0,0,0,1.41,1,1,0,0,0,1.41,0l.71-.71A1,1,0,0,0,5.64,17ZM5,12a1,1,0,0,0-1-1H3a1,1,0,0,0,0,2H4A1,1,0,0,0,5,12Zm7-7a1,1,0,0,0,1-1V3a1,1,0,0,0-2,0V4A1,1,0,0,0,12,5ZM5.64,7.05a1,1,0,0,0,.7.29,1,1,0,0,0,.71-.29,1,1,0,0,0,0-1.41l-.71-.71A1,1,0,0,0,4.93,6.34Zm12,.29a1,1,0,0,0,.7-.29l.71-.71a1,1,0,1,0-1.41-1.41L17,5.64a1,1,0,0,0,0,1.41A1,1,0,0,0,17.66,7.34ZM21,11H20a1,1,0,0,0,0,2h1a1,1,0,0,0,0-2Zm-9,8a1,1,0,0,0-1,1v1a1,1,0,0,0,2,0V20A1,1,0,0,0,12,19ZM18.36,17A1,1,0,0,0,17,18.36l.71.71a1,1,0,0,0,1.41,0,1,1,0,0,0,0-1.41ZM12,6.5A5.5,5.5,0,1,0,17.5,12,5.51,5.51,0,0,0,12,6.5Zm0,9A3.5,3.5,0,1,1,15.5,12,3.5,3.5,0,0,1,12,15.5Z"/>
                    </svg>
                    <svg class="swap-on h-10 w-10 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path d="M21.64,13a1,1,0,0,0-1.05-.14,8.05,8.05,0,0,1-3.37.73A8.15,8.15,0,0,1,9.08,5.49a8.59,8.59,0,0,1,.25-2A1,1,0,0,0,8,2.36,10.14,10.14,0,1,0,22,14.05,1,1,0,0,0,21.64,13Zm-9.5,6.69A8.14,8.14,0,0,1,7.08,5.22v.27A10.15,10.15,0,0,0,17.22,15.63a9.79,9.79,0,0,0,2.1-.22A8.11,8.11,0,0,1,12.14,19.73Z"/>
                    </svg>
                </label>
            </div>
        </div>
        <div class="flex flex-col space-y-4 mt-4">
            <?php foreach( $fields::GROUPS as $name ) : ?>
                <div class="collapse collapse-arrow border">
                    <input type="checkbox" checked>
                    <div class="collapse-title text-xl font-bold">گروه <?php echo $fields->getGroupName( $name ); ?></div>
                    <div class="collapse-content">
                        <a class="btn btn-accent btn-block" href="sample.php?site_id=<?php echo $site[ '_id' ]; ?>&sample_id=new-<?php echo $name; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                                <path fill-rule="evenodd" d="M12 3.75a.75.75 0 0 1 .75.75v6.75h6.75a.75.75 0 0 1 0 1.5h-6.75v6.75a.75.75 0 0 1-1.5 0v-6.75H4.5a.75.75 0 0 1 0-1.5h6.75V4.5a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd" />
                            </svg>
                            ساخت الگوی جدید
                        </a>
                        <?php if( isset( $samples[ $name ] ) ) : ?>
                            <div class="grid gap-3 md:grid-cols-2 mt-4">
                                <?php foreach( $samples[ $name ] as $sample ) : ?>
                                    <div class="w-full grid grid-cols-2 bg-primary text-primary-content rounded-lg px-3 py-3 items-center">
                                        <div class="font-bold justify-self-start"><?php echo $sample[ 'name' ]; ?></div>
                                        <div class="justify-self-end">
                                            <div class="tooltip" data-tip="کپی کردن الگو">
                                                <a class="btn btn-sm btn-square" href="sample.php?site_id=<?php echo $site[ '_id' ]; ?>&sample_id=<?php echo $sample[ '_id' ]; ?>&action=copy">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
                                                        <path d="M7 3.5A1.5 1.5 0 0 1 8.5 2h3.879a1.5 1.5 0 0 1 1.06.44l3.122 3.12A1.5 1.5 0 0 1 17 6.622V12.5a1.5 1.5 0 0 1-1.5 1.5h-1v-3.379a3 3 0 0 0-.879-2.121L10.5 5.379A3 3 0 0 0 8.379 4.5H7v-1Z" />
                                                        <path d="M4.5 6A1.5 1.5 0 0 0 3 7.5v9A1.5 1.5 0 0 0 4.5 18h7a1.5 1.5 0 0 0 1.5-1.5v-5.879a1.5 1.5 0 0 0-.44-1.06L9.44 6.439A1.5 1.5 0 0 0 8.378 6H4.5Z" />
                                                    </svg>
                                                </a>
                                            </div>
                                            <div class="tooltip" data-tip="ویرایش الگو">
                                                <a class="btn btn-sm btn-square" href="sample.php?site_id=<?php echo $site[ '_id' ]; ?>&sample_id=<?php echo $sample[ '_id' ]; ?>">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
                                                        <path d="m5.433 13.917 1.262-3.155A4 4 0 0 1 7.58 9.42l6.92-6.918a2.121 2.121 0 0 1 3 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 0 1-.65-.65Z" />
                                                        <path d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0 0 10 3H4.75A2.75 2.75 0 0 0 2 5.75v9.5A2.75 2.75 0 0 0 4.75 18h9.5A2.75 2.75 0 0 0 17 15.25V10a.75.75 0 0 0-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5Z" />
                                                    </svg>
                                                </a>
                                            </div>
                                            <div class="tooltip" data-tip="حذف الگو">
                                                <a class="btn btn-sm btn-square" href="sample.php?site_id=<?php echo $site[ '_id' ]; ?>&sample_id=<?php echo $sample[ '_id' ]; ?>&action=delete" onclick="return confirm('آیا مطمئن هستید؟')">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
                                                        <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script>
        (function() {
            
            let theme = localStorage.getItem('theme');
            
            if( theme ) {
                
                if( theme == 'dark' ) {
                    
                    document.documentElement.setAttribute( 'data-theme', 'dark' );
                    document.querySelector('.theme-controller').checked = true;
                
                } else {
                    
                    document.documentElement.setAttribute( 'data-theme', 'light' );
                    document.querySelector('.theme-controller').checked = false;
                
                }
            
            } else {
                
                if( window.matchMedia && window.matchMedia( '(prefers-color-scheme: dark)' ).matches ) {

                    document.documentElement.setAttribute( 'data-theme', 'dark' );
                    document.querySelector('.theme-controller').checked = true;

                } else {
                    
                    document.documentElement.setAttribute( 'data-theme', 'light' );
                    document.querySelector('.theme-controller').checked = false;
                
                }
                
            }
            
            document.querySelector('.theme-controller').addEventListener('change', function( e ) {
                
                if( e.target.checked ) {
                    
                    document.documentElement.setAttribute( 'data-theme', 'dark' );
                    localStorage.setItem('theme', 'dark');
                
                } else {
                    
                    document.documentElement.setAttribute( 'data-theme', 'light' );
                    localStorage.setItem('theme', 'light');
                
                }
            
            });
            
        })();
    </script>
</body>
</html>
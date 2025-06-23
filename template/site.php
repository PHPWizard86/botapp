<!DOCTYPE html>
<html dir="rtl" lang="fa-IR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>الگو ساز</title>
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
<body class="flex flex-row justify-center align-center">
    <div class="my-20 shadow-2xl w-3/4 rounded-2xl px-3 py-3 bg-base-100 border">
        <div class="navbar bg-primary text-primary-content rounded-2xl">
            <div class="navbar-start flex flex-col items-start">
                <h1 class="text-3xl font-bold">الگو ساز</h1>
                <div class="breadcrumbs text-sm">
                    <ul>
                        <li>
                            <span class="inline-flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
                                    <path fill-rule="evenodd" d="M7.487 2.89a.75.75 0 1 0-1.474-.28l-.455 2.388H3.61a.75.75 0 0 0 0 1.5h1.663l-.571 2.998H2.75a.75.75 0 0 0 0 1.5h1.666l-.403 2.114a.75.75 0 0 0 1.474.28l.456-2.394h2.973l-.403 2.114a.75.75 0 0 0 1.474.28l.456-2.394h1.947a.75.75 0 0 0 0-1.5h-1.661l.57-2.998h1.95a.75.75 0 0 0 0-1.5h-1.664l.402-2.108a.75.75 0 0 0-1.474-.28l-.455 2.388H7.085l.402-2.108ZM6.8 6.498l-.571 2.998h2.973l.57-2.998H6.8Z" clip-rule="evenodd" />
                                </svg>
                                سایت ها
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
        <?php if( $sites === null ) : ?>
            <div role="alert" class="alert alert-info mt-4">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="size-6 shrink-0 stroke-current">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="font-bold">در حال حاضر سایتی به ربات متصل نیست!</span>
            </div>
        <?php endif; ?>
        <?php if( $sites ) : ?>
            <div class="grid gap-3 md:grid-cols-2 mt-4">
            <?php foreach( $sites as $site ) : ?>
                <div class="w-full grid grid-cols-2 bg-accent text-accent-content rounded-lg px-3 py-3 items-center">
                    <div class="font-bold justify-self-start"><?php echo $site[ 'name' ]; ?></div>
                    <a class="btn btn-sm justify-self-end" href="sample.php?site_id=<?php echo $site[ '_id' ]; ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
                            <path d="M10 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" />
                            <path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 0 1 0-1.186A10.004 10.004 0 0 1 10 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0 1 10 17c-4.257 0-7.893-2.66-9.336-6.41ZM14 10a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" clip-rule="evenodd" />
                        </svg>
                        مشاهده الگوها
                    </a>
                </div>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
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
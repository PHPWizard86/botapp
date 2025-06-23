<?php
    $title = isset( $sample[ 'name' ] ) ? "{$sample[ 'name' ]} &gt; {$site[ 'name' ]} &gt; {$group_name}" : "الگوی جدید &gt; {$site[ 'name' ]} &gt; {$group_name}";
    $h1 = isset( $sample[ 'name' ] ) ? $sample[ 'name' ] : "الگوی جدید";
?>
<!DOCTYPE html>
<html dir="rtl" lang="fa-IR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $title; ?></title>
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
    <script src="https://cdn.jsdelivr.net/npm/tinymce@7.2.1/tinymce.min.js"></script>
</head>
<body class="flex max-md:flex-col md:space-x-4 md:space-x-reverse px-4">
    <div class="md:my-10 shadow-2xl md:flex-1 max-md:w-full rounded-2xl px-3 py-3 bg-base-100 border max-md:mb-10">
        <div class="navbar bg-primary text-primary-content rounded-2xl">
            <div class="navbar-start flex flex-col items-start">
                <h1 class="text-3xl font-bold"><?php echo $h1; ?></h1>
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
                            <a class="inline-flex items-center gap-2" href="sample.php?site_id=<?php echo $site[ '_id' ]; ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
                                    <path fill-rule="evenodd" d="M3.757 4.5c.18.217.376.42.586.608.153-.61.354-1.175.596-1.678A5.53 5.53 0 0 0 3.757 4.5ZM8 1a6.994 6.994 0 0 0-7 7 7 7 0 1 0 7-7Zm0 1.5c-.476 0-1.091.386-1.633 1.427-.293.564-.531 1.267-.683 2.063A5.48 5.48 0 0 0 8 6.5a5.48 5.48 0 0 0 2.316-.51c-.152-.796-.39-1.499-.683-2.063C9.09 2.886 8.476 2.5 8 2.5Zm3.657 2.608a8.823 8.823 0 0 0-.596-1.678c.444.298.842.659 1.182 1.07-.18.217-.376.42-.586.608Zm-1.166 2.436A6.983 6.983 0 0 1 8 8a6.983 6.983 0 0 1-2.49-.456 10.703 10.703 0 0 0 .202 2.6c.72.231 1.49.356 2.288.356.798 0 1.568-.125 2.29-.356a10.705 10.705 0 0 0 .2-2.6Zm1.433 1.85a12.652 12.652 0 0 0 .018-2.609c.405-.276.78-.594 1.117-.947a5.48 5.48 0 0 1 .44 2.262 7.536 7.536 0 0 1-1.575 1.293Zm-2.172 2.435a9.046 9.046 0 0 1-3.504 0c.039.084.078.166.12.244C6.907 13.114 7.523 13.5 8 13.5s1.091-.386 1.633-1.427c.04-.078.08-.16.12-.244Zm1.31.74a8.5 8.5 0 0 0 .492-1.298c.457-.197.893-.43 1.307-.696a5.526 5.526 0 0 1-1.8 1.995Zm-6.123 0a8.507 8.507 0 0 1-.493-1.298 8.985 8.985 0 0 1-1.307-.696 5.526 5.526 0 0 0 1.8 1.995ZM2.5 8.1c.463.5.993.935 1.575 1.293a12.652 12.652 0 0 1-.018-2.608 7.037 7.037 0 0 1-1.117-.947 5.48 5.48 0 0 0-.44 2.262Z" clip-rule="evenodd" />
                                </svg>
                                <?php echo $site[ 'name' ]; ?>
                            </a>
                        </li>
                        <li>
                            <span class="inline-flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
                                    <path d="M9 3.889c0-.273.188-.502.417-.65.355-.229.583-.587.583-.989C10 1.56 9.328 1 8.5 1S7 1.56 7 2.25c0 .41.237.774.603 1.002.22.137.397.355.397.613 0 .331-.275.596-.605.579-.744-.04-1.482-.1-2.214-.18a.75.75 0 0 0-.83.81c.067.764.111 1.535.133 2.312A.6.6 0 0 1 3.882 8c-.268 0-.495-.185-.64-.412C3.015 7.231 2.655 7 2.25 7 1.56 7 1 7.672 1 8.5S1.56 10 2.25 10c.404 0 .764-.23.993-.588.144-.227.37-.412.64-.412a.6.6 0 0 1 .601.614 39.338 39.338 0 0 1-.231 3.3.75.75 0 0 0 .661.829c.826.093 1.66.161 2.5.204A.56.56 0 0 0 8 13.386c0-.271-.187-.499-.415-.645C7.23 12.512 7 12.153 7 11.75c0-.69.672-1.25 1.5-1.25s1.5.56 1.5 1.25c0 .403-.23.762-.585.99-.228.147-.415.375-.415.646v.11c0 .278.223.504.5.504 1.196 0 2.381-.052 3.552-.154a.75.75 0 0 0 .68-.661c.135-1.177.22-2.37.253-3.574a.597.597 0 0 0-.6-.611c-.27 0-.498.187-.644.415-.229.356-.588.585-.991.585-.69 0-1.25-.672-1.25-1.5S11.06 7 11.75 7c.403 0 .762.23.99.585.147.228.375.415.646.415a.597.597 0 0 0 .599-.61 40.914 40.914 0 0 0-.132-2.365.75.75 0 0 0-.815-.684A39.51 39.51 0 0 1 9.5 4.5a.501.501 0 0 1-.5-.503v-.108Z" />
                                </svg>
                                <?php echo $group_name; ?>
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
        <form class="flex flex-col space-y-4 mt-4" method="post">
            <label class="form-control w-full">
                <div class="label">
                    <span class="label-text text-lg">نام الگو</span>
                </div>
                <input type="text" oninput="titleSetter(event)" name="name" value="<?php echo $sample[ 'name' ] ?? ''; ?>" autocomplete="off" class="input input-bordered input-primary w-full" required>
            </label>
            <label class="form-control w-full">
                <div class="label">
                    <span class="label-text text-lg">الگوی عنوان پست</span>
                </div>
                <input type="text" name="title" value="<?php echo $sample[ 'title' ] ?? ''; ?>" autocomplete="off" class="input input-bordered input-primary w-full" required>
            </label>
            <label class="form-control w-full">
                <div class="label">
                    <span class="label-text text-lg">الگوی نامک پست</span>
                </div>
                <input type="text" name="slug" value="<?php echo $sample[ 'slug' ] ?? ''; ?>" autocomplete="off" class="input input-bordered input-primary w-full">
            </label>
            <label id="content-tiny" class="form-control">
                <div class="label">
                    <span class="label-text text-lg">الگوی محتوای پست</span>
                </div>
                <textarea id="content" name="content"><?php echo $sample[ 'content' ] ?? ''; ?></textarea>
            </label>
            <?php if( !empty( $site[ 'taxonomies' ] ) ) : ?>
            <div class="form-control overflow-x-auto">
                <div class="label">
                    <span class="label-text text-lg">الگوی طبقه بندی ها</span>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>نام</th>
                            <th>ساختار سلسله مراتبی</th>
                            <th>مقدار</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach( $site[ 'taxonomies' ] as $key => $object ) : ?>
                            <tr>
                                <td><?php echo $object[ 'label' ]; ?></td>
                                <td>
                                    <?php if( $object[ 'hierarchical' ] ) : ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="text-success size-6">
                                            <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" />
                                        </svg>
                                    <?php else : ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="text-error size-6">
                                            <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.72 6.97a.75.75 0 1 0-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 1 0 1.06 1.06L12 13.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L13.06 12l1.72-1.72a.75.75 0 1 0-1.06-1.06L12 10.94l-1.72-1.72Z" clip-rule="evenodd" />
                                        </svg>
                                    <?php endif; ?>
                                </td>
                                <td><input type="text"<?php if( $object[ 'hierarchical' ] === false ) echo ' oninput="atIllegal(event)"' ?> name="tax[<?php echo $key; ?>]" value="<?php echo $sample[ 'tax' ][ $key ] ?? ''; ?>" autocomplete="off" class="input input-bordered input-primary w-full"></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="label select-text">
                    <span class="label-text">* طبقه بندی ها را با کاراکتر <kbd class="kbd kbd-sm">|</kbd> از یکدیگر جدا کنید.</span>
                </div>
                <div class="label select-text">
                    <span class="label-text">* برای استفاده از ساختار سلسله مراتبی از الگوی <kbd class="kbd kbd-sm">مادر</kbd> <kbd class="kbd kbd-sm">@</kbd> <kbd class="kbd kbd-sm">فرزند</kbd> استفاده کنید.</span>
                </div>
            </div>
            <?php endif; ?>
            <div class="form-control overflow-x-auto">
                <div class="label">
                    <span class="label-text text-lg">الگوی زمینه های دلخواه</span>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>
                                <div class="tooltip" data-tip="افزودن">
                                    <button onClick="cfAddRow(event)" class="btn btn-success btn-circle" type="button">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                                            <path fill-rule="evenodd" d="M12 3.75a.75.75 0 0 1 .75.75v6.75h6.75a.75.75 0 0 1 0 1.5h-6.75v6.75a.75.75 0 0 1-1.5 0v-6.75H4.5a.75.75 0 0 1 0-1.5h6.75V4.5a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </th>
                            <th>کلید</th>
                            <th>مقدار</th>
                        </tr>
                    </thead>
                    <tbody id="cf-body">
                        <?php if( !empty( $sample[ 'cf' ] ) ) : foreach( $sample[ 'cf' ] as $key => $value ) : ?>
                            <tr>
                                <td>
                                    <div class="tooltip" data-tip="حذف">
                                        <button onClick="cfRemoveRow(event)" class="btn btn-error btn-circle" type="button">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                                                <path fill-rule="evenodd" d="M4.25 12a.75.75 0 0 1 .75-.75h14a.75.75 0 0 1 0 1.5H5a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                                <td><input type="text" value="<?php echo $key; ?>" pattern="[a-z0-9\-_]+" minlength="1" maxlength="32" oninput="cfSetValueKey(event)" dir="ltr" autocomplete="off" class="input input-bordered input-primary w-full" required></td>
                                <td><input type="text" name="<?php echo 'cf['.$key.']'; ?>" value="<?php echo $value; ?>" autocomplete="off" class="cf-value input input-bordered input-primary w-full"></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="flex justify-center mt-10">
                <button class="btn btn-lg btn-block btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                        <path fill-rule="evenodd" d="M12 2.25a.75.75 0 0 1 .75.75v11.69l3.22-3.22a.75.75 0 1 1 1.06 1.06l-4.5 4.5a.75.75 0 0 1-1.06 0l-4.5-4.5a.75.75 0 1 1 1.06-1.06l3.22 3.22V3a.75.75 0 0 1 .75-.75Zm-9 13.5a.75.75 0 0 1 .75.75v2.25a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5V16.5a.75.75 0 0 1 1.5 0v2.25a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V16.5a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd" />
                    </svg>
                    ذخیره الگو
                </button>
            </div>
        </form>
    </div>
    <div class="md:my-10 max-md:mt-10 max-md:mb-4 max-md:order-first max-md:w-full shadow-2xl rounded-2xl px-3 py-3 bg-base-100 border self-start md:sticky md:top-1">
        <div class="navbar bg-primary text-primary-content rounded-2xl">
            <div class="navbar-start">
                <h2 class="text-2xl font-bold">متغیرها</h2>
            </div>
            <div class="navbar-end">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-10">
                    <path fill-rule="evenodd" d="M19.253 2.292a.75.75 0 0 1 .955.461A28.123 28.123 0 0 1 21.75 12c0 3.266-.547 6.388-1.542 9.247a.75.75 0 1 1-1.416-.494c.94-2.7 1.458-5.654 1.458-8.753s-.519-6.054-1.458-8.754a.75.75 0 0 1 .461-.954Zm-14.227.013a.75.75 0 0 1 .414.976A23.183 23.183 0 0 0 3.75 12c0 3.085.6 6.027 1.69 8.718a.75.75 0 0 1-1.39.563c-1.161-2.867-1.8-6-1.8-9.281 0-3.28.639-6.414 1.8-9.281a.75.75 0 0 1 .976-.414Zm4.275 5.052a1.5 1.5 0 0 1 2.21.803l.716 2.148L13.6 8.246a2.438 2.438 0 0 1 2.978-.892l.213.09a.75.75 0 1 1-.584 1.381l-.214-.09a.937.937 0 0 0-1.145.343l-2.021 3.033 1.084 3.255 1.445-.89a.75.75 0 1 1 .786 1.278l-1.444.889a1.5 1.5 0 0 1-2.21-.803l-.716-2.148-1.374 2.062a2.437 2.437 0 0 1-2.978.892l-.213-.09a.75.75 0 0 1 .584-1.381l.214.09a.938.938 0 0 0 1.145-.344l2.021-3.032-1.084-3.255-1.445.89a.75.75 0 1 1-.786-1.278l1.444-.89Z" clip-rule="evenodd" />
                </svg>
            </div>
        </div>
        <div id="var-wrap" class="grid lg:grid-cols-2 max-md:grid-cols-2 gap-2 my-3">
            <?php $odd = ( count( $fields ) % 2 != 0 ); foreach( $fields as $key => $field ) : ?>
                <div class="tooltip<?php if( $key === array_key_last( $fields ) && $odd ) echo " lg:col-span-2 max-md:col-span-2" ?>" data-tip="کپی کن">
                    <button class="btn btn-block" data-var="<?php echo $field[ 'variable' ]; ?>"><?php echo $field[ 'name' ]; ?></button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div id="toast" class="toast toast-center"></div>
    <script>
        (function() {
            
            tinymce.addI18n("fa",{"#":"#","Accessibility":"\u062f\u0633\u062a\u0631\u0633\u06cc","Accordion":"\u0622\u06a9\u0627\u0631\u062f\u0626\u0648\u0646","Accordion body...":"\u0628\u062f\u0646\u0647 \u0622\u06a9\u0627\u0631\u062f\u0626\u0648\u0646...","Accordion summary...":"\u062e\u0644\u0627\u0635\u0647 \u0627\u06cc \u0627\u0632 \u0622\u06a9\u0627\u0631\u062f\u0626\u0648\u0646...","Action":"\u0627\u0642\u062f\u0627\u0645","Activity":"\u0641\u0639\u0627\u0644\u06cc\u062a","Address":"\u0622\u062f\u0631\u0633","Advanced":"\u067e\u06cc\u0634\u0631\u0641\u062a\u0647","Align":"\u062a\u0631\u0627\u0632\u0628\u0646\u062f\u06cc","Align center":"\u062a\u0631\u0627\u0632\u0628\u0646\u062f\u06cc \u0627\u0632 \u0648\u0633\u0637","Align left":"\u062a\u0631\u0627\u0632\u0628\u0646\u062f\u06cc \u0627\u0632 \u0686\u067e","Align right":"\u062a\u0631\u0627\u0632\u0628\u0646\u062f\u06cc \u0627\u0632 \u0631\u0627\u0633\u062a","Alignment":"\u062a\u0631\u0627\u0632\u0628\u0646\u062f\u06cc","Alignment {0}":"\u062a\u0631\u0627\u0632 {0}","All":"\u0647\u0645\u0647","Alternative description":"\u062a\u0648\u0636\u06cc\u062d\u0627\u062a \u062c\u0627\u06cc\u06af\u0632\u06cc\u0646","Alternative source":"\u0645\u0646\u0628\u0639 \u062c\u0627\u06cc\u06af\u0632\u06cc\u0646","Alternative source URL":"\u0646\u0634\u0627\u0646\u06cc \u0648\u0628 \u0645\u0646\u0628\u0639 \u062c\u0627\u06cc\u06af\u0632\u06cc\u0646","Anchor":"\u0642\u0644\u0627\u0628","Anchor...":"\u0642\u0644\u0627\u0628...","Anchors":"\u0642\u0644\u0627\u0628\u200c\u0647\u0627","Animals and Nature":"\u062d\u06cc\u0648\u0627\u0646\u0627\u062a \u0648 \u0637\u0628\u06cc\u0639\u062a","Arrows":"\u067e\u06cc\u06a9\u0627\u0646\u200c\u0647\u0627","B":"\u0622\u0628\u06cc","Background color":"\u0631\u0646\u06af \u067e\u0633\u200c\u0632\u0645\u06cc\u0646\u0647","Background color {0}":"\u0631\u0646\u06af \u067e\u0633\u200c\u0632\u0645\u06cc\u0646\u0647 {0}","Black":"\u0633\u06cc\u0627\u0647","Block":"\u0628\u0644\u0648\u06a9","Block {0}":"{0} \u0631\u0627 \u0645\u0633\u062f\u0648\u062f \u06a9\u0646","Blockquote":"\u0646\u0642\u0644 \u0642\u0648\u0644 \u0628\u0644\u0648\u06a9\u06cc","Blocks":"\u0628\u0644\u0648\u06a9\u200c\u0647\u0627","Blue":"\u0622\u0628\u06cc","Blue component":"\u062c\u0632\u0621 \u0622\u0628\u06cc","Body":"\u0628\u062f\u0646\u0647","Bold":"\u067e\u0631\u0631\u0646\u06af","Border":"\u062d\u0627\u0634\u06cc\u0647","Border color":"\u0631\u0646\u06af \u062d\u0627\u0634\u06cc\u0647","Border style":"\u0633\u0628\u06a9 \u062d\u0627\u0634\u06cc\u0647","Border width":"\u0639\u0631\u0636 \u062d\u0627\u0634\u06cc\u0647","Bottom":"\u067e\u0627\u06cc\u06cc\u0646","Browse files":"\u0627\u0646\u062a\u062e\u0627\u0628 \u0641\u0627\u06cc\u0644","Browse for an image":"\u0627\u0646\u062a\u062e\u0627\u0628 \u062a\u0635\u0648\u06cc\u0631...","Browse links":"\u0627\u0646\u062a\u062e\u0627\u0628 \u0644\u06cc\u0646\u06a9 \u0647\u0627","Bullet list":"\u0641\u0647\u0631\u0633\u062a \u0646\u0634\u0627\u0646\u0647\u200c\u062f\u0627\u0631","Cancel":"\u0644\u063a\u0648","Caption":"\u0639\u0646\u0648\u0627\u0646","Cell":"\u0633\u0644\u0648\u0644","Cell padding":"\u062d\u0627\u0634\u06cc\u0647 \u0628\u06cc\u0646 \u0633\u0644\u0648\u0644\u200c\u0647\u0627","Cell properties":"\u062a\u0646\u0638\u06cc\u0645\u0627\u062a \u0633\u0644\u0648\u0644","Cell spacing":"\u0641\u0627\u0635\u0644\u0647 \u0628\u06cc\u0646 \u0633\u0644\u0648\u0644\u200c\u0647\u0627","Cell styles":"\u0633\u0628\u06a9\u200c\u0647\u0627\u06cc \u062e\u0627\u0646\u0647 \u062c\u062f\u0648\u0644","Cell type":"\u0646\u0648\u0639 \u0633\u0644\u0648\u0644","Center":"\u0645\u0631\u06a9\u0632","Characters":"\u0646\u0648\u06cc\u0633\u0647\u200c\u0647\u0627","Characters (no spaces)":"\u0646\u0648\u06cc\u0633\u0647 \u0647\u0627 (\u0628\u062f\u0648\u0646 \u0641\u0627\u0635\u0644\u0647)","Circle":"\u062f\u0627\u06cc\u0631\u0647","Class":"\u062f\u0633\u062a\u0647","Clear formatting":"\u067e\u0627\u06a9 \u06a9\u0631\u062f\u0646 \u0642\u0627\u0644\u0628\u200c\u0628\u0646\u062f\u06cc","Close":"\u0628\u0633\u062a\u0646","Code":"\u06a9\u062f","Code sample...":"\u0646\u0645\u0648\u0646\u0647 \u06a9\u062f...","Code view":"\u0646\u0645\u0627\u06cc \u06a9\u062f","Color Picker":"\u0627\u0646\u062a\u062e\u0627\u0628\u200c\u06a9\u0646\u0646\u062f\u0647 \u0631\u0646\u06af","Color swatch":"\u0646\u0645\u0648\u0646\u0647 \u0631\u0646\u06af","Cols":"\u0633\u062a\u0648\u0646\u200c\u0647\u0627","Column":"\u0633\u062a\u0648\u0646","Column clipboard actions":"\u0639\u0645\u0644\u06cc\u0627\u062a \u062d\u0627\u0641\u0638\u0647 \u0645\u0648\u0642\u062a \u0633\u062a\u0648\u0646\u200c\u0647\u0627","Column group":"\u06af\u0631\u0648\u0647 \u0633\u062a\u0648\u0646\u06cc","Column header":"\u0633\u062a\u0648\u0646 \u062a\u06cc\u062a\u0631","Constrain proportions":"\u0645\u062d\u062f\u0648\u062f \u06a9\u0631\u062f\u0646 \u0645\u0634\u062e\u0635\u0627\u062a","Copy":"\u06a9\u067e\u06cc","Copy column":"\u06a9\u067e\u06cc \u0633\u062a\u0648\u0646","Copy row":"\u06a9\u067e\u06cc \u0633\u0637\u0631","Could not find the specified string.":"\u0631\u0634\u062a\u0647 \u0645\u0648\u0631\u062f \u0646\u0638\u0631 \u06cc\u0627\u0641\u062a \u0646\u0634\u062f.","Could not load emojis":"\u0627\u0645\u06a9\u0627\u0646 \u0628\u0627\u0631\u06af\u06cc\u0631\u06cc \u0627\u06cc\u0645\u0648\u062c\u06cc\u200c\u0647\u0627 \u0648\u062c\u0648\u062f \u0646\u062f\u0627\u0631\u062f","Count":"\u0634\u0645\u0627\u0631\u0634","Currency":"\u0627\u0631\u0632","Current window":"\u067e\u0646\u062c\u0631\u0647 \u062c\u0627\u0631\u06cc","Custom color":"\u0631\u0646\u06af \u0633\u0641\u0627\u0631\u0634\u06cc","Custom...":"\u0633\u0641\u0627\u0631\u0634\u06cc...","Cut":"\u0628\u0631\u0634","Cut column":"\u0628\u0631\u0634 \u0633\u062a\u0648\u0646","Cut row":"\u0628\u0631\u0634 \u0633\u0637\u0631","Dark Blue":"\u0622\u0628\u06cc \u062a\u06cc\u0631\u0647","Dark Gray":"\u062e\u0627\u06a9\u0633\u062a\u0631\u06cc \u062a\u06cc\u0631\u0647","Dark Green":"\u0633\u0628\u0632 \u062a\u06cc\u0631\u0647","Dark Orange":"\u0646\u0627\u0631\u0646\u062c\u06cc \u062a\u06cc\u0631\u0647","Dark Purple":"\u0628\u0646\u0641\u0634 \u062a\u06cc\u0631\u0647","Dark Red":"\u0642\u0631\u0645\u0632 \u062a\u06cc\u0631\u0647","Dark Turquoise":"\u0641\u06cc\u0631\u0648\u0632\u0647\u200c\u0627\u06cc \u062a\u06cc\u0631\u0647","Dark Yellow":"\u0632\u0631\u062f \u062a\u06cc\u0631\u0647","Dashed":"\u0641\u0627\u0635\u0644\u0647 \u0641\u0627\u0635\u0644\u0647","Date/time":"\u062a\u0627\u0631\u06cc\u062e/\u0632\u0645\u0627\u0646","Decrease indent":"\u06a9\u0627\u0647\u0634 \u062a\u0648\u0631\u0641\u062a\u06af\u06cc","Default":"\u067e\u06cc\u0634\u200c\u0641\u0631\u0636","Delete accordion":"\u062d\u0630\u0641 \u0622\u06a9\u0627\u0631\u062f\u0626\u0648\u0646","Delete column":"\u062d\u0630\u0641 \u0633\u062a\u0648\u0646","Delete row":"\u062d\u0630\u0641 \u0633\u0637\u0631","Delete table":"\u062d\u0630\u0641 \u062c\u062f\u0648\u0644","Dimensions":"\u0627\u0628\u0639\u0627\u062f","Disc":"\u062f\u06cc\u0633\u06a9","Div":"\u0628\u062e\u0634","Document":"\u0633\u0646\u062f","Dotted":"\u0646\u0642\u0637\u0647 \u0646\u0642\u0637\u0647","Double":"\u062f\u0648 \u062e\u0637\u06cc","Drop an image here":"\u062a\u0635\u0648\u06cc\u0631 \u0645\u0648\u0631\u062f \u0646\u0638\u0631 \u0631\u0627 \u0627\u06cc\u0646\u062c\u0627 \u0631\u0647\u0627 \u06a9\u0646\u06cc\u062f","Dropped file type is not supported":"\u0641\u0631\u0645\u062a \u0641\u0627\u06cc\u0644 \u062d\u0630\u0641 \u0634\u062f\u0647 \u067e\u0634\u062a\u06cc\u0628\u0627\u0646\u06cc \u0646\u0645\u06cc\u200c\u0634\u0648\u062f","Edit":"\u0648\u06cc\u0631\u0627\u06cc\u0634","Embed":"\u062c\u0627\u0633\u0627\u0632\u06cc","Emojis":"\u0627\u0633\u062a\u06cc\u06a9\u0631\u0647\u0627","Emojis...":"\u0627\u0633\u062a\u06cc\u06a9\u0631\u0647\u0627...","Error":"\u062e\u0637\u0627","Error: Form submit field collision.":"\u062e\u0637\u0627: \u062a\u062f\u0627\u062e\u0644 \u062f\u0631 \u062b\u0628\u062a \u0641\u0631\u0645.","Error: No form element found.":"\u062e\u0637\u0627: \u0647\u06cc\u0686 \u0627\u0644\u0645\u0627\u0646 \u0641\u0631\u0645\u06cc \u06cc\u0627\u0641\u062a \u0646\u0634\u062f.","Extended Latin":"\u0644\u0627\u062a\u06cc\u0646 \u06af\u0633\u062a\u0631\u062f\u0647","Failed to initialize plugin: {0}":"\u0639\u062f\u0645 \u0645\u0648\u0641\u0642\u06cc\u062a \u062f\u0631 \u0631\u0627\u0647\u200c\u0627\u0646\u062f\u0627\u0632\u06cc \u0627\u0641\u0632\u0648\u0646\u0647: {0}","Failed to load plugin url: {0}":"\u0639\u062f\u0645 \u0645\u0648\u0641\u0642\u06cc\u062a \u062f\u0631 \u0628\u0627\u0631\u06af\u0630\u0627\u0631\u06cc \u0646\u0634\u0627\u0646\u06cc \u0648\u0628 \u0627\u0641\u0632\u0648\u0646\u0647: {0}","Failed to load plugin: {0} from url {1}":"\u0639\u062f\u0645 \u0645\u0648\u0641\u0642\u06cc\u062a \u062f\u0631 \u0628\u0627\u0631\u06af\u0630\u0627\u0631\u06cc \u0627\u0641\u0632\u0648\u0646\u0647: {0} \u0627\u0632 \u0646\u0634\u0627\u0646\u06cc \u0648\u0628 {1}","Failed to upload image: {0}":"\u0639\u062f\u0645 \u0645\u0648\u0641\u0642\u06cc\u062a \u062f\u0631 \u0628\u0627\u0631\u06af\u0630\u0627\u0631\u06cc \u062a\u0635\u0648\u06cc\u0631: {0}","File":"\u067e\u0631\u0648\u0646\u062f\u0647","Find":"\u06cc\u0627\u0641\u062a\u0646","Find (if searchreplace plugin activated)":"\u06cc\u0627\u0641\u062a\u0646 (\u062f\u0631 \u0635\u0648\u0631\u062a \u0641\u0639\u0627\u0644 \u0628\u0648\u062f\u0646 \u0627\u0641\u0632\u0648\u0646\u0647\u0654 \u062c\u0633\u062a\u062c\u0648/\u062c\u0627\u06cc\u06af\u0632\u06cc\u0646\u06cc)","Find and Replace":"\u062c\u0633\u062a\u200c\u0648\u200c\u062c\u0648 \u0648 \u062c\u0627\u06cc\u06af\u0632\u06cc\u0646 \u06a9\u0631\u062f\u0646","Find and replace...":"\u06cc\u0627\u0641\u062a\u0646 \u0648 \u062c\u0627\u06cc\u06af\u0632\u06cc\u0646 \u06a9\u0631\u062f\u0646...","Find in selection":"\u062f\u0631 \u06af\u0644\u0686\u06cc\u0646 \u0628\u06cc\u0627\u0628\u06cc\u062f","Find whole words only":"\u06cc\u0627\u0641\u062a\u0646 \u062f\u0642\u06cc\u0642\u0627\u064b \u06a9\u0644 \u0648\u0627\u0698\u0647","Flags":"\u067e\u0631\u0686\u0645\u200c\u0647\u0627","Focus to contextual toolbar":"\u062a\u0645\u0631\u06a9\u0632 \u0628\u0631 \u0646\u0648\u0627\u0631 \u0627\u0628\u0632\u0627\u0631 \u0628\u0627\u0641\u062a\u0627\u0631\u06cc","Focus to element path":"\u062a\u0645\u0631\u06a9\u0632 \u0628\u0631 \u0645\u0633\u06cc\u0631 \u0627\u0644\u0645\u0627\u0646","Focus to menubar":"\u062a\u0645\u0631\u06a9\u0632 \u0628\u0631 \u0646\u0648\u0627\u0631 \u0645\u0646\u0648","Focus to toolbar":"\u062a\u0645\u0631\u06a9\u0632 \u0628\u0631 \u0646\u0648\u0627\u0631 \u0627\u0628\u0632\u0627\u0631","Font":"\u0641\u0648\u0646\u062a","Font size {0}":"\u0627\u0646\u062f\u0627\u0632\u0647 \u0641\u0648\u0646\u062a {0}","Font sizes":"\u0633\u0627\u06cc\u0632 \u0641\u0648\u0646\u062a","Font {0}":"\u0641\u0648\u0646\u062a {0}","Fonts":"\u0641\u0648\u0646\u062a\u200c\u200c\u0647\u0627","Food and Drink":"\u063a\u0630\u0627 \u0648 \u0646\u0648\u0634\u06cc\u062f\u0646\u06cc","Footer":"\u067e\u0627\u0648\u0631\u0642\u06cc","Format":"\u0642\u0627\u0644\u0628","Format {0}":"\u0642\u0627\u0644\u0628 {0}","Formats":"\u0642\u0627\u0644\u0628\u200c\u0628\u0646\u062f\u06cc\u200c\u0647\u0627","Fullscreen":"\u062a\u0645\u0627\u0645\u200c\u0635\u0641\u062d\u0647","G":"\u0633\u0628\u0632","General":"\u0639\u0645\u0648\u0645\u06cc","Gray":"\u062e\u0627\u06a9\u0633\u062a\u0631\u06cc","Green":"\u0633\u0628\u0632","Green component":"\u062c\u0632\u0621 \u0633\u0628\u0632","Groove":"\u0634\u06cc\u0627\u0631\u062f\u0627\u0631","Handy Shortcuts":"\u0645\u06cc\u0627\u0646\u0628\u0631\u0647\u0627\u06cc \u0645\u0641\u06cc\u062f","Header":"\u0633\u0631\u0628\u0631\u06af","Header cell":"\u0633\u0644\u0648\u0644 \u0633\u0631\u0633\u062a\u0648\u0646","Heading 1":"\u0633\u0631\u0641\u0635\u0644 1","Heading 2":"\u0633\u0631\u0641\u0635\u0644 2","Heading 3":"\u0633\u0631\u0641\u0635\u0644 3","Heading 4":"\u0633\u0631\u0641\u0635\u0644 4","Heading 5":"\u0633\u0631\u0641\u0635\u0644 5","Heading 6":"\u0633\u0631\u0641\u0635\u0644 6","Headings":"\u0633\u0631\u0641\u0635\u0644\u200c\u0647\u0627","Height":"\u0627\u0631\u062a\u0641\u0627\u0639","Help":"\u0631\u0627\u0647\u0646\u0645\u0627","Hex color code":"\u06a9\u062f \u0631\u0646\u06af 16 \u0628\u06cc\u062a\u06cc","Hidden":"\u0645\u062e\u0641\u06cc","Horizontal align":"\u062a\u0631\u0627\u0632 \u0627\u0641\u0642\u06cc","Horizontal line":"\u062e\u0637 \u0627\u0641\u0642\u06cc","Horizontal space":"\u0641\u0636\u0627\u06cc \u0627\u0641\u0642\u06cc","ID":"\u0634\u0646\u0627\u0633\u0647","ID should start with a letter, followed only by letters, numbers, dashes, dots, colons or underscores.":"\u0634\u0646\u0627\u0633\u0647 \u0628\u0627\u06cc\u062f \u062a\u0648\u0633\u0637 \u06cc\u06a9 \u062d\u0631\u0641 \u0627\u0646\u06af\u0644\u06cc\u0633\u06cc \u0634\u0631\u0648\u0639 \u0634\u062f\u0647 \u0648 \u0628\u0639\u062f \u0627\u0632 \u0622\u0646 \u0641\u0642\u0637 \u062d\u0631\u0648\u0641\u060c \u0627\u0639\u062f\u0627\u062f\u060c \u062e\u0637 \u0641\u0627\u0635\u0644\u0647 (-)\u060c \u0646\u0642\u0637\u0647 (.)\u060c \u062f\u0648 \u0646\u0642\u0637\u0647 (:) \u06cc\u0627 \u0632\u06cc\u0631\u062e\u0637 (_) \u0642\u0631\u0627\u0631 \u06af\u06cc\u0631\u062f.","Image is decorative":"\u0627\u06cc\u0646 \u062a\u0635\u0648\u06cc\u0631 \u062f\u06a9\u0648\u0631\u06cc \u0627\u0633\u062a","Image list":"\u0641\u0647\u0631\u0633\u062a \u062a\u0635\u0648\u06cc\u0631","Image title":"\u0639\u0646\u0648\u0627\u0646 \u062a\u0635\u0648\u06cc\u0631","Image...":"\u062a\u0635\u0648\u06cc\u0631...","ImageProxy HTTP error: Could not find Image Proxy":"\u062e\u0637\u0627\u06cc ImageProxy HTTP: \u0634\u06cc\u0621 ImageProxy \u067e\u06cc\u062f\u0627 \u0646\u0634\u062f","ImageProxy HTTP error: Incorrect Image Proxy URL":"\u062e\u0637\u0627\u06cc ImageProxy HTTP: \u0622\u062f\u0631\u0633 ImageProxy \u0627\u0634\u062a\u0628\u0627\u0647 \u0627\u0633\u062a","ImageProxy HTTP error: Rejected request":"\u062e\u0637\u0627\u06cc ImageProxy HTTP: \u062f\u0631\u062e\u0648\u0627\u0633\u062a \u0628\u0631\u06af\u0631\u062f\u0627\u0646\u062f\u0647 \u0634\u062f","ImageProxy HTTP error: Unknown ImageProxy error":"\u062e\u0637\u0627\u06cc ImageProxy HTTP: \u062e\u0637\u0627 \u0634\u0646\u0627\u0633\u0627\u06cc\u06cc \u0646\u0634\u062f","Increase indent":"\u0627\u0641\u0632\u0627\u06cc\u0634 \u062a\u0648\u0631\u0641\u062a\u06af\u06cc","Inline":"\u0647\u0645\u200c\u0631\u0627\u0633\u062a\u0627","Insert":"\u062f\u0631\u062c","Insert Template":"\u062f\u0631\u062c \u0642\u0627\u0644\u0628","Insert accordion":"\u0648\u0627\u0631\u062f \u06a9\u0631\u062f\u0646 \u0622\u06a9\u0627\u0631\u062f\u0626\u0648\u0646","Insert column after":"\u062f\u0631\u062c \u0633\u062a\u0648\u0646 \u062f\u0631 \u067e\u0627\u06cc\u06cc\u0646","Insert column before":"\u062f\u0631\u062c \u0633\u062a\u0648\u0646 \u062f\u0631 \u0628\u0627\u0644\u0627","Insert date/time":"\u062f\u0631\u062c \u062a\u0627\u0631\u06cc\u062e/\u0632\u0645\u0627\u0646","Insert image":"\u062f\u0631\u062c \u062a\u0635\u0648\u06cc\u0631","Insert link (if link plugin activated)":"\u062f\u0631\u062c \u067e\u06cc\u0648\u0646\u062f (\u062f\u0631 \u0635\u0648\u0631\u062a \u0641\u0639\u0627\u0644 \u0628\u0648\u062f\u0646 \u0627\u0641\u0632\u0648\u0646\u0647\u0654 \u067e\u06cc\u0648\u0646\u062f)","Insert row after":"\u062f\u0631\u062c \u0633\u0637\u0631 \u062f\u0631 \u067e\u0627\u06cc\u06cc\u0646","Insert row before":"\u062f\u0631\u062c \u0633\u0637\u0631 \u062f\u0631 \u0628\u0627\u0644\u0627","Insert table":"\u062f\u0631\u062c \u062c\u062f\u0648\u0644","Insert template...":"\u062f\u0631\u062c \u0627\u0644\u06af\u0648...","Insert video":"\u062f\u0631\u062c \u0648\u06cc\u062f\u06cc\u0648","Insert/Edit code sample":"\u062f\u0631\u062c/\u0648\u06cc\u0631\u0627\u06cc\u0634 \u0646\u0645\u0648\u0646\u0647 \u06a9\u062f","Insert/edit image":"\u062f\u0631\u062c/\u0648\u06cc\u0631\u0627\u06cc\u0634 \u062a\u0635\u0648\u06cc\u0631","Insert/edit link":"\u062f\u0631\u062c/\u0648\u06cc\u0631\u0627\u06cc\u0634 \u067e\u06cc\u0648\u0646\u062f","Insert/edit media":"\u062f\u0631\u062c/\u0648\u06cc\u0631\u0627\u06cc\u0634 \u0631\u0633\u0627\u0646\u0647","Insert/edit video":"\u062f\u0631\u062c/\u0648\u06cc\u0631\u0627\u06cc\u0634 \u0648\u06cc\u062f\u06cc\u0648","Inset":"\u062a\u0648 \u0631\u0641\u062a\u0647","Invalid hex color code: {0}":"\u06a9\u062f \u0631\u0646\u06af 16 \u0628\u06cc\u062a\u06cc \u0645\u0639\u062a\u0628\u0631: {0}","Invalid input":"\u0648\u0631\u0648\u062f\u06cc \u0646\u0627\u0645\u0639\u062a\u0628\u0631","Italic":"\u06a9\u062c","Justify":"\u062a\u0631\u0627\u0632\u0628\u0646\u062f\u06cc \u062f\u0648\u0637\u0631\u0641\u0647","Keyboard Navigation":"\u0645\u0631\u0648\u0631 \u0628\u0627 \u0635\u0641\u062d\u0647 \u06a9\u0644\u06cc\u062f","Language":"\u0632\u0628\u0627\u0646","Learn more...":"\u06cc\u0627\u062f\u06af\u06cc\u0631\u06cc \u0628\u06cc\u0634\u062a\u0631...","Left":"\u0686\u067e","Left to right":"\u0686\u067e \u0628\u0647 \u0631\u0627\u0633\u062a","Light Blue":"\u0622\u0628\u06cc \u0631\u0648\u0634\u0646","Light Gray":"\u062e\u0627\u06a9\u0633\u062a\u0631\u06cc \u0631\u0648\u0634\u0646","Light Green":"\u0633\u0628\u0632 \u0631\u0648\u0634\u0646","Light Purple":"\u0628\u0646\u0641\u0634 \u0631\u0648\u0634\u0646","Light Red":"\u0642\u0631\u0645\u0632 \u0631\u0648\u0634\u0646","Light Yellow":"\u0632\u0631\u062f \u0631\u0648\u0634\u0646","Line height":"\u0628\u0644\u0646\u062f\u06cc \u062e\u0637 ","Link list":"\u0641\u0647\u0631\u0633\u062a \u067e\u06cc\u0648\u0646\u062f\u0647\u0627","Link...":"\u067e\u06cc\u0648\u0646\u062f...","List Properties":"\u062a\u0646\u0638\u06cc\u0645\u0627\u062a \u0641\u0647\u0631\u0633\u062a","List properties...":"\u062a\u0646\u0638\u06cc\u0645\u0627\u062a \u0641\u0647\u0631\u0633\u062a","Loading emojis...":"\u0641\u0631\u0627\u062e\u0648\u0627\u0646\u06cc \u0627\u0633\u062a\u06cc\u06a9\u0631\u0647\u0627...","Loading...":"\u0628\u0627\u0631\u06af\u06cc\u0631\u06cc...","Lower Alpha":"\u062d\u0631\u0648\u0641 \u06a9\u0648\u0686\u06a9","Lower Greek":"\u062d\u0631\u0648\u0641 \u06a9\u0648\u0686\u06a9 \u06cc\u0648\u0646\u0627\u0646\u06cc","Lower Roman":"\u0627\u0639\u062f\u0627\u062f \u0631\u0648\u0645\u06cc \u06a9\u0648\u0686\u06a9","Match case":"\u0646\u0645\u0648\u0646\u0647 \u0645\u0646\u0637\u0628\u0642","Mathematical":"\u0631\u06cc\u0627\u0636\u06cc","Media poster (Image URL)":"\u067e\u0648\u0633\u062a\u0631 \u0631\u0633\u0627\u0646\u0647 (\u0646\u0634\u0627\u0646\u06cc \u0648\u0628 \u062a\u0635\u0648\u06cc\u0631)","Media...":"\u0631\u0633\u0627\u0646\u0647...","Medium Blue":"\u0622\u0628\u06cc \u0633\u06cc\u0631","Medium Gray":"\u062e\u0627\u06a9\u0633\u062a\u0631\u06cc \u0646\u06cc\u0645\u0647\u200c\u0631\u0648\u0634\u0646","Medium Purple":"\u0622\u0628\u06cc \u0628\u0646\u0641\u0634","Merge cells":"\u0627\u062f\u063a\u0627\u0645 \u0633\u0644\u0648\u0644\u200c\u0647\u0627","Middle":"\u0648\u0633\u0637","Midnight Blue":"\u0622\u0628\u06cc \u0646\u0641\u062a\u06cc","More...":"\u0628\u06cc\u0634\u062a\u0631...","Name":"\u0646\u0627\u0645","Navy Blue":"\u0633\u0631\u0645\u0647\u200c\u0627\u06cc","New document":"\u0633\u0646\u062f \u062c\u062f\u06cc\u062f","New window":"\u067e\u0646\u062c\u0631\u0647 \u062c\u062f\u06cc\u062f","Next":"\u0628\u0639\u062f\u06cc","No":"\u062e\u06cc\u0631","No alignment":"\u0628\u062f\u0648\u0646 \u062a\u0631\u0627\u0632\u0628\u0646\u062f\u06cc","No color":"\u0628\u062f\u0648\u0646 \u0631\u0646\u06af","Nonbreaking space":"\u0641\u0636\u0627\u06cc \u062e\u0627\u0644\u06cc \u0628\u0631\u0634 \u0646\u0627\u067e\u0630\u06cc\u0631","None":"\u0647\u06cc\u0686\u200c\u06a9\u062f\u0627\u0645","Numbered list":"\u0641\u0647\u0631\u0633\u062a \u0634\u0645\u0627\u0631\u0647\u200c\u062f\u0627\u0631","OR":"\u06cc\u0627","Objects":"\u0627\u0634\u06cc\u0627","Ok":"\u062a\u0623\u06cc\u06cc\u062f","Open help dialog":"\u0628\u0627\u0632 \u06a9\u0631\u062f\u0646 \u06a9\u0627\u062f\u0631 \u0631\u0627\u0647\u0646\u0645\u0627","Open link":"\u0628\u0627\u0632\u06a9\u0631\u062f\u0646 \u0644\u06cc\u0646\u06a9","Open link in...":"\u0628\u0627\u0632 \u06a9\u0631\u062f\u0646 \u067e\u06cc\u0648\u0646\u062f \u062f\u0631...","Open popup menu for split buttons":"\u0645\u0646\u0648\u06cc \u0628\u0627\u0632\u0634\u0648 \u0628\u0631\u0627\u06cc \u062f\u06a9\u0645\u0647 \u0647\u0627\u06cc \u062a\u0642\u0633\u06cc\u0645 \u0634\u062f\u0647 \u0631\u0627 \u0628\u0627\u0632 \u06a9\u0646\u06cc\u062f","Orange":"\u0646\u0627\u0631\u0646\u062c\u06cc","Outset":"\u0628\u0631\u062c\u0633\u062a\u0647","Page break":"\u0628\u0631\u0634 \u0635\u0641\u062d\u0647","Paragraph":"\u067e\u0627\u0631\u0627\u06af\u0631\u0627\u0641","Paste":"\u062c\u0627\u06cc \u06af\u0630\u0627\u0631\u06cc","Paste as text":"\u0686\u0633\u0628\u0627\u0646\u062f\u0646 \u0628\u0647\u200c\u0635\u0648\u0631\u062a \u0645\u062a\u0646","Paste column after":"\u0686\u0633\u0628\u0627\u0646\u062f\u0646 \u0633\u062a\u0648\u0646 \u0628\u0639\u062f \u0627\u0632 \u0633\u062a\u0648\u0646 \u062c\u0627\u0631\u06cc","Paste column before":"\u0686\u0633\u0628\u0627\u0646\u062f\u0646 \u0633\u062a\u0648\u0646 \u0642\u0628\u0644 \u0627\u0632 \u0633\u062a\u0648\u0646 \u062c\u0627\u0631\u06cc","Paste is now in plain text mode. Contents will now be pasted as plain text until you toggle this option off.":"\u0642\u0627\u0628\u0644\u06cc\u062a \u0686\u0633\u0628\u0627\u0646\u062f\u0646 \u062f\u0631 \u062d\u0627\u0644 \u062d\u0627\u0636\u0631 \u062f\u0631 \u062d\u0627\u0644\u062a \u0645\u062a\u0646 \u0633\u0627\u062f\u0647 \u0627\u0633\u062a. \u062a\u0627 \u0632\u0645\u0627\u0646 \u0641\u0639\u0627\u0644 \u0628\u0648\u062f\u0646 \u0627\u06cc\u0646 \u062d\u0627\u0644\u062a\u060c \u0645\u062a\u0648\u0646 \u0628\u0647 \u0635\u0648\u0631\u062a \u0633\u0627\u062f\u0647 \u0686\u0633\u0628\u0627\u0646\u062f\u0647 \u0645\u06cc\u200c\u0634\u0648\u0646\u062f.","Paste or type a link":"\u0686\u0633\u0628\u0627\u0646\u062f\u0646 \u06cc\u0627 \u062a\u0627\u06cc\u067e \u06a9\u0631\u062f\u0646 \u067e\u06cc\u0648\u0646\u062f","Paste row after":"\u0686\u0633\u0628\u0627\u0646\u062f\u0646 \u0633\u0637\u0631 \u062f\u0631 \u067e\u0627\u06cc\u06cc\u0646","Paste row before":"\u0686\u0633\u0628\u0627\u0646\u062f\u0646 \u0633\u0637\u0631 \u062f\u0631 \u0628\u0627\u0644\u0627","Paste your embed code below:":"\u0686\u0633\u0628\u0627\u0646\u062f\u0646 \u06a9\u062f \u062c\u0627\u0633\u0627\u0632\u06cc \u0634\u0645\u0627 \u062f\u0631 \u0632\u06cc\u0631:","People":"\u0627\u0641\u0631\u0627\u062f","Plugins":"\u0627\u0641\u0632\u0648\u0646\u0647\u200c\u0647\u0627","Plugins installed ({0}):":"\u0627\u0641\u0632\u0648\u0646\u0647\u200c\u0647\u0627\u06cc \u0646\u0635\u0628\u200c\u0634\u062f\u0647 ({0}):","Powered by {0}":"\u0642\u0648\u062a\u200c\u06af\u0631\u0641\u062a\u0647 \u0627\u0632 {0}","Pre":"\u067e\u06cc\u0634","Preferences":"\u062a\u0631\u062c\u06cc\u062d\u0627\u062a","Preformatted":"\u0627\u0632 \u067e\u06cc\u0634 \u0642\u0627\u0644\u0628\u200c\u0628\u0646\u062f\u06cc\u200c\u0634\u062f\u0647","Premium plugins:":"\u0627\u0641\u0632\u0648\u0646\u0647\u200c\u0647\u0627\u06cc \u067e\u0648\u0644\u06cc:","Press the Up and Down arrow keys to resize the editor.":"\u0628\u0631\u0627\u06cc \u062a\u063a\u06cc\u06cc\u0631 \u0627\u0646\u062f\u0627\u0632\u0647 \u0648\u06cc\u0631\u0627\u06cc\u0634\u06af\u0631\u060c \u06a9\u0644\u06cc\u062f\u0647\u0627\u06cc \u062c\u0647\u062a \u062f\u0627\u0631 \u0628\u0627\u0644\u0627 \u0648 \u067e\u0627\u06cc\u06cc\u0646 \u0631\u0627 \u0641\u0634\u0627\u0631 \u062f\u0647\u06cc\u062f.","Press the arrow keys to resize the editor.":"\u0628\u0631\u0627\u06cc \u062a\u063a\u06cc\u06cc\u0631 \u0627\u0646\u062f\u0627\u0632\u0647 \u0648\u06cc\u0631\u0627\u06cc\u0634\u06af\u0631\u060c \u06a9\u0644\u06cc\u062f\u0647\u0627\u06cc \u062c\u0647\u062a \u062f\u0627\u0631 \u0631\u0627 \u0641\u0634\u0627\u0631 \u0628\u062f\u0647.","Press {0} for help":"{0} \u0631\u0627 \u0628\u0631\u0627\u06cc \u0631\u0627\u0647\u0646\u0645\u0627\u06cc\u06cc \u0641\u0634\u0627\u0631 \u0628\u062f\u0647","Preview":"\u067e\u06cc\u0634\u200c\u0646\u0645\u0627\u06cc\u0634","Previous":"\u0642\u0628\u0644\u06cc","Print":"\u0686\u0627\u067e","Print...":"\u0686\u0627\u067e...","Purple":"\u0628\u0646\u0641\u0634","Quotations":"\u0646\u0642\u0644\u200c\u0642\u0648\u0644\u200c\u0647\u0627","R":"\u0642\u0631\u0645\u0632","Range 0 to 255":"\u0628\u0627\u0632\u0647\u200c\u06cc \u0635\u0641\u0631 \u062a\u0627 255","Red":"\u0642\u0631\u0645\u0632","Red component":"\u062c\u0632\u0621 \u0642\u0631\u0645\u0632","Redo":"\u062f\u0648\u0628\u0627\u0631\u0647 \u0627\u0646\u062c\u0627\u0645\u0634 \u0628\u062f\u0647","Remove":"\u067e\u0627\u06a9 \u06a9\u0631\u062f\u0646","Remove color":"\u062d\u0630\u0641 \u0631\u0646\u06af","Remove link":"\u062d\u0630\u0641 \u067e\u06cc\u0648\u0646\u062f","Replace":"\u062c\u0627\u06cc\u06af\u0632\u06cc\u0646 \u06a9\u0631\u062f\u0646","Replace all":"\u062c\u0627\u06cc\u06af\u0632\u06cc\u0646 \u06a9\u0631\u062f\u0646 \u0647\u0645\u0647","Replace with":"\u062c\u0627\u06cc\u06af\u0632\u06cc\u0646 \u06a9\u0631\u062f\u0646 \u0628\u0627","Resize":"\u062a\u063a\u06cc\u06cc\u0631 \u0627\u0646\u062f\u0627\u0632\u0647","Restore last draft":"\u0628\u0627\u0632\u06cc\u0627\u0628\u06cc \u0622\u062e\u0631\u06cc\u0646 \u067e\u06cc\u0634\u200c\u0646\u0648\u06cc\u0633","Reveal or hide additional toolbar items":"\u0622\u0634\u06a9\u0627\u0631 \u06cc\u0627 \u067e\u0646\u0647\u0627\u0646 \u06a9\u0631\u062f\u0646 \u0645\u0648\u0627\u0631\u062f \u0627\u0636\u0627\u0641\u06cc \u0646\u0648\u0627\u0631 \u0627\u0628\u0632\u0627\u0631","Rich Text Area":"\u062c\u0639\u0628\u0647 \u0645\u062a\u0646 \u0628\u0632\u0631\u06af (Textarea)","Rich Text Area. Press ALT-0 for help.":"\u0646\u0627\u062d\u06cc\u0647 \u0645\u062a\u0646 \u063a\u0646\u06cc. \u062c\u0647\u062a \u0645\u0634\u0627\u0647\u062f\u0647\u0654 \u0631\u0627\u0647\u0646\u0645\u0627 \u06a9\u0644\u06cc\u062f\u0647\u0627\u06cc ALT + 0 \u0631\u0627 \u0641\u0634\u0627\u0631 \u062f\u0647\u06cc\u062f.","Rich Text Area. Press ALT-F9 for menu. Press ALT-F10 for toolbar. Press ALT-0 for help":"\u0646\u0627\u062d\u06cc\u0647 \u0645\u062a\u0646 \u063a\u0646\u06cc. \u062c\u0647\u062a \u0645\u0634\u0627\u0647\u062f\u0647\u0654 \u0645\u0646\u0648 \u0627\u0632 \u06a9\u0644\u06cc\u062f\u0647\u0627\u06cc \u062a\u0631\u06a9\u06cc\u0628\u06cc ALT + F9 \u0627\u0633\u062a\u0641\u0627\u062f\u0647 \u06a9\u0646\u06cc\u062f. \u062c\u0647\u062a \u0645\u0634\u0627\u0647\u062f\u0647\u0654 \u0646\u0648\u0627\u0631 \u0627\u0628\u0632\u0627\u0631 \u0627\u0632 \u06a9\u0644\u06cc\u062f\u0647\u0627\u06cc \u062a\u0631\u06a9\u06cc\u0628\u06cc ALT + F10 \u0627\u0633\u062a\u0641\u0627\u062f\u0647 \u06a9\u0646\u06cc\u062f. \u062c\u0647\u062a \u0645\u0634\u0627\u0647\u062f\u0647 \u0631\u0627\u0647\u0646\u0645\u0627 \u0627\u0632 \u06a9\u0644\u06cc\u062f\u0647\u0627\u06cc \u062a\u0631\u06a9\u06cc\u0628\u06cc ALT + 0 \u0627\u0633\u062a\u0641\u0627\u062f\u0647 \u06a9\u0646\u06cc\u062f.","Ridge":"\u0644\u0628\u0647\u200c\u062f\u0627\u0631","Right":"\u0631\u0627\u0633\u062a","Right to left":"\u0631\u0627\u0633\u062a \u0628\u0647 \u0686\u067e","Row":"\u0631\u062f\u06cc\u0641","Row clipboard actions":"\u0639\u0645\u0644\u06cc\u0627\u062a \u062d\u0627\u0641\u0638\u0647 \u0645\u0648\u0642\u062a \u0631\u062f\u06cc\u0641\u200c\u0647\u0627","Row group":"\u06af\u0631\u0648\u0647 \u0633\u0637\u0631\u06cc","Row header":"\u0633\u0637\u0631 \u062a\u06cc\u062a\u0631","Row properties":"\u062a\u0646\u0638\u06cc\u0645\u0627\u062a \u0633\u0637\u0631","Row type":"\u0646\u0648\u0639 \u0633\u0637\u0631","Rows":"\u0631\u062f\u06cc\u0641\u200c\u0647\u0627","Save":"\u0630\u062e\u064a\u0631\u0647","Save (if save plugin activated)":"\u0630\u062e\u06cc\u0631\u0647\xa0(\u062f\u0631 \u0635\u0648\u0631\u062a \u0641\u0639\u0627\u0644 \u0628\u0648\u062f\u0646 \u0627\u0641\u0632\u0648\u0646\u0647\u0654 \u0630\u062e\u06cc\u0631\u0647)","Scope":"\u06af\u0633\u062a\u0631\u0647","Search":"\u062c\u0633\u062a\u062c\u0648","Select all":"\u0627\u0646\u062a\u062e\u0627\u0628 \u0647\u0645\u0647","Select...":"\u0627\u0646\u062a\u062e\u0627\u0628...","Selection":"\u0627\u0646\u062a\u062e\u0627\u0628","Shortcut":"\u0645\u06cc\u0627\u0646\u0628\u0631","Show blocks":"\u0646\u0645\u0627\u06cc\u0634 \u0628\u0644\u0648\u06a9\u200c\u0647\u0627","Show caption":"\u0646\u0645\u0627\u06cc\u0634 \u0639\u0646\u0648\u0627\u0646","Show invisible characters":"\u0646\u0645\u0627\u06cc\u0634 \u0646\u0648\u06cc\u0633\u0647\u200c\u0647\u0627\u06cc \u0646\u0627\u067e\u06cc\u062f\u0627","Size":"\u0627\u0646\u062f\u0627\u0632\u0647","Solid":"\u062e\u0637 \u0645\u0645\u062a\u062f","Source":"\u0645\u0646\u0628\u0639","Source code":"\u06a9\u062f \u0645\u0646\u0628\u0639","Special Character":"\u0646\u0648\u06cc\u0633\u06c0 \u0648\u06cc\u0698\u0647","Special character...":"\u0646\u0648\u06cc\u0633\u06c0 \u0648\u06cc\u0698\u0647...","Split cell":"\u062c\u062f\u0627\u0633\u0627\u0632\u06cc \u0633\u0644\u0648\u0644\u200c\u0647\u0627","Square":"\u0645\u0631\u0628\u0639","Start list at number":"\u0644\u06cc\u0633\u062a \u0631\u0627 \u062f\u0631 \u0634\u0645\u0627\u0631\u0647 \u0634\u0631\u0648\u0639 \u06a9\u0646\u06cc\u062f","Strikethrough":"\u062e\u0637 \u0632\u062f\u0646","Style":"\u0633\u0628\u06a9","Subscript":"\u0632\u06cc\u0631\u0646\u06af\u0627\u0634\u062a","Superscript":"\u0628\u0627\u0644\u0627\u0646\u06af\u0627\u0634\u062a","Switch to or from fullscreen mode":"\u062a\u063a\u06cc\u06cc\u0631 \u0627\u0632 \u062d\u0627\u0644\u062a \u062a\u0645\u0627\u0645\u200c\u0635\u0641\u062d\u0647 \u06cc\u0627 \u0628\u0647 \u062d\u0627\u0644\u062a \u062a\u0645\u0627\u0645\u200c\u0635\u0641\u062d\u0647","Symbols":"\u0646\u0645\u0627\u062f\u0647\u0627","System Font":"\u0641\u0648\u0646\u062a \u0633\u06cc\u0633\u062a\u0645\u06cc","Table":"\u062c\u062f\u0648\u0644","Table caption":"\u0639\u0646\u0648\u0627\u0646 \u062c\u062f\u0648\u0644","Table properties":"\u062a\u0646\u0638\u06cc\u0645\u0627\u062a \u062c\u062f\u0648\u0644","Table styles":"\u0633\u0628\u06a9\u200c\u0647\u0627\u06cc \u062c\u062f\u0648\u0644","Template":"\u0627\u0644\u06af\u0648","Templates":"\u0627\u0644\u06af\u0648\u0647\u0627","Text":"\u0645\u062a\u0646","Text color":"\u0631\u0646\u06af \u0645\u062a\u0646","Text color {0}":"\u0631\u0646\u06a9 \u0645\u062a\u0646 {0}","Text to display":"\u0645\u062a\u0646 \u0628\u0631\u0627\u06cc \u0646\u0645\u0627\u06cc\u0634","The URL you entered seems to be an email address. Do you want to add the required mailto: prefix?":"\u0628\u0647 \u0646\u0638\u0631 \u0645\u06cc\u200c\u0631\u0633\u062f \u0646\u0634\u0627\u0646\u06cc \u0648\u0628 \u0648\u0627\u0631\u062f\u0634\u062f\u0647 \u0646\u0634\u0627\u0646\u06cc \u0627\u06cc\u0645\u06cc\u0644 \u0627\u0633\u062a. \u0622\u06cc\u0627 \u0645\u0627\u06cc\u0644 \u0628\u0647 \u0627\u0641\u0632\u0648\u062f\u0646 \u067e\u06cc\u0634\u0648\u0646\u062f \u0644\u0627\u0632\u0645 :mailto \u0647\u0633\u062a\u06cc\u062f\u061f","The URL you entered seems to be an external link. Do you want to add the required http:// prefix?":"\u0628\u0647 \u0646\u0638\u0631 \u0645\u06cc \u0631\u0633\u062f \u0646\u0634\u0627\u0646\u06cc \u0648\u0628 \u0648\u0627\u0631\u062f\u0634\u062f\u0647 \u067e\u06cc\u0648\u0646\u062f\u06cc \u062e\u0627\u0631\u062c\u06cc \u0627\u0633\u062a. \u0622\u06cc\u0627 \u0645\u0627\u06cc\u0644 \u0628\u0647 \u0627\u0641\u0632\u0648\u062f\u0646 \u067e\u06cc\u0634\u0648\u0646\u062f //:http \u0647\u0633\u062a\u06cc\u062f\u061f","The URL you entered seems to be an external link. Do you want to add the required https:// prefix?":"\u0622\u062f\u0631\u0633 \u0627\u06cc\u0646\u062a\u0631\u0646\u062a\u06cc \u06a9\u0647 \u0634\u0645\u0627 \u0648\u0627\u0631\u062f \u06a9\u0631\u062f\u0647 \u0627\u06cc\u062f \u06af\u0648\u06cc\u0627 \u06cc\u06a9 \u0622\u062f\u0631\u0633 \u0627\u06cc\u0646\u062a\u0631\u0646\u062a\u06cc \u062e\u0627\u0631\u062c\u06cc \u0627\u0633\u062a. \u0622\u06cc\u0627 \u0645\u06cc\u062e\u0648\u0627\u0647\u06cc\u062f \u06a9\u0647 \u067e\u06cc\u0634\u0648\u0646\u062f \u0636\u0631\u0648\u0631\u06cc https:// \u0627\u0636\u0627\u0641\u0647 \u06a9\u0646\u0645\u061f","Title":"\u0639\u0646\u0648\u0627\u0646","To open the popup, press Shift+Enter":"\u062c\u0647\u062a \u0628\u0627\u0632 \u06a9\u0631\u062f\u0646 \u067e\u0646\u062c\u0631\u0647 \u0628\u0627\u0632\u0634\u0648\u060c \u06a9\u0644\u06cc\u062f\u0647\u0627\u06cc Shift + Enter \u0631\u0627 \u0641\u0634\u0627\u0631 \u062f\u0647\u06cc\u062f.","Toggle accordion":"\u062a\u063a\u06cc\u06cc\u0631 \u0648\u0636\u0639\u06cc\u062a \u0622\u06a9\u0627\u0631\u062f\u0626\u0648\u0646","Tools":"\u0627\u0628\u0632\u0627\u0631\u0647\u0627","Top":"\u0628\u0627\u0644\u0627","Travel and Places":"\u0633\u0641\u0631 \u0648 \u0627\u0645\u0627\u06a9\u0646","Turquoise":"\u0641\u06cc\u0631\u0648\u0632\u0647\u200c\u0627\u06cc","Underline":"\u0632\u06cc\u0631 \u062e\u0637 \u062f\u0627\u0631","Undo":"\u0628\u0631\u06af\u0631\u062f","Upload":"\u0622\u067e\u0644\u0648\u062f","Uploading image":"\u062f\u0631 \u062d\u0627\u0644 \u0628\u0627\u0631\u06af\u0632\u0627\u0631\u06cc \u062a\u0635\u0648\u06cc\u0631","Upper Alpha":"\u062d\u0631\u0648\u0641 \u0628\u0632\u0631\u06af","Upper Roman":"\u0627\u0639\u062f\u0627\u062f \u0631\u0648\u0645\u06cc \u0628\u0632\u0631\u06af","Url":"\u0646\u0634\u0627\u0646\u06cc \u0648\u0628","User Defined":"\u0628\u0647 \u062e\u0648\u0627\u0633\u062a \u06a9\u0627\u0631\u0628\u0631","Valid":"\u0645\u0639\u062a\u0628\u0631","Version":"\u0646\u0633\u062e\u0647","Vertical align":"\u062a\u0631\u0627\u0632 \u0639\u0645\u0648\u062f\u06cc","Vertical space":"\u0641\u0636\u0627\u06cc \u0639\u0645\u0648\u062f\u06cc","View":"\u0646\u0645\u0627\u06cc\u0634","Visual aids":"\u06a9\u0645\u06a9\u200c\u0647\u0627\u06cc \u0628\u0635\u0631\u06cc","Warn":"\u0647\u0634\u062f\u0627\u0631","White":"\u0633\u0641\u06cc\u062f","Width":"\u0639\u0631\u0636","Word count":"\u062a\u0639\u062f\u0627\u062f \u0648\u0627\u0698\u0647\u200c\u0647\u0627","Words":"\u06a9\u0644\u0645\u0627\u062a","Words: {0}":"\u0648\u0627\u0698\u0647\u200c\u0647\u0627: {0}","Yellow":"\u0632\u0631\u062f","Yes":"\u0628\u0644\u0647","You are using {0}":"\u062f\u0631 \u062d\u0627\u0644 \u0627\u0633\u062a\u0641\u0627\u062f\u0647 \u0627\u0632 {0} \u0647\u0633\u062a\u06cc\u062f","You have unsaved changes are you sure you want to navigate away?":"\u062a\u063a\u06cc\u06cc\u0631\u0627\u062a\u200c\u062a\u0627\u0646 \u0630\u062e\u06cc\u0631\u0647 \u0646\u0634\u062f\u0647\u200c\u0627\u0646\u062f\u060c \u0622\u06cc\u0627 \u0645\u0637\u0645\u0626\u0646\u06cc\u062f \u06a9\u0647 \u0645\u06cc\u200c\u062e\u0648\u0627\u0647\u06cc\u062f \u062e\u0627\u0631\u062c \u0634\u0648\u06cc\u062f\u061f","Your browser doesn't support direct access to the clipboard. Please use the Ctrl+X/C/V keyboard shortcuts instead.":"\u0645\u0631\u0648\u0631\u06af\u0631 \u0634\u0645\u0627 \u0627\u0632 \u062f\u0633\u062a\u0631\u0633\u06cc \u0645\u0633\u062a\u0642\u06cc\u0645 \u0628\u0647 \u06a9\u0644\u06cc\u067e\u200c\u0628\u0648\u0631\u062f \u067e\u0634\u062a\u06cc\u0628\u0627\u0646\u06cc \u0646\u0645\u06cc\u200c\u06a9\u0646\u062f\u060c \u0644\u0637\u0641\u0627\u064b \u0627\u0632 \u0645\u06cc\u0627\u0646\u0628\u0631\u0647\u0627\u06cc Ctrl+X/C/V \u0635\u0641\u062d\u0647 \u06a9\u0644\u06cc\u062f \u0627\u0633\u062a\u0641\u0627\u062f\u0647 \u06a9\u0646\u06cc\u062f.","_dir":"rtl","alignment":"\u062a\u0631\u0627\u0632\u0628\u0646\u062f\u06cc","austral sign":"\u0646\u0645\u0627\u062f \u0622\u0633\u062a\u0631\u0627\u0644","cedi sign":"\u0646\u0645\u0627\u062f \u0633\u062f\u06cc","colon sign":"\u0646\u0645\u0627\u062f \u062f\u0648\u0646\u0642\u0637\u0647","cruzeiro sign":"\u0646\u0645\u0627\u062f \u06a9\u0631\u0648\u0632\u06cc\u0631\u0648","currency sign":"\u0646\u0645\u0627\u062f \u0627\u0631\u0632","dollar sign":"\u0646\u0645\u0627\u062f \u062f\u0644\u0627\u0631","dong sign":"\u0646\u0645\u0627\u062f \u062f\u0627\u0646\u06af","drachma sign":"\u0646\u0645\u0627\u062f \u062f\u0631\u0627\u062e\u0645\u0627","euro-currency sign":"\u0646\u0645\u0627\u062f \u06cc\u0648\u0631\u0648","example":"\u0645\u062b\u0627\u0644","formatting":"\u0642\u0627\u0644\u0628\u200c\u0628\u0646\u062f\u06cc","french franc sign":"\u0646\u0645\u0627\u062f \u0641\u0631\u0627\u0646\u06a9 \u0641\u0631\u0627\u0646\u0633\u0647","german penny symbol":"\u0646\u0645\u0627\u062f \u067e\u0646\u06cc \u0622\u0644\u0645\u0627\u0646\u06cc","guarani sign":"\u0646\u0645\u0627\u062f \u06af\u0648\u0627\u0631\u0627\u0646\u06cc","history":"\u062a\u0627\u0631\u06cc\u062e\u0686\u0647","hryvnia sign":"\u0646\u0645\u0627\u062f \u06af\u0631\u06cc\u0648\u0646\u0627","indentation":"\u062a\u0648\u0631\u0641\u062a\u06af\u06cc","indian rupee sign":"\u0646\u0645\u0627\u062f \u0631\u0648\u067e\u06cc\u0647 \u0647\u0646\u062f\u06cc","kip sign":"\u0646\u0645\u0627\u062f \u06a9\u06cc\u067e","lira sign":"\u0646\u0645\u0627\u062f \u0644\u06cc\u0631\u0647","livre tournois sign":"\u0646\u0645\u0627\u062f \u0644\u06cc\u0648\u0631\u0647 \u062a\u0648\u0631\u0646\u0648\u0627","manat sign":"\u0646\u0645\u0627\u062f \u0645\u0646\u0627\u062a","mill sign":"\u0646\u0645\u0627\u062f \u0645\u06cc\u0644","naira sign":"\u0646\u0645\u0627\u062f \u0646\u0627\u06cc\u0631\u0627","new sheqel sign":"\u0646\u0645\u0627\u062f \u0634\u06a9\u0644 \u062c\u062f\u06cc\u062f","nordic mark sign":"\u0646\u0645\u0627\u062f \u0645\u0627\u0631\u06a9 \u0646\u0631\u0648\u0698","peseta sign":"\u0646\u0645\u0627\u062f \u067e\u0632\u062a\u0627","peso sign":"\u0646\u0645\u0627\u062f \u067e\u0632\u0648","ruble sign":"\u0646\u0645\u0627\u062f \u0631\u0648\u0628\u0644","rupee sign":"\u0646\u0645\u0627\u062f \u0631\u0648\u067e\u06cc\u0647","spesmilo sign":"\u0646\u0645\u0627\u062f \u0627\u0633\u067e\u0633\u0645\u06cc\u0644\u0648","styles":"\u0633\u0628\u06a9\u200c\u0647\u0627","tenge sign":"\u0646\u0645\u0627\u062f \u062a\u0646\u06af\u0647","tugrik sign":"\u0646\u0645\u0627\u062f \u062a\u0648\u06af\u0631\u0648\u06af","turkish lira sign":"\u0646\u0645\u0627\u062f \u0644\u06cc\u0631\u0647 \u062a\u0631\u06a9\u06cc","won sign":"\u0646\u0645\u0627\u062f \u0648\u0648\u0646","yen character":"\u0646\u0648\u06cc\u0633\u0647 \u06cc\u0646","yen/yuan character variant one":"\u0646\u0648\u06cc\u0633\u0647 \u062c\u0627\u06cc\u06af\u0632\u06cc\u0646 \u06cc\u0646/\u06cc\u0648\u0627\u0646","yuan character":"\u0646\u0648\u06cc\u0633\u0647 \u06cc\u0648\u0627\u0646","yuan character, in hong kong and taiwan":"\u0646\u0648\u06cc\u0633\u0647 \u06cc\u0648\u0627\u0646\u060c \u062f\u0631 \u0647\u0646\u06af\u200c\u06a9\u0646\u06af \u0648 \u062a\u0627\u06cc\u0648\u0627\u0646","{0} characters":"{0} \u06a9\u0627\u0631\u0627\u06a9\u062a\u0631","{0} columns, {1} rows":"{0} \u0633\u062a\u0648\u0646\u060c {1} \u0633\u0637\u0631","{0} words":"{0} \u0648\u0627\u0698\u0647"});
            
            window.titleSetter = function( e ) {
                
                if( e.target.value.trim() ) {
                    
                    document.querySelector('h1').innerHTML = e.target.value.trim();
                    let title_parts = document.querySelector('title').innerHTML.split( ' &gt; ' );
                    title_parts[ 0 ] = e.target.value.trim();
                    document.querySelector('title').innerHTML = title_parts.join( ' &gt; ');
                    
                    
                } else {
                    
                    document.querySelector('h1').innerHTML = '<?php echo $h1; ?>';
                    document.querySelector('title').innerHTML = '<?php echo $title; ?>';
                    
                }
            
            }
        
            window.cfAddRow = function( e ) {
            
                let row = '<tr><td><div class="tooltip" data-tip="حذف"><button onClick="cfRemoveRow(event)" class="btn btn-error btn-circle" type="button"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6"><path fill-rule="evenodd" d="M4.25 12a.75.75 0 0 1 .75-.75h14a.75.75 0 0 1 0 1.5H5a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd"/></svg></button></div></td><td><input type="text" pattern="[a-z0-9\-_]+" minlength="1" maxlength="32" oninput="cfSetValueKey(event)" dir="ltr" autocomplete="off" class="input input-bordered input-primary w-full" required></td><td><input type="text" autocomplete="off" class="cf-value input input-bordered input-primary w-full"></td></tr>';
                
                document.getElementById( 'cf-body' ).insertAdjacentHTML( 'beforeend', row );
            
            }
            
            window.cfRemoveRow = function( e ) {
                
                document.getElementById( 'cf-body' ).removeChild( e.target.closest( 'tr' ) );
            
            }
            
            window.cfSetValueKey = function( e ) {
                
                if( e.target.checkValidity() ) {
                    
                    e.target.classList.remove( 'input-error' );
                    
                    e.target.closest( 'tr' ).querySelector( '.cf-value' ).setAttribute( 'name', 'cf[' +e.target.value +']' );
                
                } else {
                    
                    e.target.classList.add( 'input-error' );
                    
                    e.target.closest( 'tr' ).querySelector( '.cf-value' ).removeAttribute( 'name' );
                
                }
            
            }
            
            window.atIllegal = function( e ) {
                
                if( e.target.value.includes( '@' ) ) {
                    
                    e.target.classList.add( 'input-error' );
                
                } else {
                    
                    e.target.classList.remove( 'input-error' );
                
                }
            
            }
            
            function initTiny( is_dark = false ) {
            
                let content = document.getElementById('content').textContent;
                
                if( tinymce.activeEditor ) {
                
                    content = tinymce.activeEditor.getContent();
                    
                    tinymce.activeEditor.destroy();
                
                }
                
                tinymce.init({
                    selector: '#content',
                    setup: function( editor ) {
                    
                      editor.on( 'init' , function ( e ) {
                      
                        editor.setContent( content );
                        
                      } );
                      
                    },
                    license_key: 'gpl',
                    skin: is_dark ? 'oxide-dark' : 'oxide',
                    content_css: is_dark ? 'dark' : 'default',
                    menubar : false,
                    statusbar : false,
                    plugins: "directionality pagebreak code image autoresize lists link",
                    toolbar1: "blocks | forecolor backcolor | styleselect | bold italic underline | alignleft aligncenter alignright alignjustify | link",
                    toolbar2: "bullist numlist outdent indent | undo redo | rtl ltr | pagebreak image | code",
                    image_list: [
                        { title: 'تصویر مطلب', value: 'wpttb_cover.png' }
                    ],
                    object_resizing: false,
                    language: 'fa',
                    min_height: 350,
                    pagebreak_separator: '<!--more-->',
                });
                
            }
        
            function toast( color ) {
                
                let alert = document.createElement('div');
                alert.classList.add( 'alert', 'border-none', 'bg-' + color, 'text-' + color + '-content' );
                alert.innerHTML = '<span>کپی شد!</span>';
                let toast = document.getElementById( 'toast' );
                
                if( toast.childElementCount >= 3 ) {
                    
                    toast.removeChild( toast.firstChild );
                
                }
                
                let alert_el = toast.appendChild( alert );
                
                setTimeout( function() {
                    
                    if( toast.contains( alert_el ) ) {
                        
                        toast.removeChild( alert_el );
                    
                    }
                
                }, 5000 );
            
            }
            
            const theme = localStorage.getItem('theme');
            
            if( theme ) {
                
                if( theme == 'dark' ) {
                    
                    document.documentElement.setAttribute( 'data-theme', 'dark' );
                    document.querySelector('.theme-controller').checked = true;
                    
                    initTiny( true );
                
                } else {
                    
                    document.documentElement.setAttribute( 'data-theme', 'light' );
                    document.querySelector('.theme-controller').checked = false;
                    
                    initTiny( false );
                
                }
            
            } else {
                
                if( window.matchMedia && window.matchMedia( '(prefers-color-scheme: dark)' ).matches ) {

                    document.documentElement.setAttribute( 'data-theme', 'dark' );
                    document.querySelector('.theme-controller').checked = true;
                    
                    initTiny( true );

                } else {
                    
                    document.documentElement.setAttribute( 'data-theme', 'light' );
                    document.querySelector('.theme-controller').checked = false;
                    
                    initTiny( false );
                
                }
                
            }
            
            document.querySelector('.theme-controller').addEventListener('change', function( e ) {
                
                if( e.target.checked ) {
                    
                    document.documentElement.setAttribute( 'data-theme', 'dark' );
                    localStorage.setItem('theme', 'dark');
                    
                    initTiny( true );
                
                } else {
                    
                    document.documentElement.setAttribute( 'data-theme', 'light' );
                    localStorage.setItem('theme', 'light');
                    
                    initTiny( false );
                
                }
            
            });
            
            document.getElementById('content-tiny').addEventListener('click', function( e ) {
                
                if( tinymce.activeEditor ) {
                    
                    tinymce.activeEditor.execCommand( 'mceFocus' );
                
                }
            
            } );
            
            const colors = [ 'primary', 'secondary', 'accent', 'info', 'success', 'warning', 'error' ];
            const var_wrap = document.getElementById('var-wrap');
            
            for( const svar of var_wrap.children ) {
            
                const random_color = colors[ Math.floor( Math.random() * colors.length ) ];
                
                svar.classList.add( 'tooltip-' + random_color );
                
                const var_btn = svar.querySelector('.btn');
                var_btn.classList.add( 'btn-' + random_color );
                
                var_btn.addEventListener('click', function( e ) {
                
                    const var_val = e.target.getAttribute( 'data-var' );
                    
                    navigator.clipboard.writeText( var_val ).then( function() {
                        
                        toast( random_color );
                        
                    } , function( err ) {
                    
                        console.error( 'خطا در کپی کردن متغییر: ', err );
                        
                    } );
                
                } );
            
            }
            
        })();
    </script>
</body>
</html>
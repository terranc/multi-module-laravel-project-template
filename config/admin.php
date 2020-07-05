<?php
return [

    /*
    |--------------------------------------------------------------------------
    | Laravel-admin name
    |--------------------------------------------------------------------------
    |
    | This value is the name of laravel-admin, This setting is displayed on the
    | login page.
    |
    */
    'name'                      => env('APP_TITLE', 'DEMO'),

    /*
    |--------------------------------------------------------------------------
    | Laravel-admin logo
    |--------------------------------------------------------------------------
    |
    | The logo of all admin pages. You can also set it as an image by using a
    | `img` tag, eg '<img src="http://logo-url" alt="Admin logo">'.
    |
    */
    'logo'                      => '<strong>' . env('APP_TITLE', 'DEMO') . '</strong>后台管理',

    /*
    |--------------------------------------------------------------------------
    | Laravel-admin mini logo
    |--------------------------------------------------------------------------
    |
    | The logo of all admin pages when the sidebar menu is collapsed. You can
    | also set it as an image by using a `img` tag, eg
    | '<img src="http://logo-url" alt="Admin logo">'.
    |
    */
    'logo-mini'                 => '<strong>' . mb_substr(env('APP_TITLE', 'DEMO'), 0, 1) . '</strong>',

    /*
    |--------------------------------------------------------------------------
    | Laravel-admin bootstrap setting
    |--------------------------------------------------------------------------
    |
    | This value is the path of laravel-admin bootstrap file.
    |
    */
    'bootstrap'                 => base_path('modules/Admin/bootstrap.php'),

    /*
    |--------------------------------------------------------------------------
    | Laravel-admin route settings
    |--------------------------------------------------------------------------
    |
    | The routing configuration of the admin page, including the path prefix,
    | the controller namespace, and the default middleware. If you want to
    | access through the root path, just set the prefix to empty string.
    |
    */
    'route'                     => [

        'prefix' => env('ADMIN_ROUTE_PREFIX', 'admin'),

        'namespace' => 'Modules\\Admin\\Controllers',

        'middleware' => ['multi-session:path,/admin', 'web', 'admin', 'admin.https'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Laravel-admin install directory
    |--------------------------------------------------------------------------
    |
    | The installation directory of the controller and routing configuration
    | files of the administration page. The default is `app/Admin`, which must
    | be set before running `artisan admin::install` to take effect.
    |
    */
    'directory'                 => base_path('modules/Admin'),

    /*
    |--------------------------------------------------------------------------
    | Laravel-admin html title
    |--------------------------------------------------------------------------
    |
    | Html title for all pages.
    |
    */
    'title'                     => env('APP_TITLE', 'DEMO'),

    /*
    |--------------------------------------------------------------------------
    | Access via `https`
    |--------------------------------------------------------------------------
    |
    | If your page is going to be accessed via https, set it to `true`.
    |
    */
    'https'                     => env('ADMIN_HTTPS', false),

    /*
    |--------------------------------------------------------------------------
    | Laravel-admin auth setting
    |--------------------------------------------------------------------------
    |
    | Authentication settings for all admin pages. Include an authentication
    | guard and a user provider setting of authentication driver.
    |
    | You can specify a controller for `login` `logout` and other auth routes.
    |
    */
    'auth'                      => [

        'controller' => Modules\Admin\Controllers\AuthController::class,

        'guard' => 'admin',

        'guards' => [
            'admin' => [
                'driver'   => 'session',
                'provider' => 'admin',
            ],
        ],

        'providers'   => [
            'admin' => [
                'driver' => 'eloquent',
                'model'  => Encore\Admin\Auth\Database\Administrator::class,
            ],
        ],

        // Add "remember me" to login form
        'remember'    => false,

        // Redirect to the specified URI when user is not authorized.
        'redirect_to' => 'auth/login',

        // The URIs that should be excluded from authorization.
        'excepts'     => [
            'auth/login',
            'auth/logout',
            '_handle_action_',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Laravel-admin upload setting
    |--------------------------------------------------------------------------
    |
    | File system configuration for form upload files and images, including
    | disk and upload path.
    |
    */
    'upload'                    => [

        // Disk in `config/filesystem.php`.
        'disk'      => 'qiniu',

        // Image and file upload path under the disk above.
        'directory' => [
            'image' => 'images',
            'file'  => 'files',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Laravel-admin database settings
    |--------------------------------------------------------------------------
    |
    | Here are database settings for laravel-admin builtin model & tables.
    |
    */
    'database'                  => [

        // Database connection for following tables.
        'connection'             => '',

        // User tables and model.
        'users_table'            => 'admin_users',
        'users_model'            => Encore\Admin\Auth\Database\Administrator::class,

        // Role table and model.
        'roles_table'            => 'admin_roles',
        'roles_model'            => Encore\Admin\Auth\Database\Role::class,

        // Permission table and model.
        'permissions_table'      => 'admin_permissions',
        'permissions_model'      => Encore\Admin\Auth\Database\Permission::class,

        // Menu table and model.
        'menu_table'             => 'admin_menu',
        'menu_model'             => Encore\Admin\Auth\Database\Menu::class,

        // Pivot table for table above.
        'operation_log_table'    => 'admin_operation_log',
        'user_permissions_table' => 'admin_user_permissions',
        'role_users_table'       => 'admin_role_users',
        'role_permissions_table' => 'admin_role_permissions',
        'role_menu_table'        => 'admin_role_menu',
    ],

    /*
    |--------------------------------------------------------------------------
    | User operation log setting
    |--------------------------------------------------------------------------
    |
    | By setting this option to open or close operation log in laravel-admin.
    |
    */
    'operation_log'             => [

        'enable'          => false,

        /*
         * Only logging allowed methods in the list
         */
        'allowed_methods' => ['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'CONNECT', 'OPTIONS', 'TRACE', 'PATCH'],

        /*
         * Routes that will not log to database.
         *
         * All method to path like: admin/auth/logs
         * or specific method to path like: get:admin/auth/logs.
         */
        'except'          => [
            'admin/auth/logs*',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Indicates whether to check route permission.
    |--------------------------------------------------------------------------
    */
    'check_route_permission'    => true,

    /*
    |--------------------------------------------------------------------------
    | Indicates whether to check menu roles.
    |--------------------------------------------------------------------------
    */
    'check_menu_roles'          => true,

    /*
    |--------------------------------------------------------------------------
    | User default avatar
    |--------------------------------------------------------------------------
    |
    | Set a default avatar for newly created users.
    |
    */
    'default_avatar'            => '/vendor/laravel-admin/AdminLTE/dist/img/user2-160x160.jpg',

    /*
    |--------------------------------------------------------------------------
    | Admin map field provider
    |--------------------------------------------------------------------------
    |
    | Supported: "tencent", "google", "yandex".
    |
    */
    'map_provider'              => 'google',

    /*
    |--------------------------------------------------------------------------
    | Application Skin
    |--------------------------------------------------------------------------
    |
    | This value is the skin of admin pages.
    | @see https://adminlte.io/docs/2.4/layout
    |
    | Supported:
    |    "skin-blue", "skin-blue-light", "skin-yellow", "skin-yellow-light",
    |    "skin-green", "skin-green-light", "skin-purple", "skin-purple-light",
    |    "skin-red", "skin-red-light", "skin-black", "skin-black-light".
    |
    */
    'skin'                      => 'skin-black-light',

    /*
    |--------------------------------------------------------------------------
    | Application layout
    |--------------------------------------------------------------------------
    |
    | This value is the layout of admin pages.
    | @see https://adminlte.io/docs/2.4/layout
    |
    | Supported: "fixed", "layout-boxed", "layout-top-nav", "sidebar-collapse",
    | "sidebar-mini".
    |
    */
    'layout'                    => ['sidebar-mini', 'fixed'],

    /*
    |--------------------------------------------------------------------------
    | Login page background image
    |--------------------------------------------------------------------------
    |
    | This value is used to set the background image of login page.
    |
    */
    'login_background_image'    => '/admin_assets/img/bg_login.jpg',

    /*
    |--------------------------------------------------------------------------
    | Show version at footer
    |--------------------------------------------------------------------------
    |
    | Whether to display the version number of laravel-admin at the footer of
    | each page
    |
    */
    'show_version'              => false,

    /*
    |--------------------------------------------------------------------------
    | Show environment at footer
    |--------------------------------------------------------------------------
    |
    | Whether to display the environment at the footer of each page
    |
    */
    'show_environment'          => false,

    /*
    |--------------------------------------------------------------------------
    | Menu bind to permission
    |--------------------------------------------------------------------------
    |
    | whether enable menu bind to a permission
    */
    'menu_bind_permission'      => true,

    /*
    |--------------------------------------------------------------------------
    | Enable default breadcrumb
    |--------------------------------------------------------------------------
    |
    | Whether enable default breadcrumb for every page content.
    */
    'enable_default_breadcrumb' => false,

    /*
    |--------------------------------------------------------------------------
    | Enable/Disable assets minify
    |--------------------------------------------------------------------------
    */
    'minify_assets'             => [

        // Assets will not be minified.
        'excepts' => [
            'vendor/ueditor/ueditor.config.js',
            'vendor/ueditor/ueditor.all.js',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Enable/Disable sidebar menu search
    |--------------------------------------------------------------------------
    */
    'enable_menu_search'        => false,

    /*
    |--------------------------------------------------------------------------
    | Alert message that will displayed on top of the page.
    |--------------------------------------------------------------------------
    */
    'top_alert'                 => '',

    /*
    |--------------------------------------------------------------------------
    | The global Grid action display class.
    |--------------------------------------------------------------------------
    */
    'grid_action_class'         => \Encore\Admin\Grid\Displayers\DropdownActions::class,

    /*
    |--------------------------------------------------------------------------
    | Extension Directory
    |--------------------------------------------------------------------------
    |
    | When you use command `php artisan admin:extend` to generate extensions,
    | the extension files will be generated in this directory.
    */
    'extension_dir'             => base_path('modules/Admin/Extensions'),

    /*
    |--------------------------------------------------------------------------
    | Settings for extensions.
    |--------------------------------------------------------------------------
    |
    | You can find all available extensions here
    | https://github.com/laravel-admin-extensions.
    |
    */
    'extensions'                => [
//        'ueditor'      => [
//            // 如果要关掉这个扩展，设置为false
//            'enable'     => true,
//            // 编辑器的前端配置 参考：http://fex.baidu.com/ueditor/#start-config
//            'config'     => [
//                'initialContent'          => '',
//                'contextMenu'             => [],
//                'autoClearinitialContent' => false,
//                'wordCount'               => false,
//                'removeFormatTags'        => 'b,big,code,del,dfn,em,font,i,ins,kbd,q,samp,small,span,strike,strong,sub,sup,tt,u,var',
//                'removeFormatAttributes'  => 'class,style,lang,accuse,align,hspace,valign,data-width,data-brushtype,opacity,border,title,placeholder',
//                'autoHeightEnabled'       => false,
//                'indentValue'             => '2em',
//                'initialFrameHeight'      => 400,
//                'imageScaleEnabled'       => true,
//                'elementPathEnabled'      => false,
//                'zIndex'                  => 1030,
//                'maxListLevel'            => -1,
//                'initialStyle'            => '',
//                'iframeCssUrl'            => '/css/ueditor-iframe.css',
//                'filterTxtRules'          => 'function(){return {"-":"script style object iframe embed input select",p:{$:{}},br:{$:{}},div:function(e){for(var t,n=UE.uNode.createElement("p");t=e.firstChild();)"text"!=t.type&&UE.dom.dtd.$block[t.tagName]?n.firstChild()?(e.parentNode.insertBefore(n,e),n=UE.uNode.createElement("p")):e.parentNode.insertBefore(t,e):n.appendChild(t);n.firstChild()&&e.parentNode.insertBefore(n,e),e.parentNode.removeChild(e)},ol:t,ul:t,dl:t,dt:t,dd:t,li:t,caption:e,th:e,tr:e,h1:e,h2:e,h3:e,h4:e,h5:e,h6:e,td:function(e){e.innerText()&&e.parentNode.insertAfter(UE.uNode.createText(" &nbsp; &nbsp;"),e),e.parentNode.removeChild(e,e.innerText())}}}',
//                //                'filterRules' => 'function(){return {br:{},iframe:{},b:function(node){node.tagName="strong"},strong:{$:{}},img:{$:{"id":1,"width":1,"height":1,"word_img":1,"src":1,"class":1,"_url":1}},p:{"br":1,"BR":1,"img":1,"IMG":1,"embed":1,"object":1,$:{}},span:{$:{"class":1}},strong:{$:{}},i:function(node){node.tagName="em"},a:function(node){var url=node.getAttr("href");var title=node.getAttr("title");if(url.indexOf("mafengwo")!==-1){node.parentNode.removeChild(node,true);return 0}node.setAttr();node.setAttr("href",url);node.setAttr("title",title);node.setAttr("target","_blank")},object:1,embed:1,dl:function(node){node.tagName="ul";node.setAttr()},dt:function(node){node.tagName="li";node.setAttr()},dd:function(node){node.tagName="li";node.setAttr()},li:function(node){var className=node.getAttr("class");if(!className||!/list\-/.test(className)){node.setAttr()}var tmpNodes=node.getNodesByTagName("ol ul");UE.utils.each(tmpNodes,function(n){node.parentNode.insertAfter(n,node)})},div:function(node){node.tagName="p";node.setAttr()},ol:{$:{}},ul:{$:{}},caption:{$:{}},th:{$:{}},td:{$:{valign:1,align:1,rowspan:1,colspan:1,width:1,height:1}},tr:{$:{}},h3:{$:{}},h2:{$:{}},hr:{$:{}}}}',
//                'autotypeset'             => [
//                    'mergeEmptyline'  => true,           //合并空行
//                    'removeClass'     => true,              //去掉冗余的class
//                    'removeEmptyline' => false,         //去掉空行
//                    'textAlign'       => false,
//                    'imageBlockLine'  => false,       //图片的浮动方式，独占一行剧中,左右浮动，默认: center,left,right,none 去掉这个属性表示不执行排版
//                    'pasteFilter'     => true,             //根据规则过滤没事粘贴进来的内容
//                    'clearFontSize'   => false,           //去掉所有的内嵌字号，使用编辑器默认的字号
//                    'clearFontFamily' => false,         //去掉所有的内嵌字体，使用编辑器默认的字体
//                    'removeEmptyNode' => false,         // 去掉空节点
//                    'indent'          => false,                  // 行首缩进
//                    'indentValue '    => '0em',            //行首缩进的大小
//                    'bdc2sb'          => false,
//                    'tobdc'           => false,
//                ],
//                'fontfamily'              => [
//                    [
//                        'label' => '',
//                        'name'  => 'yahei',
//                        'val'   => '微软雅黑',
//                    ],
//                    [
//                        'label' => '',
//                        'name'  => 'songti',
//                        'val'   => '宋体,SimSun',
//                    ],
//                    [
//                        'label' => '',
//                        'name'  => 'kaiti',
//                        'val'   => '楷体,楷体_GB2312,SimKai',
//                    ],
//                    [
//                        'label' => '',
//                        'name'  => 'heiti',
//                        'val'   => '黑体,SimHei',
//                    ],
//                    [
//                        'label' => '',
//                        'name'  => 'lishu',
//                        'val'   => '隶书,SimLi',
//                    ],
//                    [
//                        'label' => '',
//                        'name'  => 'arial',
//                        'val'   => 'arial,helvetica,sans-serif',
//                    ],
//                ],
//                'fontsize'                => [10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 24, 28, 32, 36],
//                'autoTransWordToList'     => true,
//                'insertorderedlist'       => [
//                    'decimal'         => '',         //'1,2,3...'
//                    'lower-alpha'     => '',    // 'a,b,c...'
//                    'lower-roman'     => '',    //'i,ii,iii...'
//                    'upper-alpha'     => '', //lang   //'A,B,C'
//                    'upper-roman'     => '',     //'I,II,III...'
//                    'cjk-ideographic' => '一、二、三、',
//                    'lower-greek'     => 'α,β,γ,δ',
//                ],
//                'insertunorderedlist'     => [
//                    'circle' => '',  // '○ 小圆圈'
//                    'disc'   => '',    // '● 小圆点'
//                    'square' => ''   //'■ 小方块'
//                ],
//                'toolbars'                => [
//                    [
//                        'source',
//                        'fullscreen',
//                        'undo',
//                        'redo',
//                        '|',
//                        'bold',
//                        'italic',
//                        'underline',
//                        'strikethrough',
//                        'fontsize',
//                        '|',
//                        'indent',
//                        'justifyleft',
//                        'justifycenter',
//                        'justifyright',
//                        'justifyjustify',
//                        'justifyindent',
//                        '|',
//                        'rowspacingtop',
//                        'rowspacingbottom',
//                        'lineheight',
//                        '|',
//                        'forecolor',
//                        'backcolor',
//                        'insertorderedlist',
//                        'insertunorderedlist',
//                        'removeformat',
//                        'formatmatch',
//                        '|',
//                        'link',
//                        'unlink',
//                        '|',
//                        'simpleupload',
//                        'insertimage',
//                        'insertvideo',
//                        '|',
//                        'imagenone',
//                        'imageleft',
//                        'imageright',
//                        '|',
//                        'horizontal',
//                        'spechars',
//                        'removeformat',
//                        '|',
//                        'searchreplace',
//                    ],
//                ],
//            ],
//            'field_type' => 'ueditor',
//        ],
    ],
];

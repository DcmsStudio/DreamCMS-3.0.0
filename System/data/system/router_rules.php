<?php





$router->map('/', array('controller' => 'main')); // main page will call controller "main" with method "index()"


$router->map('/main', array('controller' => 'main'));
$router->map('/main/css/:css', array('controller' => 'main', 'action' => 'css'), array('css' => '.+'));
$router->map('/main/js/:jscript', array('controller' => 'main', 'action' => 'js'), array('jscript' => '.+'));
$router->map('/main/corejs/:jscript', array('controller' => 'main', 'action' => 'corejs'), array('jscript' => '.+'));



$router->map('/main/comment', array('controller' => 'main', 'action' => 'comment'));

$router->map('/main/captchaaudio', array('controller' => 'main', 'action' => 'captchaaudio'));
$router->map('/main/captcha', array('controller' => 'main', 'action' => 'captcha'));
$router->map('/main/bbcodepreview/:allowedbbcode', array('controller' => 'main', 'action' => 'bbcodepreview'), array('allowedbbcode' => '[a-z0-9_]+'));

$router->map('/main/imgpreview/:chain/:format/:img', array('controller' => 'main', 'action' => 'imgpreview'), 
array('chain' => '[a-z0-9_]+', 'format' => '[a-z]+', 'img' => '.+'));
$router->map('/main/runcomponent/:com/:params', array('controller' => 'main', 'action' => 'runcomponent'), array('com' => '[\w]{2,}', 'params' => '.+'));


$router->map('/login', array('controller' => 'auth', 'action' => 'login'));
$router->map('/logout', array('controller' => 'auth', 'action' => 'logout'));
$router->map('/signup', array('controller' => 'auth', 'action' => 'signup'));

// Profile Rules
$router->map('/profile/?(index)?', array('controller' => 'user', 'action' => 'index')); // User (loggedin)
$router->map('/profile/password', array('controller' => 'user', 'action' => 'password')); // User (loggedin)
$router->map('/profile/settings', array('controller' => 'user', 'action' => 'settings')); // User (loggedin)
$router->map('/profile/avatar', array('controller' => 'user', 'action' => 'avatar')); // User (loggedin)
$router->map('/profile/other', array('controller' => 'user', 'action' => 'other')); // User (loggedin)


$router->map('/profile/:id', array('controller' => 'user', 'action' => 'getprofile'), array('id' => '[\d]{1,}')); // View a Profile


// News Rules
$router->map('/news/index', array('controller' => 'news', 'action' => 'index'));
$router->map('/news/captcha', array('controller' => 'news', 'action' => 'captcha'));
$router->map('/news/tag/:tag', array('controller' => 'news', 'action' => 'index'), array('tag' => '.+'));
$router->map('/news/:id', array('controller' => 'news', 'action' => 'show'), array('id' => '[\d]{1,}'));

$router->map('/news/:id/:catid/:page', array('controller' => 'news', 'action' => 'show'), array('id' => '[\d]{1,}', 'catid' => '[\d]{1,}', 'page' => '[\d]{1,}'));
$router->map('/newsrate/:id/:rate', array('controller' => 'news', 'action' => 'show'), array('id' => '[\d]{1,}', 'rate' => '[\d]{1,3}'));
$router->map('/news/comment/:id', array('controller' => 'news', 'action' => 'comment'), array('id' => '[\d]{1,}'));

$router->map('/newsarchiv/:catid', array('controller' => 'news', 'action' => 'index'), array('catid' => '[\d]{1,}'));
$router->map('/newsarchiv/:catid/:page', array('controller' => 'news', 'action' => 'index'), array('catid' => '[\d]{1,}', 'page' => '[\d]{1,}'));
$router->map('/newsarchiv/:catid/:page/:order/:sort', array('controller' => 'news', 'action' => 'index'), 
		array('catid' => '[\d]{1,}', 'page' => '[\d]{1,}', 'order' => '[\w]{3,}', 'sort' => '(asc|desc)'
			
			));


$router->map('/newsarchiv/:catid/:perpage/:start/:end/:ii/:order/:sort/:q/:page', array('controller' => 'news', 'action' => 'index'), array(
    'catid' => '[\d]{1,}',
    'perpage' => '[\d]{1,}',
    'start' => '[\d]{1,}',
    'end' => '[\d]{1,}',
    'li' => '[\d]{1,}',
	'order' => '[a-z]{3,}',
    'sort' => '[a-z]{3,4}',
    'q' => '.+',
    'page' => '[\d]{1,}')
);


// Article Rules
$router->map('/article/?(index)?', array('controller' => 'article', 'action' => 'index'));
$router->map('/articles/?(index)?', array('controller' => 'article', 'action' => 'index'));
$router->map('/articles/tag/:tag', array('controller' => 'article', 'action' => 'index'), array('tag' => '.+'));
$router->map('/article/:id', array('controller' => 'article', 'action' => 'show'), array('id' => '[\d]{1,}'));
$router->map('/article/:id/:catid/:page', array('controller' => 'article', 'action' => 'show'), array('id' => '[\d]{1,}', 'catid' => '[\d]{1,}', 'page' => '[\d]{1,}'));
$router->map('/articlerate/:id/:rate', array('controller' => 'article', 'action' => 'show'), array('id' => '[\d]{1,}', 'rate' => '[\d]{1,3}'));
$router->map('/article/comment/:id', array('controller' => 'article', 'action' => 'comment'), array('id' => '[\d]{1,}'));


$router->map('/articles/:catid', array('controller' => 'article', 'action' => 'index'), array('catid' => '[\d]{1,}'));
$router->map('/articles/:catid/:page', array('controller' => 'article', 'action' => 'index'), array('catid' => '[\d]{1,}', 'page' => '[\d]{1,}'));
$router->map('/articles/:catid/:perpage/:start/:end/:ii/:sort/:q/:page', array('controller' => 'article', 'action' => 'index'), array(
    'catid' => '[\d]{1,}',
    'perpage' => '[\d]{1,}',
    'start' => '[\d]{1,}',
    'end' => '[\d]{1,}',
    'li' => '[\d]{1,}',
    'sort' => '[a-z]{1,}',
    'q' => '.+',
    'page' => '[\d]{1,}')
);

// Statische seiten
$router->map('/page/?(index)?', array('controller' => 'page', 'action' => 'index'));
$router->map('/page/:page', array('controller' => 'page', 'action' => 'index'), array('page' => '[\d]{1,}'));
$router->map('/page/:page/:rate/:site', array('controller' => 'page', 'action' => 'index'), array('site' => '[\w\_]+', 'page' => '[\d]{1,}', 'rate' => '[\d]{1,}'));



// Application Rules
$router->map('/apps/:req', 
	array('controller' => 'apps'), 
	array('req' => '(.*)')
	);


// Forum
$router->map('/forum/?(index)?', array('controller' => 'forum', 'action' => 'index'));

$router->map('/forum/newthread/:forumid', array('controller' => 'forum', 'action' => 'addthread'), array('forumid' => '[\d]{1,}'));
$router->map('/forum/newthread', array('controller' => 'forum', 'action' => 'addthread')); // POST
$router->map('/forum/upload', array('controller' => 'forum', 'action' => 'upload')); 
$router->map('/forum/upload/:do/:attachmentid', array('controller' => 'forum', 'action' => 'upload'), array('do' => 'remove', 'attachmentid' => '[\d]{1,}') ); 

$router->map('/forum/loadattachment/:attachmentid', array('controller' => 'forum', 'action' => 'getattachment'), 

	array('attachmentid' => '[0-9a-f]{8}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{12}')

); 

$router->map('/replythread/:threadid/:do/:postid', array('controller' => 'forum', 'action' => 'replythread'), 
array(
'threadid' => '\d{1,}', 'do' => 'reply|quote', 'postid' => '\d{1,}'
));
$router->map('/replythread/:threadid', array('controller' => 'forum', 'action' => 'replythread'), array('threadid' => '[\d]{1,}'));

$router->map('/forum/:forumid', array('controller' => 'forum', 'action' => 'index'), array('forumid' => '[\d]{1,}'));
$router->map('/forum/:forumname', array('controller' => 'forum', 'action' => 'index'), array('forumname' => '[\w\_]+'));

$router->map('/thread/:threadid', array('controller' => 'forum', 'action' => 'thread'), array('threadid' => '[\d]{1,}'));
$router->map('/thread/:threadid/:page', array('controller' => 'forum', 'action' => 'thread'), array('threadid' => '[\d]{1,}', 'page' => '[\d]{1,}'));


// Register
$router->map('/register/?(index)?', array('controller' => 'register', 'action' => 'index'));
$router->map('/register/verify/:key', array('controller' => 'register', 'action' => 'verify'), array('key' => '[a-zA-Z0-9]{12}'));


// Guestbook & User Guestbook
$router->map('/guestbook/?(index)?', array('controller' => 'guestbook', 'action' => 'index'));
$router->map('/guestbook/submit', array('controller' => 'guestbook', 'action' => 'add'));
$router->map('/guestbook/:page', array('controller' => 'guestbook', 'action' => 'index'), array('page' => '[\d]{1,}') );
$router->map('/guestbook/:username', array('controller' => 'guestbook', 'action' => 'usergbook'), array('username' => '[\w\-_]{1,}') );
$router->map('/guestbook/submit/:username', array('controller' => 'guestbook', 'action' => 'addusergbook'), array('username' => '[\w\-_]{1,}') );
$router->map('/guestbook/:username/:page', array('controller' => 'guestbook', 'action' => 'usergbook'), array('username' => '[\w\-_]{1,}', 'page' => '[\d]{1,}') );

$router->map('/guestbook/:username/publish/:id', array('controller' => 'guestbook', 'action' => 'publish'), array('username' => '[\w\-_]{1,}', 'id' => '[\d]{1,}') );
$router->map('/guestbook/:username/remove/:id', array('controller' => 'guestbook', 'action' => 'remove'), array('username' => '[\w\-_]{1,}', 'id' => '[\d]{1,}') );

?>
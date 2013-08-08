<?php

require_once '/part/to/' . 'AppDotNetPHP/EZAppDotNet.php';

require_once '/part/to/' . 'runkeeper/vendor/autoload.php';
require_once '/part/to/' . 'runkeeper/lib/runkeeperAPI.class.php';

$app_scope        =  array(
	// 'email', // Access the user's email address // has no effect?
	// 'export', // Export all user data (shows a warning)
	 'files',
	// 'follow', // Follow and unfollow other users
	// 'messages', // Access the user's private messages
	// 'public_messages', // Access the user's messages
	// 'stream', // Read the user's personalized stream
	// 'update_profile', // Modify user parameters
	 'write_post', // Post on behalf of the user
);

$app = new EZAppDotNet();
$url = $app->getAuthUrl();

$_SESSION['path'] = 'fit/';

// check that the user is signed in
if ($app->getSession()) {

	try {
		$denied = $app->getUser();
	//	print " error - we were granted access without a token?!?\n";
	//	exit;
	}
	catch (AppDotNetException $e) { // catch revoked access and existing session // Safari 6 doesn't like
		if ($e->getCode()==401) {
			print " success (could not get access)\n";
		}
		else {
			throw $e;
		}
		$app->deleteSession();
		header('Location: .'); die;
	}

// otherwise prompt to sign in
} else {

echo '<div id=userblk>';

	echo '<a href="'.$url.'"><u>Sign in using App.net</u></a>';
	if (isset($_SESSION['rem'])) {
		echo 'Remember me <input type="checkbox" id="rem" value="1" checked/>';
	} else {
		echo 'Remember me <input type="checkbox" id="rem" value="2" />';
	}
	?>
	<script>
	document.getElementById('rem').onclick = function(e){
		if (document.getElementById('rem').value=='1') {
			window.location='?rem=2';
		} else {
			window.location='?rem=1';
		};
	}
	</script>
	<?php
}

if($app->getSession()) {

$rk = new runkeeperAPI('/part/to/runkeeper/config/rk-api.yml');

$rkToken = $rk->getRunkeeperToken($_REQUEST['code']);

if(!$rkToken) { // EZAppDotNetPHP getSession-type function call (?)
} else 
$_SESSION['runkeeperAPIAccessToken'] = $rk->readToken();

header('Location: /' . $_SESSION['path']);

}

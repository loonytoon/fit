<!DOCTYPE html>
<head>

<meta charset=utf-8>

<meta name="viewport" content="width=device-width">

<title>Fit &sect; lab.cdn.cx : lab &middot; /see/ /dee/ /en/ -dot- /see/ /eks/ :</title>

	<style>
		@import 'http://yui.yahooapis.com/pure/0.2.1/pure-min.css';
		@import '/f/ss-standard-optimized.css';
		body {
			padding: 0;
			margin: 0;
		}
		html {/*body*/
			height: 100%;
		}

		#userblk { background: rgba(255,255,255, .7); padding-left: 1em }

 @media screen and (min-width: 36em) {
   #userblk { padding-left: 0; position: absolute; right: 1em; top: 0; z-index: 53 }
 }

	</style>
</head>
<body>
<?php

// checking if the 'Remember me' checkbox was clicked
if (isset($_GET['rem'])) {
	session_start();
	if ($_GET['rem']=='1') {
		$_SESSION['rem']=1;
	} else {
		unset($_SESSION['rem']);
	}
	header('Location: .');
}

require_once '/path/to/' . 'AppDotNetPHP/EZAppDotNet.php';

require_once '/path/to/' . 'runkeeper/vendor/autoload.php';
require_once '/path/to/' . 'runkeeper/lib/runkeeperAPI.class.php';

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


	// get the current user as JSON
	$data = $app->getUserTokenInfo('me');

echo '<div id=userblk>';

	// accessing the user's name
	echo '<h3>'.$data['user']['name'].'</h3>';

	// accessing the user's avatar image // GIF ragged since server-side scale not available (yet)
	echo '<img height=48 style="border:2px solid #000;" src="'.$data['user']['avatar_image']['url'].'?h=48&amp;w=48" /><br>';

	echo '<u><a href="/signout.php">Sign out</a></u>';

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

?>
</div>

<h1>Do you feel healthy?<?php

if(!$app->getSession()) {
	echo '<span> &mdash; <a href="'.$url.'">Sign in using App.net</a>';
	echo ' and find out.</span>';
}

?></h1>

<?php
if($app->getSession()) {

$rk = new runkeeperAPI('/path/to/runkeeper/config/rk-api.yml');

echo 'RunKeeper:';

// EZAppDotNetPHP getSession (?)

if(!key_exists('runkeeperAPIAccessToken', $_SESSION)) {

echo ' <a href="';
echo $rk->connectRunkeeperButtonUrl();
echo '">connect</a>';

//print_r($rk);

} else {

echo ' ';

$rk->setRunkeeperToken($_SESSION['runkeeperAPIAccessToken']);

$profile_read = $rk->doRunkeeperRequest('Profile', 'Read');

echo '<a href="' . $profile_read->profile . '">' . $profile_read->name . '</a> ' . $profile_read->athlete_type;

echo '<pre>';

//print_r($profile_read);
echo $rk->api_last_error;

$user_read = $rk->doRunkeeperRequest('User', 'Read');

//print_r($user_read);
echo $rk->api_last_error;

//$weight_read = $rk->doRunkeeperRequest('Weight', 'Read', null, $user_read->weight);

$settings_read = $rk->doRunkeeperRequest('Settings', 'Read');

//echo print_r($weight_read, true);
//echo $settings_read->weight_units;

echo "\n";

//print_r($settings_read);
echo $rk->api_last_error;
//print_r($rk->api_request_log);

/* Do a "Read" request on "FitnessActivityFeed" interface => return all fields available for this Interface or false if request fails */
$rkActivities = $rk->doRunkeeperRequest('FitnessActivityFeed','Read');
if ($rkActivities) {
//print_r($rkActivities);
print_r($rkActivities->items[0]);

/*

climb (m)

distance (m)
duration (s)
total_distance (m)

*/

echo $rkActivities->size . " activities\n"; // The total number of fitness activities across all pages
}
else {
echo $rk->api_last_error;
//print_r($rk->api_request_log);
}

// Do something with a result from the previous request
/**/
// Do a "Read" request on "FitnessActivities" interface => return all fields available for this Interface or false if request fails
//$rkActivities = $rk->doRunkeeperRequest('FitnessActivity','Read',null,'/fitnessActivities/221748494');
$rkActivities = $rk->doRunkeeperRequest('FitnessActivitySummary','Read',null, $rkActivities->items[0]->uri);
if ($rkActivities) {
print_r($rkActivities);
}
else {
echo $rk->api_last_error;
//print_r($rk->api_request_log);
}
/**/

$record_read = $rk->doRunkeeperRequest('Records', 'Read'); // ,null,$user_read->records);

/*

climb (m)

total_distance (m) [ distance available in non-Summary ]
duration (s)

*/

print_r($record_read);
echo $rk->api_last_error;
//print_r($rk->api_request_log);

echo '</pre>';

}

?>

<form action='' class=pure-form1 method=post>
</form>

<?php

} else {

?>

<section
</section>

<?php
}
?>

<footer></footer>
</body>
</html>

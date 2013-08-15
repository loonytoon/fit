<?php
//change path to the location of your config file
require_once '../' . 'fitconfig.php';
?><!DOCTYPE html>
<html lang="en">
        <head>
                <meta charset="utf-8" />

                <!-- Always force latest IE rendering engine (even in intranet) & Chrome Frame
                Remove this if you use the .htaccess -->
                <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />

                <title>fit</title>
                <meta name="description" content="" />
                <meta name="author" content="ian" />

                <meta name="viewport" content="width=device-width; initial-scale=1.0" />

                <!-- Replace favicon.ico & apple-touch-icon.png in the root of your domain and delete these references -->
                <link rel="shortcut icon" href="/favicon.ico" />
                <link rel="apple-touch-icon" href="/apple-touch-icon.png" />

                <link rel="stylesheet" href="../thirdparty/css/foundation.css">

                <script src="../thirdparty/js/vendor/custom.modernizr.js"></script>
                 
				<link rel="stylesheet" href="css/app.css">

 			
				<style>
					@media screen and (min-width: 36em) {
						#userblk {
							padding-left: 0;
							position: absolute;
							right: 1em;
							top: 0;
							z-index: 53
						}
					}
				</style>
        </head>
 
<body class="dark">


<header>
	<div class="row">
   <div class="large-8 columns"> <h1>fit-app.net <span class="small-text">Do you feel healthy?</span></h1>
   
   
   </div>


<?php

function calculate_duration($duration)
{
	return $duration_string;
}

function activityToEnglish($activity)
{
	$activityMap = array(
    "Cycling2" => "cycling",
    "Walking" => "for a walk",
	"Running" => "for a run");
	
	if (array_key_exists($activity,$activityMap))
	{
		return $activityMap[$activity];
	}
	else
	{
		return strtolower($activity);
	}	
}


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

require_once APPDOT_PATH . 'AppDotNetPHP/EZAppDotNet.php';

require_once RK_PATH . 'runkeeper/vendor/autoload.php';
require_once RK_PATH . 'runkeeper/lib/runkeeperAPI.class.php';

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

$_SESSION['path'] = 'fitapp/';


echo '<div class="large-4 columns"><aside class="userinfo">';
// check that the user is signed in
if ($app->getSession()) {
	//echo "hello";
	try {
		$denied = $app->getUser();
		//print " error - we were granted access without a token?!?\n";
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



	// accessing the user's name
	echo '<h3>'.$data['user']['name'].'</h3>';

	// accessing the user's avatar image // GIF ragged since server-side scale not available (yet)
	echo '<img height=48 style="border:2px solid #000;" src="'.$data['user']['avatar_image']['url'].'?h=48&amp;w=48" /><br>';

	echo '<u><a href="/signout.php">Sign out</a></u>';

// otherwise prompt to sign in
} else {



	echo '<a href="'.$url.'"><u>Sign in using App.net</u></a>';
	if (isset($_SESSION['rem'])) {
		echo 'Remember me <input type="checkbox" id="rem" value="1" checked/>';
	} else {
		echo 'Remember me <input type="checkbox" id="rem" value="2" />';
	}
	?>
	<script>
		document.getElementById('rem').onclick = function(e) {
			if (document.getElementById('rem').value == '1') {
				window.location = '?rem=2';
			} else {
				window.location = '?rem=1';
			};
		}
	</script>
	<?php
	}
?>





</aside>
<aside class="userinfo">


<?php
if($app->getSession()) {

$rk = new runkeeperAPI(RK_API_YML);

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



?>

</aside>
</div>
</header>
<section class="row">
	

<?php
$rkActivities = $rk->doRunkeeperRequest('FitnessActivityFeed','Read');
$user_info = $rk->doRunkeeperRequest('User', 'Read');
if ($rkActivities) {
//print_r($rkActivities);
//print_r($rkActivities->items);

$distance_unit = "km";
$distance_convert = 0.001;
for ($i=0; $i < 9; $i++) { 
	
 $value = $rkActivities->items[$i];
	
	
	$da =new DateTime($value->start_time);
	//$value->start_time
	
	
	
	//
	?>
	
	<article class="activity">
		<div class="row">
					<div class="small-6 large-10 columns">
						<h4><?php echo $value -> type; ?> at <?php echo $value -> start_time; ?></h4>
						<span>Distance: <?php echo round($value -> total_distance * $distance_convert, 2);
							echo " ";
							echo $distance_unit;
						?></span> <span>Duration: <?php echo round($value->duration/60,1);?>minutes</span>
					</div>
					
					<div class="small-6 large-2 columns">
						<a href="javascript:void(0)" class="button round expand openPostBox" data-post-form-id="pf<?php echo $i; ?>">Post to adn</a>
					</div>
					
					</div>
						<!-- <div class="row">
						<div class="large-12 columns">-->
								<div class="row">
								<form class="postbox" id="pf<?php echo $i; ?>">
									<div class="small-6 large-10 columns">
									<label>Message</label><br/>
<textarea class="postContent">I went <?php echo activityToEnglish($value -> type); ?> on <?php echo $da->format("l jS F Y \a\\t g:ia")?>, it was <?php echo "".round($value -> total_distance * $distance_convert, 2)." ".$distance_unit;?> long and took <?php echo round($value->duration/60,1);?> minutes http://www.runkeeper.com/user/<?php echo $user_info -> userID; ?>/activity/<?php echo(str_ireplace("/fitnessActivities/", "", $value -> uri)); ?>
 #adnfitnesschallenge</textarea>
</div>
<div class="small-6 large-2 columns">
									<a href="javascript:void(0)" class="button expand round postToAdn">Post</a><br />
									<a href="javascript:void(0)" data-post-form-id="pf<?php echo $i; ?>" class="small button expand round secondary cancelPost">cancel</a>
									</div>
								</form>
								</div>
						<!--	</div>
						</div>-->

					
				</article>
				
	<?php
	}
	/*

	climb (m)

	distance (m)
	duration (s)
	total_distance (m)

	*/

	
	}
	else {
	echo $rk->api_last_error;
	//print_r($rk->api_request_log);
	}
?>

	
	
</section>

<footer></footer>


<?php

}
?>

<form action='' class=pure-form1 method=post>
</form>

<?php

} else {
?>

<section>
</section>

<?php
}
?>

	<script src="../thirdparty/js/vendor/jquery.js"></script>
		<script src="../thirdparty/js/vendor/appnet.2.js"></script>
		<script src="../thirdparty/js/foundation.min.js"></script>

		<script>
		 				$(document).foundation();
			var fit = window.fit || {};
			fit.user_token = "<?php echo $_SESSION["AppDotNetPHPAccessToken"]; ?>
				";

				function togglePostBox(id) {
				$("#"+id).toggle("slow");
				}

				$(document).on("click", "a.openPostBox", function() {

				var $this = $(this);
				togglePostBox($this.attr("data-post-form-id"))
				$this.toggleClass("secondary");
				$this.toggleClass("small");

				});

				$(document).on("click", "a.cancelPost", function() {
				console.log(this);
				var $this = $(this);
				togglePostBox($this.attr("data-post-form-id"))
				//console.log($this.parent().children('a.openPostBox'));
				var button = $this.parents(".activity").find('a.openPostBox');
				button.toggleClass("secondary");
				button.toggleClass("small");
				});

				if (fit.user_token != "") {

				$.appnet.authorize(fit.user_token);

				$(document).on("click", "a.postToAdn", function() {
				// $(this)
				var $this = $(this);
				var data = $this.parents("form").find(".postContent")[0].innerHTML;
				var promise2 = $.appnet.post.create({
				text : data
				});
				promise2.then(function(repsonse) {
				//console.log(response);

				togglePostBox($this.parent());
				var b = $this.parent().siblings('a.openPostBox')
				b.toggleClass("secondary");
				b.toggleClass("success");
				b.toggleClass("small");
				b[0].innerHTML = "Activity Posted";

				}, function(response) {
				console.log('Error!');
				});
				});

				/*	var apiurl = "https://alpha-api.app.net/stream/0/posts?access_token="+fit.access_token;

				/*
				*/

				}
				else
				{
				$("a.postToAdn").addClass("hide");
				$("a.openPostBox").addClass("is-disabled")
				}

		</script>
</body>
</html>

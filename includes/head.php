<? date_default_timezone_set('America/New_York');



$currentPage=$_SERVER['PHP_SELF'];

$url_prefix="test/";

$title_prefix="IGIS";



switch($currentPage) {

	case $url_prefix."/addguides.php":

		$page_title = "Add New Guides";

		break;

	case $url_prefix."/documents.php":

		$page_title = "Documents";

		break;

	case $url_prefix."/definepoints.php":

		$page_title = "Define Points";

		break;

	case $url_prefix."/defineregulartours.php":

		$page_title = "Define Regular Tours";

		break;

	case $url_prefix."/definetourtypes.php":

		$page_title = "Define Tour Types";

		break;

	case $url_prefix."/directory.php":

		$page_title = "Directory";

		break;

	case $url_prefix."/execboard.php":

		$page_title = "Exec Control Board";

		break;

	case $url_prefix."/failedlogin.php":

		$page_title = "Login Failure";

		break;

	case $url_prefix."/guide.php":

		$page_title = $firstname." ".$lastname; //these variables are defined on the page the head is included on

		break;

	case $url_prefix."/guideEdit.php":

		$page_title = "Edit ".$firstname." ".$lastname; //these variables are defined on the page the head is included on

		break;

	case $url_prefix."/home.php":

		$page_title = "";

		break;

	case $url_prefix."/manageguides.php":

		$page_title = "Manage Guides";

		break;

	case $url_prefix."/listservs.php":

		$page_title = "Manage Listservs";

		break;

	case $url_prefix."/managepoints.php":

		$page_title = "Manage Points";

		break;

	case $url_prefix."/manageprobieclasses.php":

		$page_title = "Manage Probie Classes";

		break;

	case $url_prefix."/managetourcredit.php":

		$page_title = "Manage Tour Credit";

		break;

	case $url_prefix."/managetourrequests.php":

		$page_title = "Manage Tour Requests";

		break;

	case $url_prefix."/tourrequest.php":

		$page_title = "Tour Request";

		break;

	case $url_prefix."/managetours.php":

		$page_title = "Manage Tours";

		break;

	case $url_prefix."/myigis.php":

		$page_title = "My IGIS";

		break;

	case $url_prefix."/ohattendance.php":

		$page_title = "OH Attendance";

		break;

	case $url_prefix."/signin.php":

		$page_title = "Sign In";

		break;

	case $url_prefix."/signups.php":

		$page_title = "Tour Signups";

		break;

	case $url_prefix."/tourcount.php":

		$page_title = "Tour Count List";

		break;

	case $url_prefix."/tourstats.php":

		$page_title = "Tour Statistics";

		break;

	case $url_prefix."/massaddtours.php":

			$page_title = "Quick Add Tours";

		break;

	default:

		$page_title = "(unknown page)";

		break;

}



if ($page_title!='') {

	$page_title=$title_prefix.' | '.$page_title;

} else {

	$page_title=$title_prefix;

}



?>

	<meta charset="utf-8">

	<meta http-equiv="X-UA-Compatible" content="IE=edge">

	<meta name="viewport" content="width=device-width, initial-scale=1">

	<meta name="description" content="">

	<meta name="author" content="">

	<link rel="shortcut icon" href="../../assets/ico/favicon.ico">



	<title><?echo $page_title?></title>



	<!-- JQuery (must go first because other things depend on it) -->

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>

	<!-- Bootstrap core CSS -->

	<link href="css/bootstrap.min.css" rel="stylesheet">

	<!-- Calendar Picker CSS -->

	<link href="css/datepicker3.css" rel="stylesheet">

	<!-- Calendar Picker Javascript -->

	<script src="js/bootstrap-datepicker.js"></script>



	<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->

	<!--[if lt IE 9]>

		<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>

		<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>

	<![endif]-->



	<!-- for some reason, this works best when placed after everything else (it can't be placed first): -->

	<script src="js/bootstrap.min.js"></script>



	<!--Bootstrap 3.2.0 Checkboxes Fix, from https://github.com/rokat/kalatheme/blob/7.x-3.x/css/tweaks.css -->

	<style>

		td> .radio input[type="radio"],

		td> .radio-inline input[type="radio"],

		td> .checkbox input[type="checkbox"],

		td> .checkbox-inline input[type="checkbox"]{

		  margin-left: 0px;

		  position:static;

		}

	</style>



	<script>

	$(function () {

		$(window).on('resize load', function() {

			$("#global-alert-div").css({"top": $(".navbar").height() + "px"});

			$('body').css({"padding-top": $(".navbar").height() + $("#global-alert-div").height() + "px",

							"padding-bottom": $(".footer").height()});

		});

	});



	//Google Analytics tracking:

  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){

  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),

  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)

  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');



  ga('create', 'UA-60489354-1', 'auto');

  ga('send', 'pageview');

	</script>

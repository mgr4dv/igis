<?

$home_url = "./home.php";

$directory_url = "./directory.php";

$guide_url = "./guide.php";

$signups_url = "./signups.php";

$ohpublic_url = "./ohpublic.php";

$myigis_url = "./myigis.php";

$documents_url = "./documents.php";

$logout_url = "./logout.php";



$managetours_url = "./managetours.php";

$managetourrequests_url = "./managetourrequests.php";

$managetourcredit_url = "./managetourcredit.php";

$defineregulartours_url = "./defineregulartours.php";

$definetourtypes_url = "./definetourtypes.php";

$massaddtours_url = "./massaddtours.php";


$ohschedule_url = "./ohschedule.php";

$ohattendance_url = "./ohattendance.php";

$managepoints_url = "./managepoints.php";

$definepoints_url = "./definepoints.php";



$manageguides_url = "./manageguides.php";

$manageprobieclasses_url = "./manageprobieclasses.php";

$addguides_url = "./addguides.php";

$execboard_url = "./execboard.php";



$tourcount_url="./tourcount.php";

$tourstats_url="./tourstats.php";

$emaillist_url = "./listservs.php";

$testout_url = "./testout.php";


$alertText = $igis_settings['global_alert'];

$alertColor = $igis_settings['global_alert_color'];

?>



<div class="navbar navbar-inverse navbar-fixed-top" role="navigation" style="box-shadow: 0px -5px 5px #888888">

	<div class="container">

		<div class="navbar-header">

			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navigationbar">

				<span class="sr-only">Toggle navigation</span>

				<span class="icon-bar"></span>

				<span class="icon-bar"></span>

				<span class="icon-bar"></span>

			</button>

			<a class="navbar-brand" href="<?echo $home_url?>"><img src="Rotunda_logo.png" alt="[logo]" style="display:inline" height=25 width=30>IGIS 3.0</a>

        </div>

		<div class="collapse navbar-collapse" id="navigationbar">

			<ul class="nav navbar-nav">

				<li <? if ($_SERVER['PHP_SELF']==$signups_url) echo 'class="active"'; ?> ><a href="<?echo $signups_url?>">Tour Signups</a></li>

				<li <? if ($_SERVER['PHP_SELF']==$ohpublic_url) echo 'class="active"'; ?> ><a href="<?echo $ohpublic_url?>">Oh Schedule</a></li>

				<li <? if ($_SERVER['PHP_SELF']==$directory_url || $_SERVER['PHP_SELF']==$guide_url) echo 'class="active"'; ?> ><a href="<?echo $directory_url?>">Directory</a></li>

				<li <? if ($_SERVER['PHP_SELF']==$myigis_url) echo 'class="active"'; ?> ><a href="<?echo $myigis_url?>">My IGIS</a></li>

				<li <? if ($_SERVER['PHP_SELF']==$documents_url) echo 'class="active"'; ?> ><a href="<?echo $documents_url?>">Documents</a></li>

				<? if ($is_exec) echo //any exec member can see people's tour counts and the statistics

				'<li class="dropdown">

					<a href="#" class="dropdown-toggle" data-toggle="dropdown">Exec<b class="caret"></b></a>

					<ul class="dropdown-menu">

						<li><a href="'.$tourcount_url.'">Tour Count List</a></li>

						<li class="divider"></li>

						<li><a href="'.$tourstats_url.'" id="test">Tour Statistics</a></li>

						<li class="divider"></li>

						<li><a href="'.$emaillist_url.'">Email Lists</a></li>

					</ul>

				</li>' ?>

				<? if ($is_scheduler || $is_techchair) echo //only the scheduler (or tech chair) can do things to the tour schedule

				'<li class="dropdown">

					<a href="#" class="dropdown-toggle" data-toggle="dropdown">Scheduling<b class="caret"></b></a>

					<ul class="dropdown-menu">

						<li><a href="'.$managetours_url.'">Manage Tours</a></li>

						<li><a href="'.$managetourrequests_url.'">Manage Tour Requests</a></li>

						<li class="divider"></li>

						<li><a href="'.$managetourcredit_url.'">Manage Tour Credit</a></li>

						<li class="divider"></li>

						<li><a href="'.$defineregulartours_url.'">Define Regular Tours</a></li>

						<li><a href="'.$definetourtypes_url.'">Define Tour Types</a></li>

						<li><a href="'.$massaddtours_url.'">Quick Add Tours</a></li>

					</ul>

				</li>' ?>

				<? if ($is_disciplinarian || $is_chair || $is_vicechair || $is_techchair) echo //only the disciplinarian, chair, or vice chair (or tech chair) can do points stuff

				'<li class="dropdown">

					<a href="#" class="dropdown-toggle" data-toggle="dropdown">Discipline<b class="caret"></b></a>

					<ul class="dropdown-menu">

						<li><a href="'.$ohschedule_url.'">Office Hour Schedule</a></li>

						<li><a href="'.$ohattendance_url.'">Office Hour Attendance</a></li>

						<li class="divider"></li>

						<li><a href="'.$managepoints_url.'">Manage Points</a></li>

						<li class="divider"></li>

						<li><a href="'.$definepoints_url.'">Define Point Values</a></li>

					</ul>

				</li>' ?>

				<? if ($is_chair || $is_techchair) echo //only the chair (or tech chair) can do membership (etc.) stuff

				'<li class="dropdown">

					<a href="#" class="dropdown-toggle" data-toggle="dropdown">UGS Management<b class="caret"></b></a>

					<ul class="dropdown-menu">

						<li><a href="'.$manageguides_url.'">Manage Guides</a></li>

						<li class="divider"></li>

						<li><a href="'.$manageprobieclasses_url.'">Manage Probie Classes</a></li>

						<li><a href="'.$addguides_url.'">Add New Guides</a></li>

						<li class="divider"></li>

						<li><a href="'.$execboard_url.'">Exec Control Board</a></li>

					</ul>

				</li>' ?>

				<? if (false) echo

				'<li><a href="#">Office Hour Sign-in</a></li>

				' ?>

			</ul>

			<ul class="nav navbar-nav navbar-right">

				<li><a href="<?echo $logout_url?>">Logout</a></li>

			</ul>

		</div><!--/.nav-collapse -->

    </div>

</div>

<div class="container" id="global-alert-div" style="min-height:10px; width:100%; position:fixed; z-index:100;">

<? if ($alertText!='') {

	echo '<div class="alert alert-'.$alertColor.'" role="alert" style="margin-bottom:0px">'.$alertText.'</div>';

}?>

</div>

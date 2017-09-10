<?
require_once("twitter_api/twitter_oauth/autoloader.php");
//require_once("twitter_api/twitter_oauth/src/TwitterOAuth.php");
//require_once("twitter_api/twitter_oauth/src/Config.php");

use Abraham\TwitterOAuth\TwitterOAuth;

$consumer_key = 'qd0c7O2D1QqL0qKSwfHt5Q5s1';
$consumer_secret = 'bLO7oYALXaQqGxeE3iVmXG4EACpSEzVwfzy3hUSDcmvdlPIHLZ';
$accesstoken = '202340844-0fSqlCOefVspkcyPeStUMlmORXolT2aBIPEtuDPs';
$accesstoken_secret = 'rAnU8Gzss2C8x0zdqffX4pGnixeofc5oMI2yH65HneTvb';
 
$connection = new TwitterOAuth($consumer_key, $consumer_secret, $accesstoken, $accesstoken_secret);
$content = $connection->get("statuses/home_timeline");

$userParameters = array(
    'screen_name' => 'LIFESynBio,SynBioBeta,synthaes,nysynbio,TeselaGen,Syn_Biology,SynBioHUB,SynBio1,synbio_itb,SynBioNews,ACSSynBio,q_syntheticbiol,synatom,iGEM,glowingplant,SynthBestiary,SyntheticBiolog,GenomeBiology,biobricks,SynBioCnsltg,synbiology,PolymChem,SynBioSoc,iGEMCalgary,sgi_dna,SynBio4all,PLOSSynbio,iGEMQueens,GenoCon2,genocad,MolSystBiol,UiOslo_iGEM,SYBHEL_Project,The4thDomain,UCSFSysSynBio,BioLogikLabs,CSBEd,bio_fiction,CUGEM,ManchesterGEM,SynBioWatch,ArsBiotechnica,Codagenix,GetSynBio,synenergene,UT_iGEM,bioSYNergy_dk,Syn_Bio,igemamsterdam,syntheticbiol,iGEMupoSevilla,synbiotic,SynthBioWorld,iGEMW,synbiosig,synbioproject,synberc,BBSRC,syn372,BioSysBio,Genopole,BactoBot',
);
$users = $connection->get("users/lookup", $userParameters);

$tableHead = '<th>#</th>
				<th>Handle</th>
				<th>Name</th>
				<th>Description</th>
				<th style="min-width:100px">Created</th>
				<th style="min-width:100px">Latest</th>
				<th>Num. Tweets</th>
				<th>Following</th>
				<th>Followers</th>
				<th>Favorites</th>
				<th>Website</th>';
$tableRows = '';
for ($i=0; $i<count($users); $i++) {
	$tableRows = $tableRows.'<tr>
		<td>'.($i+1).'</td>
		<td><a href="http://www.twitter.com/'.$users[$i]->screen_name.'" target="_blank">'.$users[$i]->screen_name.'</a></td>
		<td>'.$users[$i]->name.'</td>
		<td style="font-size: 8pt">'.$users[$i]->description.'</td>
		<td>'.date('Y-m-d', strtotime($users[$i]->created_at)).'</td>
		<td>'.date('Y-m-d', strtotime($users[$i]->status->created_at)).'</td>
		<td>'.$users[$i]->statuses_count.'</td>
		<td>'.$users[$i]->friends_count.'</td>
		<td>'.$users[$i]->followers_count.'</td>
		<td>'.$users[$i]->favourites_count.'</td>
		<td><a href="'.$users[$i]->url.'" target="_blank">[click]</a></td>
		</tr>';
}
?>

<!DOCTYPE html>
<html lang="en">

<!-- Header information for webpage (reused except for title) -->
	<head>
		<?
		include_once("includes/head.php");
		?>
	</head>
	
	<!-- Body of webpage (not reused, but reusable elements inside) -->
	<body>

		
		<div class="container">
			<div class="well">
				<!--<? echo print_r($users[0])?>
				<br>
				<br>
				Handle: <?echo $users[0]->screen_name?><br>
				Name: <?echo $users[0]->name?><br>
				Description: "<?echo $users[0]->description?>"<br>
				Created: <? echo date('M j, Y', strtotime($users[0]->created_at))?> -- Age: <?$age = date_diff(new DateTime($users[0]->created_at), new DateTime()); echo $age->y . " year(s), " . $age->m." month(s), and ".$age->d." day(s) (".$age->days . " days)";?><br>
				Latest: <? echo date('M j, Y', strtotime($users[0]->status->created_at))?> -- <?$tweet_age = date_diff(new DateTime($users[0]->status->created_at), new DateTime()); echo $tweet_age->y . " year(s), " . $tweet_age->m." month(s), and ".$tweet_age->d." day(s) (".$tweet_age->days . " days) ago";?><br>
				Tweets: <?echo $users[0]->statuses_count?><br>
				Following: <?echo $users[0]->friends_count?><br>
				Followers: <?echo $users[0]->followers_count?><br>
				Favorites: <?echo $users[0]->favourites_count?><br>
				Website: <?echo $users[0]->url?><br>-->
				<table class="table">
					<thead>
						<?echo $tableHead?>
					</thead>
					<tbody>
						<?echo $tableRows?>
					</tbody>
				</table>
			</div>
		</div>


	</body>
	
	<script>
		
	</script>
</html>
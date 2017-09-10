<?
date_default_timezone_set('America/New_York');
require_once("../authenticate.php");

$electionID = $_POST['electionID'];

$candidateIDs = mysqli_select($link, "SELECT candidate_id FROM elections_candidates WHERE election_id=".$electionID." ORDER BY candidate_id ASC");
$numCandidates = mysqli_num_rows($candidateIDs);
$candidateIDs = mysqli_fetch_array($candidateIDs);

$candidateGuideIDs = mysqli_select($link, "SELECT guide_id FROM elections_candidates WHERE election_id=".$electionID." ORDER BY candidate_id ASC");
$candidateGuideIDs = mysqli_fetch_array($candidateGuideIDs);

$candidateFirstNames = mysqli_select($link, "SELECT firstname FROM elections_candidates INNER JOIN guides ON elections_candidates.guide_id=guides.guide_id WHERE election_id=".$electionID." ORDER BY candidate_id ASC");
$candidateFirstNames = mysqli_fetch_array($candidateFirstNames);

$candidateLastNames = mysqli_select($link, "SELECT lastname FROM elections_candidates INNER JOIN guides ON elections_candidates.guide_id=guides.guide_id WHERE election_id=".$electionID." ORDER BY candidate_id ASC");
$candidateLastNames = mysqli_fetch_array($candidateLastNames);

$votes = mysqli_select($link, "SELECT * FROM elections_votes WHERE election_id=".$electionID);
$numVotes = mysqli_num_rows($votes);
$votes = mysqli_fetch_array($votes);

$voters = mysqli_select($link, "SELECT DISTINCT guide_id FROM elections_votes WHERE election_id=".$electionID);
$numVoters = mysqli_num_rows($voters);
$voters = mysqli_fetch_array($voters);

$round = 0;
$roundResults = array();
$candidateTotals = array();
$majority = false;
while (!$majority) {
	$round++;

	//pre-set all candidate totals to zero:
	for ($c=0; $c<$numCandidates; $c++) {
		for ($r=0; $r<$numCandidates; $r++) {
			$candidateTotals[$c][$r] = 0;
		}
	}

	//sort votes into candidate-ranking table (2D-array):
	for ($v=0; $v<$numVotes; $v++) {
		$cand = $votes[$v]['candidate_id'];
		$c = array_search($cand,$candidateIDs);
		$r = $votes[$v]['rank'];
		$candidateTotals[$c][$r]++;
	}

	//create vote table:
	$votesListForSortingAndRanking = array();
	for ($c=0; $c<$numCandidates; $c++) {
		$roundResults[$round][$c]['votes'] = $candidateTotals[$c][1];
		$votesListForSortingAndRanking[$c] = $candidateTotals[$c][1];
		$roundResults[$round][$c]['guideID'] = $candidateGuideIDs[$c];
		$roundResults[$round][$c]['firstname'] = $candidateFirstNames[$c];
		$roundResults[$round][$c]['lastname'] = $candidateLastNames[$c];
		$roundResults[$round][$c]['majority'] = 0;
		$roundResults[$round][$c]['resultingRank'] = 0;
	}

	//update vote table with ranks:
	$votesListForSortingAndRanking = rsort($votesListForSortingAndRanking);
	$firstPlaceInd = -1;
	for ($c=0; $c<$numCandidates; $c++) {
		$roundResults[$round][$c]['resultingRank'] = array_search($roundResults[$round][$c]['votes'], $votesListForSortingAndRanking);
		if ($roundResults[$round][$c]['resultingRank']==1) {
			$firstPlaceInd = $c;
		}
		if ($roundResults[$round][$c]['resultingRank']==$numCandidates) {
			$lastPlaceInd = $c;
		}
	}

	//check if #1-ranked candidate had a majority:
	if ($roundResults[$round][$firstPlaceInd]['votes']/$numVotes > 0.5) {
		$majority = true;
		//if so, update the vote table and break out of the loops:
		$roundResults[$round][$firstPlaceInd]['majority'] = 1;
		break;
	}
	if ($majority) break;


	//if no majority was found...
	//identify all guides who voted for the loser:
	$loserID = $candidateIDs[$lastPlaceInd];
	$loserVoters = array();
	for ($v=0; $v<$numVotes; $v++) {
		if ($votes[$v]['candidate_id']==$loserID && $votes[$v]['rank']==1) {
			array_push($loserVoters,$votes[$v]['guide_id']);
		}
	}

	//find all their votes and subtract one from the ranking (to turn the #2 vote into the #1 vote and remove the original #1 vote from relevance):

	//then repeat with the newly-update votes.
}

$out['numVotes'] = $numVotes;
$out['roundResults'] = $roundResults;

echo json_encode($out);
?>

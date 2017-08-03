<? date_default_timezone_set('America/New_York');

require_once("authenticate.php");
$constitutionURL = $igis_settings['constitution_url'];
$bylawsURL = $igis_settings['bylaws_url'];

?>
<!DOCTYPE html>
<html lang="en">

<!-- Header information for webpage (reused except for title) -->

	<head>

		<?
		include_once("includes/head.php");
		?>
		<style>
			.responsive-iframe-container {
				position: relative;
				padding-bottom: 56.25%;
				padding-top: 30px;
				height: 0;
				overflow: hidden;
			}

			.responsive-iframe-container iframe,
			.responsive-iframe-container object,
			.responsive-iframe-container embed {
				position: absolute;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
			}
		</style>


	</head>



<!-- Body of webpage (not reused, but reusable elements inside) -->

	<body style="padding-top:40px; padding-bottom: 60px">

		<? include_once("includes/nav.php") ?>

		<? include_once("includes/footer.php") ?>

		<div class="container">
			<div class="page-header" style="margin-top:0px">
				<h1>UGS Documents!</h1>
			</div>
			</br>
			<!-- <a href="/documents/HistoricalSlaveryTour.pdf">Slavery tour outline</a>
			</br>
			<a href="/documents/Dani_Bernstein_Historical_Outline.pdf">Link</a>
			</br>
			<a href="/documents/Sapna_Rao_Historical_Tour_Outline_2015.pdf">Link</a> -->
			<div class="col-md-3">
				<h4>Big Guiding Links</h4>
				<ul class="list-group">
					<a href="https://docs.google.com/a/virginia.edu/spreadsheets/d/1x4rFZoFXfrPB4y73d3iiBtHeKxtu-SSAYXvOgLrOYwg/edit?usp=sharing" target="_blank" class="list-group-item">Task Tracking</a>
					<a href="https://docs.google.com/spreadsheets/d/14PohO-HVAvfPdW1VWoKHwciZKN73hEQTE1_rx28n6sI/edit#gid=0" target="_blank" class="list-group-item">Attendance</a>
					<a href="https://docs.google.com/a/virginia.edu/forms/d/1mNWAmGdMMLPE0M9y96nW1cnCwat9IP-G82q3TXrDO-M/edit" target="_blank" class="list-group-item">Events</a>
					<a href="https://docs.google.com/document/d/1L8TiuMbI3OM3TRxYFhHpxUtG4rbP8kFfkNBBAeLjtHk/edit" target="_blank" class="list-group-item">Big Guiding Guide</a>
				</ul>
			</div>
			<div class="col-md-3">
				<h4>Historical Tour Outlines</h4>
				<ul class="list-group">
					<a href="/documents/Dani_Bernstein_Historical_Outline.pdf" target="_blank" class="list-group-item">Dani Bernstein's Historical Outline</a>
					<a href="/documents/Sapna_Rao_Historical_Tour_Outline_2015.pdf" target="_blank" class="list-group-item">Sapna Rao's Historical Outline</a>
          <a href="/documents/HistoricalSlaveryTour.pdf" target="_blank" class="list-group-item">Tori Travers' Slavery Tour Outline</a>
          <a href="/documents/MasterHAAOutline.pdf" target="_blank" class="list-group-item">HAA Outline</a>
          <a href="/documents/HistoryofWomenOutline2014.pdf" target="_blank" class="list-group-item">HOW Outline</a>
				</ul>
			</div>
			<div class="col-md-3">
				<h4>Probie Packet</h4>
				<ul class="list-group">
					<a href="/documents/Packet Part 1- Admissions.docx" target="_blank" class="list-group-item">Admissions</a>
					<a href="/documents/Packet Part 2- Jefferson.doc" target="_blank" class="list-group-item">Jefferson</a>
					<a href="/documents/Packet Part 3- Academical Village.docx" target="_blank" class="list-group-item">Academical Village</a>
					<a href="/documents/Packet Part 4- UVA in the 1800s.doc" target="_blank" class="list-group-item">UVA in the 1800s</a>
					<a href="/documents/Packet Part 5- UVA in the 1990s and 2000.doc" target="_blank" class="list-group-item">UVA in the 1900s and 2000s</a>
					<a href="/documents/Packet Part 6- Traditions.doc" target="_blank" class="list-group-item">Traditions</a>
					<a href="/documents/Packet Part 7- UGS Appendix.docx" target="_blank" class="list-group-item">Appendix</a>
				</ul>
			</div>
    <div class="col-md-3">
			<h4>Quiz Keys</h4>
			<ul class="list-group">
				<a href="/documents/Shoaibi_Quiz1AnswerKey.docx" target="_blank" class="list-group-item">Quiz Key 1</a>
				<a href="/documents/Shoaibi_Quiz2AnswerKey.docx" target="_blank" class="list-group-item">Quiz Key 2</a>
				<a href="/documents/Shoaibi_Quiz3Key.docx" target="_blank" class="list-group-item">Quiz Key 3</a>
				<a href="/documents/Shoaibi_Quiz4AnswerKey.docx" target="_blank" class="list-group-item">Quiz Key 4</a>
				<a href="/documents/Shoaibi_Quiz5AnswerKey.docx" target="_blank" class="list-group-item">Quiz Key 5</a>
			</ul>
		</div>
    </div>

		<div class="container">

				<div class="col-md-3">
					<h4>Other Outlines</h4>
					<ul class="list-group">
	          <a href="/documents/Garden_Tour_Outline.pdf" target="_blank" class="list-group-item">Garden Tour Outline</a>
	          <a href="/documents/KidsTourOutline.pdf" target="_blank" class="list-group-item">Kids Tour Outline</a>
						<a href="/documents/FirstYearToursSimple.pdf" target="_blank" class="list-group-item">First Year Tour Outline (simple)</a>
						<a href="/documents/FirstYearToursDetailed.pdf" target="_blank" class="list-group-item">First YearTour Outline (detailed)</a>
					</ul>
				</div>
			<div class="col-md-3">
				<h4>Governing Documents</h4>
				<ul class="list-group">
					<a href="<?echo $constitutionURL?>" target="_blank" class="list-group-item">UGS Constitution</a>
					<a href="<?echo $bylawsURL?>" target="_blank" class="list-group-item">UGS Bylaws</a>
				</ul>
			</div>
			<div class="col-md-3">
				<h4>Open IGIS</h4>
				<ul class="list-group">
					<a href="https://www.github.com/mgr4dv/IGIS" target="_blank" class="list-group-item">IGIS Source Code (Github)</a>
					<a href="/documents/uvaguide_members.ods" target="_blank" class="list-group-item">Tour Data</a>
				</ul>
			</div>
    </div>
		<div class="container">
		</br>
			Have more documents that Guides could benefit from? Email them to the tech chair (tech@uvaguides.org)
		</br>
		</div>

	</body>
</html>

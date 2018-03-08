<? date_default_timezone_set('America/New_York');
require_once("authenticate.php");

$permssion_level = 3;
include("permission.php");

$defaultNumRows = 30;


function newRow($rownum) {
	$tabOffset = ($rownum-1)*3;
	$computingID = '<input id="computingID'.$rownum.'" tabindex="'.($tabOffset+1).'" class="form-control" style="display:inline; width:120px"></input>';
	$firstname = '<input id="firstname'.$rownum.'" tabindex="'.($tabOffset+2).'" class="form-control" style="display:inline; width:200px"></input>';
	$lastname = '<input id="lastname'.$rownum.'" tabindex="'.($tabOffset+3).'" class="form-control" style="display:inline; width:200px"></input>';
	return '<tr>
				<td style="vertical-align:middle">'.$rownum.'</td>
				<td>'.$computingID.'</td>
				<td>'.$firstname.'</td>
				<td>'.$lastname.'</td>
			</tr>';
}

$probieclassoptions = '';
$probieclass_query = mysqli_query($link,"SELECT chair FROM probieclass ORDER BY date DESC"); 
while ($class=mysqli_fetch_array($probieclass_query)) {
	$probieclassoptions = $probieclassoptions.'<option value="'.$class[0].'">'.$class[0].'</option>';
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
	<body style="padding-top:40px; padding-bottom: 60px">

		<!-- Navigation bar across the top and footer across the bottom -->
		<?include_once("includes/nav.php")?>
		<?include_once("includes/footer.php")?>


		<div class="container">
			<h1>Add new Guides</h1>
			<div class="well">
				<div class="row">
					<div class="col-md-2"></div>
					<div class="col-md-8">
						<div class="panel panel-info">
							<div class="panel-heading">
								Guides to add:
							</div>
							<div class="panel-body">
								<table class="table">
									<thead>
										<tr>
											<th></th>
											<th>Computing ID</th>
											<th>First Name</th>
											<th>Last Name</th>
										</tr>
									</thead>
									<tbody id="newGuideRows">
										<?
										for ($i=1; $i<=$defaultNumRows; $i++) {
											echo newRow($i);
										}
										?>
									</tbody>
								</table>
								<button class="btn btn-info" onclick="addNewRow()">Add New Row</button>
								<span style="float:right">
									Add to probie class:
									<select id="probieclass" class="form-control" style="cursor:pointer; display:inline; width:150px; margin-right:5px">
										<?echo $probieclassoptions?>
									</select>
									<button class="btn btn-primary" onclick="addGuides()">Submit</button>
								</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		
	</body>
	
	<script>
		$(function () {
			
		});
		
		var numRowsWritten = <?echo $defaultNumRows?>;
		
		function addNewRow() {
			var offset = (numRowsWritten)*3;
			numRowsWritten++;
			var computingID = '<input id="computingID'+numRowsWritten+'" tabindex="'+(offset+1)+'" class="form-control" style="display:inline; width:120px"></input>';
			var firstname = '<input id="firstname'+numRowsWritten+'" tabindex="'+(offset+2)+'" class="form-control" style="display:inline; width:200px"></input>';
			var lastname = '<input id="lastname'+numRowsWritten+'" tabindex="'+(offset+3)+'" class="form-control" style="display:inline; width:200px"></input>';
			var rowToInsert = '<tr><td style="vertical-align:middle">'+numRowsWritten+'</td><td>'+computingID+'</td><td>'+firstname+'</td><td>'+lastname+'</td></tr>';
			$('#newGuideRows').append(rowToInsert);
		}
		
		function addGuides() {
			var computingIDs = '';
			var firstnames = '';
			var lastnames = '';
			for (i=1; i<=numRowsWritten; i++) {
				//add to the list only if all three parts are filled in
				if ($('#computingID'+i).val()!='' && $('#firstname'+i).val()!='' && lastnames+$('#lastname'+i).val()!='') {
					computingIDs = computingIDs+$('#computingID'+i).val()+',';
					firstnames = firstnames+$('#firstname'+i).val()+',';
					lastnames = lastnames+$('#lastname'+i).val()+',';
				}
			}
			
			computingIDs = trimCommas(computingIDs);
			firstnames = trimCommas(firstnames);
			lastnames = trimCommas(lastnames);
			probieclass = $('#probieclass').val();
			
			//alert("Computing IDs: "+computingIDs+"\nFirst Names: "+firstnames+"\nLast Names: "+lastnames+"\n\nProbie Class: "+probieclass); //for debugging
			
			$.post("functions/addnewguides.php",{
					computingIDs:computingIDs,
					firstnames:firstnames,
					lastnames:lastnames,
					probieclass:probieclass,
				}, function(data) {
					//alert(data); //for debugging
					data = JSON.parse(data);
					if (data.error!='') {
						alert(data.error); //Show any error that occurs
					} else {
						location.reload();
					}
				});
		}
		
		function trimCommas(str) {
			//Remove all duplicate commas:
			for (i=str.length-1; i>=1; i--) {
				if (str[i]==',' && str[i-1]==',') {
					//alert('Found two commas in a row!'); //for debugging
					str = str.slice(0,i)+str.slice(i+1,str.length);
				}
			}
			//Remove final comma:
			if (str[str.length-1]==',') {
				str = str.slice(0,str.length-1);
			}
			return str;
		}
	</script>
</html>

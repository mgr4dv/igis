<? date_default_timezone_set('America/New_York');
require_once("authenticate.php");
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
			form {
				margin: 0 auto;
				width: 800px;
			}
			.addform{
				text-align: center;
			}
		</style>
	</head>

<!-- Body of webpage (not reused, but reusable elements inside) -->
		<?include_once("includes/nav.php")?>
		<?include_once("includes/footer.php")?>
		<div class="addform">
			<textarea id="tourinfo" style="height:500px;width:800px" type="text" name="fname" size="10" line></textarea><br>
			<button id="newToursSubmitButton" type="button" class="btn btn-primary" data-dismiss="modal">Schedule Tours</button>
		</div>

		</body>

	<script>

			$('#newToursSubmitButton').click(function() {
				var tourinfoarray = tourinfo.value.split("\n");
				for(i = 0; i < tourinfoarray.length; i += 5){
					var timeDateArray = tourinfoarray[i].split(" ");
					var date = "" + timeDateArray[2] + "-" + getMonthFromString(timeDateArray[1]) + "-" + timeDateArray[0];
					if (timeDateArray[3] === "8:30"){
						var time = "9:15 AM";
					} else if(timeDateArray[3] === "10:15"){
						var time = "11:00 AM";
					} else if (timeDateArray[3] === "01:15" || timeDateArray[3] === "02:00"){
						var time = "2:00 PM";
					}

					var notes = tourinfoarray[i+1];
					var numTourists = parseInt(tourinfoarray[i+3]);
					var numSlots;

					if(numTourists < 30){
						numSlots = 1;
					} else {
						numSlots = Math.floor(numTourists/25) + 1;
					}
					newTour(date,time,7,numSlots,notes);
				}

			});

			function getMonthFromString(mon){
			   return new Date(Date.parse(mon +" 1, 2012")).getMonth()+1;
			};

			function newTour(date,time,type,numSlots,notes) {
			$.post("functions/changetour.php",{
					newTour:1,
					deleteTour:0,
					tourID:0,
					date:date,
					time:time,
					type:type,
					numSlots:numSlots,
					notes:notes
				}, function(data) {
					//alert(data); //for debugging
					data = JSON.parse(data);
					if (data.error!='') {
						alert(data.error); //Show any errors that occur.
					} else {
						$('#alertMsg').html('<b>Successfully added the '+data.timeStr+' '+data.abbrev+' on '+data.dateStr+'.</b>');
						$('#successAlert').show();
					}
				});
		}
	</script>

</html>

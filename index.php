<?php
date_default_timezone_set('Europe/Brussels');
require_once('request.php');

$ini = parse_ini_file('settings.ini', TRUE);

$user = 'jbelien';
$req = request($ini['oauth'], 'https://api.twitter.com/1.1/users/show.json', array('screen_name' => $user));
//var_dump($req);

$tw_count = array(); $fo_count = array(); $fr_count = array();
$dataset = array();

$dblink = new MySQLi($ini['mysql']['host'], $ini['mysql']['username'], $ini['mysql']['passwd'], $ini['mysql']['dbname']);
if ($dblink->connect_error) trigger_error('Erreur de connexion : '.$dblink->connect_error);
$q = $dblink->query("SELECT * FROM `check` WHERE `UserId` = ".$req->id." ORDER BY `Datetime` ASC") or trigger_error($dblink->error);
while ($r = $q->fetch_assoc()) {
	$timestamp = intval(strtotime($r['Datetime']));
	$tw_count[$timestamp * 1000] = intval($r['TweetsCount']);
	$fo_count[$timestamp * 1000] = intval($r['FollowersCount']);
	$fr_count[$timestamp * 1000] = intval($r['FriendsCount']);

	$dataset[$timestamp] = $r;
}
$q->free();
$dblink->close();

krsort($dataset);
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<title></title>
		<meta name="description" content="">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="css/bootstrap.min.css">
		<link rel="stylesheet" href="css/font-awesome.min.css">
		<link rel="stylesheet" href="css/style.css">
	</head>
	<body>
		<div id="wrap">
			<div class="container">
				<div class="page-header">
					<h1><i class="fa fa-twitter"></i> Twitter Statistics for <a href="https://twitter.com/<?php echo $user; ?>">@<?php echo $user; ?></a></h1>
				</div>
				<?php
				echo '<div class="row"><div class="col-sm-1">Name</div><div class="col-sm-11">'.$req->name.'</div></div>';
				echo '<div class="row"><div class="col-sm-1">Location</div><div class="col-sm-11">'.$req->location.'</div></div>';
				echo '<div class="row"><div class="col-sm-1">Since</div><div class="col-sm-11">'.date('j F Y', strtotime($req->created_at)).'</div></div>';

				echo '<div class="row">';
					echo '<div class="col-sm-4"><h2>Tweets</h2><div id="chart1" style="height:200px;"></div></div>';
					echo '<div class="col-sm-4"><h2>Followers</h2><div id="chart2" style="height:200px;"></div></div>';
					echo '<div class="col-sm-4"><h2>Friends</h2><div id="chart3" style="height:200px;"></div></div>';
				echo '</div>';

				echo '<table class="table table-striped table-condensed" style="margin-top:20px;">';
				echo '<thead>';
					echo '<tr>';
						echo '<th>Date</th>';
						echo '<th>Tweets</th>';
						echo '<th>Followers</th>';
						echo '<th>Friends</th>';
					echo '</tr>';
				echo '</thead>';
				echo '<tbody>';
				$keys = array_keys($dataset); $i = 0; $count = count($dataset);
				foreach ($dataset as $t => $d) {
					$diff1 = array(); $diff2 = array();

					echo '<tr>';
					echo '<td>'.date('j F Y H:i', $t).'</td>';
					echo '<td>'.$d['TweetsCount'].'</td>';
					echo '<td>'.$d['FollowersCount'].'</td>';
					echo '<td>';
						echo $d['FriendsCount'];
						if (($i+1) < $count) {
							$k = $keys[$i+1];
							$prev = $dataset[$k];

							$u1 = explode(',', $d['Friends']);
							$u2 = explode(',', $prev['Friends']);

							$diff1 = array_diff($u1, $u2);
							if (!empty($diff1)) {
								$req = request($ini['oauth'], 'https://api.twitter.com/1.1/users/lookup.json', array('user_id' => implode(',', $diff1)), 'GET');
								echo '<div>'; foreach($req as $r) { echo '<span class="label label-success"><a href="https://twitter.com/'.$r->screen_name.'" style="color:#fff;">@'.$r->screen_name.'</a></span> '; } echo '</div>';
							}
							$diff2 = array_diff($u2, $u1);
							if (!empty($diff2)) {
								$req = request($ini['oauth'], 'https://api.twitter.com/1.1/users/lookup.json', array('user_id' => implode(',', $diff2)), 'GET');
								echo '<div>'; foreach($req as $r) { echo '<span class="label label-danger"><a href="https://twitter.com/'.$r->screen_name.'" style="color:#fff;">@'.$r->screen_name.'</a></span> '; } echo '</div>';
							}
						}
					echo '</td>';
					echo '</tr>';
					$i++;
				}
				echo '</tbody>';
				echo '</table>';
				?>
			</div>
		</div>
		<footer>
			<div class="container"><span class="text-muted">&copy; <?php echo (date('Y') > 2014 ? '2014-'.date('y') : date('Y')); ?> J.Beli&euml;n</span></div>
		</footer>

		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
		<script>window.jQuery || document.write('<script src="js/jquery-1.10.2.min.js"><\/script>')</script>
		<script src="js/bootstrap.min.js"></script>
		<script src="js/jquery.flot.min.js"></script>
		<script src="js/jquery.flot.time.min.js"></script>
		<script type="text/javascript">
			$(function() {
				var d1 = <?php echo json_encode(array_map(function($key, $value) { return array($key, $value); },array_keys($tw_count),array_values($tw_count))); ?>;
				var d2 = <?php echo json_encode(array_map(function($key, $value) { return array($key, $value); },array_keys($fo_count),array_values($fo_count))); ?>;
				var d3 = <?php echo json_encode(array_map(function($key, $value) { return array($key, $value); },array_keys($fr_count),array_values($fr_count))); ?>;

				$.plot("#chart1", [ { data: d1, color: 0 } ], { xaxis: { mode: "time" }, yaxis: { tickDecimals: 0 } });
				$.plot("#chart2", [ { data: d2, color: 1 } ], { color: "#f6f6f6", xaxis: { mode: "time" }, yaxis: { tickDecimals: 0 } });
				$.plot("#chart3", [ { data: d3, color: 4 } ], { color: "#f6f6f6", xaxis: { mode: "time" }, yaxis: { tickDecimals: 0 } });
			});
		</script>
	</body>
</html>

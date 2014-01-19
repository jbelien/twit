<?php
require_once('request.php');

$ini = parse_ini_file('settings.ini', TRUE);

//$r = request($ini['oauth'], 'https://api.twitter.com/1.1/users/show.json', array('screen_name' => 'jbelien'));
//var_dump($r);
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
					<h1><i class="fa fa-twitter"></i> Twitter Statistics of <a href="https://twitter.com/jbelien">@jbelien</a></h1>
				</div>
				<?php
				$params = array(
					'screen_name' => 'jbelien'
				);

				$req1 = request($ini['oauth'], 'https://api.twitter.com/1.1/users/show.json', $params);
				//var_dump($req);
				echo '<div class="row"><div class="col-sm-2">Name</div><div class="col-sm-10">'.$req1->name.'</div></div>';
				echo '<div class="row"><div class="col-sm-2">Location</div><div class="col-sm-10">'.$req1->location.'</div></div>';
				echo '<div class="row"><div class="col-sm-2">Since</div><div class="col-sm-10">'.date('j F Y', strtotime($req1->created_at)).'</div></div>';
				echo '<h2 class="text-info">'.date('j F Y').'</h2>';
				echo '<div class="row"><div class="col-sm-2">Followers</div><div class="col-sm-10">'.$req1->followers_count.'</div></div>';
				echo '<div class="row"><div class="col-sm-2">Friends</div><div class="col-sm-10">'.$req1->friends_count.'</div></div>';
				echo '<div class="row"><div class="col-sm-2">Tweets</div><div class="col-sm-10">'.$req1->statuses_count.'</div></div>';

				echo '<div class="row">';
					$req2 = request($ini['oauth'], 'https://api.twitter.com/1.1/followers/ids.json', $params);
					echo '<div class="col-sm-6">';
						echo '<h3>Followers ('.$req1->followers_count.')</h3>';
						$ids = array_chunk($req2->ids, 100);
						echo '<table class="table table-striped table-condensed" style="font-size:0.8em;">';
						$c = count($ids);
						for ($i = 0; $i < $c; $i++) {
							$_req = request($ini['oauth'], 'https://api.twitter.com/1.1/users/lookup.json', array('user_id' => implode(',', $ids[$i])), 'GET');
							foreach($_req as $u) {
								echo '<tr>';
								echo '<td><a href="https://twitter.com/'.$u->screen_name.'">@'.$u->screen_name.'</a></td>';
								echo '<td>'.$u->name.'</td>';
								echo '<td>'.$u->location.'</td>';
								echo '<td>'.date('j F Y', strtotime($u->created_at)).'</td>';
								echo '</tr>';
							}
						}
						echo '</table>';
					echo '</div>';
					$req = request($ini['oauth'], 'https://api.twitter.com/1.1/friends/ids.json', $params);
					echo '<div class="col-sm-6">';
						echo '<h3>Friends ('.$req1->friends_count.')</h3>';
					echo '</div>';
				echo '</div>';
				?>
			</div>
		</div>
		<footer>
			<div class="container"><span class="text-muted">&copy; <?php echo (date('Y') > 2014 ? '2014-'.date('y') : date('Y')); ?> J.Beli&euml;n</span></div>
		</footer>

		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
		<script>window.jQuery || document.write('<script src="js/vendor/jquery-1.10.2.min.js"><\/script>')</script>
		<script src="js/bootstrap.min.js"></script>
	</body>
</html>

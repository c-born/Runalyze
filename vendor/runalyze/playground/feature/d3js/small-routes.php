<?php

use Runalyze\Model\Route;
use League\Geotools\Geohash\Geohash;

$LOAD_JS = false;
require_once '../../bootstrap.php';

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$size = isset($_GET['size']) ? (int)$_GET['size'] : 100;
$stepsize = isset($_GET['stepsize']) ? (int)$_GET['stepsize'] : 10;
?>

<script src="http://d3js.org/d3.v2.min.js?2.10.0"></script>

<style>
.route {
	display: inline-block;
	margin: 10px;
	width: <?php echo $size; ?>px;
	height: <?php echo $size; ?>px;
}
.line {
	fill: none;
	stroke: steelblue;
	stroke-width: 1.5px;
}
</style>

<script type="text/javascript">
var size = <?php echo $size; ?>;
</script>

<?php
$Statement = DB::getInstance()->query(
	'SELECT `id`, `geohashes`, `max`, `min` FROM `'.PREFIX.'route`
	WHERE `geohashes` IS NOT NULL AND `geohashes` != ""
	ORDER BY `id` DESC LIMIT '.$limit
);

while ($Data = $Statement->fetch()) {
	$Route = new Route\Entity($Data);
	$Loop = new Route\Loop($Route);
	$Loop->setStepSize($stepsize);
	$Path = array();

	while (!$Loop->isAtEnd()) {
		$Loop->nextStep();

		$Coordinate = (new Geohash())->decode($Loop->geohash())->getCoordinate();

		if (abs($Coordinate->getLatitude()) > 1e-5 || abs($Coordinate->getLongitude()) > 1e-5) {
			$Path[] = array('y' => $Coordinate->getLatitude(), 'x' => $Coordinate->getLongitude());
		}
	}

	$Min = (new Geohash())->decode($Route->get(Route\Entity::MIN))->getCoordinate();
	$Max = (new Geohash())->decode($Route->get(Route\Entity::MAX))->getCoordinate();

	$MinLat = $Min->getLatitude();
	$MaxLat = $Max->getLatitude();
	$MinLng = $Min->getLongitude();
	$MaxLng = $Max->getLongitude();
	$Diff = abs($MaxLat - $MinLat) - abs($MaxLng - $MinLng)*cos(deg2rad($MaxLat));

	if ($Diff > 0) {
		$MaxLng += $Diff/2;
		$MinLng -= $Diff/2;
	} else {
		$MaxLat += -$Diff/2;
		$MinLat -= -$Diff/2;
	}

	if (!empty($Path)) {
		echo '<div id="route-'.$Route->id().'" class="route" title="'.htmlspecialchars($Route->name()).'"></div>';
		echo '<script type="text/javascript">';
		echo 'var y = d3.scale.linear().domain(['.$MinLat.', '.$MaxLat.']).range([size, 0]);';
		echo 'var x = d3.scale.linear().domain(['.$MinLng.', '.$MaxLng.']).range([0, size]);';
		echo 'var line = d3.svg.line().interpolate(\'monotone\').x(function(d) { return x(d.x); }).y(function(d) { return y(d.y); });';
		echo 'var data = '.json_encode($Path).';';
		echo 'd3.select("#route-'.$Route->id().'").append("svg").datum(data).attr("width", size).attr("height", size).append("path").attr("class", "line").attr("d", line);';
		echo '</script>';
	}
}
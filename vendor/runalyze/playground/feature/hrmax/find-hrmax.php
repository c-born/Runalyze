<?php
require_once '../../bootstrap.php';

use Runalyze\Activity\Duration;

$months = isset($_GET['months']) ? (int)$_GET['months'] : 3;

$sportStatement = DB::getInstance()->query(
	'SELECT
		`id`,
		`name`
	FROM `'.PREFIX.'sport`'
);

while ($s = $sportStatement->fetch()) {
	$where = isset($_GET['id']) ? '`id`="'.(int)$_GET['id'].'"' : ('`s` > 1800 AND `sportid`='.$s['id'].' AND `act`.`time` >= UNIX_TIMESTAMP(CURRENT_DATE - INTERVAL '.$months.' MONTH)');

	$Statement = DB::getInstance()->query(
		'SELECT
			`act`.`id`,
			`act`.`time` as `timestamp`,
			`act`.`distance` as `km`,
			`act`.`s`,
			`act`.`pulse_max`,
			`act`.`pulse_avg`,
			`track`.`time`,
			`track`.`distance`,
			`track`.`heartrate`
		FROM `'.PREFIX.'training` AS `act`
		LEFT JOIN `'.PREFIX.'trackdata` AS `track` ON `track`.`activityid` = `act`.`id`
		WHERE '.$where.' AND `act`.`accountid` > -1 AND `track`.`heartrate` IS NOT NULL AND `track`.`heartrate` != ""
		ORDER BY `pulse_max` DESC
		LIMIT 1'
	);

	if ($data = $Statement->fetch()) {
		echo '<strong>'.$s['name'].': '.$data['pulse_max'].'</strong>';
		echo ' ('.date('d.m.Y', $data['timestamp']).', '.$data['km'].'k in '.Duration::format($data['s']).' at &oslash; '.$data['pulse_avg'].'bpm)<br/>';
	}
}

<?php 

$cities = DatabaseHelper::getInstance()->getCities();
$citiesMapper = array();
foreach ($cities as $city) {
	$citiesMapper[$city['Id']] = $city['Name'];
}

?><html>
<head>
<title></title>
<style type="text/css">

#rides {
	text-align: left;
	width: 100%;
}

#rides tr {
	border-bottom: 3px solid black;	
}

#rides th {
	font-weight: bold;
	padding: 5px 3px;
}

tr.even {
	background: #E6E6FA;
}

</style>
</head>
<body>
<h1><?php echo _('Thanks for showing interest!')?></h1>
<p><?php echo sprintf(_('%d new rides were found for you:'), count($this->rides))?></p>
<table id="rides">
	<tr>
		<th id="resultsFrom"><?php echo _('From')?></th>
		<th id="resultsTo"><?php echo _('To')?></th>
		<th id="resultsIn"><?php echo _('In')?></th>
		<th id="resultsOut"><?php echo _('Out')?></th>
		<th id="resultsContact"><?php echo _('Name')?></th>
		<th id="resultsEmail"><?php echo _('Email')?></th>
		<th id="resultsPhone"><?php echo _('Phone')?></th>
		<th id="resultsComment"><?php echo _('Comment')?></th>
	</tr>
<?php
$i = 0; 
foreach ($this->rides as $ride): 
?>
	<tr class="<?php echo (++$i % 2 == 1) ? 'odd' : 'even'?>">
		<td><?php echo $citiesMapper[$ride['SrcCityId']] . (Utils::isEmptyString($ride['SrcLocation']) ? '' : ', ' . $ride['SrcLocation'])?></td>
		<td><?php echo $citiesMapper[$ride['DestCityId']] . (Utils::isEmptyString($ride['DestLocation']) ? '' : ', ' . $ride['DestLocation'])?></td>
		<td><?php echo Utils::FormatTime($ride['TimeMorning'])?></td>
		<td><?php echo Utils::FormatTime($ride['TimeEvening'])?></td>
		<td><?php echo $ride['Name']?></td>
		<td><a href="mailto:<?php echo Utils::buildEmail($ride['Email'])?>?subject=Carpool"><?php echo $ride['Email']?></a></td>
		<td><?php echo $ride['Phone']?></td>
		<td><?php echo $ride['Comment']?></td>
	</tr>
<?php endforeach; ?>
</table>
</body>
</html>

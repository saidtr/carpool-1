<html>
<head><title></title></head>
<body>
<h1><?php echo _('Thanks for showing interest!')?></h1>
<p><?php echo sprintf(_('%d new rides were found for you!'), count($this->rides))?></p>
<table>
<?php foreach ($this->rides as $ride): ?>
<tr>
	<td>Baqa-Jatt, bla</td>
	<td>Ramat Gan, Checkpoint NBX</td>
	<td>05:00</td>
	<td>Differs</td>
	<td>asd</td>
	<td><a href="mailto:sdasd@itay.pk?subject=Carpool">sdasd</a>
	</td>
	<td></td>
	<td></td>
</tr>
<?php endforeach; ?>
</table>
</body>
</html>

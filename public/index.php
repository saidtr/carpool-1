<?php 

include "env.php";
include APP_PATH . "/Bootstrap.php";

$db = DatabaseHelper::getInstance();

AuthHandler::putUserToken();

$currentRegion = RegionManager::getInstance()->getCurrentRegionId();
$availableDestCities = $db->getAvailableCities('Dest', $currentRegion);
$availableSrcCities = $db->getAvailableCities('Src', $currentRegion);

$displayDest = (getConfiguration('mode.single.dest', 0) == 0);

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" type="text/css" href="css/reset-fonts.css">
<link rel="stylesheet" type="text/css" href="css/common.css">
<?php if (LocaleManager::getInstance()->isRtl()):?>
<link rel="stylesheet" type="text/css" href="css/common_rtl.css">
<?php endif;?>
<title>Carpool</title>
</head>
<body>
<div id="bd">
<?php echo View_Navbar::buildNavbar()?>
<?php echo View_Header::render(null, _('The colleagues, listed below, may be able to provide a ride both to and from the listed locations'))?>
<div id="content">
	<div id="searchFormHolder">
		<form id="searchForm" action="xhr/SearchRides.php">
			<fieldset>
			<legend></legend>
			<dl>
				<dd>
					<label for="wantTo"><?php echo _('Show me')?>&nbsp;</label>
					<select id="wantTo" name="wantTo">
						<option value="<?php echo STATUS_DONT_CARE?>" selected="selected"><?php echo _('Everything')?></option>
						<option value="<?php echo STATUS_OFFERED ?>"><?php echo _('Available rides')?></option>
						<option value="<?php echo STATUS_LOOKING ?>"><?php echo _('Looking for a ride')?></option>
						<option value="<?php echo STATUS_SHARING ?>"><?php echo _('Sharing a ride')?></option>
					</select>
				</dd>
				<dd>
					<label for="srcCity"><?php echo _('From')?>&nbsp;</label>
					<select id="srcCity" name="srcCity">
						<option value="<?php echo LOCATION_DONT_CARE ?>"><?php echo _('Everywhere')?></option> 
						<?php foreach($availableSrcCities as $city):?>
						<option value="<?php echo $city['Id']?>"><?php echo htmlspecialchars($city['Name'])?></option>
						<?php endforeach; ?>
					</select>
				</dd>
				<?php if ($displayDest): ?>
				<dd>
					<label for="destCity"><?php echo _('To')?>&nbsp;</label>
					<select id="destCity" name="destCity">
						<option value="<?php echo LOCATION_DONT_CARE ?>"><?php echo _('Everywhere')?></option>
						<?php foreach($availableDestCities as $city):?>
						<option value="<?php echo $city['Id']?>"><?php echo htmlspecialchars($city['Name'])?></option>
						<?php endforeach; ?>
					</select>
				</dd>	
				<?php endif; ?>
				<!-- 
				<dd>
					<label for="srcCityFilter"><?php echo _('From')?>&nbsp;</label>
					<input type="text" id="srcCityFilter" name="srcCityFilter" />
				</dd>
				<?php if ($displayDest): ?>
				<dd>
					<label for="destCityFilter"><?php echo _('To')?>&nbsp;</label>
					<input type="text" id="destCityFilter" name="destCityFilter" />
				</dd>
				<?php endif; ?>
				 -->
				<dd class="hidden">
					<input type="submit"/>
				</dd>	
				<dd>
					<p id="loadingNotice"></p>
				</dd>		
			</dl>
			</fieldset>
		</form>
		<form id="regionSelectorForm">
			<dl>
    			<dd>
        			<label for="regionSelector"><?php echo _('Region')?>&nbsp;</label>
        			<select id="regionSelector" name="regionSelector">
        			<?php foreach (RegionManager::getInstance()->getRegions() as $regionId => $region): ?>
        				<option value="<?php echo $regionId ?>" <?php echo ($regionId == $currentRegion) ? 'selected="selected"' : ''?>><?php echo _($region['Abbrev'])?></option>
        			<?php endforeach; ?>					
        			</select>
    			</dd>
    			<dd class="hidden">
    				<input type="submit"/>
    			</dd>
			</dl>	
		</form>
	</div>
	<div class="clearFloat"></div>
	<div id="results">
		<table id="resultsTable">
			<tr>
				<th id="resultsWhat"><?php echo _('I\'m')?></th>
				<th id="resultsFrom"><?php echo _('From')?></th>
				<?php if ($displayDest): ?>
				<th id="resultsTo"><?php echo _('To')?></th>
				<?php endif; ?>
				<th id="resultsIn"><?php echo _('In')?></th>
				<th id="resultsOut"><?php echo _('Out')?></th>
				<th id="resultsContact"><?php echo _('Name')?></th>
				<th id="resultsEmail"><?php echo _('Email')?></th>
				<th id="resultsPhone"><?php echo _('Phone')?></th>
				<th id="resultsComment"><?php echo _('Comment')?></th>
			</tr>
		</table>
		<p id="resultsMessage"></p>
	</div>
</div>
</div>
<?php 
View_Php_To_Js::putVariable('cities', $db->getCities($currentRegion));
View_Php_To_Js::putConstant('DEFAULT_DOMAIN', getConfiguration('default.domain'));
View_Php_To_Js::putConstant('APP_NAME', _(getConfiguration('app.name')));
View_Php_To_Js::putConstant('DISPLAY_DEST', $displayDest ? '1' : '0');
View_Php_To_Js::putTranslations(
    array(
    	'Sorry, no results found.', 
    	'Sorry, something went wrong. Request could not be completed.',
        'N/A',
        'Differs',
        'Show interest',
        'Loading...',
        'Could not add ride',
        'Thanks for showing interest! You will notified about new rides.',
        'Providing',
        'Looking',
        'Sharing'
    )
);
echo View_Php_To_Js::render();
?>
<script type="text/javascript" src="lib/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="lib/form/jquery.form.min.js"></script>
<script type="text/javascript" src="js/utils.js"></script>
<script type="text/javascript" src="js/filter.js"></script>
<script type="text/javascript" src="js/index.js"></script>
</body>
</html>
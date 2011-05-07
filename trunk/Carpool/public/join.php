<?php 

include "env.php";
include APP_PATH . "/Bootstrap.php";

// TODO: Change password

AuthHandler::putUserToken();

$db = DatabaseHelper::getInstance();

$contact = AuthHandler::getLoggedInUser();

// Is the user logged; in some cases, user might be logged even if there is no ride
$isLogged = false;
// True if the user is logged and registered to a ride
$hasRide = false;
// True if the user has ride and the ride is active
$isActive = false;

// In single destination mode, hide the "to" fields
$displayDest = (getConfiguration('mode.single.dest', 0) == 0);
// In "domain users" mode, only internal mail addresses are allowed
$domainUsersMode = (getConfiguration('mode.domain.users', 0) == 1);
// In LDAP authentication, the email cannot be changed
$canUpdateEmail = (AuthHandler::getAuthMode() != AuthHandler::AUTH_MODE_LDAP);

if ($contact) {
    $isLogged = true;
    
	extract($contact, EXTR_PREFIX_ALL, 'contact');
    if ($domainUsersMode) {
        // Trim the domain part
        $contact_Email = substr($contact_Email, 0, strpos($contact_Email, '@'));
    }
	
	$rideData = $db->getRideProvidedByContactId($contact_Id);
	
	if ($rideData !== false) { 	
	    $hasRide = true;
        extract($rideData, EXTR_PREFIX_ALL, 'ride');
        $isActive = ($ride_Active == RIDE_ACTIVE);
	}
}

$defaultSrcCity       = getConfiguration('default.src.city');
$defaultSrcLocation   = getConfiguration('default.src.loc');
$defaultDestCity      = getConfiguration('default.dest.city');
$defaultDestLocation  = getConfiguration('default.dest.loc');

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" type="text/css" href="css/reset-fonts.css">
<link rel="stylesheet" type="text/css" href="lib/ac/jquery.autocomplete.css">
<link rel="stylesheet" type="text/css" href="css/common.css">
<?php if (LocaleManager::getInstance()->isRtl()):?>
<link rel="stylesheet" type="text/css" href="css/common_rtl.css">
<?php endif;?>
<title>Carpool</title>
</head>
<body>
<div id="bd">
<?php echo View_Navbar::buildNavbar()?>
<?php
$header = ($hasRide) ? _("Hello again, ") . htmlspecialchars($contact_Name) : _("Join the carpool.");
echo View_Header::render($header); 
?>
<div id="content">
	<div>
	<form id="addRideForm" action="#" method="post" onsubmit="return false;"> 
		<p id="formMessage"></p>
		<fieldset>
			<legend>I want to...</legend>
			<ul class="radioSelectorHolder">
    			<li><input type="radio" name="wantTo" <?php if (!isset($ride_Status) || (isset($ride_Status) && $ride_Status == STATUS_LOOKING)) echo 'checked="checked"' ?> value="<?php echo STATUS_LOOKING ?>" /><?php echo _('I want to join a ride')?></li>
    			<li><input type="radio" name="wantTo" <?php if (isset($ride_Status) && $ride_Status == STATUS_OFFERED) echo 'checked="checked"' ?> value="<?php echo STATUS_OFFERED ?>" /><?php echo _('I want to provide a ride')?></li>
			</ul>
		</fieldset>
		<fieldset>
			<legend>Ride details</legend>
			<dl>
				<dd class="mandatory">
					<label for="srcCity"><?php echo _('Coming from city')?></label>
					<input class="textInput" id="srcCity" name="srcCity" type="text" size=16 value="" />
				</dd>
				<dd class="optional">
					<label for="srcLocation"><?php echo _('Location')?></label>
					<input id="srcLocation" name="srcLocation" type="text" size=30 value="<?php echo (isset($ride_SrcLocation) ? $ride_SrcLocation : $defaultSrcLocation) ?>" />
				</dd>
			</dl>
			<div class="clearFloat"></div>
			<input id="srcCityId" name="srcCityId" type="hidden" value="<?php echo (isset($ride_SrcCityId) ? $ride_SrcCityId : ($defaultSrcCity ? $defaultSrcCity : LOCATION_NOT_FOUND)) ?>"/>
			<?php if ($displayDest): ?>
			<dl>
				<dd class="mandatory">
					<label for="destCity"><?php echo _('To')?></label>
					<input class="textInput" id="destCity" name="destCity" type="text" size=16 value=""/>
				</dd>
				<dd class="optional">
					<label for="destLocation"><?php echo _('Location')?></label>
					<input id="destLocation" name="destLocation" type="text" size=30 value="<?php echo (isset($ride_destLocation) ? $ride_destLocation : $defaultDestLocation) ?>"/>
				</dd>
			</dl>
			<input id="destCityId" name="destCityId" type="hidden" value="<?php echo (isset($ride_DestCityId) ? $ride_DestCityId : ($defaultDestCity ? $defaultDestCity : LOCATION_NOT_FOUND)) ?>"/>
			<?php endif; ?>
			<div class="clearFloat"></div>
			<dl>
				<dd class="optional">
					<label for="timeMorning"><?php echo _('Coming')?></label>
					<select id="timeMorning" name="timeMorning">
						<option selected="selected" value="<?php echo TIME_DIFFERS ?>"><?php echo _('Not always the same time')?></option>
						<?php for($i = 5; $i <= 10; ++$i): ?>
						<option value="<?php echo $i?>" <?php if (isset($ride_TimeMorning) && $ride_TimeMorning == $i) echo 'selected="selected"'?>><?php echo Utils::FormatTime($i) ?></option>
						<?php endfor; ?>
						<option value="<?php echo TIME_IRRELEVANT ?>" <?php if (isset($ride_TimeMorning) && $ride_TimeMorning == TIME_IRRELEVANT) echo 'selected="selected"'?>><?php echo _('Irrelevant')?></option>
					</select>
				</dd>			
				<dd class="optional">
					<label for="timeEvening"><?php echo _('Leaving')?></label>
					<select id="timeEvening" name="timeEvening">
						<option value="<?php echo TIME_DIFFERS ?>"><?php echo _('Not always the same time')?></option>
					    <?php for($i = 15; $i <= 22; ++$i): ?>
						<option value="<?php echo $i?>" <?php if (isset($ride_TimeEvening) && $ride_TimeEvening == $i) echo 'selected="selected"'?>><?php echo Utils::FormatTime($i) ?></option>
						<?php endfor; ?>
						<option value="<?php echo TIME_IRRELEVANT ?>" <?php if (isset($ride_TimeEvening) && $ride_TimeEvening == TIME_IRRELEVANT) echo 'selected="selected"'?>><?php echo _('Irrelevant')?></option>
					</select>
				</dd>			
			</dl>
			<div class="clearFloat"></div>			
		</fieldset>
		<fieldset>
			<legend>Contact details</legend>
			<dl class="noFloat">
				<dd class="mandatory">
					<label for="name"><?php echo _('Name')?></label>
					<input class="textInput" id="name" name="name" type="text" size=30 value="<?php echo (isset($contact_Name) ? $contact_Name : '')?>" />
				</dd>
				<dd class="mandatory">
					<label for="email"><?php echo _('Email')?></label>
					<input class="textInput" id="email" name="email" type="text" size=20 value="<?php echo (isset($contact_Email) ? $contact_Email : '')?>" <?php if (!$canUpdateEmail) echo 'readonly'?> />
					<?php 
					
					if ($domainUsersMode) {
					    echo '@' . getConfiguration('default.domain');
					}
					if (!$canUpdateEmail) {
					    echo '<p class="description">' . _('Authentication policy does not allow you to change email account.') . '</p>';   
					} else if ($domainUsersMode) {
					    echo '<p class="description">' . _('Please use your company email, without the domain suffix.') . '</p>';
					}
					?>
				</dd>
				<?php if (AuthHandler::getAuthMode() == AuthHandler::AUTH_MODE_PASS): ?>
				<dd class="mandatory">
					<label for="passw1"><?php echo _('Password')?></label>
					<input class="textInput" id="passw1" name="passw1" type="password" size=20 value="" />
				</dd>
				<dd class="mandatory">
					<label for="passw2"><?php echo _('Confirm password')?></label>
					<input class="textInput" id="passw2" name="passw2" type="password" size=20 value="" />
				</dd>												
				<?php endif; ?>
				<dd>
					<label for="phone"><?php echo _('Phone')?></label>
					<input class="textInput" id="phone" name="phone" type="text" size=10 value="<?php echo (isset($contact_Phone) ? $contact_Phone : '')?>" />
				</dd>				
				<dd>
					<label for="comment"><?php echo _('Comments')?></label>
					<textarea id="comment" name="comment" rows=2 cols=40><?php echo (isset($ride_Comment) ? $ride_Comment : '')?></textarea>
				</dd>
				<dd>				
					<label for="notify"><?php echo _('Notify me by mail about new rides that may be relevant to me')?>
						<input type="checkbox" id="notify" name="notify" value="1" <?php if (isset($ride_Notify) && $ride_Notify !== '0') echo 'checked="checked"'; ?> >
					</label>
				</dd>
			</dl>
		</fieldset>	
		<fieldset>
			<legend><?php echo _('Submit')?></legend>
			<input type="submit" value="<?php echo ($hasRide) ? _('Update') : _('Go!') ?>" />
			<?php if ($hasRide): ?>
			<input type="button" id="deleteButton" value="<?php echo _('Delete')?>!" />
			<input type="button" id="activateToggleButton" value="<?php echo ($isActive ? _("Deactivate") : _("Activate"))?>" />
			<?php endif; ?>
			<span id="submitStatus"></span>
		</fieldset>
	</form>
	</div>
</div>
</div>
<script type="text/javascript" src="lib/jquery-1.5.2.min.js"></script>
<script type="text/javascript" src="lib/packed/join.pack.js"></script>
<?php
View_Php_To_Js::putTranslations(array(
	'Are you sure you want to completely, fully, eternally delete your ride?',
	'An error occured.',
	'Could not add ride',
	'Could not update ride',
	'Sorry, something went wrong.',
	'Congrats! You broke everything!',
    'Working...'
)); 
View_Php_To_Js::putVariable('cities', $db->getCities());
echo View_Php_To_Js::render();

?>
<script type="text/javascript" src="js/utils.js"></script>
<script type="text/javascript" src="js/join.js"></script>
</body>
</html>
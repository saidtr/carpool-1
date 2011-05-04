<?php 

$authUrl = Utils::buildLocalUrl('auth.php', array('c' => $this->contact['Id'], 'i' => $this->contact['Identifier']));
 
?><style>
#content p { font-size: large }
</style>
<h1><?php printf(_('Thanks, %s'), htmlspecialchars($this->contact['Name'])) ?>!</h1>
<div id="content">
<p><?php echo _('You sucssfully joined the carpool.') ?></p>
<?php if (AuthHandler::getAuthMode() == AuthHandler::AUTH_MODE_TOKEN): ?>
    <p><?php echo _('You can always update or delete your account by browsing to the following link')?>:</p>
    <p id="authLink"><a href="<?php echo htmlspecialchars($authUrl) ?>"><?php echo htmlspecialchars($authUrl) ?></a></p>
    <p><?php echo _('To use it, just paste the exact link to your browser address bar and hit "Enter".')?></p>
<?php else: ?>
<p><?php printf(_('You can always use "<a href="%s">My Profile</a>" page to update or delete your account any time in the future.'), Utils::buildLocalUrl('join.php'))?></p>
<?php endif; ?>
<p><?php echo _('Unless you ask for it, you will never get any more emails from this site.') ?></p>
<p><?php echo _('Thanks') ?>,<br/><?php printf('The %s team', _(getConfiguration('app.name'))) ?></p>
</div>

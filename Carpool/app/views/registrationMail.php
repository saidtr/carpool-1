<?php 

$authUrl = Utils::buildLocalUrl('auth.php', array('c' => $this->contact['Id'], 'i' => $this->contact['Identifier']));
 
?><style>
#content p { font-size: large }
</style>
<h1><?php printf(_('Thanks, %s'), htmlspecialchars($this->contact['Name'])) ?>!</h1>
<div id="content">
<p><?php echo _('You sucssfully joined the carpool.') ?></p>
<p><?php printf(_('You can always update or delete your account by browsing to %s'), '<a href="' . htmlspecialchars($authUrl) . '">' . htmlspecialchars($authUrl) . '</a>') ?></p>
<p><?php echo _('Unless you ask for it, you will never get any more emails from this site.') ?></p>
<p><?php echo _('Thanks') ?>,<br/><?php printf('The %s team', _(getConfiguration('app.name'))) ?></p>
</div>

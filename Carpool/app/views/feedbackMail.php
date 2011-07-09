<h1>New <?php echo getConfiguration('app.name') ?> Feedback: <?php echo $this->wantTo ?></h1>
<div id="content">
<p><?php echo nl2br(htmlspecialchars($this->content)) ?></p>
<?php if (isset($this->email) && !Utils::isEmptyString($this->email)): ?>
<p><b>Submitter mail:&nbsp;</b><?php echo htmlspecialchars($this->email) ?></p>
<?php endif; ?>
</div>

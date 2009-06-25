<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en">
<head>
	<title>Anfiniti RPG<?php isset($title) ? $this->escape(" - $title") : false; ?></title>
	<?php foreach ($styleSheets AS $style) { ?>
		<link rel="stylesheet" type="text/css" href="<?php $this->escape($style) ?>" />
	<?php } ?>
	<?php if (!empty($inlineCss)) { ?>
		<style type="text/css">
		<?php echo $inlineCss ?>
		</style>
	<?php } ?>
	<?php foreach ($scriptFiles AS $file) { ?>
		<script type="text/javascript" src="<?php $this->escape($file) ?>"></script>
	<?php } ?>
	<?php if (!empty($inlineScript)) { ?>
		<script type="text/javascript">
		<?php echo $inlineScript ?>
		</script>
	<?php } ?>
	<script type="text/javascript">
	
	var rpg = {};
	
	YUI().use('node', function(Y)
	{
		rpg.NavManager = function()
		{
			this.currentId = '';
			this.timeout   = 0;
			this.navIds    = [];
			
			this.init = function()
			{
				Y.get('#nav').queryAll('a').each(function(el) {
					if (el.get('id').substr(0, 4) === 'nav_') {
						this.addHoverTab(el);
					}
				}, this);
			};
			
			this.addHoverTab = function(el)
			{
				el.on('mouseover', function() { this.start(el); }, this);
				el.on('mouseout', function() { this.cancel(el); }, this);
				this.navIds.push(el.get('id'));
			};
			
			this.start = function(el)
			{
				if (!this.timeout) {
					this.timeout   = Y.later(500, this, this.show);
					this.currentId = el.get('id');
				}
			};
			
			this.show = function()
			{
				for (var i = 0, l = this.navIds.length; i < l; i++) {
					Y.get('#sub' + this.navIds[i]).setStyle('display', 'none');
					Y.get('#' + this.navIds[i]).removeClass('current');
				}
				
				Y.get('#sub' + this.currentId).setStyle('display', '');
				Y.get('#' + this.currentId).addClass('current');
			};
			
			this.cancel = function()
			{
				if (this.timeout) {
					this.timeout.cancel()
					this.timeout = false;
				}
			};
		};
		
		Y.on('domready', function() {
			rpg.navmanager = new rpg.NavManager();
			rpg.navmanager.init();
		});
	});
	
	</script>
</head>
<body>

<div id="top">
	<div class="toplinks">
		<a href="#">Logged in as <strong>Indigo</strong></a>
		<a href="<?php echo RPG::url('login/logout/' . md5(rand())); ?>">Logout</a>
	</div>
	<h1>Anfiniti RPG</h1>
</div>

<div id="nav">
	<?php foreach ($navigation AS $navId => $navEntry) { ?>
		<a <?php echo $navEntry['current'] ? 'class="current"' : '' ?> id="nav_<?php echo $navId ?>" href="<?php echo RPG::url($navEntry['url']) ?>"><?php echo $navEntry['text'] ?></a>
	<?php } ?>
</div>

<?php foreach ($subNavigation AS $subNavId => $subNav) { ?>
<div id="subnav_<?php echo $subNavId ?>" class="subnav" <?php echo $subNav['current'] ? '' : 'style="display:none"' ?>>
	<?php foreach ($subNav['entries'] AS $subNavUrl => $subNavEntry) { ?>
		<a <?php echo $subNavEntry['current'] ? 'class="current"' : '' ?> href="<?php echo RPG::url($subNavUrl) ?>"><?php echo $subNavEntry['text'] ?></a>
	<?php } ?>
</div>
<?php } ?>

<div id="main">

	<div id="navbits">
	
	</div>
	
	<div id="content">
	<?php echo $content ?>
	</div>
	
	<div id="footer">
	
	</div>

</div>

</body>
</html>
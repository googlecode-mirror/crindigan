<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
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
</head>
<body>

<div id="top">
	<div class="toplinks">
	<?php if (RPG::user()->isLoggedIn()) { ?>
		<a href="#">Logged in as <strong><?php $this->escape(RPG::user()->name) ?></strong></a>
		<a href="<?php echo RPG::url('auth/logout/' . md5(rand())); ?>">Logout</a>
	<?php } else { ?>
		<form action="<?php echo RPG::url('auth/login') ?>" method="post">
		<input type="text" name="username" id="login_username" size="12" value="Username" title="Username" />
		<input type="password" name="password" id="login_password" size="12" value="Password" title="Password" />
		<!--<label for="login_remember">Remember Me </label>--><input type="checkbox" name="remember" id="login_remember" value="1" title="Remember me" />
		<input type="submit" value="Log in" />
		</form>
		<!-- <a href="<?php echo RPG::url('auth/register') ?>">Register</a> -->
	<?php } ?>
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

	<?php if (isset($title)) { ?>
	<h2 id="pagetitle">&raquo; <?php $this->escape($title) ?></h2>
	<?php } ?>
	
	<div id="content">
	<?php echo $content ?>
	</div>
	
	<div id="footer">
	
	</div>

</div>

</body>
</html>
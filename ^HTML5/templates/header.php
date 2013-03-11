<!DOCTYPE HTML>
<html>
	<head>
		<title><?php html($_title); ?></title>
		<base href="<?php html($_siteURL); ?>">
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
<?php foreach($_meta as $k => $v){ ?>
		<meta name="<?php html($k)?>" content="<?php html($v)?>">
<?php }
      foreach($_script as $v){ ?>
		<script type="text/javascript" src="<?php html($v)?>"></script>
<?php }
      foreach($_link as $v){ ?>
		<link rel="<?php html($v['rel'])?>" type="<?php html($v['type'])?>" href="<?php html($v['href'])?>" title="<?php html($v['title'])?>">
<?php }
      echo $_head;
?>
	</head>
	<body>
		<header>
			<h1 id="logo"><span><?php html($_siteName)?></span></h1>
			<div id="login"><?php
if ($_login_id) {
		echo 'Hello, '.$_loginname
			.'. (<a href="'.$_logoutURL.'">Logout</a> | '
			.'<a href="'.CONT.'Account/edit" title="Your Account Settings">Account</a>)'
			;
} else {
		echo '(<a id="_loginLink" href="'.$_loginURL.'?_Lbl=Back&amp;_URI='.urlencode($_SERVER["REQUEST_URI"]).'">Login</a>)'
			.'<script type="text/javascript">document.getElementById(\'_loginLink\').href += encodeURIComponent(location.hash);</script>';
}
			?></div>
			<nav>
				<a href="/" title="Home Page">Home</a>
				<a href="/Home/Contact" title="Contact">Contact</a>
<?php if(G::$S && G::$S->Login && G::$S->roleTest('Admin')){ ?>
				<a href="<?php echo CONT;?>Admin">Admin</a>
<?php } ?>
			</nav>
			<div class="clear"></div>
		</header>

<?php G::$V->render('subheader');

if (0 < $v = count($a = G::msg())) { ?>
		<details id="msg" open="open">
			<summary><?php echo $v;?> Messages:</summary>
			<ul>
<?php foreach($a as $v){ ?>
				<li class="<?php echo $v[1]; ?>"><?php echo $v[0]; ?></li>
<?php } ?>
			</ul>
		</details>
<?php } ?>

		<section id="body">

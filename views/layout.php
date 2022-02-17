<!doctype html>
<!-- default layout -->
<html lang="<?=SYSTEM_LANGUAGE?>">
	<head itemscope itemtype="http://schema.org/WPHeader">
		<meta charset="utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<title itemprop="headline"><?=Elf::get_data('title')?Elf::$_data['title']:Elf::lang()->item('title')?></title>
		<meta itemprop="description" name="description" content="<?=Elf::get_data('description')?Elf::$_data['description']:Elf::lang()->item('description')?>" />
		<meta itemprop="keywords" name="keywords" content="<?=Elf::get_data('keywords')?Elf::$_data['keywords']:Elf::lang()->item('keywords')?>" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		
		<link href="//use.fontawesome.com/releases/v5.5.0/css/all.css" rel="stylesheet" />

		<?php if (defined('YANDEX_VERIFICATION') && YANDEX_VERIFICATION):?>
		<meta name="yandex-verification" content="<?=YANDEX_VERIFICATION?>" />
		<?php endif;?>
		<?php if (defined('GOOGLE_VERIFICATION') && GOOGLE_VERIFICATION):?>
		<meta name="google-site-verification" content="<?=GOOGLE_VERIFICATION?>" />
		<?php endif;?>
		<link rel="stylesheet" href="{site_url}css/elf.css" />		
<!-- module css files -->

		<script src="{site_url}js/jquery.js"></script>
		<script src="{site_url}js/main.js"></script>
<!-- module js files -->

		<link rel="shortcut icon" href="/favicon.png" />
		<?php if (Elf::get_data('canonical')):?>
		<link rel="canonical" href="{site_url}<% canonical %>" />
		<?php else:?>
		<% pagination.seo %>
		<?php endif;?>
		<% catalog.seo %>

		<?php if (defined('YANDEX_COUNTER') && YANDEX_COUNTER):?>	
		<!-- Yandex.Metrika counter -->
		<script>
		   (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
		   m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
		   (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

		   ym(<?=YANDEX_COUNTER?>, "init", {
				clickmap:true,
				trackLinks:true,
				accurateTrackBounce:true
		   });
		</script>
		<!-- /Yandex.Metrika counter -->
		<?php endif;?>

		<?php if (defined('GOOGLE_TAG_MANAGER') && GOOGLE_TAG_MANAGER):?>	
		<!-- Google Tag Manager -->
		<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
		new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
		j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
		'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
		})(window,document,'script','dataLayer','<?=GOOGLE_TAG_MANAGER?>');</script>
		<!-- End Google Tag Manager -->
		<?php endif;?>
	</head>
	<body>
	<div id="modal"></div>
	<div id="wait-window"><i class="fas fa-circle-notch fa-spin fa-5x fa-fw"></i></div>
	<% content %>
	<?php if (!empty(Elf::$_data['preloadialog'])) {
			echo Elf::$_data['preloadialog'];
			unset(Elf::$_data['preloadialog']);
			Elf::session()->set('flashdata');
		}
	?>
	</body>
</html>
<!doctype html>
<!-- admin lay -->
<html lang="<?=SYSTEM_LANGUAGE?>">
	<head>
		<meta charset="utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<title><?=!empty(Elf::$_data['title'])?Elf::$_data['title']:Elf::lang()->item('title')?></title>
		<meta name="description" content="<?=!empty(Elf::$_data['description'])?Elf::$_data['description']:Elf::lang()->item('description')?>" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />

		<link href="//use.fontawesome.com/releases/v5.5.0/css/all.css" rel="stylesheet" />
		<link rel="stylesheet" href="{site_url}css/elf.css" />
		<link rel="stylesheet" href="{site_url}css/elf_admin.css" />
		<link rel="stylesheet" href="{site_url}css/elf_uploader.css" />
<!-- module css files -->

		<script src="{site_url}js/jquery.js"></script>
		<script src="{site_url}js/elf_uploader.js"></script>
		<script src="{site_url}js/elf_forms.js"></script>
		<script src="{site_url}js/main.js"></script>
<!-- module js files -->

		<link rel="shortcut icon" href="/favicon.png" />
	</head>
	<body class="admin">
	<script src="{site_url}js/calendar.js"></script>
	<div id="modal"></div>
	<div id="wait-window"><i class="fas fa-circle-notch fa-spin fa-5x fa-fw"></i></div>
	<div class="adm-top-panel"></div>
	<div class="flex">
		<?=Elf::load_template('admin/menu')?>
		<div class="adm-content">
		<% content %>
		</div>
	</div>
	<div class="cntr copyright">&copy; 2010-<?=date('Y')?> Webstab</div>
	<?php if (!empty(Elf::$_data['preloadialog'])) {
			echo Elf::$_data['preloadialog'];
			unset(Elf::$_data['preloadialog']);
			Elf::session()->set('flashdata');
		}
	?>
	</body>
</html>

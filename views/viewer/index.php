<?php
	$params = Elf::$_data['params'];
?>
<h2 class="adm-tlt"><?=!empty($params['title'])?$params['title']:'System viewer page title'?></h2>
<h5>
	<?php if (isset($params['edit_lnk'])): if (!empty($params['edit_lnk']['request']) && ($params['edit_lnk']['request'] == 'json')):?>
	<a href="javascript:;" data-params="<?=str_replace(['%%id%%','%%offset%%'],['0',Elf::$_data['offset']],$params['edit_lnk']['url'])?>" onclick="showDialog(this)">Новая запись</a>
	<?php elseif (empty($params['edit_lnk']['request']) || ($params['edit_lnk']['request'] == 'get')):?>
	<a href="<?=str_replace(['%%id%%','%%offset%%'],['0',Elf::$_data['offset']],$params['edit_lnk']['url'])?>">Новая запись</a>
	<?php endif;endif;?>
</h5>
<% pagi %>
<?php if (!empty(Elf::$_data['data'])):?>
<table class="edit<?=!empty($params['class'])?" {$params['class']}":""?>">
	<tr class="top">
		<?php if (!isset($params['id_col']) || $params['id_col']):?>
		<td>#</td>
		<?php endif;?>
		<?php if (!empty($params['fields'])): foreach ($params['fields'] as $f):?>
		<td><?=$f['title']?></td>
		<?php endforeach;endif;?>
		<td>Операции</td>
	</tr>
	<?php foreach (Elf::$_data['data'] as $v):?>
	<tr class="data">
		<?php if (!isset($params['id_col']) || $params['id_col']):?>
		<td><?=$v['id']?></td>
		<?php endif;?>
		<?php if (!empty($params['fields'])): foreach ($params['fields'] as $k=>$f): if (array_key_exists($k, $v)):?>
			<td>
				<?php	
						if (!isset($f['type'])) $f['type'] = 'text';
						switch ($f['type']) {
							case 'image':
								echo $v[$k]?'<img src="'.$v[$k].'" />':'-';
								break;
							case 'checkbox':
								echo '<input type="checkbox" '.($v[$k]?'checked="checked"':'').' disabled="disabled" />';
								break;
							case 'integer':
								echo (int)$v[$k];
								break;
							case 'float':
								echo (float)$v[$k];
								break;
							case 'boolean':
								echo $v[$k]?Elf::lang()->item('yes'):Elf::lang()->item('no');
								break;
							default:
								echo $v[$k]?(!empty($f['lang'])?Elf::lang($f['lang'])->item($v[$k]):$v[$k]):'-';
								break;
						}
				?>
			</td>
		<?php endif;endforeach;endif;?>
		<td>
			<?php if (isset($params['edit_lnk'])): if (!empty($params['edit_lnk']['request']) && ($params['edit_lnk']['request'] == 'json')):?>
			<a href="javascript:;" data-params="<?=str_replace(['%%id%%','%%offset%%'],[$v['id'],Elf::$_data['offset']],$params['edit_lnk']['url'])?>" onclick="showDialog(this)">ред.</a>
			<?php elseif (empty($params['edit_lnk']['request']) || ($params['edit_lnk']['request'] == 'get')):?>
			<a href="<?=str_replace(['%%id%%','%%offset%%'],[$v['id'],Elf::$_data['offset']],$params['edit_lnk']['url'])?>">ред.</a>
			<?php endif;endif;?>
			<?php if (isset($params['del_lnk'])): if (!empty($params['del_lnk']['request']) && ($params['del_lnk']['request'] == 'json')):?>
			<a href="javascript:;" data-params="<?=str_replace(['%%id%%','%%offset%%'],[$v['id'],Elf::$_data['offset']],$params['del_lnk']['url'])?>" onclick="if (confirm('Подтвердите удаление записи')) showDialog(this)">удл.</a>
			<?php elseif (empty($params['del_lnk']['request']) || ($params['del_lnk']['request'] == 'get')):?>
			<a href="<?=str_replace(['%%id%%','%%offset%%'],[$v['id'],Elf::$_data['offset']],$params['del_lnk']['url'])?>" onclick="return confirm('Подтвердите удаление записи')">удл.</a>
			<?php endif;endif;?>
		</td>
	</tr>
	<?php endforeach;?>
</table>
<?php else:?>
<div class="alert"><% lang:datanotfound %></div>
<?php endif;?>

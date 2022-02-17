<?php if (Elf::get_data('recs')): $frm = Elf::get_data('frm'); $form = new Elf\Libs\Forms; foreach (Elf::get_data('recs') as $v):?>
<tr class="elf-form-related-recs-data" id="elf-form-related-rec-<?=$frm['slave_id']?>-<?=$v['id']?>">
	<?php foreach (Elf::$_data['fields'] as $k=>$f): if (array_key_exists($k, $v)): $vv = $v[$k];?>
	<td data-k="<?=$k?>" <?=!$f['show_in_related_data']?'class="hide"':''?>>
		<?php
			switch ($f['type']) {
				case 'select_simple':
				case 'select_enum':
				case 'radio':
					echo Elf::lang(Elf::get_data('lang'))->item($vv);
					break;
				case 'checkbox':
					echo $vv?'<i class="far fa-check-square"></i>':'<i class="far fa-square"></i>';
					break;
				case 'eval':
					switch ($f['default_value']) {
						case 'CURRENT_TIMESTAMP':
						case 'ONCE_TIMESTAMP':
							echo (int)$vv?date('d.m.Y', (int)$vv):'&nbsp;';
							break;
						default:
							echo $vv;
							break;
					}
					break;
				case 'date':
					echo preg_match('/\d{2}\.\d{2}\.\d{4}/', $vv)?$vv:((int)$vv?date('d.m.Y', (int)$vv):$vv);
					break;
				case 'link':
					echo $form->get_visible_field_value_of_link($f['id'], $vv);
					break;
				default:
					echo $vv?$vv:'&nbsp;';
					break;
			}
		?>
	</td>
	<?php endif; endforeach;?>
	<td>
		<input type="hidden" name="id[<?=$v['id']?>]" value="<?=$v['id']?>" class="elf-form-related-data" data-related-form-id="<% form_id %>" />
		<a href="javascript:;" data-params="dialog=<?=str_replace(EXT,'',$frm['fullpath'])?>;<?=$frm['getter_variable_name']?>=<?=$v['id']?>;master_id=<?=$frm['master_id']?>;master_field=<?=$frm['master_field']?>;slave_field=<?=$frm['slave_field']?>;caption=<?=$frm['name']?>;<?=$frm['slave_field']?>=<?=$v[$frm['slave_field']]?>;is_related_form=<?=$frm['slave_id']?>" onclick="showDialog(this)"><i class="fas fa-edit"></i></a>
		<a href="javascript:;" title="удалить запись" onclick="_remove_related_rec(<?=$v['id']?>,<?=$frm['slave_id']?>)"><i class="fas fa-times-circle"></i></a>
	</td>
</tr>
<?php endforeach; endif;?>
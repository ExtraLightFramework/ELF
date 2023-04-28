<h2 class="adm-tlt"><% lang:slog:title %></h2>
<div>
	<% lang:slog:select_manager %>: <?=Elf::slog()->manager_selector(Elf::get_data('manager_id'))?>
</div>
<?php if (Elf::get_data('manager_id')):?>
	<?php if (($stat = Elf::get_data('stat'))
			&& !empty($stat['settings']['data_found'])):?>
	<h4>Период <?=$stat['settings']['period']?></h4>
	
	<table class="elf-stat">
		<tr class="elf-stat-date">
			<td>Событие</td>
		<?php foreach ($stat as $k=>$v):if ($k != 'settings'):?>
			<td><?=date('d.m.y', $k)?> (<?=Elf::lang()->item('day.week.short.'.date('w', $k))?>)<br />кол./длит.</td>
		<?php endif;endforeach;?>
		</tr>
		<?php foreach ($stat['settings']['model_actions'] as $ma):?>
			<tr class="elf-stat-data">
				<td><?=Elf::lang('slog')->item($ma)?></td>
		<?php foreach ($stat as $k=>$v):if ($k != 'settings'):?>
				<td><?=$v[$ma]['cnt']?> / <?=Elf::sec_to_hms($v[$ma]['duration'])?></td>
		<?php endif;endforeach;?>
			</tr>
		<?php endforeach;?>
	</table>
	
	<?php else:?>
	<div class="alert">За период <?=$stat['settings']['period']?> данные не найдены</div>
	<?php endif;?>
<?php else:?>
	<div class="alert">Выберите оператора</div>
<?php endif;?>
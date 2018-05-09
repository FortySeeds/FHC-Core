<table id="notiztable" class="table table-bordered table-hover">
	<thead>
	<tr>
		<th><?= ucfirst($this->p->t('global', 'datum')) ?></th>
		<th><?= ucfirst($this->p->t('global', 'notiz')) ?></th>
		<th>User</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($notizen as $notiz): ?>	
		<tr data-toggle="tooltip"
			title="<?php echo isset($notiz->text) ? strip_tags($notiz->text) : '' ?>">		
			<td><?php echo date_format(date_create($notiz->insertamum), 'd.m.Y H:i:s') ?></td>
			<td style="cursor: pointer"><?php echo html_escape($notiz->titel) ?></td>
			<td><?php echo $notiz->verfasser_uid ?></td>
			<td style="display: none"><?php echo $notiz->notiz_id ?></td>
		</tr>
	<?php endforeach ?>
	</tbody>
</table>
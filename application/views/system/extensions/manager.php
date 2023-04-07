<?php
	$this->load->view(
		'templates/FHC-Header',
		array(
			// Title
			'title' => 'Extensions manager',

			// JS & CSS includes
			'jquery3' => true,
			'jqueryui1' => true,
			'jquerycheckboxes1' => true,
			'bootstrap3' => true,
			'tabulator4' => true,

			// Styles includes
			'fontawesome4' => true,
			'sbadmintemplate3' => true,

			// FHC JS & CSS includes
			'ajaxlib' => true,
			'dialoglib' => true,
			'tablewidget' => true,
			'phrases' => array(
				'extensions',
				'table',
				'ui'
			),
			'customJSs' => array('public/js/ExtensionsManager.js')
		)
	);
?>

	<body>
		<div class="container-fluid">
			<h3 class="page-header">
				<?php echo $this->p->t('extensions', 'title').' - '.SERVER_NAME; ?>
			</h3>

			<div>
				<?php $this->load->view('system/extensions/tableWidget.php'); ?>
			</div>

			<?php echo form_open_multipart(current_url().'/uploadExtension'); ?>
				<div class="row">
					<div class="col-md-4">
						<input class="form-control" type="file" name="extension" />
					</div>
					<div class="col-md-2">
						<div class="form-check">
							<input type="checkbox" class="form-check-input" id="notPerformSql" name="notPerformSql"/>
							<label class="form-check-label" for="notPerformSql"><?php echo $this->p->t('extensions', 'performSql'); ?></label>
						</div>
					</div>
					<div class="col-md-2">
						<input type="button" class="btn btn-default" id="uploadExtension" value="<?php echo $this->p->t('extensions', 'uploadExtension'); ?>" />
					</div>
				</div>
			</form>
			<br /><br /><br /><br /><br /><br /><br /><br />
		</div>
		
	</body>

<?php $this->load->view('templates/FHC-Footer'); ?>

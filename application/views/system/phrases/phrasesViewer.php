<?php
	$this->load->view(
		'templates/FHC-Header',
		array(
			'title' => 'Phrases Viewer',
			'jquery3' => true,
			'jqueryui1' => true,
			'bootstrap3' => true,
			'fontawesome4' => true,
			'sbadmintemplate3' => true,
			'tablesorter2' => true,
			'ajaxlib' => true,
			'phrases' => array(
				'ui' => array('bitteEintragWaehlen')
			),
			'filterwidget' => true,
			'navigationwidget' => true,
			'customCSSs' => 'public/css/sbadmin2/tablesort_bootstrap.css',
			'customJSs' => array('public/js/bootstrapper.js')
		)
	);
?>

<body>
	<div id="wrapper">

		<?php echo $this->widgetlib->widget('NavigationWidget'); ?>

		<div id="page-wrapper">
			<div class="container-fluid">
				<div class="row">
					<div class="col-lg-12">
						<h3 class="page-header">
							Phrases Viewer
						</h3>
					</div>
				</div>
				<div>
					<?php $this->load->view('system/phrases/phrasesViewerData.php'); ?>
				</div>
			</div>
		</div>
	</div>
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>

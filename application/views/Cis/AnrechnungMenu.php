<?php
$includesArray = array(
	'title' => 'AnrechnungsMenu',
	'customJSModules' => ['public/js/apps/Cis/AnrechnungMenu.js'],
	'tabulator5' => true,
	'primevue3' => true,
	'customCSSs' => ['public/css/components/calendar.css', 'public/css/components/FilterComponent.css'],
	
);

$this->load->view('templates/CISHTML-Header', $includesArray);
?>

<div id="AnrechnungMenuApp" >

</div>

<?php $this->load->view('templates/CISHTML-Footer', $includesArray); ?>

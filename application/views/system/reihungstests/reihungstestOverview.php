<?php
	$includesArray = array(
		'title' => 'Reihungstest Overview',
		'vue3' => true,
		'axios027' => true,
		'bootstrap5' => true,
		'tabulator5' => true,
		'fontawesome6' => true,
		'navigationcomponent' => true,
		'filtercomponent' => true,
		'customJSModules' => array('public/js/apps/Reihungstest/Reihungstest.js'),
	);

	$this->load->view('templates/FHC-Header', $includesArray);
?>

	<div id="main">
		<!-- Navigation component -->
		<core-navigation-cmpt v-bind:add-side-menu-entries="appSideMenuEntries"></core-navigation-cmpt>

		<div id="content">
			<h3>Reihungstest Uebersicht</h3>
			
			<div class="row">
				<stg-dropdown-cmpt @stg-Changed="stgChangedHandler"></stg-dropdown-cmpt>
				<year-dropdown-cmpt @year-Changed="yearChangedHandler"></year-dropdown-cmpt>
				<button-cmpt @click="handleButtonClick">Laden</button-cmpt>
				<button-cmpt @click="download">Download</button-cmpt>
				<button-cmpt @click="reset">Zurücksetzen</button-cmpt>
			</div>

			<core-filter-cmpt
				ref="rtTable"
				:tabulator-options="rtViewTabulatorOptions"
				:tabulator-events="rtViewerTabulatorEventHandlers"
				@nw-new-entry="newSideMenuEntryHandler"
				:table-only=true
				:hideTopMenu=false
			>
			</core-filter-cmpt>
		</div>
	</div>

<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>

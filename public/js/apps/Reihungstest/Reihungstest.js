/**
 * Copyright (C) 2022 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

import {rtViewTabulatorOptions} from './TabulatorSetup.js';
import {rtViewerTabulatorEventHandlers} from './TabulatorSetup.js';

import {CoreFilterCmpt} from '../../components/filter/Filter.js';
import {CoreNavigationCmpt} from '../../components/navigation/Navigation.js';
import {StgDropdown} from '../../components/Reihungstest/StgDropdown.js';
import {YearDropdown} from '../../components/Reihungstest/YearDropdown.js';
import {Button} from '../../components/Reihungstest/Button.js';
import {CoreRESTClient} from "../../RESTClient";

const rtOverviewApp = Vue.createApp({
	data: function() {
		return {
			appSideMenuEntries: {},
			rtViewTabulatorOptions: rtViewTabulatorOptions,
			rtViewerTabulatorEventHandlers: rtViewerTabulatorEventHandlers,
			stg: null,
			year: null
		};
	},
	components: {
		CoreNavigationCmpt,
		CoreFilterCmpt,
		StgDropdownCmpt : StgDropdown,
		YearDropdownCmpt : YearDropdown,
		ButtonCmpt : Button
	},
	methods: {
		newSideMenuEntryHandler: function(payload) {
			this.appSideMenuEntries = payload;
		},
		stgChangedHandler: function(stg) {
			this.stg = stg;
		},
		yearChangedHandler: function(year) {
			this.year = year;
		},
		handleButtonClick: function() {
			this.loadReport();
		},
		download: function() {
			this.$refs.rtTable.tabulator.download("csv", "Test.csv");
		},
		reset: function() {
			this.$refs.rtTable.tabulator.setData();
		},
		async loadReport() {
			try {
				const res = await CoreRESTClient.get('components/Reihungstest/Reihungstest/loadReport',
					{
					'stg' : this.stg,
					'studiensemester' : this.year
				});
				if (CoreRESTClient.isSuccess(res.data))
				{
					this.$refs.rtTable.tabulator.setData(CoreRESTClient.getData(res.data.retval));
				}
			} catch (error) {
				this.errors = "Fehler beim Laden der Studiengaenge";
				console.error(error);
			}
		},
	},


});

rtOverviewApp.mount('#main');


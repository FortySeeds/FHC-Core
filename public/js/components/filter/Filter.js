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

import {CoreFilterAPIs} from './API.js';
import {CoreRESTClient} from '../../RESTClient.js';
import {CoreFetchCmpt} from '../../components/Fetch.js';

/**
 *
 */
export const CoreFilterCmpt = {
	emits: ['nwNewEntry'],
	components: {
		CoreFetchCmpt
	},
	props: {
		filterType: {
			type: String,
			required: true
		},
		tabulatorOptions: Object
	},
	data() {
		return {
			// Tabulator
			tabulator: null,

			// FilterCmpt properties
			fields: null,
			fieldsToDisplay: null,
			dataset: null,
			datasetMetadata: null,
			selectedFields: null,
			notSelectedFields: null,
			filterFields: null,
			notFilterFields: null,

			// FetchCmpt binded properties
			fetchCmptRefresh: false,
			fetchCmptApiFunction: null,
			fetchCmptApiFunctionParams: null,
			fetchCmptDataFetched: null
		};
	},
	created() {
		this.getFilter(); // get the filter data
	},
	updated() {
		//
		let dataset = JSON.parse(JSON.stringify(this.dataset));
		let datasetMetadata = JSON.parse(JSON.stringify(this.datasetMetadata));
		let fieldsToDisplay = JSON.parse(JSON.stringify(this.fieldsToDisplay));
		let selectedFields = JSON.parse(JSON.stringify(this.selectedFields));

		//
		let columns = null;

		//
		if (this.tabulatorOptions != null && this.tabulatorOptions.hasOwnProperty('columns'))
		{
			columns = this.tabulatorOptions.columns;
		}

		// If columns is not valid
		if (!Array.isArray(columns) || (Array.isArray(columns) && columns.length < datasetMetadata.length))
		{
			columns = []; // 

			//
			for (let i = 0; i < datasetMetadata.length; i++)
			{
				//
				if (fieldsToDisplay.indexOf(datasetMetadata[i].name) != -1)
				{
					//
					columns[i] = {
						title: datasetMetadata[i].name,
						field: datasetMetadata[i].name
					};
				}
			}
		}
		else // 
		{
			let tmpColumns = [];

			//
			for (let i = 0; i < columns.length; i++)
			{
				if (selectedFields.indexOf(columns[i].field) != -1)
				{
					tmpColumns.push(columns[i]);
				}
			}

			columns = tmpColumns;
		}

		//
		let tabulatorOptions = {
			height: 500,
			layout: "fitColumns",
			columns: columns,
			data: JSON.parse(JSON.stringify(this.dataset)),
			reactiveData: true
		};

		//
		if (this.tabulatorOptions != null)
		{
			tabulatorOptions = this.tabulatorOptions;
			tabulatorOptions.data = JSON.parse(JSON.stringify(this.dataset));
			tabulatorOptions.reactiveData = true;
			tabulatorOptions.columns = columns;
		}

		//
		this.tabulator = new Tabulator(
			"#filterTableDataset",
			tabulatorOptions
		);
	},
	methods: {
		/**
		 *
		 */
		getFilter: function() {
			//
			this.startFetchCmpt(CoreFilterAPIs.getFilter, null, this.render);
		},
		/**
		 *
		 */
		render: function(response) {

			if (CoreRESTClient.hasData(response))
			{
				let data = CoreRESTClient.getData(response);
				this.dataset = data.dataset;
				this.datasetMetadata = data.datasetMetadata;
				this.fields = data.fields;
				this.selectedFields = data.selectedFields;
				this.notSelectedFields = this.fields.filter(x => this.selectedFields.indexOf(x) === -1);

				this.filterFields = [];
				let tmpFilterFields = [];
				for (let i = 0; i < data.datasetMetadata.length; i++)
				{
					for (let j = 0; j< data.filters.length; j++)
					{
						if (data.datasetMetadata[i].name == data.filters[j].name)
						{
							let filter = data.filters[j];
							filter.type = data.datasetMetadata[i].type;

							this.filterFields.push(filter);
							tmpFilterFields.push(filter.name);
							break;
						}
					}
				}

				this.notFilterFields = this.fields.filter(x => tmpFilterFields.indexOf(x) === -1);
				this.setFieldsToDisplay(data);
				this.setSideMenu(data);
			}
			else
			{
				console.error(CoreRESTClient.getError(response));
			}
		},
		/**
		 *
		 */
		setFieldsToDisplay: function(data) {

			let arrayFieldsToDisplay = [];

			if (data.hasOwnProperty("selectedFields") && Array.isArray(data.selectedFields))
			{
				if (data.hasOwnProperty("columnsAliases") && Array.isArray(data.columnsAliases))
				{
					for (let sfc = 0; sfc< data.selectedFields.length; sfc++)
					{
						for (let fc = 0; fc< data.fields.length; fc++)
						{
							if (data.selectedFields[sfc] == data.fields[fc])
							{
								arrayFieldsToDisplay[sfc] = data.columnsAliases[fc];
							}
						}
					}
				}
				else
				{
					arrayFieldsToDisplay = data.selectedFields;
				}
			}
	
			this.fieldsToDisplay = arrayFieldsToDisplay;
		},
		/**
		 *
		 */
		setSideMenu: function(data) {
			// Set the menu
			let filters = data.sideMenu.filters;
			let personalFilters = data.sideMenu.personalFilters;
			let filtersArray = [];

			for (let filtersCount = 0; filtersCount< filters.length; filtersCount++)
			{
				let link = filters[filtersCount].link;

				if (link == null) link = '#';

				filtersArray[filtersArray.length] = {
					link: link + filters[filtersCount].filterId,
					description: filters[filtersCount].desc,
					sort: filtersCount,
					onClickCall: this.handlerGetFilterById
				};
			}

			this.$emit(
				'nwNewEntry',
				{
					link: "#",
					description: "Filters",
					icon: "filter",
					children: filtersArray
				}
			);
		},
		/**
		 * Used to start/refresh the FetchCmpt
		 */
		startFetchCmpt: function(apiFunction, apiFunctionParameters, dataFetchedCallback) {
			// Assign the function api of the FetchCmpt binded property
			this.fetchCmptApiFunction = apiFunction;

			// In case a null value is provided set the parameters as an empty object
			if (apiFunctionParameters == null) apiFunctionParameters = {};

			// Always needed parameters
			apiFunctionParameters.filterUniqueId = FHC_JS_DATA_STORAGE_OBJECT.called_path + "/" + FHC_JS_DATA_STORAGE_OBJECT.called_method;
			apiFunctionParameters.filterType = this.filterType;

			// Assign parameters to the FetchCmpt binded properties
			this.fetchCmptApiFunctionParams = apiFunctionParameters;
			// Assign data fetch callback to the FetchCmpt binded properties
			this.fetchCmptDataFetched = dataFetchedCallback;
			// Set the FetchCmpt binded property refresh to have the component to refresh
			// NOTE: this should be the last one to be called because it triggers the FetchCmpt to start to refresh
			this.fetchCmptRefresh === true ? this.fetchCmptRefresh = false : this.fetchCmptRefresh = true;
		},

		// ------------------------------------------------------------------------------------------------------------------
		// Event handlers

		/**
		 *
		 */
		handlerSaveCustomFilter: function(event) {
			//
			this.startFetchCmpt(
				CoreFilterAPIs.saveCustomFilter,
				{
					customFilterName: document.getElementById('customFilterName').value
				},
				this.getFilter
			);
		},
		/**
		 *
		 */
		handlerApplyFilterFields: function(event) {
			let filterFields = [];
			let filterFieldDivs = document.getElementById('filterFields').getElementsByTagName('div');

			for (let i = 0; i< filterFieldDivs.length; i++)
			{
				let filterField = {};

				for (let j = 0; j< filterFieldDivs[i].children.length; j++)
				{
					if (filterFieldDivs[i].children[j].name != null)
					{
						// Name
						if (filterFieldDivs[i].children[j].name == 'fieldName')
						{
							filterField.name = filterFieldDivs[i].children[j].value;
						}
						// Operation
						if (filterFieldDivs[i].children[j].name == 'operation')
						{
							filterField.operation = filterFieldDivs[i].children[j].value;
						}
						// Condition
						if (filterFieldDivs[i].children[j].name == 'condition')
						{
							filterField.condition = filterFieldDivs[i].children[j].value;
						}
						// Option
						if (filterFieldDivs[i].children[j].name == 'option')
						{
							filterField.option = filterFieldDivs[i].children[j].value;
						}
					}
				}

				filterFields.push(filterField);
			}

			//
			this.startFetchCmpt(
				CoreFilterAPIs.applyFilterFields,
				{
					filterFields: filterFields
				},
				this.getFilter
			);
		},
		/**
		 *
		 */
		handlerAddFilterField: function(event) {
			//
			this.startFetchCmpt(
				CoreFilterAPIs.addFilterField,
				{
					filterField: event.currentTarget.value
				},
				this.getFilter
			);
		},
		/**
		 *
		 */
		handlerAddSelectedField: function(event) {
			//
			this.startFetchCmpt(
				CoreFilterAPIs.addSelectedField,
				{
					selectedField: event.currentTarget.value
				},
				this.getFilter
			);
		},
		/**
		 *
		 */
		handlerRemoveSelectedField: function(event) {
			//
			this.startFetchCmpt(
				CoreFilterAPIs.removeSelectedField,
				{
					selectedField: event.currentTarget.getAttribute('field-to-remove')
				},
				this.getFilter
			);
		},
		/**
		 *
		 */
		handlerRemoveFilterField: function(event) {
			//
			this.startFetchCmpt(
				CoreFilterAPIs.removeFilterField,
				{
					filterField: event.currentTarget.getAttribute('field-to-remove')
				},
				this.getFilter
			);
		},
		/**
		 *
		 */
		handlerGetFilterById: function(event) {
			//
			this.startFetchCmpt(
				CoreFilterAPIs.getFilterById,
				{
					filterId: event.currentTarget.getAttribute("href").substring(1)
				},
				this.render
			);
		}
	},
	template: `
		<!-- Load filter data -->
		<core-fetch-cmpt
			v-bind:api-function="fetchCmptApiFunction"
			v-bind:api-function-parameters="fetchCmptApiFunctionParams"
			v-bind:refresh="fetchCmptRefresh"
			@data-fetched="fetchCmptDataFetched">
		</core-fetch-cmpt>

		<div class="card filter-filter-options">
			<div class="card-header filter-header-title" data-bs-toggle="collapse" data-bs-target="#collapseFilterHeader">
				Filter options
			</div>
			<div id="collapseFilterHeader" class="card-body collapse">
				<!-- Filter fields options -->
				<div class="filter-options-div">
					<div class="filter-dnd-area">
						<template v-for="fieldToDisplay in fieldsToDisplay">
							<span class="filter-dnd-object">
								{{ fieldToDisplay}}
								<button
									type="button"
									class="btn-close"
									v-bind:field-to-remove="fieldToDisplay"
									@click=handlerRemoveSelectedField>
								</button>
							</span>
						</template>
					</div>
					<select class="form-select form-select-sm" @change=handlerAddSelectedField>
						<option value="">Select a field to be displayed...</option>
						<template v-for="hiddenField in notSelectedFields">
							<option v-bind:value="hiddenField">{{ hiddenField}}</option>
						</template>
					</select>
				</div>
				<!-- Filter options -->
				<div class="filter-options-div">
					<div>
						<select class="form-select form-select-sm" @change=handlerAddFilterField>
							<option value="">Add a field to the filter...</option>
							<template v-for="notFilterField in notFilterFields">
								<option v-bind:value="notFilterField">{{ notFilterField}}</option>
							</template>
						</select>
					</div>
					<div id="filterFields" class="filter-filter-fields">
						<template v-for="filterField in filterFields">
							<!-- Numeric -->
							<div v-if="filterField.type.toLowerCase().indexOf('int') >= 0" class="input-group mb-3">
								<input type="hidden" name="fieldName" v-bind:value="filterField.name">
								<span class="input-group-text">{{ filterField.name}}</span>
								<select class="form-select form-select-sm" name="operation">
									<option value="equal">Equal</option>
									<option value="nequal">Not equal</option>
									<option value="gt">Greater then</option>
									<option value="lt">Less then</option>
								</select>
								<input type="number" class="form-control" v-bind:value="filterField.condition" name="condition">
								<button
									class="btn btn-sm btn-outline-dark"
									type="button"
									v-bind:field-to-remove="filterField.name"
									@click=handlerRemoveFilterField>
									&emsp;X&emsp;
								</button>
							</div>
							<!-- Text -->
							<div
								v-if="filterField.type.toLowerCase().indexOf('varchar') >= 0
									|| filterField.type.toLowerCase().indexOf('text') >= 0
									|| filterField.type.toLowerCase().indexOf('bpchar') >= 0"
								class="input-group mb-3">
								<input type="hidden" name="fieldName" v-bind:value="filterField.name">
								<span class="input-group-text">{{ filterField.name}}</span>
								<select class="form-select form-select-sm" name="operation">
									<option value="contains">Contains</option>
									<option value="ncontains">Does not contain</option>
								</select>
								<input type="text" class="form-control" v-bind:value="filterField.condition" name="condition">
								<button
									class="btn btn-sm btn-outline-dark"
									type="button"
									v-bind:field-to-remove="filterField.name"
									@click=handlerRemoveFilterField>
									&emsp;X&emsp;
								</button>
							</div>
							<!-- Timestamp and date -->
							<div
								v-if="filterField.type.toLowerCase().indexOf('timestamp') >= 0
									|| filterField.type.toLowerCase().indexOf('date') >= 0"
								class="input-group mb-3">
								<input type="hidden" name="fieldName" v-bind:value="filterField.name">
								<span class="input-group-text">{{ filterField.name}}</span>
								<select class="form-select form-select-sm" name="operation">
									<option value="gt">Greater then</option>
									<option value="lt">Less then</option>
									<option value="set">Is set</option>
									<option value="nset">Is not set</option>
								</select>
								<input type="number" class="form-control" v-bind:value="filterField.condition" name="condition">
								<select class="form-select form-select-sm" name="option">
									<option value="minutes">Minutes</option>
									<option value="hours">Hours</option>
									<option value="days">Days</option>
									<option value="months">Months</option>
								</select>
								<button
									class="btn btn-sm btn-outline-dark"
									type="button"
									v-bind:field-to-remove="filterField.name"
									@click=handlerRemoveFilterField>
									&emsp;X&emsp;
								</button>
							</div>
						</template>
					</div>
					<div>
						<button type="button" class="btn btn-sm btn-outline-dark" @click=handlerApplyFilterFields>Apply changes</button>
					</div>
				</div>
				<!-- Filter save options -->
				<div class="input-group">
					<input type="text" class="form-control" placeholder="Custom filter name" id="customFilterName">
					<button type="button" class="btn btn-outline-secondary" @click=handlerSaveCustomFilter>Save</button>
				</div>
			</div>
		</div>

		<!-- Tabulator -->
		<div id="filterTableDataset"></div>
	`
};


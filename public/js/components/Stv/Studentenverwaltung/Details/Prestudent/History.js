import {CoreFilterCmpt} from "../../../../filter/Filter.js";

export default{
	components: {
		CoreFilterCmpt
	},
	props: {
		person_id: String
	},
	data() {
		return {
			tabulatorOptions: {
				ajaxURL: 'api/frontend/v1/stv/Prestudent/getHistoryPrestudents/' + this.person_id,
				ajaxRequestFunc: this.$fhcApi.get,
				ajaxResponse: (url, params, response) => response.data,
				//autoColumns: true,
				columns:[
					{title:"StSem", field:"studiensemester_kurzbz"},
					{title:"Prio", field:"priorisierung"},
					{title:"Stg", field:"kurzbzlang"},
					{title:"Orgform", field:"orgform_kurzbz"},
					{title:"Studienplan", field:"bezeichnung"},
					{title:"UID", field:"student_uid"},
					{title:"Status", field:"status"}
				],
				tabulatorEvents: [
					{
						event: 'tableBuilt',
						handler: async () => {
							await this.$p.loadCategory(['lehre']);

							let cm = this.$refs.table.tabulator.columnManager;

							cm.getColumnByField('orgform_kurzbz').component.updateDefinition({
								title: this.$p.t('lehre', 'organisationsform')
							});

							cm.getColumnByField('bezeichnung').component.updateDefinition({
								title: this.$p.t('lehre', 'studienplan')
							});
						}
					}
				],
				layout: 'fitDataFill',
				layoutColumnsOnNewData:	false,
				height:	'auto',
				selectable:	false,
			},
		}
	},
	template: `
		<div class="stv-list h-100 pt-3">
			<core-filter-cmpt
				ref="table"
				:tabulator-options="tabulatorOptions"
				:tabulator-events="tabulatorEvents"
				table-only
				:side-menu="false"
			>
		</core-filter-cmpt>
		</div>`
}
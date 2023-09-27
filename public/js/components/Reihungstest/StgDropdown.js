import {CoreRESTClient} from "../../RESTClient";

export const StgDropdown = {
	emits: [
		'stgChanged'
	],
	data () {
		return {
			options: [],
			selectedOption: null,
			errors: null
		};
	},
	beforeMount() {
		this.loadDropdown();
	},
	methods: {
		async loadDropdown() {
			try {
				const res = await CoreRESTClient.get('components/Reihungstest/Reihungstest/getStg');
				if (CoreRESTClient.isSuccess(res.data))
				{
					let data =  CoreRESTClient.getData(res.data);
					this.options = data;
					this.selectedOption = data[0].studiengang_kz;
					this.$emit("stgChanged", this.selectedOption);
				}
			} catch (error) {
				this.errors = "Fehler beim Laden der Studiengaenge";
				console.error(error);
			}
		},
		stgChanged(e) {
			this.$emit("stgChanged", e.target.value);
		}
	},
	template: `
		<div class="col-md-2">
			<select @change="stgChanged" class="form-control">
				<option v-for="option in options" :value="option.studiengang_kz" >
					{{ option.kurzbzlang }} - {{ option.bezeichnung }}
				</option>
			</select>
			<p v-if="errors" class="text-danger">{{ errors }}</p>
		</div>
	`
}
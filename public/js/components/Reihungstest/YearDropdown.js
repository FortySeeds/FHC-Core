import {CoreRESTClient} from "../../RESTClient";

export const YearDropdown = {
	emits: [
		'yearChanged'
	],
	data () {
		return {
			options: [],
			selectedOption: null,
			errors: null
		};
	},
	created() {
		this.loadDropdown();
	},
	methods: {
		async loadDropdown() {
			try {
				const res = await CoreRESTClient.get('components/Reihungstest/Reihungstest/getYear');
				if (CoreRESTClient.isSuccess(res.data))
				{
					let data = CoreRESTClient.getData(res.data);
					this.options = data;
					this.selectedOption = data[0].studiensemester_kurzbz;
					this.$emit("yearChanged", this.selectedOption);
				}
			} catch (error) {
				console.log(error);
			}
		},
		yearChanged(e) {
			this.$emit("yearChanged", e.target.value);
		}
	},

	template: `
		<div class="col-md-2">
			<select @change="yearChanged" class="form-control">
				<option v-for="option in options" :value="option.studiensemester_kurzbz" >
					{{ option.studiensemester_kurzbz }}
				</option>
			</select>
		</div>
	`
}
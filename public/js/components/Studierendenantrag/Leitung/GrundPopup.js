
import BsAlert from '../../Bootstrap/Alert.js';
import Phrasen from '../../../mixins/Phrasen.js';

export default {
	mixins: [
		BsAlert,
		Phrasen
	],
	props: {
		placeholder: String,
		default: String
	},
	data: () => ({
		value: '',
		result: false,
		check: false,
		isInvalid: false
	}),
	methods: {
		submit(){
			if(!this.value)	{
				this.isInvalid = true;
					}
			else {
				this.result = [this.value, this.check];
				this.hide();
			}
			return
		}
	},
	created() {
		if (this.default)
			this.value = this.default;
	},
	popup(msg, options) {
		if (typeof options === 'string')
			options = { default: options };
		return BsAlert.popup.bind(this)(msg, options);
	},
	template: `<bs-modal ref="modalContainer" class="bootstrap-prompt" v-bind="$props">
		<template v-slot:title>
			<slot></slot>
		</template>
		<template v-slot:default>
			<div>
				<textarea ref="input" class="form-control" :class="{'is-invalid' : isInvalid}" v-model="value"></textarea>
				<div v-if="isInvalid" class="invalid-feedback">
					{{p.t('kvp','new.error.required')}}
				</div>
			</div>
			<div class="form-check">
				<input ref="check" type="checkbox" class="form-check-input" id="cbid" v-model="check">
				<label class="form-check-label" for="cbid">{{p.t('studierendenantrag','fuer_alle_uebernehmen')}}</label>
			</div>
		</template>
		<template v-slot:footer>
			<button type="button" class="btn btn-primary" @click="submit">{{p.t('ui','ok')}}</button>
			<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{p.t('ui','cancel')}}</button>
		</template>
	</bs-modal>`
}

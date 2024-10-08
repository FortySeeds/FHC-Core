import Phrasen from '../../plugin/Phrasen.js';

export default {
	data: () => ({
		modal: null
	}),
	props: {
		backdrop: {
			type: [Boolean,String],
			default: true,
			validator(value) {
				return ['static', true, false].includes(value);
			}
		},
		focus: {
			type: Boolean,
			default: true
		},
		keyboard: {
			type: Boolean,
			default: true
		},
		noCloseBtn: Boolean,
		dialogClass: [String,Array,Object]
	},
	emits: [
		"hideBsModal",
		"hiddenBsModal",
		"hidePreventedBsModal",
		"showBsModal",
		"shownBsModal"
	],
	methods: {
		dispose() {
			return this.modal.dispose();
		},
		handleUpdate() {
			return this.modal.handleUpdate();
		},
		hide() {
			return this.modal.hide();
		},
		show(relatedTarget) {
			return this.modal.show(relatedTarget);
		},
		toggle() {
			return this.modal.toggle();
		}
	},
	mounted() {
		if(this.$refs.modal)
		{
			this.modal = new bootstrap.Modal(this.$refs.modal, {
				backdrop: this.backdrop,
				focus: this.focus,
				keyboard: this.keyboard
			});
		}
	},
	popup(body, options, title, footer) {
		const BsModal = this,
			slots = {};
		if (body !== undefined)
			slots.default = () => body;
		if (title !== undefined)
			slots.title = () => title;
		if (footer !== undefined)
			slots.footer = () => footer;
		return new Promise((resolve,reject) => {
			const instance = Vue.createApp({
				setup() {
					return () => Vue.h(BsModal, {...{
						class: 'fade'
					},...options, ...{
						ref: 'modal',
						'onHidden.bs.modal': instance.unmount
					}}, slots);
				},
				mounted() {
					this.$refs.modal.show();
				},
				beforeUnmount() {
					if (this.$refs.modal)
						this.$refs.modal.result !== false ? resolve(this.$refs.modal.result) : reject();
				},
				unmounted() {
					wrapper.parentElement.removeChild(wrapper);
				}
			});
			const wrapper = document.createElement("div");
			instance.use(Phrasen); // TODO(chris): find a more dynamic way
			instance.mount(wrapper);
			document.body.appendChild(wrapper);
		});
	},
	template: `<div ref="modal" class="bootstrap-modal modal" tabindex="-1" @[\`hide.bs.modal\`]="$emit('hideBsModal', $event)" @[\`hidden.bs.modal\`]="$emit('hiddenBsModal', $event)" @[\`hidePrevented.bs.modal\`]="$emit('hidePreventedBsModal', $event)" @[\`show.bs.modal\`]="$emit('showBsModal', $event)" @[\`shown.bs.modal\`]="$emit('shownBsModal', $event)">
		<div class="modal-dialog" :class="dialogClass">
			<div class="modal-content">
				<div v-if="$slots.title" class="modal-header">
					<h5 class="modal-title"><slot name="title"/></h5>
					<button v-if="!noCloseBtn" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<slot></slot>
				</div>
				<div v-if="$slots.footer" class="modal-footer">
					<slot name="footer"/>
				</div>
			</div>
		</div>
	</div>`
}

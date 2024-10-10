import CalendarDate from '../../../composables/CalendarDate.js';

function ggt(m,n) { return n==0 ? m : ggt(n, m%n); }
function kgv(m,n) { return (m*n) / ggt(m,n); }

export default {
	inject: [
		'date',
		'focusDate',
		'size',
		'events',
		'noMonthView'
	],
	props: {
		year: Number,
		week: Number
	},
	emits: [
		'updateMode',
		'page:back',
		'page:forward',
		'input'
	],
	data() {
		return {
			hours: [7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24]
		};
	},
	computed: {
		days() {
			
			let tmpDate = new CalendarDate(this.year,1,1); // NOTE(chris): somewhere in the middle of the year
			tmpDate.w = this.week;
			let startDay = tmpDate.firstDayOfWeek;
			let result = [];
			for (let i = 0; i < 7; i++) {
				result.push(new Date(startDay.getFullYear(), startDay.getMonth(), startDay.getDate() + i));
			}
			return result;

		},
		eventsPerDayAndHour() {
			const res = {};
			this.days.forEach(day => {
				let key = day.toDateString();
				
				let nextDay = new Date(day);
				nextDay.setDate(nextDay.getDate()+1);
				nextDay.setMilliseconds(nextDay.getMilliseconds()-1);
				let d = {events:[],lanes:1};
				if (this.events[key]) {
					this.events[key].forEach(evt => {
						let event = {orig:evt,lane:1,maxLane:1,start: evt.start < day ? day : evt.start, end: evt.end > nextDay ? nextDay : evt.end,shared:[],setSharedMaxRecursive(doneItems) {
							this.maxLane = Math.max(doneItems[0].maxLane, this.maxLane);
							doneItems.push(this);
							this.shared.filter(other => !doneItems.includes(other)).forEach(i => i.setSharedMaxRecursive(doneItems));
						}};
						event.shared = d.events.filter(other => other.start < event.end && other.end > event.start);
						event.shared.forEach(other => other.shared.push(event));
						let occupiedLanes = event.shared.map(other => other.lane);
						while (occupiedLanes.includes(event.lane))
							event.lane++;
						event.maxLane = Math.max(...[event.lane], ...occupiedLanes);
						if (event.maxLane > 1) {
							event.setSharedMaxRecursive([event]);
						}
						d.events.push(event);
					});
					d.lanes = d.events.map(e => e.maxLane).reduce((res, i) => kgv(res, i), 1);
				}
				res[key] = d;
			});
			return res;
		},
		smallestTimeFrame() {
			return [30,15,10,5][this.size];
		}
	},
	methods: {
		getAbsolutePositionForHour(hour){
			// used for the absolute positioning of the gutters of hours
			return (100 / this.hours.length) * (hour - (24-this.hours.length)) + '%';
		},
		changeToMonth(day) {
			if (!this.noMonthView) {
				this.date.set(day);
				this.focusDate.set(day);
				this.$emit('updateMode', 'month');
			}
		},
		dateToMinutesOfDay(day) {
			return Math.floor(((day.getHours()-7) * 60 + day.getMinutes()) / this.smallestTimeFrame) + 1;
		}
	},
	mounted() {
		setTimeout(() => this.$refs.eventcontainer.scrollTop = this.$refs.eventcontainer.scrollHeight / 3 + 1, 0);
	},
	template: `
	<div class="fhc-calendar-week-page">
	
		<div class="d-flex flex-column border-top">
			<div class="fhc-calendar-week-page-header d-grid border-2 border-bottom text-center" :style="{'z-index':2,'grid-template-columns': 'repeat(' + days.length + ', 1fr)', 'grid-template-rows':1}" style="position:sticky; top:0; " >
				<div type="button" v-for="day in days" :key="day" class="flex-grow-1" :title="day.toLocaleString(undefined, {dateStyle:'short'})" @click.prevent="changeToMonth(day)">
					<div class="fw-bold">{{day.toLocaleString(undefined, {weekday: size < 2 ? 'narrow' : (size < 3 ? 'short' : 'long')})}}</div>
					<a href="#" class="small text-secondary text-decoration-none" >{{day.toLocaleString(undefined, [{day:'numeric',month:'numeric'},{day:'numeric',month:'numeric'},{day:'numeric',month:'numeric'},{dateStyle:'short'}][this.size])}}</a>
				</div>
			</div>
			<div ref="eventcontainer" class="position-relative flex-grow-1">
			<div v-for="hour in hours" :key="hour"  class="position-absolute border-top" :style="{top:getAbsolutePositionForHour(hour),left:0,right:0,'z-index':0}"></div>
				<div class="events">
					<div class="hours">
						<div v-for="hour in hours" style="min-height:100px" :key="hour" class="text-muted text-end small" :ref="'hour' + hour">{{hour}}:00</div>
					</div>
					<div v-for="day in eventsPerDayAndHour" :key="day" class=" day border-start" :style="{'grid-template-columns': 'repeat(' + day.lanes + ', 1fr)', 'grid-template-rows': 'repeat(' + (1080 / smallestTimeFrame) + ', 1fr)'}">
						<div :style="{'background-color':event.orig.color}" class="mx-2 border border-dark border-2 small rounded overflow-hidden "  @click.prevent="$emit('input', event.orig)" :style="{'z-index':1,'grid-column-start': 1+(event.lane-1)*day.lanes/event.maxLane, 'grid-column-end': 1+event.lane*day.lanes/event.maxLane, 'grid-row-start': dateToMinutesOfDay(event.start), 'grid-row-end': dateToMinutesOfDay(event.end) ,'--test': dateToMinutesOfDay(event.end)}" v-for="event in day.events" :key="event">	
							<slot :event="event" :day="day">
								<p>this is a placeholder which means that no template was passed to the Calendar Page slot</p>
							</slot>
						</div>
						
					</div>
				</div>
			</div>
		</div>
	</div>`
}

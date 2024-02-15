//import ProfilView from "../../components/Cis/Profil/Profil.js";
import StudentProfil from "../../components/Cis/Profil/StudentProfil.js";
import MitarbeiterProfil from "../../components/Cis/Profil/MitarbeiterProfil.js";
import ViewStudentProfil from "../../components/Cis/Profil/StudentViewProfil.js";
import ViewMitarbeiterProfil from "../../components/Cis/Profil/MitarbeiterViewProfil.js";
import fhcapifactory from "../api/fhcapifactory.js";

Vue.$fhcapi = fhcapifactory;
Vue.$collapseFormatter  = function(data){
	//data - an array of objects containing the column title and value for each cell
	var container = document.createElement("div");
	container.classList.add("tabulator-collapsed-row");
	container.classList.add("text-break");
  
	var list = document.createElement("div");
	list.classList.add("row");
	
	
	container.appendChild(list);
  
	data.forEach(function(col){
		let item = document.createElement("div");
		item.classList.add("col-6");
		let item2 = document.createElement("div");
		item2.classList.add("col-6");
		
		item.innerHTML = "<strong>" + col.title + "</strong>";
		item2.innerHTML = col.value?col.value:"-";
		
		list.appendChild(item);
		list.appendChild(item2);
	});
  
	return Object.keys(data).length ? container : "";
  };

  

const testapp = Vue.createApp({
	
	components: {
		StudentProfil,
		MitarbeiterProfil,
		ViewStudentProfil,
		ViewMitarbeiterProfil,
	},
	data() {
		return {
			
			view:null,
			data:null,
			// notfound is null by default, but contains an UID if no user exists with that UID
			notFoundUID:null,
			
		}
	},
	//? use function syntax for provide so that we can access `this`
	provide() {
		return {

			getZustellkontakteCount: this.zustellKontakteCount,
			getZustelladressenCount: this.zustellAdressenCount,
			collapseFunction:  (e, column)=> {
		
				//* check if property doesn't exist already and add it to the reactive this properties
				if(this[e.target.id] === undefined){
				this[e.target.id] = true
			
				} 
				this[e.target.id]=!this[e.target.id];
			
				//* gets all event icons of the different rows to use the onClick event later
				let allClickableIcons = column._column.cells.map((row) => {
				return row.element.children[0];
				});
			
				//* changes the icon that shows or hides all the collapsed columns
				//* if the replace function does not find the class to replace, it just simply returns false
				if (this[e.target.id]) {
				e.target.classList.replace("fa-angle-up", "fa-angle-down");
				} else {
				e.target.classList.replace("fa-angle-down", "fa-angle-up");
				}
			
				//* changes the icon for every collapsed column to open or closed
				if (this[e.target.id]) {
				allClickableIcons
					.filter((column) => {
					return !column.classList.contains("open");
					})
					.forEach((col) => {
					col.click();
					});
				} else {
				allClickableIcons
					.filter((column) => {
					return column.classList.contains("open");
					})
					.forEach((col) => {
					col.click();
					});
				}
		  },
		  sortProfilUpdates: (ele1,ele2)=>{

			let result = 0;
			if(ele1.status === 'pending'){
			  result= -1;
			}
			else if(ele1.status === 'accepted'){
			  result= ele2.status ==='rejected'? -1 : 1;
			}
			else{
			  result= 1;
			}
			//? if they have the same status the insert date is used for ordering
			if(ele1.status === ele2.status){
			  result= new Date(ele2.insertamum.split('.').reverse().join('-')) - new Date(ele1.insertamum.split('.').reverse().join('-'));
			}
			return result;
		  }
		}
		
	},
	methods:{
		zustellAdressenCount(){
			if(!this.data || !this.data.adressen){
				
				return null;
			}

			return this.data.adressen.filter(adresse => {
				return adresse.zustelladresse;
			}).map(adr => {
				
				return adr.adresse_id;
			});
			
		},

		zustellKontakteCount(){
			if(!this.data || !this.data.kontakte){
				return null;
			}

			return this.data.kontakte.filter(kontakt => {
				return kontakt.zustellung;
			}).map(kon =>{
				return kon.kontakt_id;
			});
		},
	},
	computed:{
		

		filteredEditData(){
			if(!this.data){
				return;
			}
			
			
			return {
			  view:null,
			  data:{
			  Personen_Informationen : {
				title:"Personen Informationen",
				view:null,
				data:{
				  
				  vorname: {
					title:"vorname",
					view:"TextInputDokument",
					withFiles:true,
					data:{
					  titel:"vorname",
					  value:this.data.vorname,
					  
					}},
					nachname: {
					  title:"nachname",
					  view:"TextInputDokument",
					  withFiles:true,
					  data:{
						titel:"nachname",
						value:this.data.nachname,
					  }
					},
					titel:{
					  title:"titel",
					  view:"TextInputDokument",
					  withFiles:true,
					  data:{
						titel:"titel",
						value:this.data.titel,
					  }
					},
					postnomen:{
					  title:"postnomen",
					  view:"TextInputDokument",
					  withFiles:true,
					  data:{
						titel:"postnomen",
						value:this.data.postnomen,
					  }
					},
				  }
				},
				Private_Kontakte: {
				  title:"Private Kontakte" ,
				  data:this.data.kontakte.filter(item => {
					return !this.data.profilUpdates?.some((update) => update.status ==='pending' && update.requested_change?.kontakt_id === item.kontakt_id);
				  }).map(kontakt => {
					return {
					  listview:'Kontakt',
					  view:'EditKontakt',
					  data:kontakt
					}})
				 },
				Private_Adressen: {
				  title: "Private Adressen",
				  data:this.data.adressen.filter(item => {
					return !this.data.profilUpdates?.some(update => {
					  return  update.status ==='pending' &&  update.requested_change?.adresse_id == item.adresse_id;
					})
				  }).map(kontakt => {
					return {
					  listview:'Adresse',
					  view:'EditAdresse',
					  data:kontakt
					}})
				 },
				},
			 
			};
		  },
	  
	},


	created(){

		let path = location.pathname;
		
		let uid = path.substring(path.lastIndexOf('/')).replace("/","");

		Vue.$fhcapi.UserData.getView(uid).then((res)=>{
			if(!res.data){
				this.notFoundUID=uid;
			}else{
				this.view = res.data?.view;
				this.data = res.data?.data;
			}
			
		});
	},
	template:`
	<div>
	
		<div v-if="notFoundUID">
		
			<h3>Es wurden keine oder mehrere Profile für {{this.notFoundUID}} gefunden</h3>

		</div>

		<component v-else :is="view" :data="data" :editData="filteredEditData" ></component>
	
	</div>`
	
	
});
testapp.mount("#content");
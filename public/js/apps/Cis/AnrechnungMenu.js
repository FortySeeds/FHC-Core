import fhcapifactory from "../api/fhcapifactory";
Vue.$fhcapi = fhcapifactory;

const AnrechnungMenuApp = Vue.createApp({
    data(){
        return {
            lehrveranstaltungen:[],
            selectedLehrveranstaltung:null,
            selectedSemester:null,
            semester:[],
            aktuelleLehrveranstaltungen:[],
        }
    },
    methods:{
        lehrveranstaltungFun: function(id){
            console.log(`clicked on id: ${id}`);
        },
        RequestAnrechnungLink:function(lehrveranstaltung_id,semester){
            if(lehrveranstaltung_id || semester){
                return FHC_JS_DATA_STORAGE_OBJECT.app_root 
                + FHC_JS_DATA_STORAGE_OBJECT.ci_router
                + `/lehre/anrechnung/requestAnrechnung?studiensemester=${semester}&lv_id=${lehrveranstaltung_id}`;
            }else{
                return null;
            }
        },
    },
    
        
    
    mounted(){

    },
    watch:{
        selectedSemester:function(newValue,oldValue){
            if(newValue){
                Vue.$fhcapi.Menu.getLvEinheiten(newValue).then(res => {
                    console.log("lehreinheiten fuer semester "+newValue+": "+JSON.stringify(res.data));
                    this.lehrveranstaltungen = res.data;
                })
            }else{
                this.lehrveranstaltungen = [];
                this.selectedLehrveranstaltung = null;
            }
            
        },
    },
    created(){
        Vue.$fhcapi.Menu.getAktuelleLvEinheiten().then(res => {
            this.aktuelleLehrveranstaltungen = res.data;
        })

        Vue.$fhcapi.Menu.getSemesterOfStudent().then(res => {
            this.semester = res.data;
        })
    },
template: /*html*/`
<div class="container">
<!--<pre>{{JSON.stringify(aktuelleLehrveranstaltungen,null,2)}}</pre>-->
<div class="row mb-2">
<div class="col-6">
<p>Semester</p>
<select v-model="selectedSemester" class="form-select" aria-label="Default select example">
  <option selected :value="null"></option>
  <option v-for="sem in semester" :value="sem">{{sem}}</option>
  
</select>
</div>
<div class="col-6">
<p>Lehrveranstaltung</p>
<select v-model="selectedLehrveranstaltung" class="form-select" aria-label="Default select example">
  <option selected :value="null"></option>
  <option v-for="lehrveranstaltung in lehrveranstaltungen" :value="lehrveranstaltung.lehrveranstaltung_id">{{lehrveranstaltung.bezeichnung}}</option>
  
</select>
</div>
</div>
<div class="row mb-2">
<div class="col">
<h4 class="my-3">Aktuelle Lehrveranstaltungen ({{aktuelleLehrveranstaltungen.aktuelleSemester}}):</h4>
<ul class="list-group">
  <a :href="RequestAnrechnungLink(lehrveranstaltung.lehrveranstaltung_id, aktuelleLehrveranstaltungen.aktuelleSemester)" role="button" @click="lehrveranstaltungFun(lehrveranstaltung.lehrveranstaltung_id)" v-for="lehrveranstaltung in aktuelleLehrveranstaltungen.lehrveranstaltungen" class="list-group-item">{{lehrveranstaltung.bezeichnung}}</a>
  
</ul>
</div>
</div>
<!--https://cis.technikum-wien.at/index.ci.php/lehre/anrechnung/RequestAnrechnung?studiensemester=SS2024&lv_id=40262&fhc_controller_id=65ddb744356e5-->
<a :href="RequestAnrechnungLink(selectedLehrveranstaltung,selectedSemester)" class="mt-2 btn btn-outline-primary">Anrechnungs Übersicht</a>
</div>



`
});

AnrechnungMenuApp.mount("#AnrechnungMenuApp");
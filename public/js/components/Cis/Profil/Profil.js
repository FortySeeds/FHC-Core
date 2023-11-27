
import fhcapifactory from "../../../apps/api/fhcapifactory.js";
import {CoreFilterCmpt} from "../../../components/filter/Filter.js"


/* [
    {title: 'Log ID', field: 'LogId', headerFilter: true},
    {title: 'Request ID', field: 'RequestId', headerFilter: true},
    {title: 'Execution time', field: 'ExecutionTime', headerFilter: true},
    {title: 'Executed by', field: 'ExecutedBy', headerFilter: true},
    {title: 'Description', field: 'Description', headerFilter: true},
    {title: 'Data', field: 'Data', headerFilter: true},
    {title: 'Web service type', field: 'WebserviceType', headerFilter: true}
] */

//? old data
/* ajaxUrl: FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router+
                "/Cis/Profil/getBenutzerFunktionen", */

export default {
    components:{
        CoreFilterCmpt,
    },
    data() {
        return {
            
            person_info: null,
            //? beinhaltet die Information ob der angefragte user ein Student oder Mitarbeiter ist
            role: null,
            //"bf_bezeichnung", "oe_bezeichnung", "datum_von", "datum_bis", "wochenstunden" ]

            funktionen_table_options: {
                height: 300,
                layout: 'fitColumns',
                //ajaxUrl: FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router+
                //"/Cis/Profil/getBenutzerFunktionen",
                data:[{Bezeichnung:"test1",Organisationseinheit:"test2",Gültig_von:"test3",Gültig_bis:"test4",Wochenstunden:"test5"}],
                
                columns: [{title: 'Bezeichnung', field: 'Bezeichnung', headerFilter: true},
                {title: 'Organisationseinheit', field: 'Organisationseinheit', headerFilter: true},
                {title: 'Gültig_von', field: 'Gültig_von', headerFilter: true},
                {title: 'Gültig_bis', field: 'Gültig_bis', headerFilter: true},
                {title: 'Wochenstunden', field: 'Wochenstunden', headerFilter: true},]
                
            },
            betriebsmittel_table_options:{
                height: 300,
                layout: 'fitColumns',
                data:[{betriebsmittel:"test1",Nummer:"test2",Ausgegeben_am:"test3"}],
                
                columns: [{title: 'Betriebsmittel', field: 'betriebsmittel', headerFilter: true},
                {title: 'Nummer', field: 'Nummer', headerFilter: true},
                {title: 'Ausgegeben_am', field: 'Ausgegeben_am', headerFilter: true},]
                
            },
        }
    },
    
    //? this props were passed in the Profil.php view file
    props:['uid','pid'],
    methods: {
        
        concatenate_addresses(address_array){
            let result = "";
            for (let i = 0; i < address_array.length; i++) {
                result += address_array[i].strasse + " " + address_array[i].plz + " " + address_array[i].ort + "\n";
            }
            return result;
        },
        render_unterelement(wert,bezeichnung){
            if (isArray(bezeichnung)){
                
            }
        },
        concatenate_kontakte(kontakt_array){
            let result = "";
            for (let i = 0; i < kontakt_array.length; i++) {
                result += kontakt_array[i].kontakttyp + " " + kontakt_array[i].kontakt + " " + kontakt_array[i].zustellung + "\n";
            }
            return result;
        },
        sperre_foto_function(value){
            if(!this.person_info){
                return;
            }
            fhcapifactory.UserData.sperre_foto_function(value).then(res => {
                    
                    this.person_info.foto_sperre = res.data.foto_sperre;
                
            });
            
        },
        
        
    },
    computed:{
        test_computed(){
            return "test_computed";
        },
        get_Functions_Tabulator_Columns(){
            if(!this.person_info){
                return [];
            }
            return Object.keys(this.person_info.funktionen[0]).map(key => {return {title: key,field:key, headerFilter:true}});
        },
        get_image_base64_src(){
            if(!this.person_info){
                return "";
            }
            return "data:image/jpeg;base64,"+(this.person_info ? this.person_info.foto : "");
        },
        personData(){
            if(!this.person_info){
                return {};
            }
            //! postnomen is still missing
           return {
                Allgemein: {
                    Username:this.uid,
                Anrede:this.person_info.anrede,
                Titel:(this.person_info.titelpre&&this.person_info.titelpost)?this.person_info.titelpre.concat(this.person_info.titelpost):"null",
                Vorname:this.person_info.vorname,
                Nachname:this.person_info.nachname,
                Postnomen:null,
            },
            GeburtsDaten:{
                Geburtsdatum:this.person_info.gebdatum,
                Geburtsort: this.person_info.gebort,
            },
                Adressen: this.person_info.adressen,
            SpecialInformation: {
                Kurzzeichen: this.person_info.kurzbz,
                Telefon: this.person_info.telefonklappe,
            },
            };
        },
        //? this computed conains all the information that is used for the second column that displays the information of the person
        kontaktInfo(){
            if(!this.person_info){
                return {};
            }
            //! postnomen is still missing
           return {
                FhAusweisStatus: this.person_info.zutrittskarte_ausgegebenam,
                emails:this.person_info.emails,
                Kontakte:this.person_info.kontakte,
            };
        },
        
    },
    
    created(){
        
        //error //! fhcapifactory.UserData.getUser().then(res => this.person = res.data);
        fhcapifactory.UserData.isMitarbeiterOrStudent(this.uid).then(res => {this.role = res.data;});
        
        
        //.tabulator.setData(this.person_info?.funktionen);
        
    },
     mounted(){
        //? this function is to update the tabulator information only when the tabulator was build checking the tableBulit event
        //! only the tableBuilt event of the second tabulator was used to update the table informations
        this.$refs.betriebsmittelTable.tabulator.on('tableBuilt', () => {
            fhcapifactory.UserData.getMitarbeiterAnsicht().then((res)=>{
                this.person_info = res.data;
                this.$refs.funktionenTable.tabulator.setData(res.data.funktionen);
                this.$refs.betriebsmittelTable.tabulator.setData(res.data.mittel);
                
            })
        })
        
        
        
        
    },
     
    template: `
   
    <div :class="{'container':true}">
    <div :class="{'row':true}">
    <div :class="{'col':true}">
    
    </div>
    <div :class="{'col':true}">
    
    </div>
    </div>
    </div>

            <div :class="{'container-fluid':true}">
            <!-- here starts the row of the whole window -->
            <div :class="{'row':true}">
            <!-- this is the left column of the window -->
            <div :class="{'col-9':true}">
            <div :class="{'row':true}">
            <div :class="{'col':true}">
            <img :class="{'img-thumbnail':true}" :src="get_image_base64_src"></img>
            <div v-if="person_info?.foto_sperre">
            <p style="margin:0">Profilfoto gesperrt</p>
            <a href="#" @click.prevent="sperre_foto_function(false)" style="text-decoration:none">Sperre des Profilfotos aufheben</a>
            </div>
            <a href="#" @click.prevent="sperre_foto_function(true)" style="display:block; text-decoration:none"  v-else>Profilfoto sperren</a>
            
            
            </div>
            <div :class="{'col':true}">
           
            <p v-if="role=='Mitarbeiter'"><b>Mitarbeiter</b></p>
            <p v-else ><b>Student</b></p>
            
            <div v-for="(wert,bezeichnung) in personData">
            
            <div class="mb-3"  v-if="typeof wert == 'object' && bezeichnung=='Adressen'"><span style="display:block" v-for="element in wert">{{element.strasse}} <b>({{element.adr_typ}})</b><br/>{{ element.plz}} {{element.ort}}</span></div>
            <div v-else class="mb-3" ><span style="display:block;" v-for="(val,bez) in wert">{{bez}}: {{val}}</span></div>
            
            </div>
            
            </div>
            <div :class="{'col':true}">
            <ol style="list-style:none">
            
            <li v-for="(wert,bezeichnung) in kontaktInfo">
            
            <!-- HIER IST DAS DATUM DES FH AUSWEIS -->
            <div class="mb-3" v-if="bezeichnung=='FhAusweisStatus'">
            <p class="mb-0"><b>FH-Ausweis Status</b></p>
            <p class="mb-0">{{"Der FH Ausweis ist am "+ wert+ " ausgegeben worden."}}</p>
            </div>

            <!-- HIER SIND DIE EMAILS -->
      

            <div class="mb-3" v-if="typeof wert === 'object' && bezeichnung == 'emails'">
            <p class="mb-0"><b>eMail</b></p>
            <p v-for="email in wert" class="mb-0">{{email.type}}: <a  :href="'mailto:'+email.email"><b>{{email.email}}</b></a></p>
            </div>

            <!-- HIER SIND DIE PRIVATEN KONTAKTE -->
            <div class="mb-3" v-if="typeof wert === 'object' && bezeichnung=='Kontakte'">
            <p class="mb-0"><b>Private Kontakte</b></p>
            <div class="row" v-for="element in wert" >
            <div class="col-6">{{element.kontakttyp + "  " + element.kontakt+"  " }}</div>
            <div class="col-3"> {{element?.anmerkung}}</div>
            <div class="col-3"> 
            <i v-if="element.zustellung" class="fa-solid fa-check"></i>
            <i v-else="element.zustellung" class="fa-solid fa-xmark"></i>
            </div>
            </div>
            </div>
            
            <!--<pre v-else>{{JSON.stringify(wert,null,2)}}</pre>-->
            
            </li>
            </ol>


            </div>
            </div>
            
            <div :class="{'row':true}">
            
            <div :class="{'col':true}">
            <core-filter-cmpt title="Funktionen"  ref="funktionenTable" :tabulator-options="funktionen_table_options" :tableOnly />
          
            </div>
            
            </div>

            <div :class="{'row':true}">
            
            <div :class="{'col':true}">
               
            <core-filter-cmpt title="Entlehnte Betriebsmittel"  ref="betriebsmittelTable" :tabulator-options="betriebsmittel_table_options" :tableOnly />
       
            </div>
            </div>
            </div>

            <div  :class="{'col-3':true}">
            <div style="background-color:#EEEEEE" :class="{'row':true, 'py-4':true}">
            <a style="text-decoration:none" :class="{'my-1':true}" href="#">Zeitwuensche</a>
            <a style="text-decoration:none" :class="{'my-1':true}" href="#">Lehrveranstaltungen</a>
            <a style="text-decoration:none" :class="{'my-1':true}" href="#">Zeitsperren von Gschnell</a>
            </div>
            <div :class="{'row':true}">
            <h5 :class="{'fs-3':true}" style="margin-top:1em">Mailverteilers</h5>
            <p :class="{'fs-6':true}">Sie sind Mitgglied in folgenden Verteilern:</p>
            <div  :class="{'row':true, 'text-break':true}" v-for="verteiler in person_info?.mailverteiler">
            <div :class="{'col-6':true}"><a :href="verteiler.mailto"><b>{{verteiler.gruppe_kurzbz}}</b></a></div> 
            <div :class="{'col-6':true}">{{verteiler.beschreibung}}</div>
            </div>
            </div>
            </div>
            </div>
            </div>
    `,
};
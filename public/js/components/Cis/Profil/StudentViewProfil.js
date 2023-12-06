import fhcapifactory from "../../../apps/api/fhcapifactory.js";


export default {
 
  data() {
    return {
   

      
    };
  },

    //? this is the prop passed to the dynamic component with the custom data of the view
  props: ["data"],
  methods: {

    sperre_foto_function(value) {
      if (!this.data) {
        return;
      }
      fhcapifactory.UserData.sperre_foto_function(value).then((res) => {
        this.data.foto_sperre = res.data.foto_sperre;
      });
    },
  },
  computed: {
    get_image_base64_src() {
      if (!this.data) {
        return "";
      }
      return "data:image/jpeg;base64," + this.data.foto;
    },
    //? this computed function returns all the informations for the first column in the profil 
    personData() {
      if (!this.data) {
        return {};
      }

      return {
        Allgemein: {
          Username: this.data.username,
          Matrikelnummer: this.data.matrikelnummer,
          Anrede: this.data.anrede,
          Titel: this.data.titel,
          Vorname: this.data.vorname,
          Nachname: this.data.nachname,
          Postnomen: this.data.postnomen,
        },
      
        
        SpecialInformation: {
          Studiengang: this.data.studiengang,
          Semester: this.data.semester,
          Verband: this.data.verband,
          Gruppe: this.data.gruppe,
          Personenkennzeichen: this.data.personenkennzeichen,
        },
      };
    },
    //? this computed function returns the data for the second column in the profil 
    kontaktInfo() {
      if (!this.data) {
        return {};
      }

      return {
       
        emails: this.data.emails,
      
      };
    },
  },

  mounted() {
   

  },

  template: `


            <div class="container-fluid">
            <!-- here starts the row of the whole window -->
                <div class="row">
                <!-- this is the left column of the window -->
                    <div class="col-9">
                        <div class="row">
                            <div class="col">
                                <img class="img-thumbnail" :src="get_image_base64_src"></img>
                                
                               
                            </div>
                            
                          
                            <div class="col">
                        
                        
                                <h3 >Student</h3>
                                
                                <div v-for="(wert,bezeichnung) in personData">
                                
                                <div class="mb-3"  v-if="typeof wert == 'object' && bezeichnung=='Adressen'"><span style="display:block" v-for="element in wert">{{element.strasse}} <b>({{element.adr_typ}})</b><br/>{{ element.plz}} {{element.ort}}</span></div>
                                <div v-else class="mb-3" ><span style="display:block;" v-for="(val,bez) in wert">{{bez}}: {{val}}</span></div>
                                
                                </div>
                                
                            </div>
                            <div class="col">
                                <div style="list-style:none">
                                
                                    <p v-for="(wert,bezeichnung) in kontaktInfo">
                                        <!-- HIER SIND DIE EMAILS -->
                                        <div class="mb-3" v-if="typeof wert === 'object' && bezeichnung == 'emails'">
                                            <p class="mb-0"><b>eMail</b></p>
                                            <p v-for="email in wert" class="mb-0">{{email.type}}: <a style="text-decoration:none" :href="'mailto:'+email.email">{{email.email}}</a></p>
                                        </div>

                                       
                                    
                                    </p>
                                </div>


                            </div>
                        </div>
                      
                    
                     
                
                    </div>

                    <div  class="col-3">
                        <div style="background-color:#EEEEEE" class="row py-4">
                        <a style="text-decoration:none" class="my-1" href="#">Zeitwuensche</a>
                        <a style="text-decoration:none" class="my-1" href="#">Lehrveranstaltungen</a>
                        <a style="text-decoration:none" class="my-1" href="#">Zeitsperren von Gschnell</a>
                        </div>
                        <div class="row">
                            <h5 class="fs-3" style="margin-top:1em">Mailverteilers</h5>
                            <p class="fs-6">Sie sind Mitgglied in folgenden Verteilern:</p>
                            <div  class="row text-break" v-for="verteiler in data?.mailverteiler">
                                <div class="col-6"><a :href="verteiler.mailto"><b>{{verteiler.gruppe_kurzbz}}</b></a></div> 
                                <div class="col-6">{{verteiler.beschreibung}}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
    `,
};
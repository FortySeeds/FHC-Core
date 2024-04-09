export default {
  components: {
    AutoComplete: primevue.autocomplete,
  },

  props: {
    data: Object,
    isMitarbeiter: {
      type: Boolean,
      default: false,
    },
  },

  inject: ["getZustelladressenCount"],

  data() {
    return {
      gemeinden: [],
      selectedNation: null,
      nationenList: [],
      originalValue: null,
      zustellAdressenCount: null,
    };
  },

  watch: {
    "data.gemeinde": function (newValue, oldValue) {
      this.$emit("profilUpdate", this.isChanged ? this.data : null);
    },
  },
  
  methods: {
    autocompleteSearch: function (event) {
      this.gemeinden = this.gemeinden.map((gemeinde) => gemeinde);
    },

    getGemeinde: function () {
      //? only query the gemeinde is the nation is Austria and the PLZ is greater than 999 and less than 32000
      if (
        this.data.nation &&
        this.data.nation === "A" &&
        this.data.plz &&
        this.data.plz > 999 &&
        this.data.plz < 32000
      ) {
        Vue.$fhcapi.UserData.getGemeinden(this.data.nation, this.data.plz).then(
          (res) => {
            if (res.data.length) {
              this.gemeinden = res.data;
            }
          }
        );
      } else {
        this.gemeinden = [];
      }
    },

    updateValue: function (event, bind) {
      //? sets the value of a property to null when an empty string is entered to keep the isChanged function valid
      if (bind === "zustelladresse") {
        this.data[bind] = event.target.checked;
      } else {
        this.data[bind] = event.target.value === "" ? null : event.target.value;
      }

      this.$emit("profilUpdate", this.isChanged ? this.data : null);
      // update the zustellAdressen count
      this.zustellAdressenCount = this.getZustelladressenCount();
    },
  },

  computed: {
    showZustellAdressenWarning: function () {
      // if zustellAdressenCount is not 0 and the own kontakt has the flag zustellung set to true
      if (!this.zustellAdressenCount.includes(this.data.adresse_id)) {
        return this.data.zustelladresse && this.zustellAdressenCount.length;
      }
      return this.zustellAdressenCount.length >= 2 && this.data.zustelladresse;
    },
    isChanged: function () {
      if (
        !this.data.strasse ||
        !this.data.plz ||
        !this.data.ort ||
        !this.data.typ
      ) {
        return false;
      }
      return this.originalValue !== JSON.stringify(this.data);
    },
  },

  created() {
    Vue.$fhcapi.UserData.getAllNationen().then((res) => {
      this.nationenList = res.data;
      this.getGemeinde();
    });

    this.originalValue = JSON.stringify(this.data);
    this.zustellAdressenCount = this.getZustelladressenCount();
  },

  template: /*html*/ `
     <div class="gy-3 row justify-content-center align-items-center">
     <pre>{{JSON.stringify(data,null,2)}}</pre>
     <!-- warning message for too many zustellungs Adressen -->
     <div v-if="showZustellAdressenWarning" class="col-12 ">
     <div class="card bg-danger mx-2">
     <div class="card-body text-white ">
     <span>{{$p.t('profilUpdate','zustelladresseWarning')}}</span>
     </div>
     </div>
     </div>
     <!-- End of warning -->


     <div class="col-12 ">
        
       
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" @change="updateValue($event,'zustelladresse')" :checked="data.zustelladresse" id="flexCheckDefault">
                <label class="form-check-label" for="flexCheckDefault">
                    {{$p.t('person','zustelladresse')}}
                </label>
            </div>
        
       
           
            
            
        </div>
      
      
        <div  class="col-12 col-sm-9 col-xl-12 col-xxl-9 order-1">

        <div class="form-underline ">
        <div class="form-underline-titel">{{$p.t('person','strasse')}}*</div>
        <input  class="form-control" :value="data.strasse" @input="updateValue($event,'strasse')" :placeholder="data.strasse">
        
        </div>


        </div>
        
        <div class=" order-2 order-sm-4 order-xl-3 order-xxl-4 col-12 col-sm-5  col-xl-8 col-xxl-5  ">
            
            <div  class="form-underline">
                <div class="form-underline-titel">{{$p.t('profilUpdate','kontaktTyp')}}*</div>
        
                <select  :value="data.typ" @change="updateValue($event,'typ')" class="form-select" aria-label="Select Kontakttyp">
                    <option selected></option>
                    <option value="Nebenwohnsitz">{{$p.t('profilUpdate','nebenwohnsitz')}}</option>
                    <option value="Hauptwohnsitz">{{$p.t('profilUpdate','hauptwohnsitz')}}</option>
                    <option v-if="isMitarbeiter" value="Homeoffice">{{$p.t('profilUpdate','homeoffice')}}</option>
                    <option v-if="isMitarbeiter" value="Rechnungsadresse">{{$p.t('profilUpdate','rechnungsadresse')}}</option>
                  
                </select>    
            </div>
        

            
            </template>

        </div>

        <div  class="order-3 order-sm-3 order-xl-2 order-xxl-3 col-12 col-sm-7 col-xl-12 col-xxl-7 " >
            
            <div class="form-underline ">
            <div class="form-underline-titel">{{$p.t('person','ort')}}*</div>
            <input  class="form-control" :value="data.ort" @input="updateValue($event,'ort')" :placeholder="data.ort">
        
            </div>
        </div>
        <div  class="order-4 order-sm-2 order-xl-4 order-xxl-2 col-12 col-sm-3 col-xl-4 col-xxl-3 ">
            <div class="form-underline ">
            <div class="form-underline-titel">{{$p.t('person','plz')}}*</div>
    
            <input  class="form-control" :value="data.plz" @input="updateValue($event,'plz')" @input="getGemeinde" :placeholder="data.plz">
        
            </div>
        </div>
        <div class="col-6 order-5">
       
        <div class="form-underline ">
        <div class="form-underline-titel">{{$p.t('person','gemeinde')}}*</div>
        <auto-complete class="w-100" v-model="data.gemeinde" dropdown :forceSelection="data.nation ==='A'?true:false" :suggestions="gemeinden" @complete="autocompleteSearch" ></auto-complete>
        </div>
        </div>
        <div class="col-6 order-5 ">
        <div class="form-underline ">
        <div class="form-underline-titel">{{$p.t('person','nation')}}*</div>
            <select  :value="data.nation" @change="updateValue($event,'nation')" @change="getGemeinde" class="form-select" aria-label="Select Kontakttyp">
                <option selected></option>
                <option :value="nation.code" v-for="nation in nationenList">{{nation.langtext}}</option>
            
            </select> 
        
        </div>
        </div>
       
    </div>
    `,
};

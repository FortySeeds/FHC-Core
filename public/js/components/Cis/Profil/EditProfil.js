import BsModal from "../../Bootstrap/Modal.js";
import Alert from "../../Bootstrap/Alert.js";
import EditProfilSelect from "./EditProfilSelect.js";

export default {
  components: {
    BsModal,
    Alert,
    EditProfilSelect,
  },
  mixins: [BsModal],
  props: {
    value: Object,
    title: String,
    zustelladressenCount: Function,
    zustellkontakteCount: Function,
    /*
     * NOTE(chris):
     * Hack to expose in "emits" declared events to $props which we use
     * in the v-bind directive to forward all events.
     * @see: https://github.com/vuejs/core/issues/3432
     */
    onHideBsModal: Function,
    onHiddenBsModal: Function,
    onHidePreventedBsModal: Function,
    onShowBsModal: Function,
    onShownBsModal: Function,
  },
  
  data() {
    return {
      topic: null,
      profilUpdate: null,
      editData: this.value,
      fileID:null,
      breadcrumb: null,

      result: false,
      info: null,
    };
  },

  provide(){
    return {
      updateFileID: this.updateFileIDFunction,
      zustellAdresseCount:this.zustelladressenCount,
      zustellKontaktCount:this.zustellkontakteCount,
    }
  }, 

  methods: {

    updateFileIDFunction: function(newFileID){
      this.fileID = newFileID;
    },

    async submitProfilChange() {
      //? check if data is valid before making a request
      if (this.topic && this.profilUpdate) {
        
        //? if profil update contains any attachment
        if (this.fileID) {
          const fileData = await this.uploadFiles(this.fileID);
          
          this.fileID = fileData? fileData : null;
        }

        //? inserts new row in public.tbl_cis_profil_update
        //* calls the update api call if an update field is present in the data that was passed to the modal
        const handleApiResponse = (res) => {
          if (res.data.error == 0) {
            this.result = true;
            this.hide();
            Alert.popup(
              "Ihre Anfrage wurde erfolgreich gesendet. Bitte warten Sie, während sich das Team um Ihre Anfrage kümmert."
            );
          } else {
            this.result = false;
            this.hide();
            Alert.popup(
              "Ein Fehler ist aufgetreten: " + JSON.stringify(res.data.retval)
            );
          }
        };

        this.editData.updateID
          ? Vue.$fhcapi.ProfilUpdate.updateProfilRequest(
              this.topic,
              this.profilUpdate,
              this.editData.updateID,
              this.fileID? this.fileID[0]: null
            ).then((res) => {
              handleApiResponse(res);
            })
          : Vue.$fhcapi.ProfilUpdate.insertProfilRequest(
              this.topic,
              this.profilUpdate,
              this.fileID? this.fileID[0]: null

            ).then((res) => {
              handleApiResponse(res);
            });
      }
    },

    uploadFiles: async function (files) {
      let updatedFiles = [];

      if (this.editData.updateID) {
        
      }

      
      
      if (files[0].type !== "application/x.fhc-dms+json"){
        let formData = new FormData();
        formData.append("files[]", files[0]);
        const result = this.editData.updateID ?
        //? updating old attachment by replacing
        //* second parameter of api request insertFile checks if the file has to be replaced or not
        await Vue.$fhcapi.ProfilUpdate.insertFile(formData,this.editData.updateID).then(res => {
          return res.data?.map((file) => file.dms_id);
        })
        :
        //? fresh insert of new attachment
        await Vue.$fhcapi.ProfilUpdate.insertFile(formData).then(res => {
          return res.data?.map((file) => file.dms_id);
        })
        return result;
      }else{
        //? attachment hasn't been replaced
        return false;
      }

    },
  },
  computed: {},
  created() {
    if (this.editData.topic) {
      //? if the topic was passed through the prop add it to the component
      this.topic = this.editData.topic;
    }
  },
  mounted() {
    this.modal = this.$refs.modalContainer.modal;
  },
  popup(options) {
    return BsModal.popup.bind(this)(null, options);
  },
  template: `

  <bs-modal ref="modalContainer" v-bind="$props" body-class="" dialog-class="modal-lg" class="bootstrap-alert" backdrop="false" >
    
  <template v-if="title" v-slot:title>
      {{title }}  
    </template>
    <template v-slot:default>

    <nav aria-label="breadcrumb" class="ps-2  ">
      <ol class="breadcrumb ">
        <li class="breadcrumb-item"  v-for="element in breadcrumb">{{element}}</li>
      
      </ol>
    </nav>

    <edit-profil-select @submit="submitProfilChange" v-model:breadcrumb="breadcrumb" v-model:topic="topic" v-model:profilUpdate="profilUpdate" ariaLabel="test" :list="editData"></edit-profil-select>
   

    </template>
    <!-- optional footer -->
    <template   v-slot:footer>
      
    <button class="btn btn-outline-danger " @click="hide">Abbrechen</button>    
      <button :disabled="!profilUpdate"  @click="submitProfilChange" role="button" class="btn btn-primary">Senden</button>
    </template>
    <!-- end of optional footer --> 
  </bs-modal>`,
};
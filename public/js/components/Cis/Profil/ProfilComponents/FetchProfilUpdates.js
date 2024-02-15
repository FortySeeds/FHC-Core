import EditProfil from "../EditProfil.js";
//? EditProfil is the modal used to edit the profil updates
export default {
  props: {
    data: {
      type: Object,
    },
  },
  inject:['getZustellkontakteCount','getZustelladressenCount'],

  emits: ["fetchUpdates"],
  data() {
    return {};
  },
  methods: {
    deleteRequest: function (item) {
      Vue.$fhcapi.ProfilUpdate.deleteProfilRequest(item.profil_update_id).then(
        (res) => {
          if (res.data.error) {
            //? open alert
            console.log(res.data);
          } else {
            this.$emit("fetchUpdates");
          }
        }
      );
    },
    getView: function (topic, status) {

      if(!(status === "pending")){
        return "Status";
      }

      switch (topic) {
        case "Private Kontakte":
          return "EditKontakt";
          break;
        case "Add Kontakte":
          return "EditKontakt";
          break;
        case "Delete Kontakte":
          return "Kontakt";
          break;
        case "Private Adressen":
          return "EditAdresse";
          break;
        case "Add Adressen":
          return "EditAdresse";
          break;
        case "Delete Adressen":
          return "Adresse";
          break;
        default:
          return "TextInputDokument";
          break;
      }
    },
    async openModal(updateRequest) {
      let view = this.getView(updateRequest.topic, updateRequest.status);
      
      let data = null;
      let content = null;
      let files = null;
      let withFiles = false;

      if (view === "TextInputDokument") {
        data = {
          titel: updateRequest.topic,
          value: updateRequest.requested_change.value,
        };

        const filesFromDatabase = await Vue.$fhcapi.ProfilUpdate.getProfilRequestFiles(updateRequest.profil_update_id).then(res=>{
          return res.data;
        });

        files= filesFromDatabase;
        if(files){
          withFiles = true;
        }
      } else {
        data = updateRequest.requested_change;
      }

      content = {
        updateID: updateRequest.profil_update_id,
        view: view,
        data: data,
        withFiles: withFiles,
        topic: updateRequest.topic,
        files: files,
        
      };

      //?TODO: check if updateRequest.uid is a mitarbeiter, if so add the flag isMitarbeiter:true
      if(view === "EditAdresse"){
        const isMitarbeiter = await Vue.$fhcapi.UserData.isMitarbeiter(updateRequest.uid).then(res => res.data);

        if(isMitarbeiter){
          content['isMitarbeiter']=isMitarbeiter;
        }
      } 

      //? adds the status information if the profil update request was rejected or accepted
      if (updateRequest.status !== "pending") {
        content["status"] = updateRequest.status;
        content["status_message"] = updateRequest.status_message;
        content["status_timestamp"] = updateRequest.status_timestamp;
      }

      //? only show the popup if also the right content is available
      if (content) {
        EditProfil.popup({
          value: content,
          title: updateRequest.topic,
          zustellkontakteCount:this.getZustellkontakteCount,
          zustelladressenCount:this.getZustelladressenCount,
        })
          .then((res) => {
            if (res === true) {
              this.$emit("fetchUpdates");
            }
          })
          .catch((e) => {
            // Wenn der User das Modal abbricht ohne Änderungen
          });
      }
    },
  },
  created() {},
  computed: {},
  template: `
    <div  class="card text-nowrap" >
                      <div class="card-header">
                      Profil Updates
                      </div>
                      <div class="card-body" >
    <div class="table-responsive">
        <table class="m-0  table  table-hover">
            <thead >
                <tr >
                <th scope="col">Topic</th>
                <th scope="col">Status</th>
                <th scope="col">Date</th>
                <th class="text-center" scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in data" :style="item.status=='accepted'?'background-color:lightgreen':item.status==='rejected'?'background-color:lightcoral':''">
                <td class="align-middle text-wrap ">{{item.topic}}</td>
                <td class="align-middle " >{{item.status}}</td>
                <td class="align-middle">{{item.status_timestamp}}</td>
                
                <template v-if="item.status === 'pending'">
                <td>
                
                <div class="d-flex flex-row justify-content-evenly">
                <template v-if="item.topic.toLowerCase().includes('delete')">
                <div  class="align-middle text-center"><i role="button" @click="openModal(item)" class="fa fa-eye"></i></div>
                </template>

                <template v-else >
                <div class="align-middle text-center" ><i style="color:#00639c" @click="openModal(item)" role="button" class="fa fa-edit"></i></div>
                </template>
                
                <div class="align-middle text-center"><i style="color:red" role="button" @click="deleteRequest(item)" class="fa fa-trash"></i></div>
                </div>

                </td>
                </template>

                <template v-else>
                
                <td  class="align-middle text-center">
                <div class="d-flex flex-row justify-content-evenly">
                <i  role="button" @click="openModal(item)" class="fa fa-eye"></i>
                </div>
                </td>
                
                </template>
                
                
                </tr>
            </tbody>
        </table>
    </div>
    </div>
    </div>

    
    `,
};
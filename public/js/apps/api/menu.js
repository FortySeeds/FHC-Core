export default {
    search: function(searchsettings) {
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root 
                  + FHC_JS_DATA_STORAGE_OBJECT.ci_router
                  + 'components/SearchBar/search';
        return axios.post(url, searchsettings);
    },
    getLvEinheiten: function(semester){
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root 
                  + FHC_JS_DATA_STORAGE_OBJECT.ci_router
                  + `Cis/Studium/getLvEinheiten/${semester}`;
        return axios.get(url);
    },
    getAktuelleLvEinheiten: function(){
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root 
                  + FHC_JS_DATA_STORAGE_OBJECT.ci_router
                  + `Cis/Studium/getAktuelleLvEinheiten`;
        return axios.get(url);
    },
    getSemesterOfStudent:function(){
        const url = FHC_JS_DATA_STORAGE_OBJECT.app_root 
                  + FHC_JS_DATA_STORAGE_OBJECT.ci_router
                  + `Cis/Studium/getSemesterOfStudent`;
        return axios.get(url);
    },
  };
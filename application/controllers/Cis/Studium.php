<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 *
 */
class Studium extends Auth_Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct([
            'index' => ['student/anrechnung_beantragen:r', 'user:r'], // TODO(chris): permissions?
            'getLvEinheiten' => ['student/anrechnung_beantragen:r', 'user:r'],
            'getAktuelleLvEinheiten' => ['student/anrechnung_beantragen:r', 'user:r'],
            'getSemesterOfStudent' => ['student/anrechnung_beantragen:r', 'user:r'],
            
            
        ]);
        $this->load->model('education/Lehreinheit_model', 'LehreinheitModel');
        $this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');
        $this->load->model('crm/Student_model', 'StudentModel');
        $this->load->model('organisation/Studiensemester_model', 'SemesterModel');
        $this->load->model('education/Studentlehrverband_model', 'LehrverbandModel');
        
        
        $this->uid = getAuthUID();
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Public methods

    /**
     * @return void
     */
    public function index()
    {
        $this->load->view('Cis/AnrechnungMenu');
    }

    public function getLvEinheiten($semester)
    {
        //TODO: change static student_uid by actual uid
        if(!isset($semester)){
            return;
        }
        $lehrveranstaltung_res = $this->LehrveranstaltungModel->getLvsByStudent('io22m029',$semester);
        if (isError($lehrveranstaltung_res)) {
            show_error("was not able to load lehrveranstaltungen for uid: " . $this->uid);
        }
        $lehrveranstaltung_res = hasData($lehrveranstaltung_res) ? getData($lehrveranstaltung_res) : null;
     
        echo json_encode($lehrveranstaltung_res = array_map(function ($element) {
            
            $obj = new stdClass();
            $obj->bezeichnung = $element->bezeichnung;
            $obj->lehrveranstaltung_id = $element->lehrveranstaltung_id;
            return $obj;
        }, $lehrveranstaltung_res));
 
    }

    public function getAktuelleLvEinheiten()
    {

        //TODO: change static student_uid by actual uid
        //TODO: change static semester with $aktSemester
        $aktSemester = $this->SemesterModel->getAkt();
        $lehrveranstaltungArray = [];
       
        if(isError($aktSemester)){
            show_error("was not able to query the current semester");
        }
        $aktSemester = hasData($aktSemester) ? getData($aktSemester)[0] : null;
        $aktSemester = $aktSemester->studiensemester_kurzbz;
        
        $lehrveranstaltung_res = $this->LehrveranstaltungModel->getLvsByStudent('io22m029','WS2023');
        if (isError($lehrveranstaltung_res)) {
            show_error("was not able to load lehrveranstaltungen for uid: " . $this->uid);
        }
        $lehrveranstaltung_res = hasData($lehrveranstaltung_res) ? getData($lehrveranstaltung_res) : null;
        $lehrveranstaltung_res = array_map(function($lehrveranstaltung){
            return $lehrveranstaltung->lehrform_kurzbz;
        },$lehrveranstaltung_res);
        echo json_encode($lehrveranstaltung_res);
       
        /* foreach($lehrveranstaltung_res as $lehrveranstaltung){
            $lehrVerObj= new stdClass();
            $lehrVerObj->lehrveranstaltung_bezeichnung= $lehrveranstaltung->bezeichnung;
            $lehrVerObj->lehrveranstaltung_id= $lehrveranstaltung->lehrveranstaltung_id;
            $lehrVerObj->lehreinheiten= array_map(function($einheit){
                
                $einheitObj = new stdClass();   
                $einheitObj->lehreinheit_id = $einheit->lehreinheit_id;
                $einheitObj->lehrform_kurzbz = $einheit->lehrform_kurzbz;
                return $einheitObj;
            },$this->LehreinheitModel->getLesForLv($lehrveranstaltung->lehrveranstaltung_id,'WS2023')) ;
            array_push($lehrveranstaltungArray, $lehrVerObj);
        }
        echo json_encode($lehrveranstaltungArray);
        */
 
    }

    public function getSemesterOfStudent(){
        $this->LehrverbandModel->addSelect('studiensemester_kurzbz');
        //TODO: change static uid to actual uid
        $semester_res = $this->LehrverbandModel->loadWhere(['student_uid'=>'io22m029']);
        
        if(isError($semester_res)){
            show_error("was not able to load the semester for the uid ".$this->uid);
        }
        $semester_res = hasData($semester_res)? getData($semester_res):null;
        $semester_res =array_map(function($item){return $item->studiensemester_kurzbz;},$semester_res);
        echo json_encode($semester_res);
    }
}

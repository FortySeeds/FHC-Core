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
        //io22m029
        $lehrveranstaltung_res = $this->LehrveranstaltungModel->getLvsByStudent($this->uid,$semester);
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
        
       
        if(isError($aktSemester)){
            show_error("was not able to query the current semester");
        }
        $aktSemester = hasData($aktSemester) ? getData($aktSemester)[0] : null;
        $aktSemester = $aktSemester->studiensemester_kurzbz;
        //io22m029
        $lehrveranstaltung_res = $this->LehrveranstaltungModel->getLvsByStudent($this->uid,'WS2023');
        if (isError($lehrveranstaltung_res)) {
            show_error("was not able to load lehrveranstaltungen for uid: " . $this->uid);
        }
        $lehrveranstaltung_res = hasData($lehrveranstaltung_res) ? getData($lehrveranstaltung_res) : null;
        $lehrveranstaltung_res = array_map(function($lehrveranstaltung){
            $obj = new stdClass();
            $obj->lehrveranstaltung_id = $lehrveranstaltung->lehrveranstaltung_id;
            $obj->bezeichnung = $lehrveranstaltung->bezeichnung;
            return $obj;
        },$lehrveranstaltung_res);

        //TODO: "aktuelleSemester"=>$aktSemester
        echo json_encode(["lehrveranstaltungen"=>$lehrveranstaltung_res, "aktuelleSemester"=>'WS2023' ]);
       
    }

    public function getSemesterOfStudent(){
        $this->LehrverbandModel->addSelect('studiensemester_kurzbz');
        //TODO: change static uid to actual uid
        //io22m029
        $semester_res = $this->LehrverbandModel->loadWhere(['student_uid'=>$this->uid]);
        
        if(isError($semester_res)){
            show_error("was not able to load the semester for the uid ".$this->uid);
        }
        $semester_res = hasData($semester_res)? getData($semester_res):null;
        $semester_res =array_map(function($item){return $item->studiensemester_kurzbz;},$semester_res);
        echo json_encode($semester_res);
    }
}

<?php

include_once '../../libs/model.php';
include_once 'stageModel.php';

class ActivityStageModel extends Model {

    private $etapaNombre;
    private $status;
    private $projectLeader;
    private $projectManager;
    private $projectCoordinator;
    private $QAEngineer;
    // private $stages = [];

    public function __construct(){
        parent::__construct();

        $this->idProyecto = '';
        $this->projectID = '';
        $this->nombre = '';
    }

    public function setId($idProyecto){           $this->idProyecto = $idProyecto;}
    public function setProyectID($projectID){           $this->projectID = $projectID;}
    public function setName($nombre){           $this->nombre = $nombre;}
    public function addStage($stage){          array_push($stages, $stage);}

    public function getId(){        return $this->idProyecto;}
    public function getProjectID(){        return $this->projectID;}
    public function getName(){  return $this->nombre;}
    public function getStages(){  return $this->stages;}

    // GET ONE ITEM
    public function get() {
        try{
            $query = $this->prepare('SELECT idProyecto, projectID, nombre
                                      FROM proyecto
                                      WHERE idProyecto = :idProyecto');
            $query->execute([ 'idProyecto' => $this->getId()]);
            $result = $query->fetch(PDO::FETCH_ASSOC);

            $this->idProyecto = $result['idProyecto'];
            $this->projectID = $result['projectID'];
            $this->nombre = $result['nombre'];

            $stageModel = new StageModel();
            $this->stages = $stageModel->getall($result['idProyecto']);

            return $this;
        }catch(PDOException $e){
            return false;
        }
    }

    function countStages() {
      try{
          $query = $this->prepare('SELECT * from etapa');
          $query->execute([ 'idProyecto' => $idProyecto]);
          $result = $query->fetch(PDO::FETCH_ASSOC);

          return count($result);
      }catch(PDOException $e){
          return false;
      }
    }

    function getAllStages($idProyecto){
        $items = [];
        $stgModel = new StageModel();

        $activities = $stgModel->getAllActivities($idProyecto);

        foreach ($Activities as $activity) {

        }
    }




    public function save(){
        try{
            $dbh = $this->getDB();
            $query = $dbh->prepare('INSERT INTO users (userName, password, role, photo, name, address, mail, phone, active, idBranch) VALUES(:userName, :password, :role, :photo, :name, :address, :mail, :phone, :active, :idBranch)');
            $query->execute([
                'userName'  => $this->userName,
                'password'  => $this->password,
                'role'      => $this->role,
                'photo'     => $this->photo,
                'name'      => $this->name,
                'address'   => $this->address,
                'mail'      => $this->mail,
                'phone'     => $this->phone,
                'active'    => $this->active,
                'idBranch'  => $this->idBranch
                ]);
            if($query->rowCount()){
                return $dbh->lastInsertId();
            } else {
                return false;
            }
        }catch(PDOException $e){
            echo $e;
            return false;
        }
    }






}

?>

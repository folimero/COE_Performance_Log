<?php

$file = '../../libs/model.php';

if(file_exists($file)){
    require_once $file;
}else{
    $file = '../libs/model.php';
    if(file_exists($file)){
        require_once $file;
    }else{
        echo $file . " NOT EXIST";
        exit;
    }
}

$file = 'activityStage/activityProjectModel.php';

if(file_exists($file)){
    require_once $file;
}else{
    $file = '../activityStage/activityProjectModel.php';
    if(file_exists($file)){
        require_once $file;
    }else{
        echo $file . " NOT EXIST";
        exit;
    }
}

class StageModel extends Model {

    private $idEtapa;
    private $nombre;
    private $descripcion;
    private $total;
    private $completed;
    private $activities = [];

    public function __construct() {
        parent::__construct();

        $this->idEtapa = '';
        $this->nombre = '';
        $this->descripcion = '';
    }

    public function setId($idEtapa){           $this->idEtapa = $idEtapa;}
    public function setName($nombre){           $this->nombre = $nombre;}
    public function setDescription($descripcion){           $this->descripcion = $descripcion;}
    public function setTotal($total){           $this->total = $total;}
    public function setCompleted($completed){           $this->completed = $completed;}
    public function setActivities($activities){           $this->activities = $activities;}

    public function getId(){        return $this->idEtapa;}
    public function getName(){        return $this->nombre;}
    public function getDescription(){  return $this->descripcion;}
    public function getTotal(){  return $this->total;}
    public function getCompleted(){  return $this->completed;}
    public function getActivities(){  return $this->activities;}

    public function getAll($idProyecto) {
        $items = [];
        $actModel = new ActivityProjectModel();
        $activities = $actModel->getProjectActivities($idProyecto);

        try{
            $query = $this->query('SELECT idEtapa, nombre, descripcion FROM etapa');

            while($p = $query->fetch(PDO::FETCH_ASSOC)){
                $item = new StageModel();
                $item->setId($p['idEtapa']);
                $item->setName($p['nombre']);
                $item->setDescription($p['descripcion']);

                $tempActs = [];
                $tempTotal = 0;
                $tempCompleted = 0;

                foreach ($activities as $activity) {
                    if ($activity->getIdEtapa() == $item->getId()) {

                        if ($this->checkCompletion($activity)) {
                            $tempCompleted += 1;
                        }
                        $tempTotal += 1;
                        array_push($tempActs, $activity);
                    }
                }
                $item->setTotal($tempTotal);
                $item->setCompleted($tempCompleted);
                $item->setActivities($tempActs);
                array_push($items, $item);
            }
            return $items;
        }catch(PDOException $e) {
            return false;
        }
    }

    public function countStages() {
        try{
            $query = $this->prepare('SELECT * FROM etapa');
            $query->execute([ 'idProyecto' => $idProyecto]);
            $result = $query->fetch(PDO::FETCH_ASSOC);

            $this->activities = $result;
            // $this->idProyecto = $result['idProyecto'];
            // $this->projectID = $result['projectID'];
            // $this->nombre = $result['nombre'];

            return $this;
        }catch(PDOException $e) {
            return false;
        }
    }

    private function checkCompletion($activity) {
        // TIPOS DE VALIDACION
        // -1     N/A
        // 0      Sin Status
        // 1      Completado
        // 2      Aprobado
        // 3      Rechazado

        if ($activity->getCompletado() == 1 || $activity->getCompletado() == -1 || $activity->getCompletado() == 2) {
            return true;
        } else {
            return false;
        }
    }
}

?>

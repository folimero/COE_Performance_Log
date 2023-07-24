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

class ActivityProjectModel extends Model {

    private $idActividad;
    private $tipo;
    private $nombre;
    private $descripcion;
    private $idEtapa;
    private $completado;

    public function __construct(){
        parent::__construct();

        $this->idActividad = '';
        $this->tipo = '';
        $this->nombre = '';
        $this->descripcion = '';
        $this->idEtapa = '';
        $this->completado = '';
    }

    public function setId($idActividad){           $this->idActividad = $idActividad;}
    public function setType($tipo){           $this->tipo = $tipo;}
    public function setName($nombre){           $this->nombre = $nombre;}
    public function setDescription($descripcion){           $this->descripcion = $descripcion;}
    public function setIdEtapa($idEtapa){           $this->idEtapa = $idEtapa;}
    public function setCompletado($completado){           $this->completado = $completado;}

    public function getId(){        return $this->idActividad;}
    public function getType(){        return $this->tipo;}
    public function getName(){  return $this->nombre;}
    public function getDescription(){  return $this->descripcion;}
    public function getIdEtapa(){  return $this->idEtapa;}
    public function getCompletado(){  return $this->completado;}

    public function getProjectActivities($idProyecto) {
        $items = [];

        try{
            $query = $this->prepare('SELECT ap.idActividades_proyecto, a.tipo, a.nombre, a.descripcion, a.idEtapa, ap.completado
                                    FROM actividades_proyecto AS ap
                                    INNER JOIN actividad AS a
                                    ON ap.idActividad = a.idActividad
                                    WHERE ap.idProyecto = :idProyecto
                                    ORDER BY a.idEtapa ASC');
            $query->execute(['idProyecto' => $idProyecto]);

            while($p = $query->fetch(PDO::FETCH_ASSOC)){
                $item = new ActivityProjectModel();
                $item->setId($p['idActividades_proyecto']);
                $item->setType($p['tipo']);
                $item->setName($p['nombre']);
                $item->setDescription($p['descripcion']);
                $item->setIdEtapa($p['idEtapa']);
                $item->setCompletado($p['completado']);

                array_push($items, $item);
            }
            return $items;
        }catch(PDOException $e){
            echo $e;
        }
    }
}

?>

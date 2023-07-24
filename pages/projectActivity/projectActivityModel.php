<?php


$file = '../../libs/model.php';

if(file_exists($file)){
    require_once $file;
}else{
    $file = '../libs/model.php';
    if(file_exists($file)){
        require_once $file;
    }else{
        echo "GENERAL MODEL NOT EXIST";
        exit;
    }
}

class ProjectActivityModel extends Model {

    private $idActividades_proyecto;
    private $idProyecto;
    private $pNombre;
    private $idActividad;
    private $aNombre;
    private $fechaInicio;
    private $fechaRequerida;
    private $fechaEntrega;
    private $fechaCrea;
    private $completado;
    private $notas;
    private $entregadoPor;
    private $fechaAprobacion;
    private $aprobadoPor;
    private $respAct;

    public function __construct(){
        parent::__construct();

        $this->idActividades_proyecto = '';
        $this->idProyecto = '';
        $this->pNombre = '';
        $this->idActividad = '';
        $this->aNombre = '';
        $this->fechaInicio = '';
        $this->fechaRequerida = '';
        $this->fechaEntrega = '';
        $this->fechaCrea = '';
        $this->completado = '';
        $this->notas = '';
        $this->entregadoPor = '';
        $this->fechaAprobacion = '';
        $this->aprobadoPor = '';
        $this->respAct = '';
    }

    public function setId($idActividades_proyecto){         $this->idActividades_proyecto = $idActividades_proyecto;}
    public function setIdProyecto($idProyecto){             $this->idProyecto = $idProyecto;}
    public function setProyectoNombre($pNombre){             $this->pNombre = $pNombre;}
    public function setIdActividad($idActividad){           $this->idActividad = $idActividad;}
    public function setActividadNombre($aNombre){             $this->aNombre = $aNombre;}
    public function setFechaInicio($fechaInicio){           $this->fechaInicio = $fechaInicio;}
    public function setFechaRequerida($fechaRequerida){     $this->fechaRequerida = $fechaRequerida;}
    public function setFechaEntrega($fechaEntrega){         $this->fechaEntrega = $fechaEntrega;}
    public function setFechaCrea($fechaCrea){               $this->fechaCrea = $fechaCrea;}
    public function setCompletado($completado){             $this->completado = $completado;}
    public function setNotas($notas){                       $this->notas = $notas;}
    public function setEntregadoPor($entregadoPor){         $this->entregadoPor = $entregadoPor;}
    public function setFechaAprobacion($fechaAprobacion){   $this->fechaAprobacion = $fechaAprobacion;}
    public function setAprobadoPor($aprobadoPor){           $this->aprobadoPor = $aprobadoPor;}
    public function setRespAct($respAct){                   $this->respAct = $respAct;}

    public function getId(){                 return $this->idActividades_proyecto;}
    public function getIdProyecto(){         return $this->idProyecto;}
    public function getProyectoNombre(){         return $this->pNombre;}
    public function getIdActividad(){        return $this->idActividad;}
    public function getActividadNombre(){         return $this->aNombre;}
    public function getFechaInicio(){        return $this->fechaInicio;}
    public function getFechaRequerida(){     return $this->fechaRequerida;}
    public function getFechaEntrega(){       return $this->fechaEntrega;}
    public function getFechaCrea(){          return $this->fechaCrea;}
    public function getCompletado(){         return $this->completado;}
    public function getNotas(){              return $this->notas;}
    public function getEntregadoPor(){       return $this->entregadoPor;}
    public function getFechaAprobacion(){    return $this->fechaAprobacion;}
    public function getAprobadoPor(){        return $this->aprobadoPor;}
    public function getRespAct(){            return $this->respAct;}
    // GET ONE ITEM
    public function get() {
        try{
            $query = $this->prepare('SELECT ap.idActividades_proyecto, ap.idProyecto,  p.nombre AS pNombre, ap.idActividad, a.nombre AS aNombre, ap.fechaInicio, ap.fechaRequerida,
                                            ap.fechaEntrega, ap.fechaCrea, ap.completado, ap.notas,
                                            ap.entregadoPor, ap.fechaAprobacion, ap.aprobadoPor, ap.respAct
                                      FROM actividades_proyecto AS ap
                                      INNER JOIN proyecto AS p
                                      ON ap.idProyecto = p.idProyecto
                                      INNER JOIN actividad AS a
                                      ON ap.idActividad = a.idActividad
                                      WHERE ap.idActividades_proyecto = :idActividades_proyecto');
            $query->execute([ 'idActividades_proyecto' => $this->getId()]);
            $result = $query->fetch(PDO::FETCH_ASSOC);

            $this->idActividades_proyecto = $result['idActividades_proyecto'];
            $this->idProyecto = $result['idProyecto'];
            $this->pNombre = $result['pNombre'];
            $this->idActividad = $result['idActividad'];
            $this->aNombre = $result['aNombre'];
            $this->fechaInicio = $result['fechaInicio'];
            $this->fechaRequerida = $result['fechaRequerida'];
            $this->fechaEntrega = $result['fechaEntrega'];
            $this->fechaCrea = $result['fechaCrea'];
            $this->completado = $result['completado'];
            $this->notas = $result['notas'];
            $this->entregadoPor = $result['entregadoPor'];
            $this->fechaAprobacion = $result['fechaAprobacion'];
            $this->aprobadoPor = $result['aprobadoPor'];
            $this->respAct = $result['respAct'];

            return $this;
        } catch(PDOException $e) {
            return false;
        }
    }

    public function from($array){
        $this->idActividades_proyecto = $array['idActividades_proyecto'];
        $this->idProyecto = $array['idProyecto'];
        $this->pNombre = $array['pNombre'];
        $this->idActividad = $array['idActividad'];
        $this->aNombre = $array['aNombre'];
        $this->fechaInicio = $array['fechaInicio'];
        $this->fechaRequerida = $array['fechaRequerida'];
        $this->fechaEntrega = $array['fechaEntrega'];
        $this->fechaCrea = $array['fechaCrea'];
        $this->completado = $array['completado'];
        $this->notas = $array['notas'];
        $this->entregadoPor = $array['entregadoPor'];
        $this->fechaAprobacion = $array['fechaAprobacion'];
        $this->aprobadoPor = $array['aprobadoPor'];
        $this->respAct = $array['respAct'];
    }

}

?>

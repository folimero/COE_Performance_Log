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

class ProjectApproverModel extends Model {

    // Tabla proyecto_etapa
    private $idProyectoEtapa;
    private $idProyecto;
    private $idEtapa;
    private $status;
    private $createdDate;
    private $approvedDate;

    // Tabla proyecto_aprobador_etapa
    private $idProyectoAprobador;
    private $idUsuario;
    private $usuarioNombre;
    private $idRol;
    private $rolName;
    private $approved;
    private $razon;

    public function __construct(){
        parent::__construct();

        $this->idProyectoEtapa = '';
        $this->idProyecto = '';
        $this->idEtapa = '';
        $this->status = '';
        $this->createdDate = '';
        $this->approvedDate = '';

        $this->idProyectoAprobador = '';
        $this->idUsuario = '';
        $this->usuarioNombre = '';
        $this->idRol = '';
        $this->rolName = '';
        $this->approved = '';
        $this->razon = '';
    }

    public function setId($idProyectoEtapa){                $this->idProyectoEtapa = $idProyectoEtapa;}
    public function setIdProject($idProyecto){              $this->idProyecto = $idProyecto;}
    public function setIdStage($idEtapa){                   $this->idEtapa = $idEtapa;}
    public function setProjectStatus($status){              $this->status = $status;}
    public function setCreatedDate($createdDate){           $this->createdDate = $createdDate;}
    public function setApprovedDate($approvedDate){         $this->approvedDate = $approvedDate;}

    public function setIdApprover($idProyectoAprobador){    $this->idProyectoAprobador = $idProyectoAprobador;}
    public function setIdUser($idUsuario){                  $this->idUsuario = $idUsuario;}
    public function setUserName($usuarioNombre){            $this->usuarioNombre = $usuarioNombre;}
    public function setIdRol($idRol){                       $this->idRol = $idRol;}
    public function setRolName($rolName){                   $this->rolName = $rolName;}
    public function setApprovedStatus($approved){           $this->approved = $approved;}
    public function setReason($razon){                      $this->razon = $razon;}


    public function getId(){                  return $this->idProyectoEtapa;}
    public function getIdProject(){           return $this->idProyecto;}
    public function getIdStage(){             return $this->idEtapa;}
    public function getProjectStatus(){       return $this->status;}
    public function getCreatedDate(){         return $this->createdDate;}
    public function getApprovedDate(){        return $this->approvedDate;}

    public function getIdApprover(){          return $this->idProyectoAprobador;}
    public function getIdUser(){              return $this->idUsuario;}
    public function getUserName(){            return $this->usuarioNombre;}
    public function getIdRol(){               return $this->idRol;}
    public function getRolName(){             return $this->rolName;}
    public function getApprovedStatus(){      return $this->approved;}
    public function getReason(){              return $this->razon;}

    // GET ONE ITEM
    public function get($idProyecto, $idEtapa) {
        $items = [];
        try{
            $query = $this->prepare('SELECT pe.idProyectoEtapa, pe.idProyecto, pe.idEtapa, pe.status, pa.idProyectoAprobador, DATE(pe.createdDate) AS createdDate,
                                          pa.idUsuario, e.nombre AS eNombre, pa.idRol, ra.nombre AS raNombre, pa.approved, pa.razon, DATE(pe.approvedDate) AS approvedDate
                                      FROM proyecto_etapa AS pe
                                      LEFT JOIN proyecto_aprobador_etapa AS pa
                                      ON pe.idProyectoEtapa = pa.idProyectoEtapa
                                      LEFT JOIN rol_aprobador as ra
                                      ON pa.idRol = ra.idRol
                                      LEFT JOIN usuario AS u
                                      ON pa.idUsuario = u.idUsuario
                                      LEFT JOIN empleado AS e
                                      ON u.idEmpleado = e.idEmpleado
                                      WHERE pe.idProyecto = :idProyecto AND pe.idEtapa = :idEtapa');
            $query->execute([
                              'idProyecto' => $idProyecto,
                              'idEtapa' => $idEtapa
                            ]);

            while($p = $query->fetch(PDO::FETCH_ASSOC)){
                $item = new ProjectApproverModel();

                $item->setId($p['idProyectoEtapa']);
                $item->setIdProject($p['idProyecto']);
                $item->setIdStage($p['idEtapa']);
                $item->setProjectStatus($p['status']);
                $item->setCreatedDate($p['createdDate']);
                $item->setApprovedDate($p['approvedDate']);
                $item->setIdApprover($p['idProyectoAprobador']);
                $item->setIdUser($p['idUsuario']);
                $item->setUserName($p['eNombre']);
                $item->setIdRol($p['idRol']);
                $item->setRolName($p['raNombre']);
                $item->setApprovedStatus($p['approved']);
                $item->setReason($p['razon']);

                array_push($items, $item);
            }

            return $items;
        }catch(PDOException $e){
            return false;
        }
    }

    public function existProjectStage($idProyecto, $idEtapa){
        try{
            $query = $this->prepare('SELECT * FROM proyecto_etapa WHERE idProyecto = :idProyecto AND idEtapa = :idEtapa');
            $query->execute( [
                              'idProyecto' => $idProyecto,
                              'idEtapa' => $idEtapa
                            ]);

            if($query->rowCount() > 0){
                return true;
            }else{
                return false;
            }
        }catch(PDOException $e){
            echo $e;
            return false;
        }
    }

}

?>

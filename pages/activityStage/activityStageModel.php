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

$file = '../../pages/activityStage/stageModel.php';

if(file_exists($file)){
    require_once $file;
}else{
    $file = './pages/activityStage/stageModel.php';
    if(file_exists($file)){
        require_once $file;
    }else{
        echo $file . " NOT EXIST";
        exit;
    }
}

class ActivityStageModel extends Model {

    private $idProyecto;
    private $projectID;
    private $nombre;
    public $stages = [];

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

    function updatePassword($new, $iduser){
        try{
            $hash = password_hash($new, PASSWORD_DEFAULT, ['cost' => 10]);

            $query = $this->db->connect()->prepare('UPDATE users SET password = :val WHERE idUser = :idUser');
            $query->execute(['val' => $hash, 'idUser' => $iduser]);

            if($query->rowCount() > 0){
                return true;
            }else{
                return false;
            }

        }catch(PDOException $e){
            return NULL;
        }
    }

    function comparePasswords($current, $userid){
        try{
            $query = $this->db->connect()->prepare('SELECT idUser, password FROM USERS WHERE idUser = :idUser');
            $query->execute(['idUser' => $userid]);

            if($row = $query->fetch(PDO::FETCH_ASSOC)) return password_verify($current, $row['password']);

            return NULL;
        }catch(PDOException $e){
            return NULL;
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

    public function delete($idUser){
        try{
            $query = $this->prepare('DELETE FROM users WHERE idUser = :idUser');
            $query->execute([ 'idUser' => $idUser]);
            return true;
        }catch(PDOException $e){
            echo $e;
            return false;
        }
    }

    public function update(){
        try{
            $query = $this->prepare('UPDATE users SET userName = :userName, photo = :photo, name = :name, address = :address, mail = :mail, phone = :phone, active = :active, role = :role, idBranch= :idBranch WHERE idUser = :idUser');
            $query->execute([
                'idUser'        => $this->idUser,
                'userName' => $this->userName,
                'photo' => $this->photo,
                'name' => $this->name,
                'address' => $this->address,
                'mail' => $this->mail,
                'phone' => $this->phone,
                'active' => $this->active,
                'role' => $this->role,
                'idBranch' => $this->idBranch
                ]);
            return true;
        }catch(PDOException $e){
            echo $e;
            return false;
        }
    }

    public function existsUserName($userName){
        try{
            $query = $this->prepare('SELECT userName FROM users WHERE userName = :userName');
            $query->execute( ['userName' => $userName]);

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

    public function existsName($name){
        try{
            $query = $this->prepare('SELECT name FROM users WHERE name = :name');
            $query->execute( ['name' => $name]);

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

    public function from($array){
        $this->idUser = $array['idUser'];
        $this->userName = $array['userName'];
        $this->password = $array['password'];
        $this->role = $array['role'];
        $this->photo = $array['photo'];
        $this->name = $array['name'];
        $this->address = $array['address'];
        $this->mail = $array['mail'];
        $this->phone = $array['phone'];
        $this->active = $array['active'];
        $this->idBranch = $array['idBranch'];
        $this->branchName = $array['branchName'];
    }

    private function getHashedPassword($password){
        return password_hash($password, PASSWORD_DEFAULT, ['cost' => 10]);
    }

    //FIXME: validar si se requiere el parametro de hash
    public function setPassword($password, $hash = true){
        if($hash){
            $this->password = $this->getHashedPassword($password);
        }else{
            $this->password = $password;
        }
    }
}

?>

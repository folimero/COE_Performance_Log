<?php

$mainController = '../../libs/controller.php';

if(file_exists($mainController)){
    require_once $mainController;
}else{
    $mainController = '../libs/controller.php';
    if(file_exists($mainController)){
        require_once $mainController;
    }else{        
        echo "MAIN CONTROLLER NOT EXIST";
        exit;
    }
}

$activityStageModel = '../..projectActivity/projectActivityModel.php';

if(file_exists($activityStageModel)){
    require_once $activityStageModel;
}else{
    $activityStageModel = '../projectActivity/projectActivityModel.php';
    if(file_exists($activityStageModel)){
        require_once $activityStageModel;
    }else{
        echo "Project Activity Model NOT EXIST";
        exit;
    }
}

class ProjectActivity extends Controller {

    function __construct(){
        parent::__construct();
    }

    function render($id){
        $this->view->errorMessage = '';

        $projectActivityModel = new ProjectActivityModel();
        $projectActivityModel->setId($id);
        $activityInfo = $projectActivityModel->get();

        $this->view->render('projectActivity', [
            "activityInfo" => $activityInfo
        ]);
    }
}

?>

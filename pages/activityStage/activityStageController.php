<?php

$mainController = '../../libs/controller.php';

if(file_exists($mainController)){
    require_once $mainController;
}else{
    $mainController = '../libs/controller.php';
    if(file_exists($mainController)){
        require_once $mainController;
    }else{
        echo "Stage MODEL NOT EXIST";
        exit;
    }
}

$activityStageModel = 'activityStage/activityStageModel.php';

if(file_exists($activityStageModel)){
    require_once $activityStageModel;
}else{
    $activityStageModel = '../activityStage/activityStageModel.php';
    if(file_exists($activityStageModel)){
        require_once $activityStageModel;
    }else{
        echo "Activity Stage Model NOT EXIST";
        exit;
    }
}

$file = '/activityStage/projectApproverModel.php';

if(file_exists($file)){
    require_once $file;
}else{
    $file = '../activityStage/projectApproverModel.php';
    if(file_exists($file)){
        require_once $file;
    }else{
        echo $file . " NOT EXIST";
        exit;
    }
}

class ActivityStage extends Controller {

    function __construct(){
        parent::__construct();
    }

    function render($id){
        $this->view->errorMessage = '';

        $activityStage = new ActivityStageModel();
        $activityStage->setId($id);
        $activityStages = $activityStage->get();

        $this->view->render('activityStage', [
            "activityStages" => $activityStages
        ]);
    }

    function getActivityStages($id) {
        $activityStage = new ActivityStageModel();
        $activityStage->setId($id);
        $activityStages = $activityStage->get();

        return $activityStages;
    }

    function getStageReport($id, $idEtapa) {
        $activityStage = new ActivityStageModel();
        $activityStage->setId($id);
        $activityStages = $activityStage->get();

        foreach ($activityStages->getStages() as $stage) {
            if ($stage->getId() == $idEtapa) {
              return $stage;
            }
        }
        return null;
    }

    function getApprovers($id, $idEtapa) {
        $approverModel = new ProjectApproverModel();
        $approvers = $approverModel->get($id, $idEtapa);

        return $approvers;
    }

    function checkForStage($id, $idEtapa) {
        $approverModel = new ProjectApproverModel();
        $projectStage = $approverModel->existProjectStage($id, $idEtapa);

        return $projectStage;
    }
}

?>

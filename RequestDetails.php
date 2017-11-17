<?php
$series = $_REQUEST['series'] ?? 'unknown';
$stage = $_REQUEST['stage'] ?? 'unknown';
$frequency = $_REQUEST['frequency'] ?? 60;
//$stage = '875-28000';
// define('BPD_TO_M3PD',(1/6.29),true);
// define('FEET_TO_METER',0.3048,true);
// define('HP_TO_KW',0.745699872,true);

if ('unknown' === $stage) {
    echo '{unknown : true}';
} else {
    require_once './model/ModelEsp.php';
    $pump = ModelEsp::findStage($stage);
    if (null === $pump) {
        throw new Exception('pump not find in database');
        echo '{notFind : true}';
    } else {
        require_once 'CoordinateCalculator.php';
        $details = [];
        $details['id'] = $pump->id;
        $details['stage'] = $pump->model_od;
        $details['series'] = $pump->series;
        $details['shaftDiameter'] = $pump->shaft_od;
        $details['powN'] = $pump->powN;
        //$details['reg_date'] = $pump->reg_date;

        $details['frequency'] = $frequency;
        $details['coeHQ'] = $pump->getCoeHQ($frequency);
        $details['coePQ'] = $pump->getCoePQ($frequency);
        //$details['coeEQ'] = $pump->getCoeEQ();
        $details['espPoints'] = $pump->getEspPoints($frequency);

        $calculator = new CoordinateCalculator($details['espPoints'], 'ESP');
        $details['numOfUnitX'] = ($calculator->numOfUnitX);
        $details['numOfUnitY'] = ($calculator->numOfUnitY);
        $details['lengthOfX'] = ($calculator->lengthOfX);
        $details['lengthOfY1'] = ($calculator->lengthOfY1);
        $details['lengthOfY2'] = ($calculator->lengthOfY2);
        $details['lengthOfY3'] = ($calculator->lengthOfY3);
        $details['lengthOfY4'] = ($calculator->lengthOfY4);
        $details['unitX'] = ($calculator->unitX);
        $details['unitY1'] = ($calculator->unitY1);
        $details['unitY2'] = ($calculator->unitY2);
        $details['unitY3'] = ($calculator->unitY3);
        $details['unitY4'] = ($calculator->unitY4);

        //var_dump($calculator);
        //var_dump($details);
        echo json_encode($details);
    }
}

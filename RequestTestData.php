<?php
$id = $_REQUEST['id'] ?? 'unknown';

if ('unknown' === $id) {
    echo '{unknown : true}';
} else {
    require_once './model/ModelTestDataOD.php';
    $results = ModelTestDataOD::loadTestDataWithId($id);
    //var_dump($results);
    if (sizeof($results) < 1) {
      throw new Exception('id not find in testData');
      echo "{id : {$id}, notFind : true}";
    } else {
      $testData = [];
      $testData['id'] = $id;
      $testData['numOfPoints'] = sizeof($results);
      $testData['BPD'] = [];
      $testData['Head'] = [];
      $testData['BHP'] = [];
      $testData['Efficiency'] = [];
      for ($i = 0; $i < $testData['numOfPoints']; $i++) {
        array_push($testData['BPD'],$results[$i]->BPD);
        array_push($testData['Head'],$results[$i]->Head);
        array_push($testData['BHP'],$results[$i]->BHP);
        array_push($testData['Efficiency'],$results[$i]->Efficiency);
      }
    }

    echo json_encode($testData);
  }

<?php
$Q = $_REQUEST['Q'] ?? null;
$H = $_REQUEST['H'] ?? null;
$P = $_REQUEST['P'] ?? null;
//$E = $_REQUEST['E'] ?? null;
$Q2 = $_REQUEST['Q2'] ?? null;
$H2 = $_REQUEST['H2'] ?? null;
$P2 = $_REQUEST['P2'] ?? null;
//$E2 = $_REQUEST['E2'] ?? null;
$powN = $_REQUEST['powN'];

//$E = [];
//for ($i=0;$i<count($Q);$i++) {
//    $E[$i] = ($Q[$i]*$H[$i]*100)/(135788*$P[$i]);//required Q(BPD), H(ft), P(HP)
//}

require_once './RmMatrix.php';

$coe = [];
$coe['HQ'] = 0;
$coe['PQ'] = 0;
$coe['HQ2'] = 0;
$coe['PQ2'] = 0;

if ($Q && $H && $P) {
    $coe['HQ'] = RmMatrix::getCoefficients($Q, $H, $powN);
    $coe['PQ'] = RmMatrix::getCoefficients($Q, $P, $powN);
}
if ($Q2 && $H2 && $P2) {
    $coe['HQ2'] = RmMatrix::getCoefficients($Q2, $H2, $powN);
    $coe['PQ2'] = RmMatrix::getCoefficients($Q2, $P2, $powN);
}

//$coe['EQ'] = \rm\RmMatrix::getCoefficients($Q, $E, $powN);

echo json_encode($coe);

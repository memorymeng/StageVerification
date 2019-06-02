<?php
declare(strict_types=1);
require_once 'ActiveRecordModel.php';

define('BPD_TO_M3PD', (1/6.29), true);
define('FEET_TO_METER', (0.3048), true);
define('HP_TO_KW', (0.745699872), true);

/**
 * Class ModelEsp
 * default frequency from database is 60 Hz
 * @api
 * @property string id
 * @property string stage
 * @property string series
 * @property string shaft_od
 * @property string min
 * @property string bep
 * @property string max
 * @property string headlength
 * @property string flowlength
 * @property string bpdbep
 * @property string tdhbep
 * @property string bhpbep
 */
class ModelEsp extends ActiveRecordModel
{
    protected $table_name = 'mysql_od';
    protected $username = 'ray_meng';//reminder: change to a readonly account in production!
    protected $password = 'ODray01062017';
    protected $hostname = 'localhost';
    protected $dbname = 'myDB';



    public $powN = 5;
    /**
     * @param int $frequency specified frequency, 60hz by default
     * @return array coeHQ[]
     */
    public function getCoeHQ(int $frequency = 60): array
    {
        $k = $frequency / 60.0;
        $coe = [];
        $attr_prefix = 'h';
        for ($i = 0; $i < $this->powN + 1; $i++) {
            $attr = $attr_prefix . strval($i);
            //echo $this->$attr,'<br/>';
            //var_dump(floatval($this->$attr));
            $coe[$i] = floatval($this->$attr) * pow($k, 2 - $i);

            //type convertion for 50hz
            if (50 == $frequency) {
                $coe[$i] *= FEET_TO_METER / pow(BPD_TO_M3PD, $i);
            }
        }
        return $coe;
    }

    /**
     * @param int $frequency specified frequency, 60hz by default
     * @return array coePQ[]
     */
    public function getCoePQ(int $frequency = 60): array
    {
        $k = $frequency / 60.0;
        $coe = [];
        $attr_prefix = 'p';
        for ($i = 0; $i < $this->powN + 1; $i++) {
            $attr = $attr_prefix . strval($i);
            $coe[$i] = floatval($this->$attr) * pow($k, 3 - $i);

            //type convertion for 50hz
            if (50 == $frequency) {
                $coe[$i] *= HP_TO_KW / pow(BPD_TO_M3PD, $i);
            }
        }
        return $coe;
    }


    public function getEffAtPoint(float $Q, int $frequency = 60) : float
    {
        $coeHQ = $this->getCoeHQ($frequency);
        $coePQ = $this->getCoePQ($frequency);
        $H = 0.0;
        $P = 0.0;
        for ($i = 0; $i < $this->powN + 1; $i++) {
            $H += pow($Q, $i) * $coeHQ[$i];
            $P += pow($Q, $i) * $coePQ[$i];
        }
        $E = ($Q * $H * 100) / (135788 * $P);

        //type convertion for 50hz
        if (50 == $frequency) {
            $E *= HP_TO_KW / (BPD_TO_M3PD * FEET_TO_METER);
        }
        return $E;
    }

    // /**
    //  *@deprecated not accurate, error on 50hz
    //  * @param int $frequency specified frequency, 60hz by default
    //  * @return array coeEQ[]
    //  */
    // public function getCoeEQ(int $frequency = 60): array
    // {
    //     $k = $frequency / 60.0;
    //     $coe = [];
    //     $attr_prefix = 'coeEQ_';
    //     for ($i = 0; $i < $this->powN + 1; $i++) {
    //         $attr = $attr_prefix . strval($i);
    //         $coe[$i] = floatval($this->$attr) * pow($k, -1 * $i);
    //     }
    //     return $coe;
    // }

    /**
     * @param int $frequency specified frequency, 60hz by default
     * @return array points[domain_Q,domain_H,BEA_Start,BEA_End,BEP_Q,BEP_H,BEP_P,BEP_E]
     */
    public function getEspPoints(int $frequency = 60): array
    {
        $k = $frequency / 60.0;

        $bep_q = floatval($this->bep_rate) * pow($k, 1) * (50 == $frequency?BPD_TO_M3PD:1);
        $coePQ = $this->getCoePQ($frequency);
        $bep_p = 0.0;
        for ($i = 0; $i < $this->powN + 1; $i++) {
          $bep_p += $coePQ[$i] * pow(floatval($this->bep_rate) * pow($k, 1) * (50 == $frequency?BPD_TO_M3PD:1),$i);
        }
        $espPoints =
            [
                'domain_Q' => floatval($this->max_graph_rate) * pow($k, 1) * (50 == $frequency?BPD_TO_M3PD:1),
                'domain_H' => ceil(floatval($this->h0) * 1.05) * pow($k, 2) * (50 == $frequency?FEET_TO_METER:1),
                'BEA_Start' => floatval($this->min_rate) * pow($k, 1) * (50 == $frequency?BPD_TO_M3PD:1),
                'BEA_End' => floatval($this->max_rate) * pow($k, 1) * (50 == $frequency?BPD_TO_M3PD:1),
                'BEP_Q' => $bep_q,
                //'BEP_H' => floatval($this->tdhbep) * pow($k, 2) * (50 == $frequency?FEET_TO_METER:1),
                //'BEP_P' => floatval($this->bhpbep) * pow($k, 3) * (50 == $frequency?HP_TO_KW:1),
                'BEP_P' => $bep_p,
                'BEP_E' => $this->getEffAtPoint($bep_q, $frequency)//$this->getEffAtPoint(floatval($this->bep) * pow($k,1),$frequency)
            ];

        return $espPoints;
    }

    /**
     * fetch all data from database ModelEsp (where series IS NOT NULL)
     * @return array results[]
     */
    public static function fetchAll(): array
    {
        $db = new ModelEsp();
        //$db = newInstance();
        $results = $db->where('series IS NOT NULL');
        $db = null;
        return $results;
    }

    /**
     * @param int $id id of model
     * @return ModelEsp|null result
     */
    public static function findId(int $id): ?ModelEsp
    {
        $db = new ModelEsp();
        //$db = newInstance();
        $result = $db->find(strval($id));
        $db = null;
        return $result;
    }

    /**
     * @param string $seriesName
     * @return array results[]
     */
    public static function findSeries(string $seriesName): array
    {
        $db = new ModelEsp();
        //$db = newInstance();
        $results = $db->where("series = '{$seriesName}'");
        $db = null;
        return $results;
    }

    /**
     * @param string $stageName
     * @return ModelEsp|null unique result or null
     */
    public static function findStage(string $stageName): ?ModelEsp
    {
        $db = new ModelEsp();
        //$db = newInstance();
        $results = $db->where("model_od = '{$stageName}' ORDER BY edit_date desc");
        $result = null;
        if (empty($results)) {
            //throw new Exception ('ERROR: No match result found');
        // } elseif (count($results) > 1) {
        //     throw new Exception('ERROR: For unique key "Stage", more than one result found ');
        } else {
          //history record exists if count > 1
          $result = $results[0];
        }
        $db = null;
        return $result;
    }
}

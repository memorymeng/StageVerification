<?php
declare(strict_types=1);
require_once 'ActiveRecordModel.php';


/**
 * Class ModelEsp
 * default frequency from database is 60 Hz
 * @api
 * @property string TestID
 * @property string PointNumber
 * @property string BPD
 * @property string Head
 * @property string BHP
 * @property string FSG
 * @property string Efficiency
 * @property string DriveCurrent
 */
class ModelTestDataOD extends ActiveRecordModel
{
    protected $table_name = 'testdata_od';
    protected $username = 'Ray_Meng';//reminder: change to a readonly account in production!
    protected $password = 'ODray01062017';
    protected $hostname = 'localhost';
    protected $dbname = 'myDB';


    /**
     * fetch all data from database ModelTestDataOD (where TestID IS NOT NULL)
     * @return array results[]
     */
    public static function fetchAll(): array
    {
        $db = new ModelTestDataOD();
        $results = $db->where('TestID IS NOT NULL');
        $db = null;
        return $results;
    }

    /**
     * @param int $id id of test
     * @return array results[]
     */
    public static function loadTestDataWithId(int $id): array
    {
        $strId = strval($id);
        $db = new ModelTestDataOD();
        $results = $db->where("TestID = {$strId}");
        //$result = $db->find(strval($id));
        $db = null;
        return $results;
    }


}

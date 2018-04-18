<?php
declare(strict_types=1);
require_once 'ActiveRecordModel.php';


/**
 * Class ModelEsp
 * default frequency from database is 60 Hz
 * @api
 * @property string TestID
 * @property string TestDateTime
 * @property string SerialNumber
 * @property string Customer
 * @property string FieldName
 * @property string WellName
 * @property string StageType
 * @property string NumStages
 * @property string Comments
 * @property string TestFacility
 * @property string TestOperator
 * @property string BenchMode
 */
class ModelTestDataHeaderOD extends ActiveRecordModel
{
    protected $table_name = 'testdata_header_od';
    protected $username = 'Ray_Meng';//reminder: change to a readonly account in production!
    protected $password = 'ODray01062017';
    protected $hostname = 'localhost';
    protected $dbname = 'myDB';


    /**
     * fetch all data from database ModelTestDataHeaderOD (where TestID IS NOT NULL)
     * @return array results[]
     */
    public static function fetchAllEsp(): array
    {
        $db = new ModelTestDataHeaderOD();
        $results = $db->where("TestID IS NOT NULL AND BenchMode = 'ESP'");
        $db = null;
        return $results;
    }

    /**
     * @param int $id id of model
     * @return ModelTestDataHeaderOD|null result
     */
    public static function findId(int $id): ?ModelTestDataHeaderOD
    {
        $strId = strval($id);
        $db = new ModelTestDataHeaderOD();
        $result = $db->where("TestID = {$strId}");
        //$result = $db->find(strval($id));
        $db = null;
        return $result;
    }

    /**
     * @param string $serialNumber
     * @return array results[]
     */
    public static function findSerialNumber(string $serialNumber): array
    {
        $db = new ModelTestDataHeaderOD();
        $results = $db->where("SerialNumber = '{$serialNumber}'");
        $db = null;
        return $results;
    }


}

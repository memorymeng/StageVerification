<?php
declare(strict_types=1);
require_once 'ActiveRecordModel.php';


/**
 * Class ModelEsp
 * @api
 * @property string id
 * @property string username
 * @property string password
 * @property string permission
 */
class ModelUser extends ActiveRecordModel
{
    protected $table_name = 'user';
    protected $username = 'Ray_Meng';//reminder: change to a readonly account in production!
    protected $password = 'ODray01062017';
    protected $hostname = 'localhost';
    protected $dbname = 'myDB';

    /**
    *create new user in 'user' database
    *@param array $user array of all user informations (username,password,permission)
    *@return array array of excution results (id,username)
    */
    public static function createNewUser(array $user) : ?string
    {
        $user = changeKeyName($user, 'usrname', 'username');
        $user = changeKeyName($user, 'pswd', 'password');

        $db = new ModelUser();

        return $db->insert($user);
    }

    public function getUserId() : ?string
    {
        return $this->ID;
    }

    public function getUsername() : ?string
    {
        return $this->usrname;
    }

    public function getUserPermission() : ?string
    {
        return $this->permission;
    }
    /**
     * fetch all data from database ModelEsp (where series IS NOT NULL)
     * @return array results[]
     */
    public static function fetchAll(): array
    {
        $db = new ModelUser();
        $results = $db->where('USRNAME IS NOT NULL');
        $db = null;
        return $results;
    }

    /**
     * @param int $id id of model
     * @return ModelUser|null result
     */
    public static function findId(int $id): ?ModelUser
    {
        $db = new ModelUser();
        $result = $db->find(strval($id));
        $db = null;
        return $result;
    }

    /**
     * @param string $username
     * @return ModelUser|null result
     */
    public static function findUserByName(string $username): ?ModelUser
    {
        $db = new ModelUser();
        $results = $db->where("USRNAME = '{$username}'");
        $result = null;
        if (empty($results)) {
            //throw new Exception ('ERROR: No match result found');
        } elseif (count($results) > 1) {
            throw new Exception('ERROR: For unique key "username", more than one result found ');
        } else {
            $result = $results[0];
        }
        $db = null;
        return $result;
    }

    public static function verifyUserWithPassword(string $username, string $password): bool
    {
        $result = false;
        $user = ModelUser::findUserByName($username);
        if (null != $user) {
            if (password_verify($password, $user->pswd)) {
                $result = true;
            }
        }
        return $result;
    }

    public function verifyPassword(string $password): bool
    {
        if (password_verify($password, $this->pswd)) {
            return true;
        } else {
            return false;
        }
    }
}

function changeKeyName($array, $newkey, $oldkey)
{
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $array[$key] = changeKeyName($value, $newkey, $oldkey);
        } else {
            $array[$newkey] =  $array[$oldkey];
        }
    }
    unset($array[$oldkey]);
    return $array;
}

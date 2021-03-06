<?php
namespace DB;
class Db
{
    private $conn;//l连接数据库
    private $dbConf;//配置文件
    private $db;//对象
    private $prepareSql;//预编译sql
    private $prepareActive = false;

    /**
     * 参数绑定状态
     * @var bool
     */
    private $bindValActive = false;
    private $_instance;//单例
    private $lastQuerySql;//最后一条sql

    //构造
    public function __construct()
    {
        $this->connect();
    }
    public function connect()
    {
        $config = include "config.php";
        $dsn = 'mysql:host='.$config['host'];
        $dsn .=";dbname=".$config['dbname'];
        $this->dbConf = $config;
        try{
            $this->conn = new \PDO($dsn,$this->dbConf['username'],$this->dbConf['password']);
            $this->conn->query("SET NAMES ".$this->dbConf['charset']);
            $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);//设置错误模式
            $this->conn->setAttribute(\PDO::ATTR_PERSISTENT, $this->dbConf['pconnect']);
        }catch(\Exception $e){
            die($e->getCode());
        }
    }
    //单例
 /*   public function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }*/
    //query
    public function query($sql)
    {
        try{
            $sth = $this->conn->query($sql);
            $this->db = $sth;
            return $this;
        }catch (\Exception $e){
            $e->getMessage();
        }
    }

    //exec 执行select update
    public function exec($sql)
    {
        try{
            $affected_rows = $this->conn->exec($sql);
            return $affected_rows;
        }catch (\Exception $e){
            $e->getMessage();
        }
    }
    //返回一条结果 obj
    public function row()
    {
        if ($this->db) {
            return $this->db->fetch(\PDO::FETCH_OBJ);
        }
    }
    //返回一条结果 array
    public function rowArr()
    {
        if($this->db)
        {
            return $this->db->fetch(\PDO::FETCH_ASSOC);
        }
    }
    //返回多条结果 obj
    public function result()
    {
        if($this->db)
        {
          return  $this->db->fetchAll(\PDO::FETCH_OBJ);
        }
    }
    //返回多条结果 array
    public function resultArr()
    {
        if($this->db)
        {
            return $this->db->fetchAll(\PDO::FETCH_ASSOC);
        }
    }
    //预编译
    public function prepare($sql)
    {
        try{
            $this->db = $this->conn->prepare($sql,array(\PDO::ATTR_CURSOR=> \PDO::CURSOR_FWDONLY));
            $this->prepareSql = $sql;
            $this->prepareActive = true;
            return true;

        }catch (\Exception $e){
            die($e->getMessage());
        }
    }
    //绑定参数
    public function bindValue($index,$val,$type= null)
    {
        if ($this->prepareActive) {
            if (is_numeric($index))
                $index = intval($index);
            if (!is_null($type))
                $this->db->bindValue($index, $val, $type);
            else
                $this->db->bindValue($index, $val);
            $this->bindValActive = true;
            return true;
        } else {
            $this->bindValActive = false;
            return false;
        }
    }

}
<?php
/**
 * @mebar update 2015-03-19 17:54:00
 * 支持commit
 *
  $mysql_session = M("a_test", $db_config);

  $mysql_session->begin();
  $data = array(
  "name" => "1111",
  "time"=>date("Y-m-d H:i:s")
  );
  try{
  $mysql_session->setTable("a_test")->add($data);
  }catch (Exception $e) {
  $mysql_session->rollback();
  }
  $mysql_session->commit();
 *
 */
Class DBModel extends CI_Model{

    /**

      数据库操作类
      模糊查询  $condition = "uid like ".'"%'.$account.'%"';

     */
    var $link; //数据库连接
    var $error; //数据库连接
    var $autocommit; //是否开启事物
    Public $join;
    Public $field;
    Public $tab; //表名
    Public $where;
    Public $order;
    Public $limit;
    Public $group;
    Public $sql;
    Public $redis;
    static public $Instance = [];

    //构造函数
    public function __construct($tab = '',$link,$redis= '') {
        $this->link = $link;
        $this->link->query("set names utf8");
        $this->tab = $this->parseKey($tab);
    }
    
    ///获取一个数据库实例
    static public function getInstance($tab, $db_config,$redis){
        if(!isset(DBModel::$Instance[$db_config['dbname']])){
            DBModel::$Instance[$db_config['dbname']] = new DBModel($tab, $db_config['link'],$redis);
            $Instance = DBModel::$Instance[$db_config['dbname']];
        }else{          ////已存在单例
            $Instance = DBModel::$Instance[$db_config['dbname']];
            $Instance->setTable($tab);
        }
        return $Instance;
    }


    function begin() {
        $this->autocommit = true;
        $this->link->query("SET AUTOCOMMIT=0");
    }

    function rollback() {
        $this->link->query("ROLLBACK");
        $this->link->query("SET AUTOCOMMIT=1");
        $this->autocommit = false;
    }

    function commit() {
        $this->link->query("COMMIT");
        $this->link->query("SET AUTOCOMMIT=1");
        $this->autocommit = false;
    }

    function field($field) {
        $this->field = $field;
        return $this;
    }

    function where($where) {
        if (is_array($where)) {
            $this->where = 'where ' . $this->condition_check($where);
        } else {
            if (!empty($where)) {
                $this->where = 'where ' . $where;
            }
        }
        return $this;
    }

    function setTable($name = "") {
        $this->tab = $this->parseKey($name); //$name;
        return $this;
    }

    function order($order) {
        $this->order = 'order by ' . $order;
        return $this;
    }

    function group($group) {
        $this->group = 'group by ' . $group;
        return $this;
    }

    function limit($limit) {
        $this->limit = 'limit ' . $limit;
        return $this;
    }
    
    //查询
    function select($key_name = '') {
        if (isset($this->field)) {
            $sql = "select {$this->field} from {$this->tab} {$this->join} {$this->where}  {$this->group} {$this->order} {$this->limit}";
        } else {
            $sql = "select * from {$this->tab} {$this->join} {$this->where} {$this->group} {$this->order} {$this->limit} ";
        }
        
        $this->clearAtrr();
        $result = $this->link->query($sql);

        if ($result == false && $this->autocommit) {
            throw new mysqlException(mysqli_error($this->link));
        }
        $rows = array();
        
        if($result){
            while ($row = mysqli_fetch_assoc($result)) {
                if (!empty($key_name)) {
                    $keyname_arr = explode('-',$key_name);
                    if (!empty($keyname_arr[1])) {
                        //方便合并相同键名数组时 使用
                        $tmp_r = $keyname_arr[1];
                        $rows_str = $keyname_arr[0].'-'.$row[$tmp_r];
                        $rows[$rows_str] = $row;
                    }else{
                       $rows[$row[$key_name]] = $row;
                    }

                } else {
                    $rows[] = $row;
                }
            }
        }
        $this->sql = $sql;
        
        return $rows;
    }

        //联合查询专用
    function unselect($key_name = '',$map){
        if (isset($this->field)) {
            $sql = "select {$this->field} from (select * from ".$map['tab1']." {$this->where} union all select * from ".$map['tab2']." {$this->where}) untab {$this->group} {$this->order} {$this->limit}";
        } else {
            $sql = "select * from (select * from ".$map['tab1']." {$this->where} union all select * from ".$map['tab2']." {$this->where}) untab {$this->group} {$this->order} {$this->limit}";
        }
        
        $this->clearAtrr();
        $result = $this->link->query($sql);

        if ($result == false && $this->autocommit) {
            throw new mysqlException(mysqli_error($this->link));
        }
        $rows = array();
        while ($row = mysqli_fetch_assoc($result)) {
            if (!empty($key_name)) {
                $keyname_arr = explode('-',$key_name);
                if (!empty($keyname_arr[1])) {
                    //方便合并相同键名数组时 使用
                    $tmp_r = $keyname_arr[1];
                    $rows_str = $keyname_arr[0].'-'.$row[$tmp_r];
                    $rows[$rows_str] = $row;
                }else{
                   $rows[$row[$key_name]] = $row;
                }

            } else {
                $rows[] = $row;
            }
        }
        $this->sql= $sql;
        return $rows;

    }

     //联合查询总数
    function uncount($map) {
        $sql = "select count(*) from (select * from ".$map['tab1']." {$this->where} union all select * from ".$map['tab2']." {$this->where}) untab {$this->group}";
        
        $this->clearAtrr();
        $result = $this->link->query($sql);
        if ($result == false && $this->autocommit) {
            throw new mysqlException(mysqli_error($this->link));
        }
        while ($row = mysqli_fetch_array($result)) {
            $rows = $row;
        }
        $this->sql= $sql;
        return $rows[0];
    }

    //查单条
    function find() {
        if (isset($this->field)) {
            $sql = "select {$this->field} from {$this->tab} {$this->join} {$this->where} {$this->order} {$this->limit}";
        } else {
            $sql = "select * from {$this->tab} {$this->join} {$this->where} {$this->order} {$this->limit}";
        }
        
        $this->clearAtrr();
        $result = $this->link->query($sql);
        if ($result == false && $this->autocommit) {
            throw new mysqlException(mysqli_error($this->link));
        }
        if (!empty($result)) {
            while ($row = mysqli_fetch_assoc($result)) {
                $rows[] = $row;
            }
        }
        $this->sql= $sql;
        if (isset($rows[0])) {
            return $rows[0];
        }

    }

    //查询单个字段 增加第二参数 $typeT控制读取某个字段所有值
    //M('user',$db_config)->getField('name');
    //M('user',$db_config)->field("sum(money) as c")->getField('c');
    //getField ;
    function getField($fields , $typeT = false){
        if (!empty($fields)) {
            if (!empty($this->field)) {
                $sql = "select {$this->field} from {$this->tab} {$this->join} {$this->where}";
            }else{
                $sql = "select {$fields} from {$this->tab} {$this->join} {$this->where}";
            }
        }
        $this->clearAtrr();
       
        $result = $this->link->query($sql);
        if ($result == false && $this->autocommit) {
            throw new mysqlException(mysqli_error($this->link));
        }
        if (!empty($result)) {
            while ($row = mysqli_fetch_assoc($result)) {
                $rows[] = $row[$fields];
            }
        }
        
        $this->sql = $sql;
        
        if (!empty($typeT)) {
            return $rows;
        } else {
            return $rows[0];
        }

    }

    //查询总数
    function count() {
        if (isset($this->field)) {
            $sql = "select count({$this->field}) from {$this->tab} {$this->join} {$this->where} {$this->order} {$this->limit}";
        } else {
            $sql = "select count(*) from {$this->tab} {$this->join} {$this->where} {$this->order} {$this->limit}";
        }
        
        $this->clearAtrr();
        $result = $this->link->query($sql);
        if ($result == false && $this->autocommit) {
            throw new mysqlException(mysqli_error($this->link));
        }
        
        if($result){
            while ($row = mysqli_fetch_array($result)) {
                $rows = $row;
            }
        }else{
            return 0;
        }

        $this->sql= $sql;
        return $rows[0];
    }

    //插入 mtype是否批量插入
    function add($data,$mtype = 0) {
        is_array($data) ? null : $data = array();
        if ($mtype) {
            foreach ($data as $k=> $v) {
                $keys = $vals = array();
                foreach ($v as $key => $val) {
                    $keys[] = $this->parseKey($key);// "`".$key."`";
                    $vals[] = "'" . $val . "'";
                }
                $keystr = join(",", $keys);
                $valstr = join(",", $vals);
                $tmp_valstr .= "($valstr),";
            }
            $tmp_valstr = rtrim($tmp_valstr,',');
            $sql = "insert into {$this->tab}($keystr) values".$tmp_valstr;
        }else{
            foreach ($data as $key => $val) {
                $keys[] = $this->parseKey($key);// "`".$key."`";
                $vals[] = "'" . $val . "'";
            }
            $keystr = join(",", $keys);
            $valstr = join(",", $vals);
            $sql = "insert into {$this->tab}($keystr) values($valstr)";
        }
        
        $this->clearAtrr();
        $result = $this->link->query($sql);
        if ($result == false && $this->autocommit) {
            throw new mysqlException(mysqli_error($this->link));
        } else {
            return mysqli_insert_id();
        }
        $this->sql= $sql;
        return false;
    }

       //插入 主键相同自动转更新
    function add_update($data,$mtype = 0){
        is_array($data) ? null : $data = array();
        if ($mtype) {
            foreach ($data as $k=> $v) {
                $keys = $vals = array();
                foreach ($v as $key => $val) {
                    $keys[] = $this->parseKey($key);
                    $vals[] = "'" . $val . "'";
                }
                $keystr = join(",", $keys);
                $valstr = join(",", $vals);
                $tmp_valstr .= "($valstr),";
            }
            $tmp_valstr = rtrim($tmp_valstr,',');
            $sql = "replace into {$this->tab}($keystr) values".$tmp_valstr;
        }else{
            foreach ($data as $key => $val) {
                $keys[] = $this->parseKey($key);
                $vals[] = "'" . $val . "'";
            }
            $keystr = join(",", $keys);
            $valstr = join(",", $vals);
            $sql = "replace into {$this->tab}($keystr) values($valstr)";
        }
        
        $this->clearAtrr();
        $result = $this->link->query($sql);
        if ($result == false && $this->autocommit) {
            throw new mysqlException(mysqli_error($this->link));
        } else {
            return mysqli_insert_id();
        }
        $this->sql= $sql;
        return false;
    }

    /**
     * 字段和表名处理
     * @access protected
     * @param string $key
     * @return string
     */
    protected function parseKey(&$key) {
        $key   =  trim($key);
        if(!is_numeric($key) && !preg_match('/[,\'\"\*\(\)`.\s]/',$key)) {
            $key = '`'.$key.'`';
        }
        return $key;
    }


    /**
     * 设置记录的某个字段值
     * 支持使用数据库字段和方法
     * @access public
     * @param string|array $field  字段名
     * @param string $value  字段值
     * @return boolean
     */
    public function setField($field,$value='') {
        if(is_array($field)) {
            $data           =   $field;
        }else{
            $data[$field]   =   $value;
        }
        return $this->update($data);
    }



    //更新
    function update($data = array()) {
        is_array($data) ? null : $data = array();
        foreach ($data as $key => $val) {
            $key = $this->parseKey($key);
            if (is_array($val)) {
               //为数组
               $sets[] = "{$key}".'='."{$key}{$val[0]}{$val[1]}";
            }else{
               $sets[] = "{$key}='{$val}'";
            }

        }
        $setstr = join(",", $sets);
        $sql = "update {$this->tab} set $setstr {$this->where}";
        
        $this->clearAtrr();
        $result = $this->link->query($sql);
        if ($result == false && $this->autocommit) {
            throw new mysqlException(mysqli_error($this->link));
        } else {
            return mysqli_affected_rows();
        }
        $this->sql= $sql;
        return false;
    }

    //删除
    function delete() {
        $sql = "delete from {$this->tab} {$this->where}";
        $this->clearAtrr();
        $result = $this->link->query($sql);
        if ($result == false && $this->autocommit) {
            throw new mysqlException(mysqli_error($this->link));
        } else {
            return $sql;
        }
        $this->sql= $sql;
        return false;
    }

    //计算总和
    function sum($sum) {
        //判断是否同时计算多个类别
        if (is_array($sum)) {
            $j = count($sum);
            foreach ($sum as $key => $val) {
                if ($key == $j - 1) {
                    $sum_str .= "Sum($val)";
                } else {
                    $sum_str .= "Sum($val)" . ',';
                }
            }
            $sql = "select $sum_str from {$this->tab} {$this->join} {$this->where} {$this->order} {$this->limit}";
        } else {
            $sql = "select sum($sum) from {$this->tab} {$this->join} {$this->where} {$this->order} {$this->limit}";
        }
        $this->clearAtrr();
        $result = $this->link->query($sql);
        if ($result == false && $this->autocommit) {
            throw new mysqlException(mysqli_error($this->link));
        }
        while ($row = mysqli_fetch_array($result)) {
            $rows = $row;
        }
        $this->sql= $sql;
        return $rows;
    }

    //条件分析
    /*     * *
      EQ  等于（=）
      GT  大于（>）
      EGT 大于等于（>=）
      LT  小于（<）
      ELT 小于等于（<=）
      LIKE  模糊查询
      [NOT] BETWEEN （不在）区间查询
      [NOT] IN  （不在）IN 查询
     * */
    function condition_check($arr) {
        $count = count($arr) - 1;
        $i = 0;
        $where = '';
        if (isset($arr['_logic']) && $arr['_logic'] == 'or') {
            foreach ($arr as $key => $val) {
                $key = $this->parseKey($key);
                if ($i == $count) {
                    $where .= "$key = $val";
                } else {
                    $where .= "$key = $val or ";
                }
                $i++;
            }
        } else {
            foreach ($arr as $key => $val) {
                $key = $this->parseKey($key);
                if ($i == $count) {
                    if (is_array($val)) {
                        if (is_array($val[0])) {
                            foreach ($val as $key_v => $val_v) {
                                if (is_array($val_v)) {
                                    if ($key_v == count($val) - 1) {
                                        $where .= "$key $val_v[0] '$val_v[1]'";
                                    } else {
                                        $where .= "$key $val_v[0] '$val_v[1]' and ";
                                    }
                                } else {
                                    if ($val[0] == 'in' || $val[0] == 'not in') {
                                        $where .= "$key $val[0] $val[1]";
                                    } else {
                                        $where .= "$key $val[0] '$val[1]'";
                                    }
                                }
                            }
                        } else {
                            if ($val[0] == 'in' || $val[0] == 'not in') {
                                $where .= "$key $val[0] $val[1]";
                            } else {
                                $where .= "$key $val[0] '$val[1]'";
                            }
                        }
                    } else {
                        if(strpos($key,' >=')!==false || strpos($key,' <=')!==false){
                            $where .= "$key '$val'";
                        }else{
                             $where .= "$key = '$val'";
                        }
                    }
                } else {
                    if (is_array($val)) {
                        if (is_array($val[0])) {
                            foreach ($val as $key_v => $val_v) {
                                if (is_array($val_v)) {
                                    $where .= "$key $val_v[0] '$val_v[1]' and ";
                                } else {
                                    $where .= "$key $val[0] '$val[1]'";
                                }
                            }
                        } else {
                            if ($val[0] == 'in' || $val[0] == 'not in') {
                                $where .= "$key $val[0] $val[1] and ";
                            } else {
                                $where .= "$key $val[0] '$val[1]' and ";
                            }
                        }
                    } else {
                        if(strpos($key,' >=')!==false || strpos($key,' <=')!==false){
                            $where .= "$key '$val' and ";
                        }else{
                            $where .= "$key = '$val' and ";
                        }

                    }
                }
                $i++;
            }
        }
        return $where;
    }

    /**
     * 返回最后执行的sql语句
     * @access public
     * @return string
     */
    public function getLastSql() {
        return $this->sql;
    }

    //join方法联表
    /*     * *
      join('left join k_user_login on k_user.uid = k_user_login.uid left join k_user_games on k_user.uid = k_user_games.uid')
     * */
    public function join($join) {
        if (!empty($join)) {
            $this->join = $join;
        }
        return $this;
    }

    function clearAtrr() {
        unset($this->field);
        unset($this->join);
        unset($this->where);
        unset($this->order);
        unset($this->group);
        unset($this->limit);
    }

    function close() {
        unset($this->field);
        unset($this->join);
        unset($this->where);
        unset($this->order);
        unset($this->group);
        unset($this->limit);
        mysql_close($this->link);
        unset($this);
    }

    //输出页面样式
    public function showPage($totalPage, $page) {
        $strPage = "頁數：<select id=\"page\" name=\"page\" class=\"za_select\"> ";
        for ($i = 1; $i <= $totalPage; $i++) {  //循环显示出页面
            $strPage .= '<option value="' . $i . '"';
            if ($page == $i) {
                $strPage .= ' selected';
            }
            $strPage .='>' . $i . '</option>';
        }
        $strPage .= '</select> ' . $totalPage . ' 頁';
        return $strPage;
    }

}

class mysqlException extends Exception {

    public function errorMessage() {
        //error message
        $errorMsg = 'Error on line ' . $this->getLine() . ' in ' . $this->getFile()
                . ':' . $this->getMessage();
        return $errorMsg;
    }

}

//M方法
function M($tab, $db_config,$redis='') {
    return DBModel::getInstance($tab, $db_config,$redis);
}


function debug(){
    $backtrace = debug_backtrace();
    array_shift($backtrace);
	$narr = array('class', 'type', 'function', 'file', 'line');
	$err = "debug_backtrace";
	foreach($backtrace as $i => $l)
	{
		foreach($narr as $k)
		{
			if( !isset($l[$k]) ) $l[$k] = '';
		}
		$err .= "[$i] in function {$l['class']}{$l['type']}{$l['function']} ";
		if($l['file']) $err .= " in {$l['file']} ";
		if($l['line']) $err .= " on line {$l['line']} ";
		$err .= "\n\n";
	}
    echo '<pre>';
	echo $err;
}
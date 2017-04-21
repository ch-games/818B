<?php
    ///$this->load->library('DBModel');
    
    class Mongo_cli{
        private static $conn='';
        private static $db;
        static $options;
        static $username;
        static $password;
        static $host;
        static $port;
        static $conn_str;
        
        private function __construct($host='',$port='',$username='',$password='',$dbname=''){
            static::$conn_str = $host;
            static::$options = [
                'username'=>$username,
                'password'=>$password,
                'db'=>$dbname,
                'connectTimeoutMS'=>200,
                'socketTimeoutMS'=>200,
            ];
            
            try{
                static::$conn = new MongoClient(static::$conn_str,static::$options);
                static::$db = new MongoDB(static::$conn, static::$options['db']);
            }catch(Exception $e){
                $data = serialize($e);
                error_log($data.PHP_EOL,3,'./error.log');
                $this->isok = FALSE;
            }
            return $this;
        }
        
        private static function login($db,$username,$password){
            $salted = "${username}:mongo:${password}";
            $hash = md5($salted);

            $nonce = $db->command(array("getnonce" => 1));

            $saltedHash = md5($nonce["nonce"]."${username}${hash}");

            $result = $db->command(array("authenticate" => 1,
                "user" => $username,
                "nonce" => $nonce["nonce"],
                "key" => $saltedHash
            ));
            return $result;
        }
        
        public static function getInstace($host='',$port='',$username='',$password='',$dbname=''){
            if(empty($host))
                throw new Exception("lost host");
            if(empty($port))
                throw new Exception("lost port");
            if(empty($username))
                throw new Exception("lost username");
            if(empty($password))
                throw new Exception("lost password");
            if(empty($dbname))
                throw new Exception("lost dbname");
            
            if(!empty(static::$conn))
                return $this;
            else
                return new Mongo_cli($host,$port,$username,$password,$dbname);
        }
        
        public function __get($var){
            return static::$db->$var;
        }
        
        public function __call($func,$func_args){
            return static::$db->$func();
        }
    }
<?php
    class RedisConPool{
        private static $Instace = "";           ///对象
        private $sock = "";                     ///Redis连接池链接
        private $type = 1;                      ///连接池模式
        private function __construct(){
            if(!defined("REDIS_POLL_HOST") || !defined("REDIS_POLL_PORT")){
                $this->SockForRedis();
            }else{
                @$this->sock = fsockopen(REDIS_POLL_HOST,REDIS_POLL_PORT,$errno,$errstr,1);
                
                if (!$this->sock) {
                    $this->SockForRedis();
                    ///error("$errstr ($errno)\n",3,'./cyj_web/cache/error.log');       ////连接连接池失败
                } else {
                    $this->type = 1;
                }
            }
        }
        
        public function __call($func,$args){
            if($this->type == 1){
                if($this->BaseWrite(json_encode(['a'=>$func,'p'=>$args]))){
                    $data = $this->BaseRead();
                    if($data !== FALSE){
                        $data = json_decode($data,TRUE);
                        if($data)
                            return $data['i'];
                        else
                            return FALSE;
                    }else{
                        ///error("数据读取失败\n",3,'./cyj_web/cache/error.log');
                        return FALSE;
                    }
                }else{
                    ///error("数据传输失败\n",3,'./cyj_web/cache/error.log');
                    return FALSE;
                }
            }else{
                return call_user_func_array(array($this->sock, $func), $args);
            }
        }
        
        private function BaseWrite($data){
            $data .= "\n";
            $sumlen = strlen($data);
            $len = fwrite($this->sock, $data);
            if($sumlen == $len){
                return TRUE;
            }else{
                return FALSE;
            }
        }
        
        private function BaseRead(){
            $buffer = '';
            
            while (!feof($this->sock)) {
                $buffer .= fgets($this->sock, 128);
                $pos = strpos($buffer, "\n");
                if($pos !== false){
                    $buffer = trim($buffer);
                    break;
                }
            }
            
            if(strlen($buffer) > 0){
                return $buffer;
            }else{
                return FALSE;
            }
        }
        
        private function SockForRedis(){                                   ////直接调用redis
            $this->sock = new Redis();
            $this->sock->pconnect(REDIS_HOST,REDIS_PORT);
            $this->type = 0;
        }
        
        public static function getInstace(){
            if(empty(static::$Instace)){
                static::$Instace = new RedisConPool();
            }
            return static::$Instace;
        }
        
        public function close(){
            if($this->type == 1){
                fclose($this->sock);
            }else{
                $this->sock->close();
            }
            static::$Instace = "";
        }
        
        public function __destruct() {
            $this->close();
        }
    }
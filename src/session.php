<?php
namespace ptcms;

class Session
{

    public function init($name = [])
    {
        $name = array_merge(Config::get('session', []), $name);
        if (Config::get('var_session_id') && isset($_REQUEST[Config::get('var_session_id')])) {
            session_id($_REQUEST[Config::get('var_session_id')]);
        } elseif (isset($name['id'])) {
            session_id($name['id']);
        }
        if (empty($name['type']) && Config::get('driver_session')) {
            $name['type'] = Config::get('driver_session');
        }
        if (isset($name['name'])) session_name($name['name']);
        if (isset($name['path'])) session_save_path($name['path']);
        if (isset($name['domain'])) ini_set('session.cookie_domain', $name['domain']);
        if (isset($name['expire'])) ini_set('session.gc_maxlifetime', $name['expire']);
        if (isset($name['use_trans_sid'])) ini_set('session.use_trans_sid', $name['use_trans_sid'] ? 1 : 0);
        if (isset($name['use_cookies'])) ini_set('session.use_cookies', $name['use_cookies'] ? 1 : 0);
        if (isset($name['cache_limiter'])) session_cache_limiter($name['cache_limiter']);
        if (isset($name['cache_expire'])) session_cache_expire($name['cache_expire']);
        if (isset($name['type'])) {
            $type   = $name['type'];
            $class  = 'Driver_Session_' . $type;
            $hander = new $class();
            session_set_save_handler(
                [&$hander, "open"],
                [&$hander, "close"],
                [&$hander, "read"],
                [&$hander, "write"],
                [&$hander, "destroy"],
                [&$hander, "gc"]);
        }
        session_start();
    }

    public function __set($name, $value)
    {
        return $this->set($name, $value);
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function get($name = '', $default = null)
    {
        if ($name == '') return $_SESSION;
        //数组模式 找到返回
        if (strpos($name, '.')) {
            //数组模式 找到返回
            $c      = $_SESSION;
            $fields = explode('.', $name);
            foreach ($fields as $field) {
                if (!isset($c[$field])) return (is_callable($default) ? $default($name) : $default);
                $c = $c[$field];
            }
            return $c;
        } elseif (isset($_SESSION[$name])) {
            return $_SESSION[$name];
        } else {
            return (is_callable($default) ? $default($name) : $default);
        }
    }

    public function set($key, $value = '')
    {
        $_SESSION[$key] = $value;
        return true;
    }

    public function rm($key)
    {
        if (!isset($_SESSION[$key])) {
            return false;
        }

        unset($_SESSION[$key]);

        return true;
    }

    /**
     * 清空session值
     *
     * @access public
     * @return void
     */
    public static function clear()
    {

        $_SESSION = [];
    }

    /**
     * 注销session
     *
     * @access public
     * @return void
     */
    public static function destory()
    {

        if (session_id()) {
            unset($_SESSION);
            session_destroy();
        }
    }

    /**
     * 当浏览器关闭时,session将停止写入
     *
     * @access public
     * @return void
     */
    public static function close()
    {

        if (session_id()) {
            session_write_close();
        }
    }
}
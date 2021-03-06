<?php
/* * *********************************************************
 * [cmlphp] (C)2012 - 3000 http://cmlphp.com
 * @Author  linhecheng<linhechengbush@live.com>
 * @Date: 14-2-8 下午3:07
 * @version  @see \Cml\Cml::VERSION
 * cmlphp框架 memcache缓存驱动
 * *********************************************************** */
namespace Cml\Cache;

use Cml\Config;
use Cml\Exception\CacheConnectFailException;
use Cml\Exception\PhpExtendNotInstall;
use Cml\Lang;
use Cml\Log;
use Cml\Plugin;

/**
 * memcache缓存驱动
 *
 * @package Cml\Cache
 */
class Memcache extends namespace\Base
{
    /**
     * @var \Memcache | \Memcached
     */
    private $memcache;

    /**
     * @var int 类型 1Memcached 2 Memcache
     */
    private $type = 1;

    /**
     * 返回memcache驱动类型  加锁时用
     *
     * @return int
     */
    public function getDriverType()
    {
        return $this->type;
    }

    /**
     * 使用的缓存配置 默认为使用default_cache配置的参数
     *
     * @param bool ｜array $conf
     *
     * @throws CacheConnectFailException | PhpExtendNotInstall
     */
    public function __construct($conf = false)
    {
        $this->conf = $conf ? $conf : Config::get('default_cache');

        if (extension_loaded('Memcached')) {
            $this->memcache = new \Memcached('cml_memcache_pool');
            $this->type = 1;
        } elseif (extension_loaded('Memcache')) {
            $this->memcache = new \Memcache;
            $this->type = 2;
        } else {
            throw new PhpExtendNotInstall(Lang::get('_CACHE_EXTEND_NOT_INSTALL_', 'Memcached/Memcache'));
        }

        if (!$this->memcache) {
            throw new PhpExtendNotInstall(Lang::get('_CACHE_NEW_INSTANCE_ERROR_', 'Memcache'));
        }

        $singleNodeDownFunction = function ($host, $port) {
            //这边挂掉调用此回调在几s内只会调用一次。其它情况使用memcache方法均返回flase不报错
            Plugin::hook('cml.cache_server_down', ['host' => $host, 'port' => $port]);
            Log::emergency('memcache server down', ['downServer' => ['host' => $host, 'port' => $port]]);
        };

        $allNodeDownFunction = function ($serverList) {
            Plugin::hook('cml.cache_server_down', $this->conf['server'], $serverList);//全挂

            throw new CacheConnectFailException(Lang::get('_CACHE_CONNECT_FAIL_', 'Memcache',
                json_encode($serverList)
            ));
        };

        $downServer = 0;

        if ($this->type == 2) {//memcache
            foreach ($this->conf['server'] as $val) {
                if (!$this->memcache->addServer($val['host'], $val['port'])) {
                    Log::emergency('memcache server down', ['downServer' => $val]);
                }
            }

            method_exists($this->memcache, 'setFailureCallback') && $this->memcache->setFailureCallback($singleNodeDownFunction);

            $serverList = $this->memcache->getextendedstats();
            foreach ($serverList as $server => $status) {
                $status || $downServer++;
            }

            if (count($serverList) <= $downServer) {
                $allNodeDownFunction($serverList);
            }

            return;
        }

        if (md5(json_encode($this->conf['server'])) !== md5(json_encode($this->memcache->getServerList()))) {
            $this->memcache->quit();
            $this->memcache->resetServerList();
            $this->memcache->addServers(array_values($this->conf['server']));

            $this->memcache->setOptions([
                \Memcached::OPT_PREFIX_KEY => $this->conf['prefix'],
                \Memcached::OPT_DISTRIBUTION => \Memcached::DISTRIBUTION_CONSISTENT,
                \Memcached::OPT_LIBKETAMA_COMPATIBLE => true,
                \Memcached::OPT_SERVER_FAILURE_LIMIT => 1,
                \Memcached::OPT_RETRY_TIMEOUT => 30,
                \Memcached::OPT_AUTO_EJECT_HOSTS => true,
                \Memcached::OPT_REMOVE_FAILED_SERVERS => true
            ]);

            \Memcached::HAVE_JSON && $this->memcache->setOption(\Memcached::OPT_SERIALIZER, \Memcached::SERIALIZER_JSON_ARRAY);
        }

        $serverStatus = $this->memcache->getStats();
        foreach ($serverStatus as $server => $status) {
            if ($status['pid'] < 1) {
                $downServer++;
                $server = explode(':', $server);
                $singleNodeDownFunction($server[0], $server[1]);
            }
        }
        if (count($serverStatus) <= $downServer) {
            $allNodeDownFunction($serverStatus);
        }
    }

    /**
     * 根据key取值
     *
     * @param mixed $key 要获取的缓存key
     *
     * @return mixed
     */
    public function get($key)
    {
        if ($this->type === 1) {
            $return = $this->memcache->get($key);
        } else {
            $return = json_decode($this->memcache->get($this->conf['prefix'] . $key), true);
        }

        is_null($return) && $return = false;
        return $return; //orm层做判断用
    }

    /**
     * 存储对象
     *
     * @param mixed $key 要缓存的数据的key
     * @param mixed $value 要缓存的值,除resource类型外的数据类型
     * @param int $expire 缓存的有效时间 0为不过期
     *
     * @return bool
     */
    public function set($key, $value, $expire = 0)
    {
        if ($this->type === 1) {
            return $this->memcache->set($key, $value, $expire);
        } else {
            return $this->memcache->set($this->conf['prefix'] . $key, json_encode($value, JSON_UNESCAPED_UNICODE), false, $expire);
        }
    }

    /**
     * 更新对象
     *
     * @param mixed $key 要更新的数据的key
     * @param mixed $value 要更新缓存的值,除resource类型外的数据类型
     * @param int $expire 缓存的有效时间 0为不过期
     *
     * @return bool
     */
    public function update($key, $value, $expire = 0)
    {
        if ($this->type === 1) {
            return $this->memcache->replace($key, $value, $expire);
        } else {
            return $this->memcache->replace($this->conf['prefix'] . $key, json_encode($value, JSON_UNESCAPED_UNICODE), false, $expire);
        }

    }

    /**
     * 删除对象
     *
     * @param mixed $key 要删除的数据的key
     *
     * @return bool
     */
    public function delete($key)
    {
        $this->type === 2 && $key = $this->conf['prefix'] . $key;
        return $this->memcache->delete($key);
    }

    /**
     * 清洗已经存储的所有元素
     *
     */
    public function truncate()
    {
        $this->memcache->flush();
        return true;
    }

    /**
     * 自增
     *
     * @param mixed $key 要自增的缓存的数据的key
     * @param int $val 自增的进步值,默认为1
     *
     * @return bool
     */
    public function increment($key, $val = 1)
    {
        $this->type === 2 && $key = $this->conf['prefix'] . $key;
        return $this->memcache->increment($key, abs(intval($val)));
    }

    /**
     * 自减
     *
     * @param mixed $key 要自减的缓存的数据的key
     * @param int $val 自减的进步值,默认为1
     *
     * @return bool
     */
    public function decrement($key, $val = 1)
    {
        $this->type === 2 && $key = $this->conf['prefix'] . $key;
        return $this->memcache->decrement($key, abs(intval($val)));
    }

    /**
     * 返回实例便于操作未封装的方法
     *
     * @param string $key
     *
     * @return \Memcache|\Memcached
     */
    public function getInstance($key = '')
    {
        return $this->memcache;
    }
}

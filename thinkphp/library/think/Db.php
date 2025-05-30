<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think;

use think\db\Connection;
use think\db\Query;

/**
 * Class Db
 * @package think
 * @method static Query table(string $table) 指定数据表（含前缀）
 * @method static Query name(string $name) 指定数据表（不含前缀）
 * @method static Query where(mixed $field, string $op = null, mixed $condition = null) 查询条件
 * @method static Query join(mixed $join, mixed $condition = null, string $type = 'INNER') JOIN查询
 * @method static Query union(mixed $union, boolean $all = false) UNION查询
 * @method static Query limit(mixed $offset, integer $length = null) 查询LIMIT
 * @method static Query order(mixed $field, string $order = null) 查询ORDER
 * @method static Query cache(mixed $key = null , integer $expire = null) 设置查询缓存
 * @method static mixed value(string $field) 获取某个字段的值
 * @method static array column(string $field, string $key = '') 获取某个列的值
 * @method static Query view(mixed $join, mixed $field = null, mixed $on = null, string $type = 'INNER') 视图查询
 * @method static mixed find(mixed $data = null) 查询单个记录
 * @method static mixed select(mixed $data = null) 查询多个记录
 * @method static integer insert(array $data, boolean $replace = false, boolean $getLastInsID = false, string $sequence = null) 插入一条记录
 * @method static integer insertGetId(array $data, boolean $replace = false, string $sequence = null) 插入一条记录并返回自增ID
 * @method static integer insertAll(array $dataSet) 插入多条记录
 * @method static integer update(array $data) 更新记录
 * @method static integer delete(mixed $data = null) 删除记录
 * @method static boolean chunk(integer $count, callable $callback, string $column = null) 分块获取数据
 * @method static mixed query(string $sql, array $bind = [], boolean $master = false, bool $pdo = false) SQL查询
 * @method static integer execute(string $sql, array $bind = [], boolean $fetch = false, boolean $getLastInsID = false, string $sequence = null) SQL执行
 * @method static Paginator paginate(integer $listRows = 15, mixed $simple = null, array $config = []) 分页查询
 * @method static mixed transaction(callable $callback) 执行数据库事务
 * @method static void startTrans() 启动事务
 * @method static void commit() 用于非自动提交状态下面的查询提交
 * @method static void rollback() 事务回滚
 * @method static boolean batchQuery(array $sqlArray) 批处理执行SQL语句
 * @method static string quote(string $str) SQL指令安全过滤
 * @method static string getLastInsID($sequence = null) 获取最近插入的ID
 */
class Db
{
    /**
     * @var Connection[] 数据库连接实例
     */
    private static $instance = [];

    /**
     * @var int 查询次数
     */
    public static $queryTimes = 0;

    /**
     * @var int 执行次数
     */
    public static $executeTimes = 0;

    /**
     * 数据库初始化，并取得数据库类实例
     * @access public
     * @param  mixed       $config 连接配置
     * @param  bool|string $name   连接标识 true 强制重新连接
     * @return Connection
     * @throws Exception
     */
    public static function connect($config = [], $name = false)
    {
        if (false === $name) {
            $name = md5(serialize($config));
        }

        if (true === $name || !isset(self::$instance[$name])) {
            // 解析连接参数 支持数组和字符串
            $options = self::parseConfig($config);

            if (empty($options['type'])) {
                throw new \InvalidArgumentException('Undefined db type');
            }

            $class = false !== strpos($options['type'], '\\') ?
            $options['type'] :
            '\\think\\db\\connector\\' . ucwords($options['type']);

            // 记录初始化信息
            if (App::$debug) {
                Log::record('[ DB ] INIT ' . $options['type'], 'info');
            }

            if (true === $name) {
                $name = md5(serialize($config));
            }

            self::$instance[$name] = new $class($options);
        }

        return self::$instance[$name];
    }

    /**
     * 清除连接实例
     * @access public
     * @return void
     */
    public static function clear()
    {
        self::$instance = [];
    }

    /**
     * 数据库连接参数解析
     * @access private
     * @param  mixed $config 连接参数
     * @return array
     */
    private static function parseConfig($config)
    {
        if (empty($config)) {
            $config = Config::get('database');
        } elseif (is_string($config) && false === strpos($config, '/')) {
            $config = Config::get($config); // 支持读取配置参数
        }

        return is_string($config) ? self::parseDsn($config) : $config;
    }

    /**
     * DSN 解析
     * 格式： mysql://username:passwd@localhost:3306/DbName?param1=val1&param2=val2#utf8
     * @access private
     * @param  string $dsnStr 数据库 DSN 字符串解析
     * @return array
     */
    private static function parseDsn($dsnStr)
    {
        $info = parse_url($dsnStr);

        if (!$info) {
            return [];
        }

        $dsn = [
            'type'     => $info['scheme'],
            'username' => isset($info['user']) ? $info['user'] : '',
            'password' => isset($info['pass']) ? $info['pass'] : '',
            'hostname' => isset($info['host']) ? $info['host'] : '',
            'hostport' => isset($info['port']) ? $info['port'] : '',
            'database' => !empty($info['path']) ? ltrim($info['path'], '/') : '',
            'charset'  => isset($info['fragment']) ? $info['fragment'] : 'utf8',
        ];

        if (isset($info['query'])) {
            parse_str($info['query'], $dsn['params']);
        } else {
            $dsn['params'] = [];
        }

        return $dsn;
    }

    /**
     * 调用驱动类的方法
     * @access public
     * @param  string $method 方法名
     * @param  array  $params 参数
     * @return mixed
     */
    public static function __callStatic($method, $params)
    {
        return call_user_func_array([self::connect(), $method], $params);
    }
}

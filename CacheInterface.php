<?php
namespace Itxiao6\Database;

/**
 * 缓存接口
 * Interface CacheInterface
 * @package Itxiao6\Database
 */
interface CacheInterface
{
    /**
     * 生成缓存的key
     * @param $keyword
     * @return string
     */
    public function make_name($keyword);

    /**
     * 设置值
     * @param $name
     * @param $value
     * @param $time
     * @return mixed
     */
    public function set($name,$value,$time);

    /**
     * 获取值
     * @param $name
     * @return mixed
     */
    public function get($name);

    /**
     * 判断缓存连接 是否通畅
     * @return bool
     */
    public function ping();

    /**
     * 重新连接
     * @return bool
     */
    public function reconnect();

    /**
     * 记住数据
     * @param string $name
     * @param \Closure|null $callback
     * @param int $time
     * @return mixed
     */
    public function remember($name,$callback = null,$time = 0);

}
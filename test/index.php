<?php
namespace Itxiao6\Database;


include_once(__DIR__.'/../vendor/autoload.php');
use Itxiao6\Database\Capsule\Manager as DB;
use Itxiao6\Database\Capsule\Manager;
use Itxiao6\Database\Query\Builder;

/**
 * 缓存驱动
 */
class Cache implements CacheInterface
{
    /**
     * 生成缓存的key
     * @param $keyword
     * @return string
     */
    public function make_name($keyword){
        return 'databases_'.$keyword;
    }

    /**
     * 设置值
     * @param $name
     * @param $value
     * @param $time
     * @return bool|int
     */
    public function set($name,$value,$time){
        return file_put_contents(__DIR__.'/data/'.$name.'txt',['data'=>serialize($value),'invalid'=>time()+$time]);
    }

    /**
     * 获取值
     * @param $name
     * @return mixed
     * @throws \Exception
     */
    public function get($name){
        if(file_exists(__DIR__.'/data/'.$name.'txt') && is_file(__DIR__.'/data/'.$name.'txt')){
            $data = unserialize(file_get_contents(__DIR__.'/data/'.$name.'txt'));
            if((!isset($data['invalid'])) || $data['invalid'] < time()){
                unlink(__DIR__.'/data/'.$name.'txt');
                throw new \Exception('暂无数据');
            }else{
                return isset($data['data'])?$data['data']:null;
            }
        }else{
            throw new \Exception('暂无数据');
        }
    }

    /**
     * 记住数据
     * @param string $name
     * @param \Closure|null $callback
     * @param int $time
     * @return mixed
     * @throws \Exception
     */
    public function remember($name,$callback = null,$time = 0){
        try{
            # 获取缓存的数据
            $data = $this -> get($name);
        }catch (\Exception $exception){
            if($exception -> getMessage() == '暂无数据'){
                # 获取数据
                $data = $callback();
                # 存储数据
                $this -> set($name,$data,$time);
                # 返回数据
                return $data;
            }else{
                # 抛出其他异常
                throw $exception;
            }
        }
    }
}

/**
 * 定义用户模型
 */
class User extends \Itxiao6\Database\Eloquent\Model {
    /**
     * 模型的连接名称。
     *
     * @var string
     */
    protected $connection = 'default';
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        # 连接数据库
        // 实例化数据库容器
        $capsule = new DB;
        // 添加连接到容器内
        $capsule->addConnection([
            'driver'    => 'mysql',
            'host'      => '47.104.85.153',
            'database'  => 'shop',
            'username'  => 'shop',
            'password'  => '4rBrfCDdkR',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ]);
        // 设置全局静态可访问
        $capsule->setAsGlobal();
        $capsule ->bootEloquent();
        # 设置缓存类
        $this -> set_cache_driver(new Cache());
    }
    protected $table = 'admin_permissions';
}
$capsule = new \Itxiao6\Database\Capsule\Manager;
// 添加连接到容器内
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => '47.104.85.153',
    'database'  => 'shop',
    'username'  => 'shop',
    'password'  => '4rBrfCDdkR',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

$capsule -> setAsGlobal();
$capsule -> bootEloquent();
# 回调类型的事务
//Manager::connection('default') -> transaction(function(Builder $builder){
//    var_dump('测试');
//},5);
User::transaction(function(Builder $builder){
    var_dump('测试');
});
//$result = User::remember(15) -> get();

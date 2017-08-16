<?php
use Itxiao6\Database\Eloquent\Model;
use Itxiao6\Database\Capsule\Manager as DB;
# 加载自动加载规则
include(__DIR__.'/../vendor/autoload.php');
class User extends Model {
    protected $table = 'users';
    /**
     * @param String 表名(可为空)
     */
    public function __construct()
    {
        # 连接数据库
        $database = new DB;
        # 载入数据库配置
        $database->addConnection([
                'host'      => '121.42.251.110',	# 数据库连接地址
                'database'  => 'fab',		# 数据库名字
                'username'  => 'fab',		# 数据库账号
                'password'  => 'fab2017',			# 数据库密码
                'prefix'    => '',							# 数据库表前缀
                'driver'    => 'mysql',						# 数据库驱动
                'charset'   => 'utf8',						# 数据库字符集
                'collation' => 'utf8_unicode_ci',			# 数据库编码
            ]);
        # 设置全局静态可访问
        $database->setAsGlobal();
        # 启动Eloquent
        $database -> bootEloquent();
        # 调用父类构造方法
        parent::__construct();
    }
}
$user = User::where(['id'=>6]) -> cache(10) -> paginate(2);
//dd($user);
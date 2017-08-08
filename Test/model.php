<?php
use Itxiao6\Database\Eloquent\Model;
use Itxiao6\Database\Capsule\Manager as DB;
class User extends Model {
    /**
     * @param String 表名(可为空)
     */
    public function __construct()
    {
        # 连接数据库
        $database = new DB;
        # 载入数据库配置
        $database->addConnection([
                'host'      => 'location',	# 数据库连接地址
                'database'  => 'test',		# 数据库名字
                'username'  => 'root',		# 数据库账号
                'password'  => '',			# 数据库密码
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
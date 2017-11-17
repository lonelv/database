## 基于Laravel 的 database 组件开发的一个通用的数据库ORM工具。并且注释汉化、缓存可灵活拓展、不依赖于Laravel组件
它目前支持MySQL，SQL Server，Postgres和SQLite。
### 使用说明

```PHP
use Itxiao6\Database\Capsule\Manager as DB;
// 实例化数据库容器
$capsule = new DB;
// 添加连接到容器内
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'database',
    'username'  => 'root',
    'password'  => 'password',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

```

> `composer require "itxiao6/database"` 使用Composer 下载 Itxiao6/database


**使用查询生成器**

```PHP
// 查询users 表 votes 大于100 的所有数据
$users = DB::table('users')->where('votes', '>', 100)->get();
// 查询users 表 votes 大于100 的 一条数据
$users = DB::table('users')->where('votes', '>', 100)->first();
```
其他核心方法可以直接从构造器中以与DB正面相同的方式访问
```PHP
$results = DB::select('select * from users where id = ?', array(1));
```

**使用模式生成器**

```PHP
DB::schema()->create('users', function ($table) {
    $table->increments('id');
    $table->string('email')->unique();
    $table->timestamps();
});
```

**使用构造器模型**

```PHP

/**
 * 定义用户模型
 */
class User extends Itxiao6\Database\Eloquent\Model {
    protected $table = 'users';
}
// 获取用户模型的votes 大于1的所有数据
$users = User::where('votes', '>', 1)->get();
// 可连贯多where操作
$users = User::where('votes', '>', 1) -> where(['id'=>5]) -> get();
```
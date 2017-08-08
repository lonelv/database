##MinKernel database
的minkernel数据库组件是一个php的完整数据库工具包，提供了一个表达的查询生成器，
ActiveRecord ORM架构生成器和风格。它目前支持MySQL，SQL Server，Postgres和SQLite。
它也是该laravel PHP框架数据库层。
###使用说明

```PHP
use Itxiao6\Database\Capsule\Manager as DB;

$capsule = new DB;

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
$users = DB::table('users')->where('votes', '>', 100)->get();
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
class User extends Itxiao6\Database\Eloquent\Model {}

$users = User::where('votes', '>', 1)->get();
```
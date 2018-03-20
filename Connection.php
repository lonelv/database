<?php

namespace Itxiao6\Database;

use PDO;
use Closure;
use Exception;
use PDOStatement;
use LogicException;
use DateTimeInterface;
use Illuminate\Support\Arr;
use Itxiao6\Database\Query\Expression;
use Illuminate\Contracts\Events\Dispatcher;
use Itxiao6\Database\Events\QueryExecuted;
use Doctrine\DBAL\Connection as DoctrineConnection;
use Itxiao6\Database\Query\Processors\Processor;
use Itxiao6\Database\Query\Builder as QueryBuilder;
use Itxiao6\Database\Schema\Builder as SchemaBuilder;
use Itxiao6\Database\Query\Grammars\Grammar as QueryGrammar;

/**
 * 连接类
 * Class Connection
 * @package Itxiao6\Database
 */
class Connection implements ConnectionInterface
{
    use DetectsDeadlocks,
        DetectsLostConnections,
        Concerns\ManagesTransactions;

    /**
     * PDO 连接
     *
     * @var PDO
     */
    protected $pdo;

    /**
     * The active PDO connection used for reads.
     *
     * @var PDO
     */
    protected $readPdo;

    /**
     * 所连接的数据库名称
     *
     * @var string
     */
    protected $database;

    /**
     * 所连接的数据库表前缀
     *
     * @var string
     */
    protected $tablePrefix = '';

    /**
     * 数据库连接配置。
     *
     * @var array
     */
    protected $config = [];

    /**
     * The reconnector instance for the connection.
     *
     * @var callable
     */
    protected $reconnector;

    /**
     * 查询语法实现。
     *
     * @var \Itxiao6\Database\Query\Grammars\Grammar
     */
    protected $queryGrammar;

    /**
     * 架构语法实现。
     *
     * @var \Itxiao6\Database\Schema\Grammars\Grammar
     */
    protected $schemaGrammar;

    /**
     * 查询后处理器实现。
     *
     * @var \Itxiao6\Database\Query\Processors\Processor
     */
    protected $postProcessor;

    /**
     * 事件调度器实例。
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $events;

    /**
     * 连接的默认提取模式。
     *
     * @var int
     */
    protected $fetchMode = PDO::FETCH_OBJ;

    /**
     * 活动事务的数目。
     *
     * @var int
     */
    protected $transactions = 0;
    /**
     * 数据缓存时间(默认为0则不缓存)
     * @var int
     */
    public $cache_time = 0;
    /**
     * 缓存驱动
     * @var CacheInterface|null
     */
    public $cache_driver = null;
    /**
     * 分页驱动
     * @var PaginateInterface|null
     */
    public $paginate_driver = null;

    /**
     * 所有的查询都运行在连接上。
     *
     * @var array
     */
    protected $queryLog = [];

    /**
     * 是否开启了 sql Log
     *
     * @var bool
     */
    protected $loggingQueries = false;

    /**
     * 指示连接是否处于“运行”状态。
     *
     * @var bool
     */
    protected $pretending = false;

    /**
     * Doctrine 连接的例子。
     *
     * @var \Doctrine\DBAL\Connection
     */
    protected $doctrineConnection;

    /**
     * 连接解析器。
     *
     * @var array
     */
    protected static $resolvers = [];

    /**
     * 创建一个新的数据库连接实例。
     *
     * @param  \PDO|\Closure     $pdo
     * @param  string   $database
     * @param  string   $tablePrefix
     * @param  array    $config
     * @return void
     */
    public function __construct($pdo, $database = '', $tablePrefix = '', array $config = [])
    {
        $this->pdo = $pdo;

        // First we will setup the default properties. We keep track of the DB
        // name we are connected to since it is needed when some reflective
        // type commands are run such as checking whether a table exists.

        //首先我们将设置默认属性。 我们跟踪DB
        //我们连接的名称，因为它需要一些reflective
        //运行类型命令，例如检查表是否存在。
        $this->database = $database;

        $this->tablePrefix = $tablePrefix;

        $this->config = $config;

        //我们需要初始化查询语法和查询后处理器
        //这些都是数据库抽象的非常重要的部分
        //所以我们在启动时将它们初始化为默认值。
        $this->useDefaultQueryGrammar();

        $this->useDefaultPostProcessor();
    }

    /**
     * 将查询语法设置为默认实现。
     *
     * @return void
     */
    public function useDefaultQueryGrammar()
    {
        $this->queryGrammar = $this->getDefaultQueryGrammar();
    }

    /**
     * 获取默认的查询语法实例。
     *
     * @return \Itxiao6\Database\Query\Grammars\Grammar
     */
    protected function getDefaultQueryGrammar()
    {
        return new QueryGrammar;
    }

    /**
     * 将模式语法设置为默认实现。
     *
     * @return void
     */
    public function useDefaultSchemaGrammar()
    {
        $this->schemaGrammar = $this->getDefaultSchemaGrammar();
    }

    /**
     * 获取默认的模式语法实例。
     *
     * @return \Itxiao6\Database\Schema\Grammars\Grammar
     */
    protected function getDefaultSchemaGrammar()
    {
        //
    }

    /**
     * 将查询后处理器设置为默认实现。
     *
     * @return void
     */
    public function useDefaultPostProcessor()
    {
        $this->postProcessor = $this->getDefaultPostProcessor();
    }

    /**
     * 获取默认的后处理器实例。
     *
     * @return \Itxiao6\Database\Query\Processors\Processor
     */
    protected function getDefaultPostProcessor()
    {
        return new Processor;
    }

    /**
     * 获取连接的模式构建器实例。
     *
     * @return \Itxiao6\Database\Schema\Builder
     */
    public function getSchemaBuilder()
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }

        return new SchemaBuilder($this);
    }

    /**
     * 开始对数据库表的流畅查询。
     *
     * @param  string  $table
     * @return \Itxiao6\Database\Query\Builder
     */
    public function table($table)
    {
        return $this->query()->from($table);
    }

    /**
     * 获取一个新的查询构建器实例。
     *
     * @return \Itxiao6\Database\Query\Builder
     */
    public function query()
    {
        return new QueryBuilder(
            $this, $this->getQueryGrammar(), $this->getPostProcessor()
        );
    }

    /**
     * 运行一个select语句并返回一个结果。
     *
     * @param  string  $query
     * @param  array   $bindings
     * @param  bool  $useReadPdo
     * @return mixed
     */
    public function selectOne($query, $bindings = [], $useReadPdo = true)
    {
        $records = $this->select($query, $bindings, $useReadPdo);

        return array_shift($records);
    }

    /**
     * 运行 一个 select 语句
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return array
     */
    public function selectFromWriteConnection($query, $bindings = [])
    {
        return $this->select($query, $bindings, false);
    }

    /**
     * 在数据库 运行一个 select语句。
     *
     * @param  string  $query
     * @param  array  $bindings
     * @param  bool  $useReadPdo
     * @return array
     */
    public function select($query, $bindings = [], $useReadPdo = true)
    {
        return $this->run($query, $bindings, function ($query, $bindings) use ($useReadPdo) {
            if ($this->pretending()) {
                return [];
            }
            $start = microtime(true);
            // For select statements, we'll simply execute the query and return an array
            // of the database result set. Each element in the array will be a single
            // row from the database table, and will either be an array or objects.
            //对于select语句，我们只需执行查询并返回一个数组
            //数据库结果集。 数组中的每个元素都将是单个元素
            //数据库表中的行，并且将是数组或对象。
            # 判断是否使用了缓存
            if($this -> cache_time > 1){
                $statement = $this->prepared($this->getPdoForSelect($useReadPdo)
                    ->prepare($query));

                # 绑定参数
                $this->bindValues($statement, $this->prepareBindings($bindings));

                # 执行查询
                $statement->execute();

                # 解析结果集
                $resurl = $statement->fetchAll();
                
                # 返回结果
                return $resurl;
            }else if($this -> cache_driver instanceof CacheInterface){
                # 获取缓存key
                $key = $this -> cache_driver -> make_name(substr(md5($query.serialize($bindings)),0,5));
                # 返回缓存的数据
                return $this -> cache_driver -> remember($key,function() use ($useReadPdo,$bindings,$query,$start){
                    $statement = $this -> prepared($this->getPdoForSelect($useReadPdo)
                        ->prepare($query));

                    # 绑定参数
                    $this -> bindValues($statement, $this->prepareBindings($bindings));

                    # 执行查询
                    $statement -> execute();
                    # 记录sql
                    $this -> logQuery(
                        $query, $bindings, $this -> getElapsedTime($start = microtime(true))
                    );
                    # 更新缓存
                    return $statement -> fetchAll();
                },$this -> cache_time);
            }
        });
    }

    /**
     * 对数据库运行select语句并返回一个生成器。
     *
     * @param  string  $query
     * @param  array  $bindings
     * @param  bool  $useReadPdo
     * @return \Generator
     */
    public function cursor($query, $bindings = [], $useReadPdo = true)
    {
        $statement = $this->run($query, $bindings, function ($query, $bindings) use ($useReadPdo) {
            if ($this->pretending()) {
                return [];
            }

            // First we will create a statement for the query. Then, we will set the fetch
            // mode and prepare the bindings for the query. Once that's done we will be
            // ready to execute the query against the database and return the cursor.
            $statement = $this->prepared($this->getPdoForSelect($useReadPdo)
                ->prepare($query));

            $this->bindValues(
                $statement, $this->prepareBindings($bindings)
            );

            // Next, we'll execute the query against the database and return the statement
            // so we can return the cursor. The cursor will use a PHP generator to give
            // back one row at a time without using a bunch of memory to render them.
            $statement->execute();

            return $statement;
        });

        while ($record = $statement->fetch()) {
            yield $record;
        }
    }

    /**
     * 配置PDO准备语句。
     *
     * @param  \PDOStatement  $statement
     * @return \PDOStatement
     */
    protected function prepared(PDOStatement $statement)
    {
        $statement->setFetchMode($this->fetchMode);

        $this->event(new Events\StatementPrepared(
            $this, $statement
        ));

        return $statement;
    }

    /**
     * 获取PDO连接以用于选择查询。
     *
     * @param  bool  $useReadPdo
     * @return \PDO
     */
    protected function getPdoForSelect($useReadPdo = true)
    {
        return $useReadPdo ? $this->getReadPdo() : $this->getPdo();
    }

    /**
     * 对数据库运行insert语句。
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return bool
     */
    public function insert($query, $bindings = [])
    {
        return $this->statement($query, $bindings);
    }

    /**
     * 对数据库运行update语句。
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return int
     */
    public function update($query, $bindings = [])
    {
        return $this->affectingStatement($query, $bindings);
    }

    /**
     * 对数据库运行delete语句。
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return int
     */
    public function delete($query, $bindings = [])
    {
        return $this->affectingStatement($query, $bindings);
    }

    /**
     * 执行SQL语句并返回布尔结果。
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return bool
     */
    public function statement($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return true;
            }

            $statement = $this->getPdo()->prepare($query);

            $this->bindValues($statement, $this->prepareBindings($bindings));

            return $statement->execute();
        });
    }

    /**
     * 运行SQL语句并获取受影响的行数。
     *
     * @param  string  $query
     * @param  array   $bindings
     * @return int
     */
    public function affectingStatement($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return 0;
            }

            // For update or delete statements, we want to get the number of rows affected
            // by the statement and return that back to the developer. We'll first need
            // to execute the statement and then we'll use PDO to fetch the affected.
            $statement = $this->getPdo()->prepare($query);

            $this->bindValues($statement, $this->prepareBindings($bindings));

            $statement->execute();

            return $statement->rowCount();
        });
    }

    /**
     * 对PDO连接运行原始的，未准备的查询。
     *
     * @param  string  $query
     * @return bool
     */
    public function unprepared($query)
    {
        return $this->run($query, [], function ($query) {
            if ($this->pretending()) {
                return true;
            }

            return (bool) $this->getPdo()->exec($query);
        });
    }

    /**
     * 在 "dry run" 模式下执行给定的回调。
     *
     * @param  \Closure  $callback
     * @return array
     */
    public function pretend(Closure $callback)
    {
        return $this->withFreshQueryLog(function () use ($callback) {
            $this->pretending = true;

            // Basically to make the database connection "pretend", we will just return
            // the default values for all the query methods, then we will return an
            // array of queries that were "executed" within the Closure callback.
            $callback($this);

            $this->pretending = false;

            return $this->queryLog;
        });
    }

    /**
     * 在 "dry run" 模式下执行给定的回调。
     *
     * @param  \Closure  $callback
     * @return array
     */
    protected function withFreshQueryLog($callback)
    {
        $loggingQueries = $this->loggingQueries;

        // First we will back up the value of the logging queries property and then
        // we'll be ready to run callbacks. This query log will also get cleared
        // so we will have a new log of all the queries that are executed now.
        //首先我们将备份logging queries属性的值，然后
        //我们将准备好运行回调。 此查询日志也将被清除
        //所以我们将有一个新的日志，所有的查询现在执行。
        $this->enableQueryLog();

        $this->queryLog = [];

        // Now we'll execute this callback and capture the result. Once it has been
        // executed we will restore the value of query logging and give back the
        // value of hte callback so the original callers can have the results.
        //现在我们将执行这个回调并捕获结果。 一旦一直
        //执行后，我们将恢复查询记录的值并返回
        // hte回调的值，所以原来的调用者可以得到结果。
        $result = $callback();

        $this->loggingQueries = $loggingQueries;

        return $result;
    }

    /**
     * 将值绑定到给定语句中的参数。
     *
     * @param  \PDOStatement $statement
     * @param  array  $bindings
     * @return void
     */
    public function bindValues($statement, $bindings)
    {
        foreach ($bindings as $key => $value) {
            $statement->bindValue(
                is_string($key) ? $key : $key + 1, $value,
                is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
    }

    /**
     * 准备查询绑定以执行。
     *
     * @param  array  $bindings
     * @return array
     */
    public function prepareBindings(array $bindings)
    {
        $grammar = $this->getQueryGrammar();

        foreach ($bindings as $key => $value) {
            // We need to transform all instances of DateTimeInterface into the actual
            // date string. Each query grammar maintains its own date string format
            // so we'll just ask the grammar for the format to get from the date.
            //我们需要将所有的DateTimeInterface实例转换成实际的
            //日期字符串。 每个查询语法维护自己的日期字符串格式
            //所以我们只是要求语法从日期开始获取格式。
            if ($value instanceof DateTimeInterface) {
                $bindings[$key] = $value->format($grammar->getDateFormat());
            } elseif ($value === false) {
                $bindings[$key] = 0;
            }
        }

        return $bindings;
    }

    /**
     * 运行SQL语句并记录其执行上下文。
     *
     * @param  string    $query
     * @param  array     $bindings
     * @param  \Closure  $callback
     * @return mixed
     *
     * @throws \Itxiao6\Database\QueryException
     */
    protected function run($query, $bindings, Closure $callback)
    {
        $this->reconnectIfMissingConnection();

        $start = microtime(true);

        // Here we will run this query. If an exception occurs we'll determine if it was
        // caused by a connection that has been lost. If that is the cause, we'll try
        // to re-establish connection and re-run the query with a fresh connection.
        try {
            $result = $this->runQueryCallback($query, $bindings, $callback);
        } catch (QueryException $e) {
            $result = $this->handleQueryException(
                $e, $query, $bindings, $callback
            );
        }

        // Once we have run the query we will calculate the time that it took to run and
        // then log the query, bindings, and execution time so we will report them on
        // the event that the developer needs them. We'll log time in milliseconds.
        # 判断是否开启了缓存
        if($this -> cache_time < 1){
            $this->logQuery(
                $query, $bindings, $this->getElapsedTime($start)
            );
        }


        return $result;
    }

    /**
     * 运行SQL语句。
     *
     * @param  string    $query
     * @param  array     $bindings
     * @param  \Closure  $callback
     * @return mixed
     *
     * @throws \Itxiao6\Database\QueryException
     */
    protected function runQueryCallback($query, $bindings, Closure $callback)
    {
        // To execute the statement, we'll simply call the callback, which will actually
        // run the SQL against the PDO connection. Then we can calculate the time it
        // took to execute and log the query SQL, bindings and time in our memory.
        //要执行该语句，我们将简单地调用回调函数，这实际上是这样
        //针对PDO连接运行SQL。 那么我们可以计算它的时间
        //在我们的内存中执行并记录查询SQL，绑定和时间。
        try {
            $result = $callback($query, $bindings);
        }

            // If an exception occurs when attempting to run a query, we'll format the error
            // message to include the bindings with SQL, which will make this exception a
            // lot more helpful to the developer instead of just the database's errors.
            //如果尝试运行查询时发生异常，我们将格式化错误
            //将包含与SQL绑定的消息，这将导致此异常
            //对开发人员而言更有帮助，而不仅仅是数据库的错误。
        catch (Exception $e) {
            throw new QueryException(
                $query, $this->prepareBindings($bindings), $e
            );
        }

        return $result;
    }

    /**
     * 在连接的查询日志中记录查询。
     *
     * @param  string  $query
     * @param  array   $bindings
     * @param  float|null  $time
     * @return void
     */
    public function logQuery($query, $bindings, $time = null)
    {
        $this->event(new QueryExecuted($query, $bindings, $time, $this));

        if ($this->loggingQueries) {
            $this->queryLog[] = compact('query', 'bindings', 'time');
        }
    }

    /**
     * 从给定的起点获得经过的时间。
     *
     * @param  int    $start
     * @return float
     */
    protected function getElapsedTime($start)
    {
        return round((microtime(true) - $start) * 1000, 2);
    }

    /**
     * 处理查询异常。
     *
     * @param  \Exception  $e
     * @param  string  $query
     * @param  array  $bindings
     * @param  \Closure  $callback
     * @return mixed
     * @throws \Exception
     */
    protected function handleQueryException($e, $query, $bindings, Closure $callback)
    {
        if ($this->transactions >= 1) {
            throw $e;
        }

        return $this->tryAgainIfCausedByLostConnection(
            $e, $query, $bindings, $callback
        );
    }

    /**
     * 处理查询执行期间发生的查询异常。
     *
     * @param  \Itxiao6\Database\QueryException  $e
     * @param  string    $query
     * @param  array     $bindings
     * @param  \Closure  $callback
     * @return mixed
     *
     * @throws \Itxiao6\Database\QueryException
     */
    protected function tryAgainIfCausedByLostConnection(QueryException $e, $query, $bindings, Closure $callback)
    {
        if ($this->causedByLostConnection($e->getPrevious())) {
            $this->reconnect();

            return $this->runQueryCallback($query, $bindings, $callback);
        }

        throw $e;
    }

    /**
     * 重新连接到数据库。
     *
     * @return void
     *
     * @throws \LogicException
     */
    public function reconnect()
    {
        if (is_callable($this->reconnector)) {
            return call_user_func($this->reconnector, $this);
        }

        throw new LogicException('Lost connection and no reconnector available.');
    }

    /**
     * 如果缺少PDO连接，请重新连接到数据库。
     *
     * @return void
     */
    protected function reconnectIfMissingConnection()
    {
        if (is_null($this->pdo)) {
            $this->reconnect();
        }
    }

    /**
     * 断开基础PDO连接。
     *
     * @return void
     */
    public function disconnect()
    {
        $this->setPdo(null)->setReadPdo(null);
    }

    /**
     * 使用连接注册数据库查询监听器。
     *
     * @param  \Closure  $callback
     * @return void
     */
    public function listen(Closure $callback)
    {
        if (isset($this->events)) {
            $this->events->listen(Events\QueryExecuted::class, $callback);
        }
    }

    /**
     * 为此连接发起事件。
     *
     * @param  string  $event
     * @return void
     */
    protected function fireConnectionEvent($event)
    {
        if (! isset($this->events)) {
            return;
        }

        switch ($event) {
            case 'beganTransaction':
                return $this->events->dispatch(new Events\TransactionBeginning($this));
            case 'committed':
                return $this->events->dispatch(new Events\TransactionCommitted($this));
            case 'rollingBack':
                return $this->events->dispatch(new Events\TransactionRolledBack($this));
        }
    }

    /**
     * 如果可能的话，给定事件。
     *
     * @param  mixed  $event
     * @return void
     */
    protected function event($event)
    {
        if (isset($this->events)) {
            $this->events->dispatch($event);
        }
    }

    /**
     * 获取一个新的原始查询表达式。
     *
     * @param  mixed  $value
     * @return \Itxiao6\Database\Query\Expression
     */
    public function raw($value)
    {
        return new Expression($value);
    }

    /**
     * Doctrine 是否可用？
     *
     * @return bool
     */
    public function isDoctrineAvailable()
    {
        return class_exists('Doctrine\DBAL\Connection');
    }

    /**
     * 获得一个 Doctrine Schema 列 实例。
     *
     * @param  string  $table
     * @param  string  $column
     * @return \Doctrine\DBAL\Schema\Column
     */
    public function getDoctrineColumn($table, $column)
    {
        $schema = $this->getDoctrineSchemaManager();

        return $schema->listTableDetails($table)->getColumn($column);
    }

    /**
     * 获取连接的 Doctrine DBAL 架构管理器。
     *
     * @return \Doctrine\DBAL\Schema\AbstractSchemaManager
     */
    public function getDoctrineSchemaManager()
    {
        return $this->getDoctrineDriver()->getSchemaManager($this->getDoctrineConnection());
    }

    /**
     * 获取Doctrine DBAL 数据库连接实例。
     *
     * @return \Doctrine\DBAL\Connection
     */
    public function getDoctrineConnection()
    {
        if (is_null($this->doctrineConnection)) {
            $data = ['pdo' => $this->getPdo(), 'dbname' => $this->getConfig('database')];

            $this->doctrineConnection = new DoctrineConnection(
                $data, $this->getDoctrineDriver()
            );
        }

        return $this->doctrineConnection;
    }

    /**
     * 获取当前的PDO连接。
     *
     * @return \PDO
     */
    public function getPdo()
    {
        if ($this->pdo instanceof Closure) {
            return $this->pdo = call_user_func($this->pdo);
        }

        return $this->pdo;
    }

    /**
     * 获取用于阅读的当前PDO连接。
     *
     * @return \PDO
     */
    public function getReadPdo()
    {
        if ($this->transactions >= 1) {
            return $this->getPdo();
        }

        if ($this->readPdo instanceof Closure) {
            return $this->readPdo = call_user_func($this->readPdo);
        }

        return $this->readPdo ?: $this->getPdo();
    }

    /**
     * 设置PDO连接。
     *
     * @param  \PDO|null  $pdo
     * @return $this
     */
    public function setPdo($pdo)
    {
        $this->transactions = 0;

        $this->pdo = $pdo;

        return $this;
    }

    /**
     * 设置用于阅读的PDO连接。
     *
     * @param  \PDO|null  $pdo
     * @return $this
     */
    public function setReadPdo($pdo)
    {
        $this->readPdo = $pdo;

        return $this;
    }

    /**
     * 在连接上设置重新连接实例。
     *
     * @param  callable  $reconnector
     * @return $this
     */
    public function setReconnector(callable $reconnector)
    {
        $this->reconnector = $reconnector;

        return $this;
    }

    /**
     * 获取数据库连接名称。
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->getConfig('name');
    }

    /**
     * 从配置选项中获取选项。
     *
     * @param  string|null  $option
     * @return mixed
     */
    public function getConfig($option = null)
    {
        return Arr::get($this->config, $option);
    }

    /**
     * 获取PDO驱动程序名称。
     *
     * @return string
     */
    public function getDriverName()
    {
        return $this->getConfig('driver');
    }

    /**
     * 获取连接使用的查询语法。
     *
     * @return \Itxiao6\Database\Query\Grammars\Grammar
     */
    public function getQueryGrammar()
    {
        return $this->queryGrammar;
    }

    /**
     * 设置连接使用的查询语法。
     *
     * @param  \Itxiao6\Database\Query\Grammars\Grammar  $grammar
     * @return void
     */
    public function setQueryGrammar(Query\Grammars\Grammar $grammar)
    {
        $this->queryGrammar = $grammar;
    }

    /**
     * 获取连接使用的模式语法。
     *
     * @return \Itxiao6\Database\Schema\Grammars\Grammar
     */
    public function getSchemaGrammar()
    {
        return $this->schemaGrammar;
    }

    /**
     * 设置连接使用的模式语法。
     *
     * @param  \Itxiao6\Database\Schema\Grammars\Grammar  $grammar
     * @return void
     */
    public function setSchemaGrammar(Schema\Grammars\Grammar $grammar)
    {
        $this->schemaGrammar = $grammar;
    }

    /**
     * 获取连接使用的查询后处理器。
     *
     * @return \Itxiao6\Database\Query\Processors\Processor
     */
    public function getPostProcessor()
    {
        return $this->postProcessor;
    }

    /**
     * 设置连接使用的查询后处理器。
     *
     * @param  \Itxiao6\Database\Query\Processors\Processor  $processor
     * @return void
     */
    public function setPostProcessor(Processor $processor)
    {
        $this->postProcessor = $processor;
    }

    /**
     * 获取连接使用的事件调度程序。
     *
     * @return \Illuminate\Contracts\Events\Dispatcher
     */
    public function getEventDispatcher()
    {
        return $this->events;
    }

    /**
     * 在连接上设置事件分派器实例。
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function setEventDispatcher(Dispatcher $events)
    {
        $this->events = $events;
    }

    /**
     * 确定一个连接是否在 "dry run".
     *
     * @return bool
     */
    public function pretending()
    {
        return $this->pretending === true;
    }

    /**
     * 获取连接查询日志。
     *
     * @return array
     */
    public function getQueryLog()
    {
        return $this->queryLog;
    }

    /**
     * 清除查询日志。
     *
     * @return void
     */
    public function flushQueryLog()
    {
        $this->queryLog = [];
    }

    /**
     * 启用连接上的查询日志。
     *
     * @return void
     */
    public function enableQueryLog()
    {
        $this->loggingQueries = true;
    }

    /**
     * 禁用连接上的查询日志。
     *
     * @return void
     */
    public function disableQueryLog()
    {
        $this->loggingQueries = false;
    }

    /**
     * 确定我们是否记录查询。
     *
     * @return bool
     */
    public function logging()
    {
        return $this->loggingQueries;
    }

    /**
     * 获取连接的数据库的名称。
     *
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->database;
    }

    /**
     * 设置连接的数据库的名称。
     *
     * @param  string  $database
     * @return string
     */
    public function setDatabaseName($database)
    {
        $this->database = $database;
    }

    /**
     * 获取连接的表前缀。
     *
     * @return string
     */
    public function getTablePrefix()
    {
        return $this->tablePrefix;
    }

    /**
     * 设置连接使用的表前缀。
     *
     * @param  string  $prefix
     * @return void
     */
    public function setTablePrefix($prefix)
    {
        $this->tablePrefix = $prefix;

        $this->getQueryGrammar()->setTablePrefix($prefix);
    }

    /**
     * 设置表前缀并返回语法。
     *
     * @param  \Itxiao6\Database\Grammar  $grammar
     * @return \Itxiao6\Database\Grammar
     */
    public function withTablePrefix(Grammar $grammar)
    {
        $grammar->setTablePrefix($this->tablePrefix);

        return $grammar;
    }

    /**
     * 注册连接解析器。
     *
     * @param  string  $driver
     * @param  \Closure  $callback
     * @return void
     */
    public static function resolverFor($driver, Closure $callback)
    {
        static::$resolvers[$driver] = $callback;
    }

    /**
     * 获取给定驱动程序的连接解析器。
     *
     * @param  string  $driver
     * @return mixed
     */
    public static function getResolver($driver)
    {
        return isset(static::$resolvers[$driver]) ?
            static::$resolvers[$driver] : null;
    }
}
<?php

namespace Itxiao6\Database\Connectors;

use PDO;
use Exception;
use Illuminate\Support\Arr;
use Doctrine\DBAL\Driver\PDOConnection;
use Itxiao6\Database\DetectsLostConnections;

class Connector
{
    use DetectsLostConnections;

    /**
     * The default PDO connection options.
     *
     * @var array
     */
    protected $options = [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES => false,
        /**
         * 是否使用了 SWOOL 的 连接池
         */
        'IS_POND' => false,
    ];

    /**
     * Create a new PDO connection.
     * @param $dsn
     * @param array $config
     * @param array $options
     * @return PDO
     * @throws Exception
     */
    public function createConnection($dsn, array $config, array $options)
    {
        list($username, $password) = [
            Arr::get($config, 'username'), Arr::get($config, 'password'),
        ];

        try {
            return $this->createPdoConnection(
                $dsn, $username, $password, $options
            );
        } catch (Exception $e) {
            return $this->tryAgainIfCausedByLostConnection(
                $e, $dsn, $username, $password, $options
            );
        }
    }

    /**
     * Create a new PDO connection instance.
     * @param $dsn
     * @param $username
     * @param $password
     * @param $options
     * @return PDOConnection|PDO|\pdoProxy
     * @throws Exception
     */
    protected function createPdoConnection($dsn, $username, $password, $options)
    {
        if (class_exists(PDOConnection::class) && ! $this->isPersistentConnection($options)) {
            return new PDOConnection($dsn, $username, $password, $options);
        }
        /**
         * 判断是否使用了SWOOOLE 的 连接池
         */
        if(isset($options['IS_POND']) && $options['IS_POND'] === true){
            if(!class_exists(\pdoProxy::class)){
                throw new Exception('请先安装 SWOOLE 的 php-cp 连接池');
            }
            unset($options['IS_POND']);
            return new \pdoProxy($dsn, $username, $password, $options);
        }
        unset($options['IS_POND']);
        return new PDO($dsn, $username, $password, $options);
    }

    /**
     * Determine if the connection is persistent.
     *
     * @param  array  $options
     * @return bool
     */
    protected function isPersistentConnection($options)
    {
        return isset($options[PDO::ATTR_PERSISTENT]) &&
               $options[PDO::ATTR_PERSISTENT];
    }

    /**
     * Handle an exception that occurred during connect execution.
     *
     * @param  \Exception  $e
     * @param  string  $dsn
     * @param  string  $username
     * @param  string  $password
     * @param  array   $options
     * @return \PDO
     *
     * @throws \Exception
     */
    protected function tryAgainIfCausedByLostConnection(Exception $e, $dsn, $username, $password, $options)
    {
        if ($this->causedByLostConnection($e)) {
            return $this->createPdoConnection($dsn, $username, $password, $options);
        }

        throw $e;
    }

    /**
     * Get the PDO options based on the configuration.
     *
     * @param  array  $config
     * @return array
     */
    public function getOptions(array $config)
    {
        $options = Arr::get($config, 'options', []);

        return array_diff_key($this->options, $options) + $options;
    }

    /**
     * Get the default PDO connection options.
     *
     * @return array
     */
    public function getDefaultOptions()
    {
        return $this->options;
    }

    /**
     * Set the default PDO connection options.
     *
     * @param  array  $options
     * @return void
     */
    public function setDefaultOptions(array $options)
    {
        $this->options = $options;
    }
}

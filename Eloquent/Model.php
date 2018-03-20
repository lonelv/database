<?php

namespace Itxiao6\Database\Eloquent;

use Exception;
use ArrayAccess;
use JsonSerializable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Contracts\Queue\QueueableEntity;
use Itxiao6\Database\Eloquent\Relations\Pivot;
use Itxiao6\Database\Query\Builder as QueryBuilder;
use Itxiao6\Database\ConnectionResolverInterface as Resolver;

/**
 * Class Model
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * 条件
 * @method \Itxiao6\Database\Query\Builder where($column, $operator = null, $value = null, $boolean = 'and')
 * @method \Itxiao6\Database\Query\Builder orWhere($column, $operator = null, $value = null)
 * @method \Itxiao6\Database\Query\Builder whereColumn($first, $operator = null, $second = null, $boolean = 'and')
 * @method \Itxiao6\Database\Query\Builder orWhereColumn($first, $operator = null, $second = null)
 * @method \Itxiao6\Database\Query\Builder whereRaw($sql, $bindings = [], $boolean = 'and')
 * @method \Itxiao6\Database\Query\Builder orWhereRaw($sql, $bindings = [])
 * @method \Itxiao6\Database\Query\Builder whereIn($column, $values, $boolean = 'and', $not = false)
 * @method \Itxiao6\Database\Query\Builder orWhereIn($column, $values)
 * @method \Itxiao6\Database\Query\Builder whereNotIn($column, $values, $boolean = 'and')
 * @method \Itxiao6\Database\Query\Builder orWhereNotIn($column, $values)
 * @method \Itxiao6\Database\Query\Builder whereNull($column, $boolean = 'and', $not = false)
 * @method \Itxiao6\Database\Query\Builder orWhereNull($column)
 * @method \Itxiao6\Database\Query\Builder whereNotNull($column, $boolean = 'and')
 * @method \Itxiao6\Database\Query\Builder whereBetween($column, array $values, $boolean = 'and', $not = false)
 * @method \Itxiao6\Database\Query\Builder orWhereBetween($column, array $values)
 * @method \Itxiao6\Database\Query\Builder whereNotBetween($column, array $values, $boolean = 'and')
 * @method \Itxiao6\Database\Query\Builder orWhereNotBetween($column, array $values)
 * @method \Itxiao6\Database\Query\Builder orWhereNotNull($column)
 * @method \Itxiao6\Database\Query\Builder whereDate($column, $operator, $value = null, $boolean = 'and')
 * @method \Itxiao6\Database\Query\Builder orWhereDate($column, $operator, $value)
 * @method \Itxiao6\Database\Query\Builder whereTime($column, $operator, $value, $boolean = 'and')
 * @method \Itxiao6\Database\Query\Builder orWhereTime($column, $operator, $value)
 * @method \Itxiao6\Database\Query\Builder whereDay($column, $operator, $value = null, $boolean = 'and')
 * @method \Itxiao6\Database\Query\Builder whereMonth($column, $operator, $value = null, $boolean = 'and')
 * @method \Itxiao6\Database\Query\Builder whereYear($column, $operator, $value = null, $boolean = 'and')
 * @method \Itxiao6\Database\Query\Builder whereNested(\Closure $callback, $boolean = 'and')
 * @method \Itxiao6\Database\Query\Builder whereExists(\Closure $callback, $boolean = 'and', $not = false)
 * @method \Itxiao6\Database\Query\Builder orWhereExists(\Closure $callback, $not = false)
 * @method \Itxiao6\Database\Query\Builder whereNotExists(\Closure $callback, $boolean = 'and')
 * @method \Itxiao6\Database\Query\Builder orWhereNotExists(\Closure $callback)
 * @method \Itxiao6\Database\Query\Builder addWhereExistsQuery(\Itxiao6\Database\Query\Builder $query, $boolean = 'and', $not = false)
 * 排序
 * @method \Itxiao6\Database\Query\Builder orderBy($column, $direction = 'asc')
 * @method \Itxiao6\Database\Query\Builder orderByDesc($column)
 * @method \Itxiao6\Database\Query\Builder orderByRaw($sql, $bindings = [])
 * 偏移
 * @method \Itxiao6\Database\Query\Builder skip($value)
 * @method \Itxiao6\Database\Query\Builder offset($value)
 * @method \Itxiao6\Database\Query\Builder take($value)
 * @method \Itxiao6\Database\Query\Builder limit($value)
 * 锁
 * @method \Itxiao6\Database\Query\Builder lock($value = true)
 * @method \Itxiao6\Database\Query\Builder lockForUpdate()
 * @method \Itxiao6\Database\Query\Builder sharedLock()
 * 调试
 * @method \Itxiao6\Database\Query\Builder toSql()
 * @method \Itxiao6\Database\Query\Builder raw($value)
 * @method \Itxiao6\Database\Query\Builder getBindings()
 * @method \Itxiao6\Database\Query\Builder table()
 * @method \Itxiao6\Database\Query\Builder getRawBindings()
 * @method \Itxiao6\Database\Query\Builder getProcessor()
 * 查询(选择)
 * @method \Itxiao6\Database\Query\Builder find($id, $columns = ['*'])
 * @method \Itxiao6\Database\Query\Builder value($column)
 * @method \Itxiao6\Database\Query\Builder get($columns = ['*'])
 * @method \Itxiao6\Database\Query\Builder pluck($column, $key = null)
 * @method \Itxiao6\Database\Query\Builder count($columns = '*')
 * @method \Itxiao6\Database\Query\Builder min($column)
 * @method \Itxiao6\Database\Query\Builder max($column)
 * @method \Itxiao6\Database\Query\Builder sum($column)
 * @method \Itxiao6\Database\Query\Builder avg($column)
 * 写入|修改|删除
 * @method \Itxiao6\Database\Query\Builder insert(array $values)
 * @method \Itxiao6\Database\Query\Builder insertGetId(array $values, $sequence = null)
 * @method \Itxiao6\Database\Query\Builder updateOrInsert(array $attributes, array $values = [])
 * 驱动
 * @method \Itxiao6\Database\Query\Builder set_cache_driver(\Itxiao6\Database\CacheInterface $object)
 * @method \Itxiao6\Database\Query\Builder set_paginate_driver(\Itxiao6\Database\PaginateInterface $object)
 * @method \Itxiao6\Database\Query\Builder remember($time)
 *
 * @package Itxiao6\Database\Eloquent
 */
abstract class Model implements ArrayAccess, Arrayable, Jsonable, JsonSerializable, QueueableEntity, UrlRoutable
{
    use Concerns\HasAttributes,
        Concerns\HasEvents,
        Concerns\HasGlobalScopes,
        Concerns\HasRelationships,
        Concerns\HasTimestamps,
        Concerns\HidesAttributes,
        Concerns\GuardsAttributes;

    /**
     * 模型的连接名称。
     *
     * @var string
     */
    protected $connection;

    /**
     * 与模型相关联的表。
     *
     * @var string
     */
    protected $table;

    /**
     * 模型的主键。
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 自动递增ID的“类型”。
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * 指示ID是否自动递增。
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [];

    /**
     * 关系计数应该在每个查询上加载。
     *
     * @var array
     */
    protected $withCount = [];

    /**
     * 分页数返回的模型。
     *
     * @var int
     */
    protected $perPage = 15;

    /**
     * 指示模型是否存在。
     *
     * @var bool
     */
    public $exists = false;

    /**
     * 指示模型是否在当前请求生命周期内插入。
     *
     * @var bool
     */
    public $wasRecentlyCreated = false;

    /**
     * 连接解析器实例。
     *
     * @var \Itxiao6\Database\ConnectionResolverInterface
     */
    protected static $resolver;

    /**
     * 事件调度器实例。
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected static $dispatcher;

    /**
     * 引导模型的阵列。
     *
     * @var array
     */
    protected static $booted = [];

    /**
     * 模型上的全局范围阵列。
     *
     * @var array
     */
    protected static $globalScopes = [];

    /**
     * 数据创建时间 列名
     *
     * @var string
     */
    const CREATED_AT = 'created_at';

    /**
     * 数据最后更新时间 列名
     *
     * @var string
     */
    const UPDATED_AT = 'updated_at';

    /**
     * 创建一个新的 Eloquent 模型实例。
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->bootIfNotBooted();

        $this->syncOriginal();

        $this->fill($attributes);
    }

    /**
     * 检查模型是否需要启动，如果是，请执行此操作。
     *
     * @return void
     */
    protected function bootIfNotBooted()
    {
        if (! isset(static::$booted[static::class])) {
            static::$booted[static::class] = true;

            $this->fireModelEvent('booting', false);

            static::boot();

            $this->fireModelEvent('booted', false);
        }
    }

    /**
     * 模型的“启动”方法。
     *
     * @return void
     */
    protected static function boot()
    {
        static::bootTraits();
    }

    /**
     * 引导模型上的所有可引导特征。
     *
     * @return void
     */
    protected static function bootTraits()
    {
        $class = static::class;

        foreach (class_uses_recursive($class) as $trait) {
            if (method_exists($class, $method = 'boot'.class_basename($trait))) {
                forward_static_call([$class, $method]);
            }
        }
    }

    /**
     * 清除引导模型的列表，以便它们重新启动。
     *
     * @return void
     */
    public static function clearBootedModels()
    {
        static::$booted = [];

        static::$globalScopes = [];
    }

    /**
     * 使用一系列属性填充模型。
     *
     * @param  array  $attributes
     * @return $this
     *
     * @throws \Itxiao6\Database\Eloquent\MassAssignmentException
     */
    public function fill(array $attributes)
    {
        $totallyGuarded = $this->totallyGuarded();

        foreach ($this->fillableFromArray($attributes) as $key => $value) {
            $key = $this->removeTableFromKey($key);

            // The developers may choose to place some attributes in the "fillable" array
            // which means only those attributes may be set through mass assignment to
            // the model, and all others will just get ignored for security reasons.
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            } elseif ($totallyGuarded) {
                throw new MassAssignmentException($key);
            }
        }

        return $this;
    }

    /**
     * Fill the model with an array of attributes. Force mass assignment.
     * 使用一系列属性填充模型。 强制质量分配
     *
     * @param  array  $attributes
     * @return $this
     */
    public function forceFill(array $attributes)
    {
        return static::unguarded(function () use ($attributes) {
            return $this->fill($attributes);
        });
    }

    /**
     * Remove the table name from a given key.
     * 从给定的键中删除表名。
     *
     * @param  string  $key
     * @return string
     */
    protected function removeTableFromKey($key)
    {
        return Str::contains($key, '.') ? last(explode('.', $key)) : $key;
    }

    /**
     * 创建给定模型的新实例。
     *
     * @param  array  $attributes
     * @param  bool  $exists
     * @return static
     */
    public function newInstance($attributes = [], $exists = false)
    {
        // This method just provides a convenient way for us to generate fresh model
        // instances of this current model. It is particularly useful during the
        // hydration of new objects via the Eloquent query builder instances.
        //这个方法只是提供一种方便的方法来生成新的模型
        //这个当前模型的实例。 在此期间特别有用
        //通过Eloquent查询构建器实例对新对象进行水化。
        $model = new static((array) $attributes);

        $model->exists = $exists;

        $model->setConnection(
            $this->getConnectionName()
        );

        return $model;
    }

    /**
     * 创建一个现有的新模型实例。
     *
     * @param  array  $attributes
     * @param  string|null  $connection
     * @return static
     */
    public function newFromBuilder($attributes = [], $connection = null)
    {
        $model = $this->newInstance([], true);

        $model->setRawAttributes((array) $attributes, true);

        $model->setConnection($connection ?: $this->getConnectionName());

        return $model;
    }

    /**
     * 在给定的连接上开始查询模型。
     *
     * @param  string|null  $connection
     * @return \Itxiao6\Database\Eloquent\Builder
     */
    public static function on($connection = null)
    {
        // First we will just create a fresh instance of this model, and then we can
        // set the connection on the model so that it is be used for the queries
        // we execute, as well as being set on each relationship we retrieve.
        //首先，我们将创建一个这个模型的新实例，然后我们可以
        //设置模型上的连接，以便它被用于查询
        //我们执行，以及在我们检索的每个关系上设置。
        $instance = new static;

        $instance->setConnection($connection);

        return $instance->newQuery();
    }

    /**
     * Begin querying the model on the write connection.
     * 开始在写连接上查询模型。
     *
     * @return \Itxiao6\Database\Query\Builder
     */
    public static function onWriteConnection()
    {
        $instance = new static;

        return $instance->newQuery()->useWritePdo();
    }

    /**
     * Get all of the models from the database.
     * 从数据库获取所有的模型。
     *
     * @param  array|mixed  $columns
     * @return \Itxiao6\Database\Eloquent\Collection|static[]
     */
    public static function all($columns = ['*'])
    {
        return (new static)->newQuery()->get(
            is_array($columns) ? $columns : func_get_args()
        );
    }

    /**
     * Begin querying a model with eager loading.
     * 装载 一个模型 开始 查询
     *
     * @param  array|string  $relations
     * @return \Itxiao6\Database\Eloquent\Builder|static
     */
    public static function with($relations)
    {
        return (new static)->newQuery()->with(
            is_string($relations) ? func_get_args() : $relations
        );
    }

    /**
     * Eager load relations on the model.
     * Eager 在 模型 加载 关系.
     *
     * @param  array|string  $relations
     * @return $this
     */
    public function load($relations)
    {
        $query = $this->newQuery()->with(
            is_string($relations) ? func_get_args() : $relations
        );

        $query->eagerLoadRelations([$this]);

        return $this;
    }

    /**
     * Increment a column's value by a given amount.
     * 列的值递增
     *
     * @param  string  $column
     * @param  int  $amount
     * @param  array  $extra
     * @return int
     */
    protected function increment($column, $amount = 1, array $extra = [])
    {
        return $this->incrementOrDecrement($column, $amount, $extra, 'increment');
    }

    /**
     * Decrement a column's value by a given amount.
     * 列的值递减
     *
     * @param  string  $column
     * @param  int  $amount
     * @param  array  $extra
     * @return int
     */
    protected function decrement($column, $amount = 1, array $extra = [])
    {
        return $this->incrementOrDecrement($column, $amount, $extra, 'decrement');
    }

    /**
     * Run the increment or decrement method on the model.
     * 在模型上 运行 递减
     * @param  string  $column
     * @param  int  $amount
     * @param  array  $extra
     * @param  string  $method
     * @return int
     */
    protected function incrementOrDecrement($column, $amount, $extra, $method)
    {
        $query = $this->newQuery();

        if (! $this->exists) {
            return $query->{$method}($column, $amount, $extra);
        }

        $this->incrementOrDecrementAttributeValue($column, $amount, $extra, $method);

        return $query->where(
            $this->getKeyName(), $this->getKey()
        )->{$method}($column, $amount, $extra);
    }

    /**
     * 增加底层属性值并与原始文件进行同步。
     *
     * @param  string  $column
     * @param  int  $amount
     * @param  array  $extra
     * @param  string  $method
     * @return void
     */
    protected function incrementOrDecrementAttributeValue($column, $amount, $extra, $method)
    {
        $this->{$column} = $this->{$column} + ($method == 'increment' ? $amount : $amount * -1);

        $this->forceFill($extra);

        $this->syncOriginalAttribute($column);
    }

    /**
     * 更新数据库中的模型。
     *
     * @param  array  $attributes
     * @param  array  $options
     * @return bool
     */
    public function update(array $attributes = [], array $options = [])
    {
        if (! $this->exists) {
            return false;
        }

        return $this->fill($attributes)->save($options);
    }

    /**
     * 保存模型及其所有关系。
     *
     * @return bool
     */
    public function push()
    {
        if (! $this->save()) {
            return false;
        }

        // To sync all of the relationships to the database, we will simply spin through
        // the relationships and save each model via this "push" method, which allows
        // us to recurse into all of these nested relations for the model instance.
        //要将所有关系同步到数据库，我们将简单地转过来
        //关系，并通过这种“推”方法保存每个模型，这允许
        //我们递归到模型实例的所有这些嵌套关系中。
        foreach ($this->relations as $models) {
            $models = $models instanceof Collection
                        ? $models->all() : [$models];

            foreach (array_filter($models) as $model) {
                if (! $model->push()) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * 将模型保存到数据库。
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = [])
    {
        $query = $this->newQueryWithoutScopes();

        // If the "saving" event returns false we'll bail out of the save and return
        // false, indicating that the save failed. This provides a chance for any
        // listeners to cancel save operations if validations fail or whatever.
        //如果“save”事件返回false，我们将保存并返回
        // false，表示保存失败。 这提供了任何机会
        //监听器取消保存操作，如果验证失败或任何。
        if ($this->fireModelEvent('saving') === false) {
            return false;
        }

        // If the model already exists in the database we can just update our record
        // that is already in this database using the current IDs in this "where"
        // clause to only update this model. Otherwise, we'll just insert them.
        //如果模型已经存在于数据库中，我们可以更新我们的记录
        //这个数据库中已经存在这个“where”中的当前ID
        //子句仅更新此模型。 否则，我们只是插入它们。
        if ($this->exists) {
            $saved = $this->isDirty() ?
                        $this->performUpdate($query) : true;
        }

        // If the model is brand new, we'll insert it into our database and set the
        // ID attribute on the model to the value of the newly inserted row's ID
        // which is typically an auto-increment value managed by the database.
        //如果模型是全新的，我们将其插入到我们的数据库中并设置
        //模型上的ID属性为新插入的行的ID的值
        //通常是由数据库管理的自动增量值。
        else {
            $saved = $this->performInsert($query);
        }

        // If the model is successfully saved, we need to do a few more things once
        // that is done. We will call the "saved" method here to run any actions
        // we need to happen after a model gets successfully saved right here.
        //如果模型成功保存，我们需要再做一些更多的事情
        //完成了 我们将在这里调用“保存”方法来运行任何操作
        //我们需要在模型在这里成功保存之后发生。
        if ($saved) {
            $this->finishSave($options);
        }

        return $saved;
    }

    /**
     * 使用事务将模型保存到数据库。
     *
     * @param  array  $options
     * @return bool
     *
     * @throws \Throwable
     */
    public function saveOrFail(array $options = [])
    {
        return $this->getConnection()->transaction(function () use ($options) {
            return $this->save($options);
        });
    }

    /**
     * Perform any actions that are necessary after the model is saved.
     * 在保存模型后执行必要的任何操作。
     *
     * @param  array  $options
     * @return void
     */
    protected function finishSave(array $options)
    {
        $this->fireModelEvent('saved', false);

        $this->syncOriginal();

        if (Arr::get($options, 'touch', true)) {
            $this->touchOwners();
        }
    }

    /**
     * Perform a model update operation.
     * 执行模型更新操作。
     *
     * @param  \Itxiao6\Database\Eloquent\Builder  $query
     * @return bool
     */
    protected function performUpdate(Builder $query)
    {
        // If the updating event returns false, we will cancel the update operation so
        // developers can hook Validation systems into their models and cancel this
        // operation if the model does not pass validation. Otherwise, we update.
        if ($this->fireModelEvent('updating') === false) {
            return false;
        }

        // First we need to create a fresh query instance and touch the creation and
        // update timestamp on the model which are maintained by us for developer
        // convenience. Then we will just continue saving the model instances.
        if ($this->usesTimestamps()) {
            $this->updateTimestamps();
        }

        // Once we have run the update operation, we will fire the "updated" event for
        // this model instance. This will allow developers to hook into these after
        // models are updated, giving them a chance to do any special processing.
        $dirty = $this->getDirty();

        if (count($dirty) > 0) {
            $this->setKeysForSaveQuery($query)->update($dirty);

            $this->fireModelEvent('updated', false);
        }

        return true;
    }

    /**
     * 设置保存更新查询的键。
     *
     * @param  \Itxiao6\Database\Eloquent\Builder  $query
     * @return \Itxiao6\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery(Builder $query)
    {
        $query->where($this->getKeyName(), '=', $this->getKeyForSaveQuery());

        return $query;
    }

    /**
     * 获取保存查询的主键值。
     *
     * @return mixed
     */
    protected function getKeyForSaveQuery()
    {
        return isset($this->original[$this->getKeyName()])
                        ? $this->original[$this->getKeyName()]
                        : $this->getAttribute($this->getKeyName());
    }

    /**
     * 执行模型插入操作。
     *
     * @param  \Itxiao6\Database\Eloquent\Builder  $query
     * @return bool
     */
    protected function performInsert(Builder $query)
    {
        if ($this->fireModelEvent('creating') === false) {
            return false;
        }

        // First we'll need to create a fresh query instance and touch the creation and
        // update timestamps on this model, which are maintained by us for developer
        // convenience. After, we will just continue saving these model instances.
        if ($this->usesTimestamps()) {
            $this->updateTimestamps();
        }

        // If the model has an incrementing key, we can use the "insertGetId" method on
        // the query builder, which will give us back the final inserted ID for this
        // table from the database. Not all tables have to be incrementing though.
        $attributes = $this->attributes;

        if ($this->getIncrementing()) {
            $this->insertAndSetId($query, $attributes);
        }

        // If the table isn't incrementing we'll simply insert these attributes as they
        // are. These attribute arrays must contain an "id" column previously placed
        // there by the developer as the manually determined key for these models.
        else {
            if (empty($attributes)) {
                return true;
            }

            $query->insert($attributes);
        }

        // We will go ahead and set the exists property to true, so that it is set when
        // the created event is fired, just in case the developer tries to update it
        // during the event. This will allow them to do so and run an update here.
        $this->exists = true;

        $this->wasRecentlyCreated = true;

        $this->fireModelEvent('created', false);

        return true;
    }

    /**
     * 插入给定的属性并设置模型上的ID。
     *
     * @param  \Itxiao6\Database\Eloquent\Builder  $query
     * @param  array  $attributes
     * @return void
     */
    protected function insertAndSetId(Builder $query, $attributes)
    {
        $id = $query->insertGetId($attributes, $keyName = $this->getKeyName());

        $this->setAttribute($keyName, $id);
    }

    /**
     * 销毁给定ID的模型。
     *
     * @param  array|int  $ids
     * @return int
     */
    public static function destroy($ids)
    {
        // We'll initialize a count here so we will return the total number of deletes
        // for the operation. The developers can then check this number as a boolean
        // type value or get this total count of records deleted for logging, etc.
        $count = 0;

        $ids = is_array($ids) ? $ids : func_get_args();

        // We will actually pull the models from the database table and call delete on
        // each of them individually so that their events get fired properly with a
        // correct set of attributes in case the developers wants to check these.
        $key = with($instance = new static)->getKeyName();

        foreach ($instance->whereIn($key, $ids)->get() as $model) {
            if ($model->delete()) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * 从数据库中删除模型。
     *
     * @return bool|null
     *
     * @throws \Exception
     */
    public function delete()
    {
        if (is_null($this->getKeyName())) {
            throw new Exception('No primary key defined on model.');
        }

        // If the model doesn't exist, there is nothing to delete so we'll just return
        // immediately and not do anything else. Otherwise, we will continue with a
        // deletion process on the model, firing the proper events, and so forth.
        if (! $this->exists) {
            return;
        }

        if ($this->fireModelEvent('deleting') === false) {
            return false;
        }

        // Here, we'll touch the owning models, verifying these timestamps get updated
        // for the models. This will allow any caching to get broken on the parents
        // by the timestamp. Then we will go ahead and delete the model instance.
        $this->touchOwners();

        $this->performDeleteOnModel();

        $this->exists = false;

        // Once the model has been deleted, we will fire off the deleted event so that
        // the developers may hook into post-delete operations. We will then return
        // a boolean true as the delete is presumably successful on the database.
        $this->fireModelEvent('deleted', false);

        return true;
    }

    /**
     * 在软删除的模型上强制执行硬删除。
     *
     * 当trait丢失时，此方法可以保护开发人员不要运行forceDelete。
     *
     * @return bool|null
     */
    public function forceDelete()
    {
        return $this->delete();
    }

    /**
     * 在此模型实例上执行实际的删除查询。
     *
     * @return void
     */
    protected function performDeleteOnModel()
    {
        $this->setKeysForSaveQuery($this->newQueryWithoutScopes())->delete();
    }

    /**
     * 开始查询模型。
     *
     * @return \Itxiao6\Database\Eloquent\Builder
     */
    public static function query()
    {
        return (new static)->newQuery();
    }

    /**
     * 获取模型表的新查询构建器。
     *
     * @return \Itxiao6\Database\Eloquent\Builder
     */
    public function newQuery()
    {
        $builder = $this->newQueryWithoutScopes();

        foreach ($this->getGlobalScopes() as $identifier => $scope) {
            $builder->withGlobalScope($identifier, $scope);
        }

        return $builder;
    }

    /**
     * 获取一个没有任何全局范围的新查询构建器。
     *
     * @return \Itxiao6\Database\Eloquent\Builder|static
     */
    public function newQueryWithoutScopes()
    {
        $builder = $this->newEloquentBuilder($this->newBaseQueryBuilder());

        // Once we have the query builders, we will set the model instances so the
        // builder can easily access any information it may need from the model
        // while it is constructing and executing various queries against it.
        return $builder->setModel($this)
                    ->with($this->with)
                    ->withCount($this->withCount);
    }

    /**
     * 获取一个没有给定范围的新查询实例。
     *
     * @param  \Itxiao6\Database\Eloquent\Scope|string  $scope
     * @return \Itxiao6\Database\Eloquent\Builder
     */
    public function newQueryWithoutScope($scope)
    {
        $builder = $this->newQuery();

        return $builder->withoutGlobalScope($scope);
    }

    /**
     * 为该模型创建一个新的Eloquent查询构建器。
     *
     * @param  \Itxiao6\Database\Query\Builder  $query
     * @return \Itxiao6\Database\Eloquent\Builder|static
     */
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }

    /**
     * 为连接获取新的查询构建器实例。
     *
     * @return \Itxiao6\Database\Query\Builder
     */
    protected function newBaseQueryBuilder()
    {
        $connection = $this->getConnection();

        return new QueryBuilder(
            $connection, $connection->getQueryGrammar(), $connection->getPostProcessor()
        );
    }

    /**
     * 创建一个新的雄辩集合实例。
     *
     * @param  array  $models
     * @return \Itxiao6\Database\Eloquent\Collection
     */
    public function newCollection(array $models = [])
    {
        return new Collection($models);
    }

    /**
     * 创建一个新的数据库模型实例。
     *
     * @param  \Itxiao6\Database\Eloquent\Model  $parent
     * @param  array  $attributes
     * @param  string  $table
     * @param  bool  $exists
     * @param  string|null  $using
     * @return \Itxiao6\Database\Eloquent\Relations\Pivot
     */
    public function newPivot(Model $parent, array $attributes, $table, $exists, $using = null)
    {
        return $using ? $using::fromRawAttributes($parent, $attributes, $table, $exists)
                      : new Pivot($parent, $attributes, $table, $exists);
    }

    /**
     * 将模型实例转换为数组。
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge($this->attributesToArray(), $this->relationsToArray());
    }

    /**
     * 将模型实例转换为JSON。
     *
     * @param  int  $options
     * @return string
     *
     * @throws \Itxiao6\Database\Eloquent\JsonEncodingException
     */
    public function toJson($options = 0)
    {
        $json = json_encode($this->jsonSerialize(), $options);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw JsonEncodingException::forModel($this, json_last_error_msg());
        }

        return $json;
    }

    /**
     * 将对象转换成JSON可序列化。
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * 从数据库重新加载一个新的模型实例。
     *
     * @param  array|string  $with
     * @return static|null
     */
    public function fresh($with = [])
    {
        if (! $this->exists) {
            return;
        }

        return static::newQueryWithoutScopes()
                        ->with(is_string($with) ? func_get_args() : $with)
                        ->where($this->getKeyName(), $this->getKey())
                        ->first();
    }

    /**
     * 从数据库中重新加载具有新属性的当前模型实例。
     *
     * @return void
     */
    public function refresh()
    {
        if (! $this->exists) {
            return;
        }

        $this->load(array_keys($this->relations));

        $this->setRawAttributes(static::findOrFail($this->getKey())->attributes);
    }

    /**
     * 将模型克隆到一个新的，不存在的实例中。
     *
     * @param  array|null  $except
     * @return \Itxiao6\Database\Eloquent\Model
     */
    public function replicate(array $except = null)
    {
        $defaults = [
            $this->getKeyName(),
            $this->getCreatedAtColumn(),
            $this->getUpdatedAtColumn(),
        ];

        $attributes = Arr::except(
            $this->attributes, $except ? array_unique(array_merge($except, $defaults)) : $defaults
        );

        return tap(new static, function ($instance) use ($attributes) {
            $instance->setRawAttributes($attributes);

            $instance->setRelations($this->relations);
        });
    }

    /**
     * Determine if two models have the same ID and belong to the same table.
     *
     * @param  \Itxiao6\Database\Eloquent\Model  $model
     * @return bool
     */
    public function is(Model $model)
    {
        return $this->getKey() === $model->getKey() &&
               $this->getTable() === $model->getTable() &&
               $this->getConnectionName() === $model->getConnectionName();
    }

    /**
     * Get the database connection for the model.
     *
     * @return \Itxiao6\Database\Connection
     */
    public function getConnection()
    {
        return static::resolveConnection($this->getConnectionName());
    }

    /**
     * Get the current connection name for the model.
     *
     * @return string
     */
    public function getConnectionName()
    {
        return $this->connection;
    }

    /**
     * Set the connection associated with the model.
     *
     * @param  string  $name
     * @return $this
     */
    public function setConnection($name)
    {
        $this->connection = $name;

        return $this;
    }

    /**
     * Resolve a connection instance.
     *
     * @param  string|null  $connection
     * @return \Itxiao6\Database\Connection
     */
    public static function resolveConnection($connection = null)
    {
        return static::$resolver->connection($connection);
    }

    /**
     * Get the connection resolver instance.
     *
     * @return \Itxiao6\Database\ConnectionResolverInterface
     */
    public static function getConnectionResolver()
    {
        return static::$resolver;
    }

    /**
     * Set the connection resolver instance.
     *
     * @param  \Itxiao6\Database\ConnectionResolverInterface  $resolver
     * @return void
     */
    public static function setConnectionResolver(Resolver $resolver)
    {
        static::$resolver = $resolver;
    }

    /**
     * Unset the connection resolver for models.
     *
     * @return void
     */
    public static function unsetConnectionResolver()
    {
        static::$resolver = null;
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        if (! isset($this->table)) {
            return str_replace('\\', '', Str::snake(Str::plural(class_basename($this))));
        }

        return $this->table;
    }

    /**
     * Set the table associated with the model.
     *
     * @param  string  $table
     * @return $this
     */
    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Get the primary key for the model.
     *
     * @return string
     */
    public function getKeyName()
    {
        return $this->primaryKey;
    }

    /**
     * Set the primary key for the model.
     *
     * @param  string  $key
     * @return $this
     */
    public function setKeyName($key)
    {
        $this->primaryKey = $key;

        return $this;
    }

    /**
     * Get the table qualified key name.
     *
     * @return string
     */
    public function getQualifiedKeyName()
    {
        return $this->getTable().'.'.$this->getKeyName();
    }

    /**
     * Get the auto-incrementing key type.
     *
     * @return string
     */
    public function getKeyType()
    {
        return $this->keyType;
    }

    /**
     * Set the data type for the primary key.
     *
     * @param  string  $type
     * @return $this
     */
    public function setKeyType($type)
    {
        $this->keyType = $type;

        return $this;
    }

    /**
     * Get the value indicating whether the IDs are incrementing.
     *
     * @return bool
     */
    public function getIncrementing()
    {
        return $this->incrementing;
    }

    /**
     * Set whether IDs are incrementing.
     *
     * @param  bool  $value
     * @return $this
     */
    public function setIncrementing($value)
    {
        $this->incrementing = $value;

        return $this;
    }

    /**
     * Get the value of the model's primary key.
     *
     * @return mixed
     */
    public function getKey()
    {
        return $this->getAttribute($this->getKeyName());
    }

    /**
     * Get the queueable identity for the entity.
     *
     * @return mixed
     */
    public function getQueueableId()
    {
        return $this->getKey();
    }

    /**
     * Get the value of the model's route key.
     *
     * @return mixed
     */
    public function getRouteKey()
    {
        return $this->getAttribute($this->getRouteKeyName());
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return $this->getKeyName();
    }

    /**
     * Get the default foreign key name for the model.
     *
     * @return string
     */
    public function getForeignKey()
    {
        return Str::snake(class_basename($this)).'_'.$this->primaryKey;
    }

    /**
     * Get the number of models to return per page.
     *
     * @return int
     */
    public function getPerPage()
    {
        return $this->perPage;
    }

    /**
     * Set the number of models to return per page.
     *
     * @param  int  $perPage
     * @return $this
     */
    public function setPerPage($perPage)
    {
        $this->perPage = $perPage;

        return $this;
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Determine if the given attribute exists.
     *
     * @param  mixed  $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    /**
     * Get the value for a given offset.
     *
     * @param  mixed  $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    /**
     * Set the value for a given offset.
     *
     * @param  mixed  $offset
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    /**
     * Unset the value for a given offset.
     *
     * @param  mixed  $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->$offset);
    }

    /**
     * Determine if an attribute or relation exists on the model.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        return ! is_null($this->getAttribute($key));
    }

    /**
     * Unset an attribute on the model.
     *
     * @param  string  $key
     * @return void
     */
    public function __unset($key)
    {
        unset($this->attributes[$key], $this->relations[$key]);
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (in_array($method, ['increment', 'decrement'])) {
            return $this->$method(...$parameters);
        }

        return $this->newQuery()->$method(...$parameters);
    }

    /**
     * Handle dynamic static method calls into the method.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        return (new static)->$method(...$parameters);
    }

    /**
     * Convert the model to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * When a model is being unserialized, check if it needs to be booted.
     *
     * @return void
     */
    public function __wakeup()
    {
        $this->bootIfNotBooted();
    }
}

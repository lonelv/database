<?php

namespace Itxiao6\Database\Eloquent;

interface Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Itxiao6\Database\Eloquent\Builder  $builder
     * @param  \Itxiao6\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model);
}

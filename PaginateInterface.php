<?php
namespace Itxiao6\Database;

/**
 * 数据分页接口
 * Interface PaginateInterface
 * @package Itxiao6\Database
 */
interface PaginateInterface
{
    public function paginate($query_builder,$perPage = 15, $columns = ['*'], $pageName = 'page', $page = null);
}
<?php
namespace App\Services;

use App\Models\Application;
use App\Models\AppVirtualTable;

class AppCodeDatabase
{
    protected $application;
    protected $tables;

    public function __construct(Application $application, AppVirtualTableService $tables = null)
    {
        $this->application = $application;
        $this->tables = $tables ?: new AppVirtualTableService();
    }

    public function tables()
    {
        return $this->tables->listTablesForApplication($this->application)
            ->map(function (AppVirtualTable $table) {
                return $this->tables->serializeTable($table);
            })
            ->values()
            ->all();
    }

    public function schema($table)
    {
        return $this->tables->tableSchema($this->findTable($table));
    }

    public function select($table, array $where = array(), array $options = array())
    {
        $virtualTable = $this->findTable($table);
        $rows = $this->tables->selectRecords($virtualTable, $where, $options);

        return $rows->map(function ($row) use ($virtualTable) {
            return $this->tables->rowToLogicalArray($virtualTable, $row);
        })->values()->all();
    }

    public function first($table, array $where = array(), array $options = array())
    {
        $options['limit'] = 1;
        $rows = $this->select($table, $where, $options);
        return count($rows) ? $rows[0] : null;
    }

    public function insert($table, array $data)
    {
        $virtualTable = $this->findTable($table);
        $row = $this->tables->createRecord($virtualTable, $data);
        return $this->tables->rowToLogicalArray($virtualTable, $row);
    }

    public function update($table, $id, array $data)
    {
        $virtualTable = $this->findTable($table);
        $row = $this->tables->updateRecord($virtualTable, $id, $data);
        return $this->tables->rowToLogicalArray($virtualTable, $row);
    }

    public function delete($table, $id)
    {
        return (int) $this->tables->deleteRecord($this->findTable($table), $id);
    }

    public function query($sql, array $bindings = array(), $limit = 100)
    {
        $rows = $this->tables->executeReadOnlySql($this->application, $sql, $bindings, $limit);

        return array_map(function ($row) {
            return (array) $row;
        }, $rows);
    }

    public function tableSqlName($table)
    {
        return $this->findTable($table)->slug;
    }

    public function fieldSqlName($table, $field)
    {
        $virtualTable = $this->findTable($table);
        $slug = $this->tables->normalizeInputSlug($field);

        foreach ($virtualTable->fields()->where('status', 1)->get() as $item) {
            if ($item->slug === $slug) {
                return $item->physical_name;
            }
        }

        throw new \InvalidArgumentException('未知字段 [' . $field . ']');
    }

    protected function findTable($table)
    {
        return $this->tables->findTableForApplication($this->application, $table);
    }
}

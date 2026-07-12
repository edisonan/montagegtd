<?php
namespace App\Services;

use App\Models\Application;
use App\Models\AppVirtualTable;
use App\Models\AppVirtualTableField;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class AppVirtualTableService
{
    const MAX_SQL_LIMIT = 500;

    public function createTable(Application $application, array $data)
    {
        $slug = $this->normalizeSlug($data['slug']);

        $exists = AppVirtualTable::query()
            ->where('app_id', $application->id)
            ->where('slug', $slug)
            ->exists();

        if ($exists) {
            throw new InvalidArgumentException('虚拟表标识已存在');
        }

        $virtualTable = AppVirtualTable::create(array(
            'app_id' => $application->id,
            'name' => $data['name'],
            'slug' => $slug,
            'description' => isset($data['description']) ? $data['description'] : null,
            'status' => isset($data['status']) ? (int) $data['status'] : 1,
        ));

        $physicalTable = $this->buildPhysicalTableName($virtualTable);
        $virtualTable->physical_table = $physicalTable;
        $virtualTable->save();

        $this->ensurePhysicalTable($virtualTable);

        return $virtualTable->fresh();
    }

    public function createField(AppVirtualTable $virtualTable, array $data)
    {
        $slug = $this->normalizeSlug($data['slug']);
        $physicalName = $this->buildPhysicalFieldName($slug);
        $type = $this->normalizeType($data['type']);

        $exists = AppVirtualTableField::query()
            ->where('virtual_table_id', $virtualTable->id)
            ->where(function ($query) use ($slug, $physicalName) {
                $query->where('slug', $slug)
                    ->orWhere('physical_name', $physicalName);
            })
            ->exists();

        if ($exists) {
            throw new InvalidArgumentException('字段标识已存在');
        }

        $attributes = array(
            'virtual_table_id' => $virtualTable->id,
            'name' => $data['name'],
            'slug' => $slug,
            'physical_name' => $physicalName,
            'type' => $type,
            'length' => isset($data['length']) && $data['length'] !== '' ? (int) $data['length'] : null,
            'nullable' => isset($data['nullable']) ? (int) $data['nullable'] : 1,
            'default_enabled' => isset($data['default_enabled']) ? (int) $data['default_enabled'] : 0,
            'default_value' => isset($data['default_value']) ? $data['default_value'] : null,
            'indexed' => isset($data['indexed']) ? (int) $data['indexed'] : 0,
            'description' => isset($data['description']) ? $data['description'] : null,
            'sort_order' => isset($data['sort_order']) ? (int) $data['sort_order'] : 0,
            'status' => isset($data['status']) ? (int) $data['status'] : 1,
        );

        $fieldForSchema = new AppVirtualTableField($attributes);
        $this->ensurePhysicalTable($virtualTable);
        $this->ensurePhysicalField($virtualTable, $fieldForSchema);

        $field = AppVirtualTableField::create($attributes);

        return $field->fresh();
    }

    public function findTableForApplication(Application $application, $table)
    {
        $query = AppVirtualTable::query()
            ->where('app_id', $application->id)
            ->where('status', 1);

        if (is_numeric($table)) {
            return $query->where('id', (int) $table)->firstOrFail();
        }

        $slug = $this->normalizeSlug($table);
        return $query->where('slug', $slug)->firstOrFail();
    }

    public function listTablesForApplication(Application $application)
    {
        return AppVirtualTable::query()
            ->with('fields')
            ->where('app_id', $application->id)
            ->orderBy('id', 'desc')
            ->get();
    }

    public function listRecords(AppVirtualTable $virtualTable, $page, $perPage)
    {
        $this->assertPhysicalTableExists($virtualTable);

        $page = max(1, (int) $page);
        $perPage = min(100, max(1, (int) $perPage));
        $offset = ($page - 1) * $perPage;
        $query = DB::table($virtualTable->physical_table);
        $total = (clone $query)->count();
        $rows = $query->orderBy('id', 'desc')->offset($offset)->limit($perPage)->get();

        return array(
            'total' => (int) $total,
            'page' => $page,
            'per_page' => $perPage,
            'items' => $rows,
        );
    }

    public function createRecord(AppVirtualTable $virtualTable, array $data)
    {
        $this->assertPhysicalTableExists($virtualTable);
        $payload = $this->buildRecordPayload($virtualTable, $data, false);
        $now = date('Y-m-d H:i:s');
        $payload['created_at'] = $now;
        $payload['updated_at'] = $now;

        $id = DB::table($virtualTable->physical_table)->insertGetId($payload);

        return DB::table($virtualTable->physical_table)->where('id', $id)->first();
    }

    public function updateRecord(AppVirtualTable $virtualTable, $recordId, array $data)
    {
        $this->assertPhysicalTableExists($virtualTable);
        $payload = $this->buildRecordPayload($virtualTable, $data, true);
        $payload['updated_at'] = date('Y-m-d H:i:s');

        DB::table($virtualTable->physical_table)
            ->where('id', (int) $recordId)
            ->update($payload);

        return DB::table($virtualTable->physical_table)->where('id', (int) $recordId)->first();
    }

    public function deleteRecord(AppVirtualTable $virtualTable, $recordId)
    {
        $this->assertPhysicalTableExists($virtualTable);

        return DB::table($virtualTable->physical_table)
            ->where('id', (int) $recordId)
            ->delete();
    }

    public function selectRecords(AppVirtualTable $virtualTable, array $where, array $options = array())
    {
        $this->assertPhysicalTableExists($virtualTable);

        $query = DB::table($virtualTable->physical_table);
        $fields = $this->activeFieldsBySlug($virtualTable);

        foreach ($where as $slug => $value) {
            if ($slug === 'id') {
                $query->where('id', (int) $value);
                continue;
            }

            if (!isset($fields[$slug])) {
                throw new InvalidArgumentException('未知字段 [' . $slug . ']');
            }

            $query->where($fields[$slug]->physical_name, $this->castValueByType($fields[$slug], $value));
        }

        $orderBy = isset($options['order_by']) ? (string) $options['order_by'] : 'id';
        $orderDir = isset($options['order_dir']) && strtolower((string) $options['order_dir']) === 'asc' ? 'asc' : 'desc';
        $limit = isset($options['limit']) ? (int) $options['limit'] : 100;
        $limit = min(self::MAX_SQL_LIMIT, max(1, $limit));

        if ($orderBy === 'id') {
            $query->orderBy('id', $orderDir);
        } else {
            if (!isset($fields[$orderBy])) {
                throw new InvalidArgumentException('未知排序字段 [' . $orderBy . ']');
            }
            $query->orderBy($fields[$orderBy]->physical_name, $orderDir);
        }

        return $query->limit($limit)->get();
    }

    public function executeReadOnlySql(Application $application, $sql, array $bindings = array(), $limit = 100)
    {
        $compiledSql = $this->compileReadOnlySql($application, $sql, $limit);
        return DB::select($compiledSql, $bindings);
    }

    public function ensurePhysicalTable(AppVirtualTable $virtualTable)
    {
        $tableName = $virtualTable->physical_table ?: $this->buildPhysicalTableName($virtualTable);

        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->increments('id');
                $table->timestamps();
            });
        }
    }

    public function ensurePhysicalField(AppVirtualTable $virtualTable, AppVirtualTableField $field)
    {
        $tableName = $virtualTable->physical_table;
        $columnName = $field->physical_name;

        if (!$tableName || !Schema::hasTable($tableName) || Schema::hasColumn($tableName, $columnName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($field, $columnName) {
            $column = $this->addColumnToBlueprint($table, $field, $columnName);

            if ((int) $field->nullable === 1) {
                $column->nullable();
            }

            if ((int) $field->default_enabled === 1) {
                $column->default($this->castValueByType($field, $field->default_value));
            }
        });

        if ((int) $field->indexed === 1) {
            Schema::table($tableName, function (Blueprint $table) use ($columnName) {
                $table->index($columnName);
            });
        }
    }

    public function serializeTable(AppVirtualTable $virtualTable)
    {
        $data = array(
            'id' => $virtualTable->id,
            'app_id' => (int) $virtualTable->app_id,
            'name' => $virtualTable->name,
            'slug' => $virtualTable->slug,
            'physical_table' => $virtualTable->physical_table,
            'description' => $virtualTable->description,
            'status' => (int) $virtualTable->status,
            'status_text' => (int) $virtualTable->status === 1 ? '启用' : '禁用',
            'fields_count' => $virtualTable->relationLoaded('fields') ? $virtualTable->fields->count() : $virtualTable->fields()->count(),
            'updated_at' => optional($virtualTable->updated_at)->toDateTimeString(),
        );

        if ($virtualTable->relationLoaded('fields')) {
            $data['fields'] = $virtualTable->fields->map(function (AppVirtualTableField $field) {
                return $this->serializeField($field);
            })->values();
        }

        return $data;
    }

    public function serializeField(AppVirtualTableField $field)
    {
        return array(
            'id' => $field->id,
            'virtual_table_id' => (int) $field->virtual_table_id,
            'name' => $field->name,
            'slug' => $field->slug,
            'physical_name' => $field->physical_name,
            'type' => $field->type,
            'length' => $field->length ? (int) $field->length : null,
            'nullable' => (int) $field->nullable,
            'default_enabled' => (int) $field->default_enabled,
            'default_value' => $field->default_value,
            'indexed' => (int) $field->indexed,
            'description' => $field->description,
            'sort_order' => (int) $field->sort_order,
            'status' => (int) $field->status,
            'status_text' => (int) $field->status === 1 ? '启用' : '禁用',
        );
    }

    public function fieldTypeOptions()
    {
        return array(
            'string' => '短文本',
            'text' => '长文本',
            'integer' => '整数',
            'decimal' => '小数',
            'boolean' => '布尔',
            'date' => '日期',
            'datetime' => '日期时间',
            'json' => 'JSON',
        );
    }

    public function tableSchema(AppVirtualTable $virtualTable)
    {
        $fields = $virtualTable->fields()->where('status', 1)->orderBy('sort_order')->orderBy('id')->get();

        return array(
            'table' => $this->serializeTable($virtualTable->load('fields')),
            'fields' => $fields->map(function (AppVirtualTableField $field) {
                return $this->serializeField($field);
            })->values(),
        );
    }

    public function normalizeInputSlug($slug)
    {
        return $this->normalizeSlug($slug);
    }

    public function rowToLogicalArray(AppVirtualTable $virtualTable, $row)
    {
        if (!$row) {
            return null;
        }

        $source = (array) $row;
        $data = array(
            'id' => isset($source['id']) ? (int) $source['id'] : null,
            'created_at' => isset($source['created_at']) ? $source['created_at'] : null,
            'updated_at' => isset($source['updated_at']) ? $source['updated_at'] : null,
        );

        $fields = $virtualTable->fields()->where('status', 1)->get();
        foreach ($fields as $field) {
            $data[$field->slug] = array_key_exists($field->physical_name, $source) ? $source[$field->physical_name] : null;
        }

        return $data;
    }

    private function buildRecordPayload(AppVirtualTable $virtualTable, array $data, $partial)
    {
        $payload = array();
        $fields = $virtualTable->fields()->where('status', 1)->get();

        foreach ($fields as $field) {
            if (!array_key_exists($field->slug, $data)) {
                if ($partial) {
                    continue;
                }
                if ((int) $field->default_enabled === 1) {
                    $payload[$field->physical_name] = $this->castValueByType($field, $field->default_value);
                    continue;
                }
                if ((int) $field->nullable === 1) {
                    $payload[$field->physical_name] = null;
                    continue;
                }
                throw new InvalidArgumentException('字段 [' . $field->name . '] 必填');
            }

            $payload[$field->physical_name] = $this->castValueByType($field, $data[$field->slug]);
        }

        return $payload;
    }

    private function castValueByType(AppVirtualTableField $field, $value)
    {
        if ($value === '' || $value === null) {
            return null;
        }

        if ($field->type === 'integer') {
            return (int) $value;
        }

        if ($field->type === 'decimal') {
            return (float) $value;
        }

        if ($field->type === 'boolean') {
            return in_array($value, array(true, 1, '1', 'true', 'on', 'yes'), true) ? 1 : 0;
        }

        if ($field->type === 'json') {
            if (is_array($value) || is_object($value)) {
                return json_encode($value, JSON_UNESCAPED_UNICODE);
            }

            json_decode((string) $value, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new InvalidArgumentException('字段 [' . $field->name . '] 不是合法 JSON');
            }
            return (string) $value;
        }

        return (string) $value;
    }

    private function compileReadOnlySql(Application $application, $sql, $limit)
    {
        $sql = trim((string) $sql);
        if ($sql === '') {
            throw new InvalidArgumentException('SQL 不能为空');
        }

        if (!preg_match('/^select\s/i', $sql)) {
            throw new InvalidArgumentException('只允许 SELECT 查询');
        }

        if (strpos($sql, ';') !== false || preg_match('/(--|#|\/\*)/', $sql)) {
            throw new InvalidArgumentException('SQL 不允许包含注释或多语句');
        }

        if (preg_match('/\b(insert|update|delete|replace|drop|alter|create|truncate|grant|revoke|lock|unlock|call|set)\b/i', $sql)) {
            throw new InvalidArgumentException('SQL 包含不允许的关键字');
        }

        $tables = $this->listTablesForApplication($application);
        $physicalBySlug = array();
        foreach ($tables as $table) {
            $physicalBySlug[$table->slug] = $table->physical_table;
        }

        $compiled = preg_replace_callback(
            '/\b(from|join)\s+`?([a-zA-Z][a-zA-Z0-9_]*)`?/i',
            function ($matches) use ($physicalBySlug) {
                $keyword = strtoupper($matches[1]);
                $slug = strtolower($matches[2]);
                if (!isset($physicalBySlug[$slug])) {
                    throw new InvalidArgumentException('SQL 只能访问当前应用的虚拟表 [' . $slug . ']');
                }
                return $keyword . ' `' . str_replace('`', '``', $physicalBySlug[$slug]) . '`';
            },
            $sql
        );

        if (!preg_match('/\bfrom\s+`?app_vt_/i', $compiled)) {
            throw new InvalidArgumentException('SQL 必须从虚拟表查询');
        }

        if (!preg_match('/\blimit\s+\d+\b/i', $compiled)) {
            $limit = min(self::MAX_SQL_LIMIT, max(1, (int) $limit));
            $compiled .= ' LIMIT ' . $limit;
        }

        return $compiled;
    }

    private function activeFieldsBySlug(AppVirtualTable $virtualTable)
    {
        $fields = array();
        foreach ($virtualTable->fields()->where('status', 1)->get() as $field) {
            $fields[$field->slug] = $field;
        }
        return $fields;
    }

    private function addColumnToBlueprint(Blueprint $table, AppVirtualTableField $field, $columnName)
    {
        switch ($field->type) {
            case 'text':
            case 'json':
                return $table->text($columnName);
            case 'integer':
                return $table->integer($columnName);
            case 'decimal':
                return $table->decimal($columnName, 16, 4);
            case 'boolean':
                return $table->tinyInteger($columnName);
            case 'date':
                return $table->date($columnName);
            case 'datetime':
                return $table->dateTime($columnName);
            case 'string':
            default:
                return $table->string($columnName, $field->length ? min(1000, max(1, (int) $field->length)) : 255);
        }
    }

    private function normalizeSlug($slug)
    {
        $slug = strtolower(trim((string) $slug));
        $slug = preg_replace('/[^a-z0-9_]+/', '_', $slug);
        $slug = trim($slug, '_');

        if ($slug === '' || !preg_match('/^[a-z][a-z0-9_]*$/', $slug)) {
            throw new InvalidArgumentException('标识只能使用小写字母、数字、下划线，并且必须以字母开头');
        }

        return $slug;
    }

    private function normalizeType($type)
    {
        $type = strtolower(trim((string) $type));
        $options = array_keys($this->fieldTypeOptions());

        if (!in_array($type, $options, true)) {
            throw new InvalidArgumentException('不支持的字段类型');
        }

        return $type;
    }

    private function buildPhysicalTableName(AppVirtualTable $virtualTable)
    {
        return 'app_vt_' . (int) $virtualTable->id . '_' . $this->normalizeSlug($virtualTable->slug);
    }

    private function buildPhysicalFieldName($slug)
    {
        return 'f_' . $this->normalizeSlug($slug);
    }

    private function assertPhysicalTableExists(AppVirtualTable $virtualTable)
    {
        if (!$virtualTable->physical_table || !Schema::hasTable($virtualTable->physical_table)) {
            throw new InvalidArgumentException('物理表不存在，请先同步表结构');
        }
    }
}

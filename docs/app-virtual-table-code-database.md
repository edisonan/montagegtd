# App Code 虚拟表数据库使用说明

本文档说明应用 Code 文件中如何访问当前 App 的虚拟表数据。

## 执行入口

PHP 类型的 Code 文件仍然定义 `myFunction`。为了访问虚拟表数据库，把第二个参数声明为 `$db`：

```php
<?php

function myFunction(array $input, $db)
{
    return $db->tables();
}
```

兼容旧写法：只声明 `myFunction(array $input)` 时仍可正常执行，但不能使用 `$db`。

## 基本概念

- 虚拟表在界面中有一个 `slug`，例如 `customers`。
- 字段也有一个 `slug`，例如 `name`、`phone`、`score`。
- 结构化方法使用字段 `slug`。
- SQL 查询使用虚拟表 `slug` 作为表名；字段列名使用物理字段名 `f_{字段slug}`，例如字段 `name` 对应 `f_name`。
- 所有 `$db` 操作都限定在当前 App 的虚拟表内。

## 查看表和字段

```php
<?php

function myFunction(array $input, $db)
{
    return array(
        'tables' => $db->tables(),
        'customer_schema' => $db->schema('customers'),
    );
}
```

## 新增记录

```php
<?php

function myFunction(array $input, $db)
{
    return $db->insert('customers', array(
        'name' => $input['name'],
        'phone' => isset($input['phone']) ? $input['phone'] : '',
        'score' => isset($input['score']) ? $input['score'] : 0,
    ));
}
```

返回结果使用逻辑字段名：

```json
{
  "id": 1,
  "name": "Alice",
  "phone": "13800000000",
  "score": 10,
  "created_at": "2026-07-12 10:00:00",
  "updated_at": "2026-07-12 10:00:00"
}
```

## 查询记录

```php
<?php

function myFunction(array $input, $db)
{
    return $db->select('customers', array(
        'phone' => $input['phone'],
    ), array(
        'order_by' => 'id',
        'order_dir' => 'desc',
        'limit' => 20,
    ));
}
```

查询单条记录：

```php
<?php

function myFunction(array $input, $db)
{
    return $db->first('customers', array(
        'phone' => $input['phone'],
    ));
}
```

## 更新和删除

```php
<?php

function myFunction(array $input, $db)
{
    $updated = $db->update('customers', $input['id'], array(
        'score' => $input['score'],
    ));

    return array(
        'updated' => $updated,
    );
}
```

```php
<?php

function myFunction(array $input, $db)
{
    return array(
        'deleted' => $db->delete('customers', $input['id']),
    );
}
```

## SQL 查询

`query` 只允许只读 `SELECT`。表名使用虚拟表 slug，字段名使用物理字段名。

```php
<?php

function myFunction(array $input, $db)
{
    return $db->query(
        'select id, f_name, f_score from customers where f_score >= ? order by id desc',
        array((int) $input['min_score']),
        50
    );
}
```

字段物理名可以通过 helper 获取：

```php
<?php

function myFunction(array $input, $db)
{
    $nameColumn = $db->fieldSqlName('customers', 'name');
    $scoreColumn = $db->fieldSqlName('customers', 'score');

    return $db->query(
        'select id, ' . $nameColumn . ', ' . $scoreColumn . ' from customers order by id desc',
        array(),
        20
    );
}
```

## 安全限制

- `query` 必须以 `SELECT` 开头。
- 不允许多语句、SQL 注释、DDL、DML、权限操作和存储过程关键字。
- SQL 中 `FROM` / `JOIN` 的表必须是当前 App 的虚拟表 slug。
- 没有显式 `LIMIT` 时会自动追加限制，最大返回 500 行。
- 所有动态值都应该通过绑定参数传入，不要拼接用户输入。

推荐优先使用 `select`、`first`、`insert`、`update`、`delete` 这些结构化方法；只有复杂聚合、排序或联表查询时再使用 `query`。

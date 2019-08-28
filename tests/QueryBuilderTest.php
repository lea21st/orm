<?php


namespace EasySwoole\ORM\Tests;


use EasySwoole\ORM\Driver\QueryBuilder;
use PHPUnit\Framework\TestCase;

class QueryBuilderTest extends TestCase
{
    protected $builder;
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        $this->builder = new QueryBuilder();
        parent::__construct($name, $data, $dataName);
    }

    function testGet()
    {
        $this->builder->get('get');
        $this->assertEquals('SELECT  * FROM get',$this->builder->getLastPrepareQuery());
        $this->assertEquals('SELECT  * FROM get',$this->builder->getQuery());
        $this->assertEquals([],$this->builder->getLastBindParams());

        $this->builder->get('get',1);
        $this->assertEquals('SELECT  * FROM get LIMIT 1',$this->builder->getLastPrepareQuery());
        $this->assertEquals('SELECT  * FROM get LIMIT 1',$this->builder->getQuery());
        $this->assertEquals([],$this->builder->getLastBindParams());

        $this->builder->get('get',[2,10]);
        $this->assertEquals('SELECT  * FROM get LIMIT 2, 10',$this->builder->getLastPrepareQuery());
        $this->assertEquals('SELECT  * FROM get LIMIT 2, 10',$this->builder->getQuery());
        $this->assertEquals([],$this->builder->getLastBindParams());

        $this->builder->get('get',null,['col1','col2']);
        $this->assertEquals('SELECT  col1, col2 FROM get',$this->builder->getLastPrepareQuery());
        $this->assertEquals('SELECT  col1, col2 FROM get',$this->builder->getQuery());
        $this->assertEquals([],$this->builder->getLastBindParams());

        $this->builder->get('get',1,['col1','col2']);
        $this->assertEquals('SELECT  col1, col2 FROM get LIMIT 1',$this->builder->getLastPrepareQuery());
        $this->assertEquals('SELECT  col1, col2 FROM get LIMIT 1',$this->builder->getQuery());
        $this->assertEquals([],$this->builder->getLastBindParams());

        $this->builder->get('get',[2,10],['col1','col2']);
        $this->assertEquals('SELECT  col1, col2 FROM get LIMIT 2, 10',$this->builder->getLastPrepareQuery());
        $this->assertEquals('SELECT  col1, col2 FROM get LIMIT 2, 10',$this->builder->getQuery());
        $this->assertEquals([],$this->builder->getLastBindParams());
    }

    function testWhereGet()
    {
        $this->builder->where('col1',2)->get('whereGet');
        $this->assertEquals('SELECT  * FROM whereGet WHERE  col1 = ? ',$this->builder->getLastPrepareQuery());
        $this->assertEquals("SELECT  * FROM whereGet WHERE  col1 = 2 ",$this->builder->getQuery());
        $this->assertEquals([2],$this->builder->getLastBindParams());

        $this->builder->where('col1',2,">")->get('whereGet');
        $this->assertEquals('SELECT  * FROM whereGet WHERE  col1 > ? ',$this->builder->getLastPrepareQuery());
        $this->assertEquals("SELECT  * FROM whereGet WHERE  col1 > 2 ",$this->builder->getQuery());
        $this->assertEquals([2],$this->builder->getLastBindParams());

        $this->builder->where('col1',2)->where('col2','str')->get('whereGet');
        $this->assertEquals('SELECT  * FROM whereGet WHERE  col1 = ?  AND col2 = ? ',$this->builder->getLastPrepareQuery());
        $this->assertEquals("SELECT  * FROM whereGet WHERE  col1 = 2  AND col2 = 'str' ",$this->builder->getQuery());
        $this->assertEquals([2,'str'],$this->builder->getLastBindParams());

        $this->builder->where('col3',[1,2,3],'IN')->get('whereGet');
        $this->assertEquals('SELECT  * FROM whereGet WHERE  col3 IN ( ?, ?, ? ) ',$this->builder->getLastPrepareQuery());
        $this->assertEquals('SELECT  * FROM whereGet WHERE  col3 IN ( 1, 2, 3 ) ',$this->builder->getQuery());
        $this->assertEquals([1,2,3],$this->builder->getLastBindParams());
    }

    function testJoinGet()
    {
        $this->builder->join('table2','table2.col1 = getTable.col2')->get('getTable');
        $this->assertEquals('SELECT  * FROM getTable  JOIN table2 on table2.col1 = getTable.col2',$this->builder->getLastPrepareQuery());
        $this->assertEquals('SELECT  * FROM getTable  JOIN table2 on table2.col1 = getTable.col2',$this->builder->getQuery());
        $this->assertEquals([],$this->builder->getLastBindParams());

        $this->builder->join('table2','table2.col1 = getTable.col2','LEFT')->get('getTable');
        $this->assertEquals('SELECT  * FROM getTable LEFT JOIN table2 on table2.col1 = getTable.col2',$this->builder->getLastPrepareQuery());
        $this->assertEquals('SELECT  * FROM getTable LEFT JOIN table2 on table2.col1 = getTable.col2',$this->builder->getQuery());
        $this->assertEquals([],$this->builder->getLastBindParams());
    }

    function testJoinWhereGet()
    {
        $this->builder->join('table2','table2.col1 = getTable.col2')->where('table2.col1',2)->get('getTable');
        $this->assertEquals('SELECT  * FROM getTable  JOIN table2 on table2.col1 = getTable.col2 WHERE  table2.col1 = ? ',$this->builder->getLastPrepareQuery());
        $this->assertEquals('SELECT  * FROM getTable  JOIN table2 on table2.col1 = getTable.col2 WHERE  table2.col1 = 2 ',$this->builder->getQuery());
        $this->assertEquals([2],$this->builder->getLastBindParams());
    }

    function testUpdate()
    {
        $this->builder->update('updateTable', ['a' => 1]);
        $this->assertEquals('UPDATE updateTable SET `a` = ?', $this->builder->getLastPrepareQuery());
        $this->assertEquals('UPDATE updateTable SET `a` = 1', $this->builder->getQuery());
        $this->assertEquals([1], $this->builder->getLastBindParams());
    }

    function testLimitUpdate()
    {
        $this->builder->update('updateTable', ['a' => 1], 5);
        $this->assertEquals('UPDATE updateTable SET `a` = ? LIMIT 5', $this->builder->getLastPrepareQuery());
        $this->assertEquals('UPDATE updateTable SET `a` = 1 LIMIT 5', $this->builder->getQuery());
        $this->assertEquals([1], $this->builder->getLastBindParams());
    }

    function testWhereUpdate()
    {
        $this->builder->where('whereUpdate', 'whereValue')->update('updateTable', ['a' => 1]);
        $this->assertEquals('UPDATE updateTable SET `a` = ? WHERE  whereUpdate = ? ', $this->builder->getLastPrepareQuery());
        $this->assertEquals("UPDATE updateTable SET `a` = 1 WHERE  whereUpdate = 'whereValue' ", $this->builder->getQuery());
        $this->assertEquals([1, 'whereValue'], $this->builder->getLastBindParams());
    }

    /**
     * @throws \Exception
     */
    function testLockWhereLimitUpdate()
    {
        $this->builder->setQueryOption("FOR UPDATE")->where('whereUpdate', 'whereValue')->update('updateTable', ['a' => 1], 2);
        $this->assertEquals('UPDATE updateTable SET `a` = ? WHERE  whereUpdate = ?  LIMIT 2 FOR UPDATE', $this->builder->getLastPrepareQuery());
        $this->assertEquals("UPDATE updateTable SET `a` = 1 WHERE  whereUpdate = 'whereValue'  LIMIT 2 FOR UPDATE", $this->builder->getQuery());
        $this->assertEquals([1, 'whereValue'], $this->builder->getLastBindParams());
    }


    function testDelete()
    {
        $this->builder->delete('deleteTable');
        $this->assertEquals('DELETE FROM deleteTable', $this->builder->getLastPrepareQuery());
        $this->assertEquals('DELETE FROM deleteTable', $this->builder->getQuery());
        $this->assertEquals([], $this->builder->getLastBindParams());
    }

    function testLimitDelete()
    {
        $this->builder->delete('deleteTable', 1);
        $this->assertEquals('DELETE FROM deleteTable LIMIT 1', $this->builder->getLastPrepareQuery());
        $this->assertEquals('DELETE FROM deleteTable LIMIT 1', $this->builder->getQuery());
        $this->assertEquals([], $this->builder->getLastBindParams());
    }

    function testWhereDelete()
    {
        $this->builder->where('whereDelete', 'whereValue')->delete('deleteTable');
        $this->assertEquals('DELETE FROM deleteTable WHERE  whereDelete = ? ', $this->builder->getLastPrepareQuery());
        $this->assertEquals("DELETE FROM deleteTable WHERE  whereDelete = 'whereValue' ", $this->builder->getQuery());
        $this->assertEquals(['whereValue'], $this->builder->getLastBindParams());
    }

    function testInsert()
    {
        $this->builder->insert('insertTable', ['a' => 1, 'b' => "b"]);
        $this->assertEquals(null, $this->builder->getLastPrepareQuery());
        $this->assertEquals("INSERT  INTO insertTable (`a`, `b`)  VALUES (1, 'b')", $this->builder->getQuery());
        $this->assertEquals([], $this->builder->getLastBindParams());
    }
}
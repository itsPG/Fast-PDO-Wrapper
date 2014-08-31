<?php
require_once 'fast_pdo.php';
function TestFastPDO()
{
	$test = new FastPDO('pgsql:dbname=yourdb host=localhost', 'postgres', '');
	$table = 'db_for_test';
	$table2 = 'db_for_test2';

	echo "Run Test\n";
	print_r($test->Run("create table $table(uid text, name text, guid text)"));
	print_r($test->Run("create table $table2(uid text, token text)"));
	print_r($test->Run("insert into $table(uid, name, guid) values('uidtest', 'name', 'guidtest')"));
	echo "Insert Test\n";
	print_r($test->Insert($table, [
		"uid" => "testPDO id",
		"name" => "name",
		"guid" => "this is guid"
	]));
	print_r($test->Insert($table, [
		"uid" => "testPDO",
		"name" => "name2",
		"guid" => "guid2"
	]));
	print_r($test->Insert($table2, [
		"uid" => "testPDO",
		"token" => "token test"
	]));
	echo "Update Test\n";
	print_r($test->Update($table, [
		"name" => "name is changed"
	], "uid = ?", ["testPDO id"]));
	echo "count Test\n";
	print_r($test->Count($table, 'name = ?', ['name']));
	echo "Select Test\n";
	print_r($test->Select($table, "uid = ?", ["testPDO id"], '', 1));
	print_r($test->Select($table, "uid = ? and guid = ?", ["testPDO id", "this is guid"], '', [1,0], "name"));
	echo "Delete Test";
	print_r($test->Delete($table, "uid = ?", ["testPDO id"]));
	
	echo "Fetch Test\n";
	print_r($test->Fetch($table, "'1'"));
	print_r($test->Fetch($table,'',[],"inner join $table2 on (true)"));
}
TestFastPDO();
<?php
/*
	Fast PDO Wrapper
	A tiny and fast PDO wrapper aims to ease your PDO operation

	Copyright(C) 2014 PG
	Released under the MIT License
*/

class FastPDO extends PDO
{
	public $debugMode = true;

	private function MsgLogger($msg)
	{
		echo $msg;
	}
	private function GetInsertParams($params)
	{
		$columns = [];
		$values = [];
		foreach ($params as $col => $val) {
			array_push($columns, $col);
			array_push($values, $this->quote($val));
		}
		return [implode($columns, ', '), implode($values, ', ')];
	}
	private function GetUpdateParams($params)
	{
		$ret = [];
		foreach ($params as $left => $right) {
			array_push($ret, sprintf("%s=%s", $left, $this->quote($right)));
		}
		return implode($ret, ', ');
	}
	public function Run($sqlStmt, $bind = [])
	{
		$sql = $this->prepare(trim($sqlStmt));

		if ($this->debugMode) {
			$this->MsgLogger("[Run] ".trim($sqlStmt)." [".implode($bind, ',')."]\n");
		}
		try {
			if ($sql->execute($bind) === false) {
				$msg = implode($sql->errorInfo(), ', ');
				$this->MsgLogger("[Error] Bind failed : $msg\n");
				return ;
			}
			if (preg_match("/^(select)/i", $sqlStmt)) {
				return $sql->fetchAll(PDO::FETCH_ASSOC);
			}
			if (preg_match("/^(insert|update|delete)/i", $sqlStmt)) {
				return $sql->rowCount();
			}
		} catch (PDOException $e) {
			$msg = $e->getMessage();
			$this->MsgLogger("[Error] $msg \n");
		}
	}
	public function Select($tableName, $where = "", $bind = [], $extraCmd = "", $limit = 0, $fields = '*')
	{
		/* limit[0] => LIMIT,  limit[1] => offset*/
		$sql = "SELECT $fields from $tableName $extraCmd ";

		if (!empty($where)) {
			$sql.= "WHERE $where ";
		}
		if ($limit != 0) {
			if (is_array($limit)) {
				$sql.= "LIMIT $limit[0] ";
				if (count($limit) > 1) {
					$sql.= "OFFSET $limit[1] ";
				}
			} else {
				$sql.= "LIMIT $limit ";
			}
		}
		$sql.=";";
		return $this->Run($sql, $bind);
	}
	public function Count($tableName, $where = "", $bind = [], $extraCmd = "")
	{
		$result = $this->Select($tableName, $where, $bind, $extraCmd, 0, 'count(1)');
		return $result[0]['count'];
	}
	public function Insert($tableName, $params)
	{
		$escaped = $this->GetInsertParams($params);
		$sql = "INSERT INTO $tableName($escaped[0]) VALUES($escaped[1]) ;";
		return $this->Run($sql);
	}
	public function Update($tableName, $params, $where, $bind = [])
	{
		$escaped = $this->GetUpdateParams($params);
		$sql = "UPDATE $tableName SET $escaped WHERE $where ;";
		return $this->Run($sql, $bind);
	}
	public function Delete($tableName, $where, $bind = [])
	{
		$sql = "DELETE FROM $tableName WHERE $where ;";
		return $this->Run($sql, $bind);
	}
	public function Fetch($tableName, $where = '', $bind = [], $extraCmd = '', $field = '*')
	{
		$result = $this->Select($tableName, $where, $bind, $extraCmd, 1, $field);
		return count($result) > 0 ? $result[0] : null;
	}
}
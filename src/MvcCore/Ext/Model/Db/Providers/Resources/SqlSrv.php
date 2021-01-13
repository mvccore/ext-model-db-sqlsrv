<?php

/**
 * MvcCore
 *
 * This source file is subject to the BSD 3 License
 * For the full copyright and license information, please view
 * the LICENSE.md file that are distributed with this source code.
 *
 * @copyright	Copyright (c) 2016 Tom Flídr (https://github.com/mvccore/mvccore)
 * @license  https://mvccore.github.io/docs/mvccore/5.0.0/LICENCE.md
 */

namespace MvcCore\Ext\Database\Providers\Resources;

class SqlSrv extends \MvcCore\Ext\Database\Providers\Resources\Base {

	/**
	 * @inheritDocs
	 * @param int|string $connNameOrIndex	Connection name or index in system config.
	 * @param string $tableName				Database table name.
	 * @param array $dataColumns			Data to use in insert clause, keys are 
	 *										column names, values are column values.
	 * @param string $className				model class full name.
	 * @return array						First item is boolean result, 
	 *										second is affected rows count. 
	 */
	public function Insert ($connNameOrIndex, $tableName, $dataColumns, $className) {
		$params = [];
		$index = 0;
		foreach ($dataColumns as $dataColumnValue) {
			$params[":p{$index}"] = $dataColumnValue;
			$index++;
		}
		$sql = "INSERT INTO [{$tableName}] ([" . implode("], [", array_keys($dataColumns)) . 
			"]) VALUES (" . implode(", ", array_keys($params)) . ");";

		$success = FALSE;
		$error = NULL;

		$transName = 'INSERT:'.str_replace('\\', '_', $className);
		$conn = self::GetConnection($connNameOrIndex);
		try {
			$conn->BeginTransaction(0, $transName);

			$reader = $conn
				->Prepare($sql)
				->Execute($params);

			$success = $reader->GetExecResult();
			$affectedRows = $reader->GetRowsCount();

			$newId = $conn->LastInsertId($tableName);

			$conn->Commit();

			$success = TRUE;
		} catch (\Throwable $e) {
			$affectedRows = 0;
			$newId = NULL;
			$error = $e;
			if ($conn && $conn->InTransaction())
				$conn->RollBack();
		}

		return [
			$success, 
			$affectedRows,
			$newId,
			$error
		];
	}

	/**
	 * @inheritDocs
	 * @param int|string $connNameOrIndex	Connection name or index in system config.
	 * @param string $tableName				Database table name.
	 * @param array $keyColumns				Data to use in where condition, keys are 
	 *										column names, values are column values.
	 * @param array $dataColumns			Data to use in update set clause, keys are 
	 *										column names, values are column values.
	 * @return array						First item is boolean result, 
	 *										second is affected rows count. 
	 */
	public function Update ($connNameOrIndex, $tableName, $keyColumns, $dataColumns) {
		$setSqlItems = [];
		$whereSqlItems = [];
		$params = [];
		$index = 0;
		foreach ($dataColumns as $dataColumnName => $dataColumnValue) {
			$setSqlItems[] = "[{$dataColumnName}] = :p{$index}";
			$params[":p{$index}"] = $dataColumnValue;
			$index++;
		}
		foreach ($keyColumns as $keyColumnName => $keyColumnValue) {
			$whereSqlItems[] = "[{$keyColumnName}] = :p{$index}";
			$params[":p{$index}"] = $keyColumnValue;
			$index++;
		}

		$sql = "UPDATE [{$tableName}] SET " . implode(", ", $setSqlItems) . 
			" WHERE " . implode(" AND ", $whereSqlItems) . ";";
		
		$reader = self::GetConnection($connNameOrIndex)
			->Prepare($sql)
			->Execute($params);

		return [
			$reader->GetExecResult(), 
			$reader->GetRowsCount()
		];
	}

	/**
	 * @inheritDocs
	 * @param int|string $connNameOrIndex	Connection name or index in system config.
	 * @param string $tableName				Database table name.
	 * @param array $keyColumns				Data to use in where condition, keys are 
	 *										column names, values are column values.
	 * @return array						First item is boolean result, 
	 *										second is affected rows count. 
	 */
	public function Delete ($connNameOrIndex, $tableName, $keyColumns) {
		$sqlItems = [];
		$params = [];
		$index = 0;
		foreach ($keyColumns as $keyColumnName => $keyColumnValue) {
			$sqlItems[] = "[{$keyColumnName}] = :p{$index}";
			$params[":p{$index}"] = $keyColumnValue;
			$index++;
		}
		$sql = "DELETE FROM [{$tableName}] WHERE " . implode(" AND ", $sqlItems) . ";";
		$reader = self::GetConnection($connNameOrIndex)
			->Prepare($sql)
			->Execute($params);
		return [
			$reader->GetExecResult(), 
			$reader->GetRowsCount()
		];
	}
}
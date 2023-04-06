<?php

/**
 * MvcCore
 *
 * This source file is subject to the BSD 3 License
 * For the full copyright and license information, please view
 * the LICENSE.md file that are distributed with this source code.
 *
 * @copyright	Copyright (c) 2016 Tom Flidr (https://github.com/mvccore)
 * @license		https://mvccore.github.io/docs/mvccore/5.0.0/LICENSE.md
 */

namespace MvcCore\Ext\Models\Db\Connections;

class		SqlSrv 
extends		\MvcCore\Ext\Models\Db\Connection
implements	\MvcCore\Ext\Models\Db\Model\IConstants,
			\MvcCore\Ext\Models\Db\Models\SqlSrv\IConstants {

	/**
	 * SQL metadata statement with fields `AffectedRows` and `LastInsertId`.
	 */
	const METADATA_STATEMENT = "SELECT @@ROWCOUNT AS [AffectedRows], COALESCE(SCOPE_IDENTITY(), @@IDENTITY) AS [LastInsertId];";

	/**
	 * MS SQL Server connection is always multistatement.
	 * @var bool
	 */
	protected $multiStatements = TRUE;

	/**
	 * Enabled/disabled multiple batches on a single connection.
	 * @var bool
	 */
	protected $multipleActiveResultSets = FALSE;

	/**
	 * @inheritDocs
	 * @param  string $identifierName
	 * @return string
	 */
	public function QuoteName ($identifierName) {
		if (mb_substr($identifierName, 0, 1) !== '[' && mb_substr($identifierName, -1, 1) !== ']') {
			if (mb_strpos($identifierName, '.') !== FALSE) 
				return '['.str_replace('.', '].[', $identifierName).']';
			return '['.$identifierName.']';
		}
		return $identifierName;
	}
	
	/**
	 * Get `TRUE` if enabled multiple batches on a single connection,
	 * `FALSE` by default.
	 * @return bool
	 */
	public function GetMultipleActiveResultSets () {
		return $this->multipleActiveResultSets;
	}

	/**
	 * @inheritDocs
	 * @param  int    $flags Transaction isolation, read/write mode and consistent snapshot option.
	 * @param  string $name  String without spaces to identify transaction in logs.
	 * @throws \PDOException|\RuntimeException
	 * @return bool
	 */
	public function BeginTransaction ($flags = 0, $name = NULL) {
		
		if ($this->inTransaction) {
			$cfg = $this->GetConfig();
			unset($cfg->password);
			$toolClass = \MvcCore\Application::GetInstance()->GetToolClass();
			throw new \RuntimeException(
				'Connection has opened transaction already ('.($toolClass::JsonEncode($cfg)).').'
			);
		}

		$sqlItems = [];

		if (($flags & self::TRANS_ISOLATION_REPEATABLE_READ) > 0) {
			$sqlItems[] = "SET TRANSACTION ISOLATION LEVEL REPEATABLE READ;";
		} else if (($flags & self::TRANS_ISOLATION_READ_COMMITTED) > 0) {
			$sqlItems[] = "SET TRANSACTION ISOLATION LEVEL READ COMMITTED;";
		} else if (($flags & self::TRANS_ISOLATION_READ_UNCOMMITTED) > 0) {
			$sqlItems[] = "SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;";
		} else if (($flags & self::TRANS_ISOLATION_SHAPSHOT) > 0) {
			$sqlItems[] = "SET TRANSACTION ISOLATION LEVEL SNAPSHOT;";
		} else if (($flags & self::TRANS_ISOLATION_SERIALIZABLE) > 0) {
			$sqlItems[] = "SET TRANSACTION ISOLATION LEVEL SERIALIZABLE;";
		}

		if ($name === NULL) {
			$this->transactionName = 'tsql_tran_' . str_replace('.', '', uniqid('', TRUE));
		} else {
			$toolClass = \MvcCore\Application::GetInstance()->GetToolClass();
			$this->transactionName = mb_substr($toolClass::GetUnderscoredFromPascalCase($name), 0, 32);
		}

		$debugging = $this->debugger !== NULL;
		if (count($sqlItems) > 0) {
			$sqlItems[] = "/* trans_start:{$this->transactionName} */";
			if ($debugging) $reqTime = microtime(TRUE);
			$this->provider->exec(implode("\n", $sqlItems));
			if ($debugging) 
				$this->debugger->AddQuery(
					implode("\n", $sqlItems), [], $reqTime, microtime(TRUE), $this
				);
		}

		if ($debugging) $reqTime = microtime(TRUE);
		$this->provider->beginTransaction();
		if ($debugging) 
			$this->debugger->AddQuery(
				"BEGIN TRANSACTION {$this->transactionName};", [], $reqTime, microtime(TRUE), $this
			);

		$this->inTransaction = TRUE;

		return TRUE;
	}

	/**
	 * @inheritDocs
	 * @param  int $flags Transaction chaininig.
	 * @throws \PDOException
	 * @return bool
	 */
	public function Commit ($flags = 0) {
		if (!$this->inTransaction) 
			return FALSE;

		$debugging = $this->debugger !== NULL;
		if ($debugging) $reqTime = microtime(TRUE);

		$this->provider->exec("/* trans_commit:{$this->transactionName} */");
		$this->provider->commit();
		
		if ($debugging) 
			$this->debugger->AddQuery(
				"COMMIT TRANSACTION {$this->transactionName};", [], $reqTime, microtime(TRUE), $this
			);

		$this->inTransaction  = FALSE;
		$this->transactionName = NULL;
		
		return TRUE;
	}

	/**
	 * Rolls back a transaction.
	 * @param  int $flags Transaction chaininig.
	 * @throws \PDOException
	 * @return bool
	 */
	public function RollBack ($flags = NULL) {
		if (!$this->inTransaction) 
			return FALSE;
		
		$debugging = $this->debugger !== NULL;
		if ($debugging) $reqTime = microtime(TRUE);
		
		$this->provider->exec("/* trans_rollback:{$this->transactionName} */");
		$this->provider->rollBack();
		
		if ($debugging) 
			$this->debugger->AddQuery(
				"ROLLBACK TRAN {$this->transactionName};", [], $reqTime, microtime(TRUE), $this
			);

		$this->inTransaction  = FALSE;
		$this->transactionName = NULL;
		
		return TRUE;
	}



	/**
	 * @inheritDocs
	 * @param  \Throwable $e 
	 * @return bool
	 */
	protected function isConnectionLost (\Throwable $e) {
		return FALSE;
		// TODO: try to simulate lost connection error...
		/*
		$prevError = $e->getPrevious();
		$error = $prevError instanceof \PDOException
			? $prevError
			: $e;
		return (
			$error instanceof \PDOException &&
			mb_strpos(mb_strtolower($e->getMessage()), 'server has gone away') !== FALSE
		);
		*/
	}

	/**
	 * Set up connection specific properties depends on this driver.
	 * @return void
	 */
	protected function setUpConnectionSpecifics () {
		parent::setUpConnectionSpecifics();
		
		$dsnLower = ';' . trim(mb_strtolower($this->dsn), ';') . ';';
		$this->multipleActiveResultSets = (
			mb_strpos($dsnLower, ';multipleactiveresultsets=true;') !== FALSE ||
			mb_strpos($dsnLower, ';multipleactiveresultsets=1;') !== FALSE
		);

		if ($this->usingOdbcDriver) 
			$this->metaDataStatement = static::METADATA_STATEMENT;
	}
}

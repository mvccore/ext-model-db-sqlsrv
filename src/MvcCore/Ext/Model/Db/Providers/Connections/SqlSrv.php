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

namespace MvcCore\Ext\Database\Providers\Connections;

class SqlSrv 
extends \MvcCore\Ext\Database\Connection
implements	\MvcCore\Ext\Database\Model\IConstants,
			\MvcCore\Ext\Database\Models\SqlSrvs\IConstants {

	/**
	 * Microsoft SQL Server version in "PHP-standardized" version number string.
	 * @var string|NULL
	 */
	protected $version = NULL;

	/**
	 * `TRUE` for multi statements connection type.
	 * @var bool|NULL
	 */
	protected $multiStatements = NULL;



	/**
	 * Return server version in "PHP-standardized" version number string.
	 * @return null|string
	 */
	public function GetVersion () {
		return $this->version;
	}

	/**
	 * Return `TRUE` for multi statements connection type.
	 * @return bool|null
	 */
	public function IsMutliStatements () {
		return $this->mutliStatements;
	}


	
	/**
	 * @inheritDocs
	 * @param int $flags Transaction isolation, read/write mode and consistent snapshot option.
	 * @param string $name String without spaces to identify transaction in logs.
	 * @throws \PDOException|\RuntimeException
	 * @return bool
	 */
	public function BeginTransaction ($flags = 0, $name = NULL) {
		
		if ($this->inTransaction) {
			$cfg = $this->GetConfig();
			unset($cfg['password']);
			$toolClass = \MvcCore\Application::GetInstance()->GetToolClass();
			throw new \RuntimeException(
				'Connection has opened transaction already ('.($toolClass::EncodeJson($cfg)).').'
			);
		}

		$sqlItems = [];

		if (($flags & self::TRANS_ISOLATION_REPEATABLE_READ) > 0) {
			$sqlItems[] = "SET TRANSACTION ISOLATION LEVEL REPEATABLE READ;\nGO;";
		} else if (($flags & self::TRANS_ISOLATION_READ_COMMITTED) > 0) {
			$sqlItems[] = "SET TRANSACTION ISOLATION LEVEL READ COMMITTED;\nGO;";
		} else if (($flags & self::TRANS_ISOLATION_READ_UNCOMMITTED) > 0) {
			$sqlItems[] = "SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;\nGO;";
		} else if (($flags & self::TRANS_ISOLATION_SHAPSHOT) > 0) {
			$sqlItems[] = "SET TRANSACTION ISOLATION LEVEL SNAPSHOT;\nGO;";
		} else if (($flags & self::TRANS_ISOLATION_SERIALIZABLE) > 0) {
			$sqlItems[] = "SET TRANSACTION ISOLATION LEVEL SERIALIZABLE;\nGO;";
		}

		if ($name !== NULL) {
			$toolClass = \MvcCore\Application::GetInstance()->GetToolClass();
			$this->transactionName = $toolClass::GetUnderscoredFromPascalCase($name);
			$sqlItems[] = "/* trans_start:{$this->transactionName} */";
		}
		$sqlItems[] = "BEGIN TRANSACTION;\nGO;";
		
		if ($this->multiStatements) {
			$this->provider->exec(implode("\n", $sqlItems));
		} else {
			foreach ($sqlItems as $sqlItem)
				$this->provider->exec($sqlItem);
		}

		$this->inTransaction = TRUE;

		return TRUE;
	}

	/**
	 * @inheritDocs
	 * @param int $flags Transaction chaininig.
	 * @throws \PDOException
	 * @return bool
	 */
	public function Commit ($flags = 0) {
		if (!$this->inTransaction) 
			return FALSE;
		$sqlItems = [];

		if ($this->transactionName !== NULL) 
			$sqlItems[] = "/* trans_commit:{$this->transactionName} */";

		$sqlItems[] = "COMMIT TRANSACTION;\nGO;";

		if ($this->multiStatements) {
			$this->provider->exec(implode("\n", $sqlItems));
		} else {
			foreach ($sqlItems as $sqlItem)
				$this->provider->exec($sqlItem);
		}
		
		$this->inTransaction  = FALSE;
		$this->transactionName = NULL;
		
		return TRUE;
	}

	/**
	 * Rolls back a transaction.
	 * @param int $flags Transaction chaininig.
	 * @throws \PDOException
	 * @return bool
	 */
	public function RollBack ($flags = NULL) {
		if (!$this->inTransaction) 
			return FALSE;
		
		$sqlItems = [];
		
		if ($this->transactionName !== NULL) 
			$sqlItems[] = "/* trans_rollback:{$this->transactionName} */";

		$sqlItems[] = "ROLLBACK TRANSACTION;\nGO;";

		if ($this->multiStatements) {
			$this->provider->exec(implode("\n", $sqlItems));
		} else {
			foreach ($sqlItems as $sqlItem)
				$this->provider->exec($sqlItem);
		}

		$this->inTransaction  = FALSE;
		$this->transactionName = NULL;
		
		return TRUE;
	}



	/**
	 * @inheritDocs
	 * @return \PDO
	 */
	protected function connect () {
		$this->provider = new \PDO(
			$this->dsn, $this->username, $this->password, $this->options
		);
		$this->setUpConnectionSpecifics();
		return $this->provider;
	}

	/**
	 * @inheritDocs
	 * @param \Throwable $e 
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
		$multiStatementsConst = '\PDO::ATTR_EMULATE_PREPARES';
		$multiStatementsConstVal = defined($multiStatementsConst) 
			? constant($multiStatementsConst) 
			: 0;
		$this->multiStatements = isset($this->options[$multiStatementsConstVal]);
		
		$serverVersionConst = '\PDO::ATTR_SERVER_VERSION';
		$serverVersionConstVal = defined($serverVersionConst) 
			? constant($serverVersionConst) 
			: 0;
		
		$this->version = $this->provider->getAttribute($serverVersionConstVal);
	}
}

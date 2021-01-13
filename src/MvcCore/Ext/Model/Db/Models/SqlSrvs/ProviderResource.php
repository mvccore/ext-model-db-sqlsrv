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

namespace MvcCore\Ext\Database\Models\SqlSrvs;

trait ProviderResource {

	/**
	 * Database provider specific resource class instance with universal SQL statements.
	 * @var \MvcCore\Ext\Database\Providers\Resources\SqlSrv
	 */
	protected static $editProviderResource = NULL;

	/**
	 * Get database provider specific resource class instance with universal SQL statements.
	 * @return \MvcCore\Ext\Database\Providers\Resources\SqlSrv
	 */
	protected static function getEditProviderResource () {
		if (self::$editProviderResource === NULL)
			self::$editProviderResource = new \MvcCore\Ext\Database\Providers\Resources\SqlSrv;
		return self::$editProviderResource;
	}
}
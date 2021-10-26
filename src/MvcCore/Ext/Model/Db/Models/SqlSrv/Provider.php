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

namespace MvcCore\Ext\Models\Db\Models\SqlSrv;

/**
 * @mixin \MvcCore\Ext\Models\Db\Models\SqlSrv
 */
trait Provider {
	
	/**
	 * Provider specific driver name.
	 * @var string|NULL
	 */
	protected static $providerDriverName = 'sqlsrv';

	/**
	 * Connection class full name, specific for each extension.
	 * @var string
	 */
	protected static $providerConnectionClass = '\\MvcCore\\Ext\\Models\\Db\\Connections\\SqlSrv';

	/**
	 * Edit resource class full name, specific for each extension.
	 * @var string
	 */
	protected static $providerEditResourceClass = '\\MvcCore\\Ext\\Models\\Db\\Resources\\Edits\\SqlSrv';
}
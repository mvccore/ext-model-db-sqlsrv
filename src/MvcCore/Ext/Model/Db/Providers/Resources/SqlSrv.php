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

namespace MvcCore\Ext\Models\Db\Providers\Resources;

class		SqlSrv
implements	\MvcCore\Model\IConstants,
			\MvcCore\Ext\Models\Db\Model\IConstants {
	
	use \MvcCore\Model\Props;
	use \MvcCore\Model\Config;

	use \MvcCore\Model\Connection, 
		\MvcCore\Ext\Models\Db\Model\Connection {
			\MvcCore\Ext\Models\Db\Model\Connection::GetConnection insteadof \MvcCore\Model\Connection;
			\MvcCore\Model\Connection::GetConnection as GetProviderConnection;
		}
	
	use \MvcCore\Ext\Models\Db\Models\SqlSrvs\ProviderResource;
	
	use \MvcCore\Ext\Models\Db\Providers\Resources\Manipulation;
}
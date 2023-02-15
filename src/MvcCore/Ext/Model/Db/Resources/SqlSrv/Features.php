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

namespace MvcCore\Ext\Models\Db\Resources\SqlSrv;

/**
 * @mixin \MvcCore\Ext\Models\Db\Resources\SqlSrv
 */
trait Features {
	
	use \MvcCore\Model\Comparers;
	use \MvcCore\Model\Config;
	use \MvcCore\Model\Converters;
	use \MvcCore\Model\Props;
	
	use \MvcCore\Model\Connection, 
		\MvcCore\Ext\Models\Db\Model\Connection {
			\MvcCore\Ext\Models\Db\Model\Connection::GetConnection insteadof \MvcCore\Model\Connection;
			\MvcCore\Model\Connection::GetConnection as protected getProviderConnection;
		}

	use \MvcCore\Model\Parsers,
		\MvcCore\Ext\Models\Db\Model\Parsers {
			\MvcCore\Ext\Models\Db\Model\Parsers::ParseToTypes insteadof \MvcCore\Model\Parsers;
			\MvcCore\Ext\Models\Db\Model\Parsers::parseToType insteadof \MvcCore\Model\Parsers;
			\MvcCore\Ext\Models\Db\Model\Parsers::parseToDateTime insteadof \MvcCore\Model\Parsers;
			\MvcCore\Model\Parsers::parseToDateTime as parseToDateTimeDefault;
	}
	
	use \MvcCore\Ext\Models\Db\Models\SqlSrv\Provider;
}
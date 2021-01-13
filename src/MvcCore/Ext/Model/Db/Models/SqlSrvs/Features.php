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

trait Features {

	use \MvcCore\Ext\Database\Model\Props;
	use \MvcCore\Ext\Database\Model\DataMethods;
	use \MvcCore\Ext\Database\Model\Manipulation;
	use \MvcCore\Ext\Database\Model\Merging;
	
	use \MvcCore\Ext\Database\Models\SqlSrvs\ProviderResource;
}
<?php

/*
+---------------------------------------------------------------------------+
| Revive Adserver                                                           |
| http://www.revive-adserver.com                                            |
|                                                                           |
| Copyright: See the COPYRIGHT.txt file.                                    |
| License: GPLv2 or later, see the LICENSE.txt file.                        |
+---------------------------------------------------------------------------+
*/

$className = 'OA_UpgradePostscript';

class OA_UpgradePostscript
{
    public $oUpgrade;

    public function __construct()
    {
    }

    public function execute($aParams)
    {
        $this->oUpgrade = &$aParams[0];
//        if (PEAR::isError(true))
//        {
//            return false;
//        }
        return true;
    }
}

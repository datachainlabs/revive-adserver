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

require_once LIB_PATH . '/Maintenance/Statistics.php';
require_once LIB_PATH . '/Maintenance/Statistics/Task/LogCompletion.php';

require_once MAX_PATH . '/lib/OA/ServiceLocator.php';
require_once MAX_PATH . '/lib/pear/Date.php';

/**
 * A class for testing the OX_Maintenance_Statistics_Task_LogCompletion class.
 *
 * @package    OpenXMaintenance
 * @subpackage TestSuite
 */
class Test_OX_Maintenance_Statistics_Task_LogCompletion extends UnitTestCase
{
    /**
     * The constructor method.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Test the creation of the class.
     */
    public function testCreate()
    {
        $oLogCompletion = new OX_Maintenance_Statistics_Task_LogCompletion();
        $this->assertTrue(is_a($oLogCompletion, 'OX_Maintenance_Statistics_Task_LogCompletion'));
    }

    /**
     * A method to test the run() method.
     */
    public function testRun()
    {
        // Reset the testing environment
        TestEnv::restoreEnv();

        $aConf = &$GLOBALS['_MAX']['CONF'];
        $oTable = &OA_DB_Table_Core::singleton();
        $oDbh = OA_DB::singleton();
        $oServiceLocator = OA_ServiceLocator::instance();

        $oNow = new Date('2004-06-06 18:10:00');
        $oServiceLocator->register('now', $oNow);
        // Create and register a new OX_Maintenance_Statistics object
        $oMaintenanceStatistics = new OX_Maintenance_Statistics();
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);
        // Create a new OX_Maintenance_Statistics_Task_LogCompletion object
        $oLogCompletion = new OX_Maintenance_Statistics_Task_LogCompletion();

        // Set some of the object's variables, and log
        $oLogCompletion->oController->updateIntermediate = true;
        $oLogCompletion->oController->oUpdateIntermediateToDate = new Date('2004-06-06 17:59:59');
        $oLogCompletion->oController->updateFinal = false;
        $oLogCompletion->oController->oUpdateFinalToDate = null;
        $oEnd = new Date('2004-06-06 18:12:00');
        $oLogCompletion->run($oEnd);
        // Test
        $query = "
            SELECT
                *
            FROM
                " . $oDbh->quoteIdentifier($aConf['table']['prefix'] . $aConf['table']['log_maintenance_statistics'], true) . "
            WHERE
                adserver_run_type = 0";
        $rc = $oDbh->query($query);
        $aRow = $rc->fetchRow();
        $this->assertEqual($aRow['start_run'], '2004-06-06 18:10:00');
        $this->assertEqual($aRow['end_run'], '2004-06-06 18:12:00');
        $this->assertEqual($aRow['duration'], 120);
        $this->assertEqual($aRow['updated_to'], '2004-06-06 17:59:59');
        // Set some of the object's variables, and log
        $oLogCompletion->oController->updateIntermediate = false;
        $oLogCompletion->oController->oUpdateIntermediateToDate = null;
        $oLogCompletion->oController->updateFinal = true;
        $oLogCompletion->oController->oUpdateFinalToDate = new Date('2004-06-06 17:59:59');
        $oEnd = new Date('2004-06-06 18:13:00');
        $oLogCompletion->run($oEnd);
        // Test
        $query = "
            SELECT
                *
            FROM
                " . $oDbh->quoteIdentifier($aConf['table']['prefix'] . $aConf['table']['log_maintenance_statistics'], true) . "
            WHERE
                adserver_run_type = 1";
        $rc = $oDbh->query($query);
        $aRow = $rc->fetchRow();
        $this->assertEqual($aRow['start_run'], '2004-06-06 18:10:00');
        $this->assertEqual($aRow['end_run'], '2004-06-06 18:13:00');
        $this->assertEqual($aRow['duration'], 180);
        $this->assertEqual($aRow['updated_to'], '2004-06-06 17:59:59');
        // Set some of the object's variables, and log
        $oLogCompletion->oController->updateIntermediate = true;
        $oLogCompletion->oController->oUpdateIntermediateToDate = new Date('2004-06-06 17:59:59');
        $oLogCompletion->oController->updateFinal = true;
        $oLogCompletion->oController->oUpdateFinalToDate = new Date('2004-06-06 17:59:59');
        $oEnd = new Date('2004-06-06 18:14:00');
        $oLogCompletion->run($oEnd);
        // Test
        $query = "
            SELECT
                *
            FROM
                " . $oDbh->quoteIdentifier($aConf['table']['prefix'] . $aConf['table']['log_maintenance_statistics'], true) . "
            WHERE
                adserver_run_type = 2";
        $rc = $oDbh->query($query);
        $aRow = $rc->fetchRow();
        $this->assertEqual($aRow['start_run'], '2004-06-06 18:10:00');
        $this->assertEqual($aRow['end_run'], '2004-06-06 18:14:00');
        $this->assertEqual($aRow['duration'], 240);
        $this->assertEqual($aRow['updated_to'], '2004-06-06 17:59:59');

        // Reset the testing environment
        TestEnv::restoreEnv();
    }

    /**
     * Method to test the testSetMaintenanceStatisticsRunReport method.
     *
     * Requirements:
     * Test 1: Test two writes to reports.
     */
    public function testSetMaintenanceStatisticsRunReport()
    {
        $aConf = $GLOBALS['_MAX']['CONF'];
        $oDbh = OA_DB::singleton();

        // Create a new OX_Maintenance_Statistics_Task_LogCompletion object
        $oLogCompletion = new OX_Maintenance_Statistics_Task_LogCompletion();

        // Test 1
        $report = 'Maintenance run has finished :: Maintenance will run again at XYZ.';
        $oLogCompletion->_setMaintenanceStatisticsRunReport($report);

        $query = "
            SELECT
                timestamp,
                usertype,
                userid,
                action,
                object,
                details
            FROM
                " . $oDbh->quoteIdentifier($aConf['table']['prefix'] . $aConf['table']['userlog'], true) . "
            WHERE
                userlogid = 1";

        $rc = $oDbh->query($query);
        $aRow = $rc->fetchRow();
        $this->assertEqual($aRow['usertype'], phpAds_userMaintenance);
        $this->assertEqual($aRow['userid'], '0');
        $this->assertEqual($aRow['action'], phpAds_actionBatchStatistics);
        $this->assertEqual($aRow['object'], '0');
        $this->assertEqual($aRow['details'], $report);

        $report = '2nd Maintenance run has finished :: Maintenance will run again at XYZ.';
        $oLogCompletion->_setMaintenanceStatisticsRunReport($report);

        $query = "
            SELECT
                timestamp,
                usertype,
                userid,
                action,
                object,
                details
            FROM
                " . $oDbh->quoteIdentifier($aConf['table']['prefix'] . $aConf['table']['userlog'], true) . "
            WHERE
                userlogid = 2";

        $rc = $oDbh->query($query);
        $aRow = $rc->fetchRow();
        $this->assertEqual($aRow['usertype'], phpAds_userMaintenance);
        $this->assertEqual($aRow['userid'], '0');
        $this->assertEqual($aRow['action'], phpAds_actionBatchStatistics);
        $this->assertEqual($aRow['object'], '0');
        $this->assertEqual($aRow['details'], $report);

        TestEnv::restoreEnv();
    }
}

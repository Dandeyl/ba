<?php
/**
 * @runTestsInSeparateProcesses
 */
class ControlstructTest extends PHPUnit_Framework_TestCase {
    const srcPath = 'testfiles/2_test_controlstructs/';
    const cmdPath = '../cmd.php';
    
    /**
     * Increase variable in condition of if
     */
    public function testCS03_IncreaseInCondition() {
        $argv[1] = self::srcPath.'03IncreaseInCondition.php';
        require(self::cmdPath);
        $this->assertEquals(0, ScanInfo::getNumVulnerabilities());
        
        $var = ScanInfo::findVar('$a');
        $this->assertEquals(1, $var->getValue());        
    }
    
    
}
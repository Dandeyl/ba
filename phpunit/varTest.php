<?php
/**
 * @runTestsInSeparateProcesses
 */
class VarTest extends PHPUnit_Framework_TestCase {
    const srcPath = '1_test_variables/';
    const cmdPath = '../cmd.php';
    
    public function testVar1_NoVulnerabilityBasic() {
        $argv[1] = self::srcPath.'testfile01.php';
        require(self::cmdPath);
        $this->assertEquals(0, ScanInfo::getNumVulnerabilities());
        
    }
    
    /**
     * $_GET["test"] gets assigned to a variable, variable gets echo'd
     * => xss warning 
     */
    public function testVar2_SimpleVulnerability() {
        $argv[1] = self::srcPath.'testfile02.php';
        require(self::cmdPath);
        $this->assertEquals(1, ScanInfo::getNumVulnerabilities());
    }
    
    /**
     * Concat variables
     */
    public function testVar3_Concatenation() {
        $argv[1] = self::srcPath.'testfile03.php';
        require(self::cmdPath);
        
        $var = ScanInfo::findVar('$baz');
        $this->assertEquals('string', $var->getValue());
    }
    
    /**
     * Simple reference test
     */
    public function testVar4_Reference() {
        $argv[1] = self::srcPath.'testfile04.php';
        require(self::cmdPath);
        
        $this->assertEquals(1, ScanInfo::getNumVulnerabilities());
    }
    
    public function testVar10_SimpleVulnerability() {
        $argv[1] = self::srcPath.'testfile10.php';
        require(self::cmdPath);
        $this->assertEquals(1, ScanInfo::getNumVulnerabilities());
    }
}
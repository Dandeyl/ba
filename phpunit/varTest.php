<?php
/**
 * @runTestsInSeparateProcesses
 */
class VarTest extends PHPUnit_Framework_TestCase {
    const srcPath = 'testfiles/1_test_variables/';
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
    
    /**
     * Unset a variable that was made visible in a function using global
     * shouldnt affect the value out of the function
     */
    public function testVar5_UnsetGlobal() {
        $argv[1] = self::srcPath.'testfile05.php';
        require(self::cmdPath);
        
        $a = ScanInfo::findVar('$a');
        $this->assertEquals(4, $a->getValue());
    }
    
    /**
     * Unset a variable that was made visible in a function using $GLOBALS
     * should affect the value out of the function
     */
    public function testVar6_UnsetGLOBALS() {
        $argv[1] = self::srcPath.'testfile06.php';
        require(self::cmdPath);
        
        $a = ScanInfo::findVar('$a');
        $this->assertEquals(false, $a);
    }
    
    /**
     * Value change of a varible, made visible in a function with global
     * should change the value in the global scope too
     */
    public function testVar7_ChangeValueGlobal() {
        $argv[1] = self::srcPath.'testfile07.php';
        require(self::cmdPath);
        
        $a = ScanInfo::findVar('$a');
        $this->assertEquals(5, $a->getValue());
    }
    
     /**
     * Value change of a varible, made visible in a function with global
     * should change the value in the global scope too
     */
    public function testVar8_GlobalUninitialisedVariable() {
        $argv[1] = self::srcPath.'testfile08.php';
        require(self::cmdPath);
        
        $a = ScanInfo::findVar('$a');
        $this->assertEquals(false, $a);
    }
    
    public function testVar10_SimpleVulnerability() {
        $argv[1] = self::srcPath.'testfile10.php';
        require(self::cmdPath);
        $this->assertEquals(1, ScanInfo::getNumVulnerabilities());
    }
}
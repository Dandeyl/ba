<?php

/**
 * An object of this class contains information about a function or method in the source code
 */
class Obj_Function extends Obj_CodeSequenceInfo {
    /**
     * Name of the function. 
     * Method: Namespace\Classname->Functionname
     * Static Method: Namespace\Classname::Functionname
     * @var type 
     */
    protected $name;
    
    /**
     * Arguments of the current function
     * @var array 
     */
    protected $arguments;
    
    /**
     * In which scopes this function is visible.
     * @var array 
     */
    protected $scope;
    
    /**
     * Which vulnerabilities this functions protects from.
     *   "sql_injection" => array(1) // the first parameter gets secured for sql injection
     * @var array 
     */
    protected $securing_for;
    
    /**
     * Vulnerabilities and which parameters have to be checked:
     *    "sql_injection" => array() // all parameters have to be checked for sql inj.
     * @var array
     */
    protected $vulnerable_for;
    
    /**
     * Name of the functions to check if this function really is vulnerable. E.g.
     * preg_replace is just dangerous if the modifier "e" is passed. So we have to create a
     * function pluteus_check_vulnerable_preg_replace($search, $replace, $haystack) in which we
     * check the value of $search. 
     * If more than one function is given, all functions have to return false to signalise the
     * function is secure.
     * @var string[] 
     */
    protected $func_check_vulnerable;
    
    /**
     * Name of the function to undo this function. This might be used in the future for
     * intelligently guessing environment variables that can be passed to the script
     * to accomplish an attack.
     * @var type 
     */
    protected $func_undo;
    
    
    //----------------------------
    //--------- Methods ----------
    public function setExcecutable($executable) {
        $this->executable = (bool) $executable;
    }
    
    public function isExecutable() {
        return (bool) $this->executable;
    }
    
    /**
     * Set what attacks the code sequence is vulnerable for
     * @param array $vuln_for
     */
    public function setVulnerableFor($vuln_for) {
        
    }
    
    /**
     * Get what attacks the code sequence is vulnerable for
     * @return array
     */
    public function getVulnerableFor() {
        
    }
    
    
    /**
     * Sets what kind of attacks this code sequence is securing for.
     * @param type $sec_for
     */
    public function setSecuringFor($sec_for) {
        
    }
    
    /**
     * Get what attacks this code sequence is securing
     * @param type $sec_for
     */
    public function getSecuringFor() {
        
    }
    
}
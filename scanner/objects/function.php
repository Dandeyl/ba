<?php

/**
 * An object of this class contains information about a function or method in the source code
 */
class Obj_Function {
    /**
     * Name of the function. 
     * Method: Namespace\Classname->Functionname
     * Static Method: Namespace\Classname::Functionname
     * @var type 
     */
    protected $name;
    
    /**
     * Paramters of the current function
     * @var PHPParser_Node_Param 
     */
    protected $parameters;
    
    /**
     * In which scope this function is visible.
     * @var string
     */
    protected $scope;
    
    /**
     * The type of the value that gets returned
     * @var bool 
     */
    protected $return_type;
    
    /**
     * The last value this function returned
     * @var mixed 
     */
    protected $last_return_value;
    
    /**
     * Does it return a reference?
     * @var bool 
     */
    protected $return_by_ref;
    
    /**
     * Is the return value user defined?
     * @var bool 
     */
    protected $return_user_defined;
    
    /**
     * Is this function defined by the user
     * @var bool 
     */
    protected $user_defined;
    
    /**
     * Statements of this functions
     * @var array 
     */
    protected $stmts;
    
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
     * Can this function be safely executed?
     * @var bool 
     */
    protected $executable;
    
    /**
     * Name of the functions to check if this function really is vulnerable. E.g.
     * preg_replace is just dangerous if the modifier "e" is passed. So we have to create a
     * function pluteus_check_vulnerable_preg_replace($search, $replace, $haystack) in which we
     * check the value of $search. 
     * If more than one function is given, all functions have to return false to signalise the
     * function is secure.
     * @var array[] 
     */
    protected $func_check_vulnerable;
    
    /**
     * Name of the function to undo this function. This might be used in the future for
     * intelligently guessing environment variables that can be passed to the script
     * to accomplish an attack.
     * @var type 
     */
    protected $func_complimentary;
    
    
    //----------------------------
    //--------- Methods ----------
    /**
     * Initialise values
     */
    public function __construct() {
        $this->user_defined = true;
    }
    
    /**
     * Set the name of the function
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }
    
    /**
     * Get the name of the function
     * @return type
     */
    public function getName() {
        return $this->name;
    }
    
    /**
     * Set if the return value is returned by reference?
     * @param bool $byref
     */
    public function setReturnByRef($byref) {
        $this->return_by_ref = $byref;
    }
    
    /**
     * Is the return value returned by reference?
     * @return bool
     */
    public function getReturnByRef() {
        return $this->return_by_ref;
    }
    
    public function setReturnType($type) {
        $this->return_type = $type;
    }
    
    public function getReturnType() {
        return $this->return_type;
    }
    
    /**
     * Set ifthis function can be safely executed?
     * @param bool $executable
     */
    public function setExcecutable($executable) {
        $this->executable = (bool) $executable;
    }
    
    /**
     * Get if the function is safely executable.
     * @return bool
     */
    public function isExecutable() {
        return (bool) $this->executable;
    }
    
    /**
     * Set if the function is defined by the user
     * @param type $user_defined
     */
    public function setUserDefined($user_defined) {
        $this->user_defined = (bool) $user_defined;
    }
    
    /**
     * Get if the function is user defined
     * @return type
     */
    public function isUserDefined() {
        return $this->user_defined;
    }
    
    /**
     * Set parameters of this function
     * @param array $params
     * @throws Exception
     */
    public function setParameters(array $params) {
        foreach($params as $param) {
            if(!($param instanceof PHPParser_Node_Param)) {
                throw new Exception('Obj_Function: Parameters have to be of type PHPParser_Node_Param');
            }
        }
        $this->parameters = $params;
    }
    
    /**
     * Get parameters of this function
     * @return PHPParser_Node_Param[]
     */
    public function getParameters() {
        return $this->parameters;
    }
    
    
    
    /**
     * Set what attacks the code sequence is vulnerable for
     * @param array $vuln_for
     */
    public function setVulnerableFor(array $vuln_for) {
        $this->vulnerable_for = $vuln_for;
    }
    
    /**
     * Get what attacks the code sequence is vulnerable for
     * @return array
     */
    public function getVulnerableFor() {
        return $this->vulnerable_for;
    }
    
    
    /**
     * Sets what kind of attacks this code sequence is securing for.
     * @param array $sec_for
     */
    public function setSecuringFor(array $sec_for) {
        $this->securing_for = $sec_for;
    }
    
    /**
     * Get what attacks this code sequence is securing
     * @param type $sec_for
     */
    public function getSecuringFor() {
        return $this->securing_for;
    }
    
    public function setScope($scope) {
        $this->scope = $scope;
    }
    
    
    public function getScope() {
        return $this->scope;
    }
    
    public function setFunctionToCheckForVulnerability($functions) {
        foreach($functions as $vuln => $funcs) {
            foreach($funcs as $func) {
                $this->addFunctionToCheckForVulnerability($vuln, $func);
            }
        }
    }
    
    public function addFunctionToCheckForVulnerability($vulnerability, $function) {
        $this->func_check_vulnerable[$vulnerability][] = $function;
    }
    
    public function setStatements(array $stmts) {
        $this->stmts = $stmts;
    }
    
    public function getStatements() {
        return $this->stmts;
    }
}
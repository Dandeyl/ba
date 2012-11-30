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
     * Can this function be safely executed?
     * @var bool 
     */
    protected $executable;
    
    /**
     * Paramters of the current function.
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
     * @var string
     */
    protected $return_type;
    
    /**
     * Does the return value come from a source users can define?
     * @var bool 
     */
    protected $return_user_defined;
    
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
     * Which parameters define the return value
     * @var type 
     */
    protected $return_defined_by_params;
        
    /**
     * Is this function defined by the user?
     * @var bool 
     */
    protected $user_defined;
    
    /**
     * Statements of this functions. Only for user defined functions
     * @var array 
     */
    protected $stmts;
    
    /**
     * Does the return value somehow get protected from any vulnerability by this function?
     * @var string
     */
    protected $return_add_securing = null;
    
     /**
     * Does the return value somehow remove a protection?
     * @var string
     */
    protected $return_remove_securing = null;
    
    /**
     * Vulnerabilities possible through this function. Possible values:
     * xss, sql, dir, head, 
     * @var string
     */
    protected $vulnerable_for = null;
    
    /**
     * Parameters that are vulnerable. Empty array means all parameters are vulnerable.
     * Null means no parameter is vulnerable 
     * @var array 
     */
    protected $vulnerable_parameters = null;
    
    /**
     * Name of the functions to check if this function really is vulnerable. E.g.
     * preg_replace is just dangerous if the modifier "e" is passed. So we have to create a
     * function pluteus_check_vulnerable_preg_replace($search, $replace, $haystack) in which we
     * check the value of $search. If the given function returns true, this function is vulnerable.
     * @var string
     */
    protected $func_check_vulnerable;
    
    /**
     * Name of the function used to replace this function. If the function cannot be executed
     * a function that does nearly the same but skips the dangours part can be set and then will
     * be executed instead.
     * @var string 
     */
    protected $func_replacement;
    
    /**
     * Name of the function to undo this function. This might be used in the future for
     * intelligently guessing environment variables that can be passed to the script
     * to accomplish an attack.
     * @var string 
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
     * Set the parameters that are defining the return value. null if the return value
     * is not dependend on the parameters given.
     * @param array|null $params
     */
    public function setReturnDefinedByParams($params) {
        $this->return_defined_by_params = $params;
    }
    
    /**
     * Get what parameters define the return value
     * @return type
     */
    public function getReturnDefinedByParams() {
        return $this->return_defined_by_params;
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
     * @param string $vuln_for
     */
    public function setVulnerableFor($vuln_for) {
        $this->vulnerable_for = $vuln_for;
    }
    
    /**
     * Get what attacks the code sequence is vulnerable for
     * @return string
     */
    public function getVulnerableFor() {
        return $this->vulnerable_for;
    }
    
    /**
     * Get if function is vulnerable for a specific attack
     * @param string $vuln_for
     */
    public function isVulnerableFor($attack) {
        return $this->vulnerable_for == $attack;
    }
    
    /**
     * Set parameters that are vulnerable
     * @param array $parameters
     */
    public function setVulnerableParameters(array $parameters) {
        $this->vulnerable_parameters = $parameters;
    }
    
    /**
     * Parameters that are vulnerable
     * @return array
     */
    public function getVulnerableParameters() {
        return $this->vulnerable_parameters;
    }
    
    /**
     * Sets how this function is securing a value
     * @param string $sec_for
     */
    public function setReturnAddSecuring($sec_for) {
        $this->return_add_securing = $sec_for;
    }
    
    /**
     * Get how this function is securing a value
     * @param type $sec_for
     */
    public function getReturnAddSecuring() {
        return $this->return_add_securing;
    }
    
    /**
     * Sets if this function is removing a securing
     * @param string $sec_for
     */
    public function setReturnRemoveSecuring($sec_for) {
        $this->return_remove_securing = $sec_for;
    }
    
    /**
     * Get what securing mechanism this function is removing
     * @param type $sec_for
     */
    public function getReturnRemoveSecuring() {
        return $this->return_remove_securing;
    }
    
    
    public function setReturnUserDefined($user_defined) {
        $this->return_user_defined = (int) $user_defined;
    }
    
    public function getReturnUserDefined() {
        return $this->return_user_defined;
    }
    
    public function setScope($scope) {
        $this->scope = $scope;
    }
    
    
    public function getScope() {
        return $this->scope;
    }
    
    public function setFunctionToCheckForVulnerability($function) {
        $this->func_check_vulnerable = $function;
    }
    
    public function getFunctionToCheckForVulnerability() {
        return $this->func_check_vulnerable;
    }
    
    public function setFunctionReplacement($function) {
        $this->func_replacement = $function;
    }
    
    public function getFunctionReplacement() {
        return $this->func_replacement;
    }
    
    public function setStatements(array $stmts) {
        $this->stmts = $stmts;
    }
    
    public function getStatements() {
        return $this->stmts;
    }
}
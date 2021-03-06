<?php
/**
 * Object of this class represents a variable, constant or property
 */
class Obj_Varlist {
    /**
     * List of variables initialised during parsing process.
     * array(
     *    'superglobal' => array(Obj_Variable, Obj_Variable, ...),
     *    'global' => array(),
     *    'Func#test' => array()
     * )
     * @var Obj_Variable[]
     */
    protected $variables = array();
    
    /**
     * Current scope in parsing process
     * @var string 
     */
    protected $scope = '';
    
    /**
     * List of variables that reference to a yet undefined variable.
     * @var Obj_UndefinedVariableReferences
     */
    protected $vars_referencing_to_undefined_variables;
    
    
    /**
     * Initialises an empty list of undefined variables
     */
    public function __construct() {
        $this->variables['superglobal'] = array();
        $this->variables['global'] = array();
        $this->vars_referencing_to_undefined_variables = new Obj_UndefinedVariableReferences;
    }
    
    /**
     * Set the current scope
     * @param string $scope
     */
    public function setScope($scope) {
        $this->scope = $scope;
        if(!isset($this->variables[$scope])) {
            $this->variables[$scope] = array();
        }
        
        $this->vars_referencing_to_undefined_variables->setScope($scope);
    }
    
    /**
     * Get the current scope
     * @return type
     */
    public function getScope() {
        return ($this->scope ?: 'global');
    }
    
    /**
     * Finds the variable or constant with the given name. Considers the scope.
     * @param string $name
     * @return Obj_Variable|int|false Die Var
     */
    public function find($name, $return_key = false) {
        // search in SUPERGLOBAL scope
        foreach($this->variables['superglobal'] as $key => &$var) {
            if($var->getName() == $name) 
            {
                return (!$return_key) ? $var :   array('superglobal', $key);
            }
        }
        
        // search in current scope
        foreach($this->variables[$this->getScope()] as $key => &$var) {
            if($var->getName() == $name)
            {
                return (!$return_key) ? $var :   array($this->getScope(), $key);
            }
        }
        
        // variable was not found. check if it is an array
        // (if the script asked for $_GET["test"] and it's not defined return information about $_GET)
        if(($pos = strpos($name, '[')) !== false) {
            $name = substr($name, 0, $pos);
            return $this->find($name, $return_key);
        }
        
        // variable not found
        return false;
    }
    
    /**
     * Adds a variable to the varlist or overwrites an existing one
     * @param Obj_Variable $var
     * @return bool Was pushing the variable successful?
     */
    public function push(Obj_Variable $var) {
        // if reference is specified by a string, the variable does not exist yet
        if(is_string($var->getReferenceTo())) {
            $this->vars_referencing_to_undefined_variables->setUndefinedReference($var->getName(), $var->getReferenceTo());
            $var->setReferenceTo($var);
        }
        
        // search for this variable in current scope
        $hasref = ($var->getReferenceTo() != $var);
        
        if(!$hasref) {
            $oldvar = $this->find($var->getName());
            
            // variable already exists
            if($oldvar !== false) {
                /* @var $oldvar Obj_Variable */
                $oldvar_reference = $oldvar->getReferenceTo();
                
                // existing variable has a reference
                if($oldvar != $oldvar_reference) {
                    $oldvar = $oldvar_reference;
                    $var->setName($oldvar->getName());
                }

                $oldvar->toHistory();
                $var->setHistory($oldvar->getHistory());
                $this->replace($oldvar->getName(), $var);
            }
            
            // variable does not exist yet
            else {
                $scope = ($var->isSuperGlobal()) ? 'superglobal' : $this->getScope();
                
                $this_variables_key = count($this->variables[$scope]);
                $this->variables[$scope][]  = $var;
                $var = &$this->variables[$scope][$this_variables_key];
            }
        }
        
        // Variable is refering to another one
        else {
            $refered_variable_key = $this->find($var->getReferenceTo()->getName(), true);
            
            if($refered_variable_key === false) {
                // TODO:
                ScanInfo::addWarning(Warning::ReferenceVariableUndefined, $file, $line, $node);
                return false;
            }
            
            $oldvar = $this->find($var->getName());
            
            // variable has reference > current variable does not exist yet 
            if($oldvar === false) {
                $var->setReferenceTo($this->variables[$refered_variable_key[0]][$refered_variable_key[1]]);
                $this_variables_key = count($this->variables[$refered_variable_key[0]]);
                $this->variables[$refered_variable_key[0]][]  = $var;
                $var = &$this->variables[$refered_variable_key[0]][$this_variables_key];
            }
            
            // variable has reference > current variable already exists 
            else {
                 $refered_variable = clone $this->variables[$refered_variable_key[0]][$refered_variable_key[1]];
                 $refered_variable->setReferenceTo($refered_variable);
                 $refered_variable->toHistory();
                 
                 $var->setHistory($refered_variable->getHistory());
                 $var->setReferenceTo($this->variables[$refered_variable_key[0]][$refered_variable_key[1]]);
                 $this->replace($var->getName(), $var);
                
            }
            
        }
        
        $this->solveUndefinedReferencesToThisVar($var);
        return true;
    }
    
    protected function solveUndefinedReferencesToThisVar(&$arg_var) {
        $arg_var_name = $arg_var->getName();
        $undefined_references = $this->vars_referencing_to_undefined_variables->getVariablesReferencingTo($arg_var_name);
        
        // update variables referencing to this variable
        if($undefined_references) {
            foreach($undefined_references as $var_name) {
                $variable_key = $this->find($var_name, true);
                $variable = $this->variables[$variable_key[0]][$variable_key[1]];
                $variable->setReferenceTo($arg_var);
                $this->vars_referencing_to_undefined_variables->unsetUndefinedReference($var_name);
            }
        }
    }
    
    /**
     * Remove a variable from the varlist 
     * @param string $name
     */
    public function remove($name) {
        // search for this variable in current scope
        $key = $this->find($name, true);
        $oldvar = $this->variables[$key[0]][$key[1]];
        
        if($oldvar !== false) {
            $type = $oldvar->getType();
            unset($this->variables[$key[0]][$key[1]]);
            
            // TODO: if variable is an array, delete each element
        }
    }
    
    /**
     * Replace information of a variable with another variable.
     * All references will stay untouched.
     * @param string $name
     * @param Obj_Variable $variable
     * @return &Obj_Variable
     */
    private function &replace($name, Obj_Variable $variable) {
        // search for this variable in current scope
        $key = $this->find($name, true);
        $oldvar = $this->variables[$key[0]][$key[1]];
        
        if($oldvar !== false) {
            $type = $oldvar->getType();
            $this->variables[$key[0]][$key[1]] = $variable;
            return $this->variables[$key[0]][$key[1]];
            
            // TODO: if variable is an array, delete each element
        }
    }
    
}
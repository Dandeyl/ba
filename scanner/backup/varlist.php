<?php
/**
 * Object of this class represents a variable, constant or property
 */
class Obj_Varlist {
    /**
     * List of variables initialised during parsig process
     * @var Obj_Variable[]
     */
    protected $variables = array();
    
    /**
     * Current scope in parsing process
     * @var string 
     */
    protected $scope = '';
    
    public function setScope($scope) {
        $this->scope = $scope;
    }
    
    public function getScope() {
        return $this->scope;
    }
    
    /**
     * Finds the variable or constant with the given name. Considers the scope.
     * @param string $name
     * @return Obj_Variable|int|false Die Var
     */
    public function find($name, $return_key = false) {
        foreach($this->variables as $key => &$var) {
            if($var->getName() == $name 
               && ($var->hasScope($this->getScope())
                   || $var->isSuperGlobal())
               ) 
            {
                return (!$return_key) ? $var :   $key;
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
     * @return bool Was pushing the variable successful
     */
    public function push(Obj_Variable $var) {
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
                return true;
            }
            
            // variable does not exist yet
            else {
                $this->variables[] = $var;
                return true;
            }
        }
        
        // Variable is refering to another one
        else {
            $refered_variable_key = $this->find($var->getReferenceTo()->getName(), true);
            
            if($refered_variable_key === false) {
                // TODO:
                ScanInfo::addWarning(Warning::ReferenceVariableUndefined, $file, $line, $node);
                return;
            }
            
            $oldvar = $this->find($var->getName());
            
            // variable has reference > current variable does not exist yet 
            if($oldvar === false) {
                $var->setReferenceTo($this->variables[$refered_variable_key]);
                $this->variables[] = $var;
                return true;
            }
            
            // variable has reference > current variable already exists 
            else {
                 $refered_variable = clone $this->variables[$refered_variable_key];
                 $refered_variable->setReferenceTo($refered_variable);
                 $refered_variable->toHistory();
                 
                 $var->setHistory($refered_variable->getHistory());
                 $var->setReferenceTo($this->variables[$refered_variable_key]);
                 $this->replace($var->getName(), $var);
                 return true;
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
        $oldvar = $this->variables[$key];
        
        if($oldvar !== false) {
            $type = $oldvar->getType();
            unset($this->variables[$key]);
            
            // TODO: if variable is an array, delete each element
        }
    }
    
    /**
     * Replace information of a variable with another variable.
     * All references will stay untouched.
     * @param string $name
     * @param Obj_Variable $variable
     */
    private function replace($name, Obj_Variable $variable) {
        // search for this variable in current scope
        $key = $this->find($name, true);
        $oldvar = $this->variables[$key];
        
        if($oldvar !== false) {
            $type = $oldvar->getType();
            $this->variables[$key] = $variable;
            
            // TODO: if variable is an array, delete each element
        }
    }
    
}
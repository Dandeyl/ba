<?php

class Obj_UndefinedVariableReferences {
    /**
     * array(
     *   "scope1" => array(
     *       array("name_of_variable_that_references" => "name_of_variable_that_gets_referenced")
     *   )
     * )
     * @var array 
     */
    protected $variables = array("global" => array());
    
    protected $scope = 'global';
    
    public function setScope($scope) {
       if(!is_string($scope)) {
           throw new Exception("Der angegebene Scope ist nicht gültig!");
       }
       
       if($scope == null) {
           $scope = "global";
       }
       
       if(!isset($this->variables[$scope])) {
           $this->variables[$scope] = array();
       }
       
       $this->scope = $scope;
    }
    
    
    public function getScope($scope) {
        return $this->scope;
    }
    
    /**
     * 
     * @param string $var Name of variable that is referencing. $var = &...
     * @param string $ref_var_name Name of variable that gets referenced.   ... = &$var
     */
    public function setUndefinedReference($var_name, $ref_var_name) {
        $scope = $this->scope;
        $this->variables[$scope][$var_name] = $ref_var_name;
    }
    
    public function getVariablesReferencingTo($ref_var_name) {
        $scope  = $this->scope;
        $return = array_keys($this->variables[$scope], $ref_var_name);
        if(empty($return)) {
            return false;
        }
        return $return;
    }
    
    public function getReferenceOfVariable($var_name) {
        $scope  = $this->scope;
        $return = $this->variables[$scope][$var_name];
        return isset($return) ? $return : false;
    }
    
    public function unsetUndefinedReference($var_name) {
        $scope  = $this->scope;
        unset($this->variables[$scope][$var_name]);
    }
}
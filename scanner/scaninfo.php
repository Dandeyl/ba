<?php
/**
 * This class holds all information about the scanning process like which functions,
 * methods and classes are defined, which files included, which variables declared etc.
 * 
 */
abstract class ScanInfo {
    /**
     * In which run we are at the moment.
     * @var int 
     */
    protected static $scan_run;


    /**
     * List of files that get parsed.
     * @var Obj_Filetree
     */
    protected static $filetree;
    
    /**
     * List of variables in the whole programm.
     * @var Obj_Varlist
     */
    protected static $varlist;
    
    /**
     * List of functions that were defined during parsing process
     * @var Obj_Function[] 
     */
    protected static $functions;
    
    /**
     * Name of the current scope.
     * Naming conventions:
     *  - Function:          Function#<name>
     *  - Class:                Class#<name>
     *  - Namespace:        Namespace#<name>
     *  - Method:              Method#<classname>#<methodname>
     *  - Static Method: StaticMethod#<classname>#<methodname>
     * 
     * @param string $scope
     */
    protected static $scope = '';
    
    /**
     * The controlstructure we are in at the moment. Can be an array 
     * @var Obj_ControlStructure[] 
     */
    protected static $controlstructures;
    
    /**
     * List of vulnerabilities that were found.
     * @var Obj_VulnerabilityList[] 
     */
    protected static $vulnerabilitylist;
    
    
    /**
     * Includes that were not successful.
     * @var Obj_FailedInclude[]
     */
    protected static $failed_includes;
    
    
    /**
     * Initialise scanning information
     */
    public static function init() {
        // initialise Information
        self::$scan_run = 1;
        self::$varlist = new Obj_Varlist;
        self::$vulnerabilitylist = new Obj_VulnerabilityList;
        self::$controlstructures = array();
        
        // put information in varlist
        require_once dirname(__FILE__).'/info_initialisation/variable_initialisations.php';
        
        // put information in function list
        require_once dirname(__FILE__).'/info_initialisation/function_initialisations.php';
    }
    
    
    
    /////////////////////////////////////////////
    //     ---- File related methods           //
    /////////////////////////////////////////////
    
    /**
     * Return the file tree.
     * @return Obj_Filetree
     */
    public static function getFileTree() {
        return self::$filetree;
    }
    
    /**
     * Returns an array of files that were included to get to the current node
     * @return array
     */
    public static function getCurrentFileStack() {
        return self::$filetree->getCurrentFileStack();
    }
    
    /**
     * Add a file to the file tree.
     * @param string $file
     */
    public static function addFile($file, $node) {
        if(null !== self::$filetree) {
            self::$filetree->push($file, $node);
        }
        else {
            self::$filetree = new Obj_Filetree($file);
        }
    }
    
    /**
     * Make the parent file in the file tree the current file and return it.
     * @param string $file
     * @return Obj_Filetree|null
     */
    public static function parentFile() {
        // are there paths left to go?
        try {
            return self::$filetree->parent();
        }
        catch(Obj_Filetree_EndOfTree_Exception $e) {
            return null;
        }
    }
    
    /**
     * Returns the content of the currently scanned file
     * @return string
     */
    public static function getCurrentFileContent() {
        return self::$filetree->getContents();
    }
    
    
    /**
     * Return the real path of the currently scanned file.
     * @return string
     */
    public static function getCurrentFilePath() {
        return self::$filetree->getPath();
    }
    
    public static function getCurrentFileLine() {
        return self::$filetree->getLine();
    }
    
    public static function setCurrentFileLine($line) {
        self::$filetree->setLine($line);
    }
    /**
     * Add a file to the list of failed includes.
     * @param Obj_FailedInclude $fi
     */
    public static function addFailedInclude(Obj_FailedInclude $fi) {
        self::$failed_includes[] = $fi;
    }
    
    
    
    //////////////////////////////////////////
    //   ---- variable related methods
    //////////////////////////////////////////
    
    /**
     * Get the current list of variables.
     * @return Obj_Varlist
     */
    public static function getVarlist() {
        return self::$varlist;
    }
    
    /**
     * Find a variable with the given name in the current scope.
     * @param string $name
     * @return &Obj_Variable|false
     */
    public static function findVar($name, $scope=null) {
        // looking for $GLOBALS
        if(substr($name, 0, 8) == '$GLOBALS') {
            $scope_backup = self::$varlist->getScope();
            $global_name  = substr($name, 9, -1); // name of variable in global scope $GLOBALS[test] -> $test
            
            self::$varlist->setScope("");
            $var = self::$varlist->find($global_name);
            self::$varlist->setScope($scope_backup);
            
            return $var;
        }
        // scope was set
        elseif($scope !== null) {
            $scope_backup = self::$varlist->getScope();
            self::$varlist->setScope($scope);
            $var = self::$varlist->find($name);
            self::$varlist->setScope($scope_backup);
            return $var;
        }
        
        self::$varlist->setScope(self::getScope());
        return self::$varlist->find($name);
    }
    
    /**
     * Adds a variable to the varlist.
     * @param Obj_Variable $insertvar Variable that gets registered
     * @param PHPParser_Node $node Assignment node. This is a mandatory parameter to build the history of the variable.
     * @return type
     */
    public static function addVar(Obj_Variable $insertvar) {
        $name   = $insertvar->getName();
        
        if(substr($name, 0, 8) == '$GLOBALS') {
            $scope_backup = self::$varlist->getScope();
            $global_name  = substr($name, 9, -1);
            $insertvar->setName($global_name);
            
            self::$varlist->setScope("");
            $return = self::$varlist->push($insertvar);
            self::$varlist->setScope($scope_backup);
            
            return $return;
        }
        return self::$varlist->push($insertvar);
    }
    
    
    /**
     * Adds a variable to the varlist.
     * @param string $name
     * @return type
     */
    public static function removeVar($name) {
        return self::$varlist->remove($name);
    }
    
    
    //////////////////////////////////////////
    //   ---- function related methods
    //////////////////////////////////////////
    
    /**
     * 
     * @param string $name Name of the function.
     * @param bool $return_key Should the key be returned instead of the function?
     * @return Obj_Function|int|false
     */
    public static function findFunction($name, $return_key=false) {
        foreach(self::$functions as $key => $func) {
            if($func->getName() == $name) {
                if(!$return_key) {
                    return $func;
                }
                return $key;
            }
        }
        return false;
    }
    
    /**
     * Add a function to the function list
     * @param Obj_Function $function
     */
    public static function addFunction(Obj_Function $function) {
        self::$functions[] = $function;
    }


    //////////////////////////////////////////
    //   ---- scope related methods
    //////////////////////////////////////////
    
    /**
     * Set the current scope. This method has to be called every time a function, method, or namespace is entered or left. 
     * Naming conventions:
     *  - Function: Function#<name>
     *  - Class:  Class#<name>
     *  - Namespace: Namespace#<name>
     *  - Method: Method#<classname>#<methodname>
     *  - Static Method: StaticMethod#<classname>#<methodname>
     * @param string $scope_name
     */
    public static function setScope($scope_name) {
        // Todo: Check validity
        self::$scope = $scope_name;
        self::$varlist->setScope($scope_name);
    }
    
    
    /**
     * Get the name of the current scope.
     */
    public static function getScope() {
        return self::$scope;
    }
    
    ///////////////////////////////////////////////////
    //   ---- control structure related methods
    //////////////////////////////////////////////////////
    
    
    public static function findControlStructure(Obj_ControlStructure $struct) {
        return self::findControlStructureByNode($struct->getNode());
    }
    
    /**
     * Find a control structure using the STMT node.
     * @param PHPParser_Node $node
     * @return Obj_ControlStructure
     */
    public static function findControlStructureByNode(PHPParser_Node $node) {
        $registered_structs = self::$controlstructures;
        
        foreach($registered_structs as &$r_struct) {
            if(Helper_NodeEquals::equals($r_struct->getNode(), $node)) {
                return $r_struct;
            }
        }
    }  
            
    public static function addControlStructure(Obj_ControlStructure $struct) {
        return self::$controlstructures[] = $struct;
    }
    
    public static function &getControlStructures() {
        return self::$controlstructures;
    }
    
    /**
     * Removes the last control structure
     * @return Obj_ControlStructure The Last control structure element
     */
    public static function removeLastControlStructure() {
        return array_pop(self::$controlstructures);
    }
    
    //////////////////////////////////////////
    //   ---- vulnerability related methods
    //////////////////////////////////////////
    
    /**
     * Adds a detected vulnerability to the list of found vulnerabilities.
     * @param int $type
     * @param string $file
     * @param int $line
     * @param PHPParser_Node $node
     */
    public static function addVulnerability($type, $node) {
        self::$vulnerabilitylist->addVulnerability(1, $type, $node, self::getCurrentFilePath(), self::getCurrentFileLine());
    }
    
    public static function getNumVulnerabilities() {
        return self::$vulnerabilitylist->getNumVulnerabilities();
    }
    
    /**
     * Adds a warning to the scaninfo. Warnings can lead to a vulnerability
     * @param int $type
     * @param string $file
     * @param int $line
     * @param PHPParser_Node $node
     */
    public static function addWarning($type, PHPParser_Node $node) {
       self::$vulnerabilitylist->addWarning(1, $type, $node, self::getCurrentFilePath(), self::getCurrentFileLine());
    }
    
    
    
    
    
    
    
    public static function addNotFoundClass($name) {
        
    }
    
    /**
     * 
     * @param type $name
     */
    public static function addNotFoundFunction($name) {
        
    }
    
    /**
     * Export the information we got in our scan process so far. If the optional parameter
     * $full is set to true all data will be exported, otherwise just data needed for file-walking.
     * @param bool $full
     * @return string
     */
    public static function exportScanData() {
        $exp["vars"]  = self::$varlist;
        $exp["funcs"] = self::$functions;
        $exp["files"] = self::$filetree;
        $exp["scope"] = self::$scope;
        $exp["scan_run"] = self::$scan_run;
        
        
        return serialize($exp);
    }
    
    public static function exportVulnerabilityList() {
        return serialize(self::$vulnerabilitylist);
    }
    
    public static function importScanData($export) {
        $export = unserialize($export);
        self::$varlist = $export["vars"];
        self::$functions = $export["funcs"];
        self::$filetree = $export["files"];
        self::$scope = $export["scope"];
        self::$scan_run = $export["scan_run"];
    }
    
    
    /**
     * Dump all scan results
     */
    public static function dump() {
        echo "\n\n\n############### SCAN RESULTATE ###################\n\n";
        echo 'Kritische Vulnerabilities gefunden: '.self::$vulnerabilitylist->getNumVulnerabilities()."\n";
        echo 'Warnings gefunden: '.self::$vulnerabilitylist->getNumWarnings()."\n";
        echo 'Informationen gefunden: '.self::$vulnerabilitylist->getNumInformation()."\n";
        echo "\n\n";
        echo "### Vulnerabilities:\n\n";
        
        $vuln = self::$vulnerabilitylist->getVulnerabilities();
        $i = 1;
        foreach($vuln as $v) {
            echo $i." -----------------------\n";
            echo "Typ: ".$v->getType()."   \n";
            echo "Datei: ".$v->getFile()."  \n";
            echo "Zeile: ".$v->getLine()."  \n";
            echo "> ".Scanner::printNode($v->getNode());
            
            echo "\n\n\n";
            $i++;
        }
        
        echo "\n\nMemory used: ".memory_get_usage(true)."\n";
    }
}
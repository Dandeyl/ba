<?php
/**
 * This class contains information about the scanning process, like which files
 * are checked, which scan-run are we in at the moment^, which functions, classes
 * and variables are known etc.
 * 
 * ^ If a function or method is called that is unknown at runtime but declared later,
 *   all files will be scanned again.
 * 
 */
abstract class Scanner {
    /////////////////////////////////////////////////////////////////////////
    /////// Properties
    ////////////////////////////////////////////////////////////////////////
    /**
     *
     * @var PHPParser_Parser 
     */
    protected static $parser;
    
    /**
     *
     * @var PHPParser_PrettyPrinter
     */
    protected static $printer;
    
    /**
     * 
     * @var PHPParser_NodeTraverser
     */
    protected static $traverser;
        
    /**
     *
     * @var PHPParser_NodeDumper 
     */
    protected static $dumper;
    
    /**
     * Exportdata using for forks when control structures are reached.
     * @var array
     */
    protected static $exportdata;
    
    /**
     * Are we currently exiting the scanning process?
     * @var bool 
     */
    protected static $state_exiting = false;
    
    /**
     * Are we walking to a specific node without scanning the other nodes?
     * @var bool 
     */
    protected static $state_walking = false;
    
    /**
     * Scan ended completely
     * @var bool 
     */
    protected static $state_endofscan = false;
    
    
    
    
    /////////////////////////////////////////////////////////////////////////
    /////// Methods
    ////////////////////////////////////////////////////////////////////////
    
    //--- Initialisation ------------------------------------------------------
    /**
     * Autoloading for scanner files and node visitors.
     * @param string $class_name
     */
    private static function scanner_autoload($class_name) {
        switch($class_name) {
            case 'Warning':
                require(dirname(__FILE__).'/definitions/warning.php');
                break;
            case 'Assignment':
                require(dirname(__FILE__).'/definitions/assignment.php');
                break;
            case 'Securing':
                require(dirname(__FILE__).'/definitions/securing.php');
                break;
            case 'Attack':
                require(dirname(__FILE__).'/definitions/attack.php');
                break;
            case 'PHPParser_NodeVisitorAbstract':
                require(dirname(__FILE__).'/../parser/PHPParser/NodeVisitorAbstract.php');
                break;
        }
        
        if(substr($class_name, 0, 12) == 'NodeVisitor_') {
            include(dirname(__FILE__).'/nodevisitors/'.strtolower(substr($class_name, 12)).'.php');
        }
        elseif(substr($class_name, 0, 7) == 'Action_') {
            include(dirname(__FILE__).'/actions/'.strtolower(substr($class_name, 7)).'.php');
        }
        elseif(substr($class_name, 0, 7) == 'Attack_') {
            include(dirname(__FILE__).'/attacks/'.strtolower(substr($class_name, 7)).'.php');
        }
        elseif(substr($class_name, 0, 7) == 'Helper_') {
            include(dirname(__FILE__).'/helpers/'.strtolower(substr($class_name, 7)).'.php');
        }
        elseif(substr($class_name, 0, 4) == 'Obj_') {
            include(dirname(__FILE__).'/objects/'.strtolower(substr($class_name, 4)).'.php');
        }
    }
    
    /**
     * Register autoloader, initialise parser.
     */
    public static function init() {
        // -- autoloader
        spl_autoload_register(array("self", "scanner_autoload"));
        include(dirname(__FILE__).'/initialisation/function_replacements/functions.php');
        
        // -- parser
        self::$parser   = new PHPParser_Parser(new PHPParser_Lexer);
        self::$printer  = new PHPParser_PrettyPrinter_Zend;
        self::$dumper   = new PHPParser_NodeDumper;
        
        $traverser = new PHPParser_NodeTraverser;
        $traverser->addVisitor(new NodeVisitor_FileAttributeSetter);
        $traverser->addVisitor(new NodeVisitor_QualifiedNameResolver);
        $traverser->addVisitor(new NodeVisitor_LanguageConstructFunctionRenamer());
        
        //$traverser->addVisitor(new NodeVisitor_Scope);
        //$traverser->addVisitor(new NodeVisitor_Include);
        //$traverser->addVisitor(new NodeVisitor_Function);
        //$traverser->addVisitor(new NodeVisitor_FuncCall);
        $traverser->addVisitor(new NodeVisitor_ActionChooser);
        //$traverser->addVisitor(new NodeVisitor_Exit);
        //$traverser->addVisitor(new NodeVisitor_Assignments);
        //$traverser->addVisitor(new NodeVisitor_ControlStructure);
        
        
        self::$traverser = $traverser;
        
        self::registerSubscribers();
               
        // initialise scan information
        ScanInfo::init();
    }
    
    /**
     * Are we currently just walking to a node without scanning.
     * @return type
     */
    public static function isWalking() {
        return self::$state_walking;
    }
    
    /**
     * Are we currently exiting this scanning run?
     * @return type
     */
    public static function isExiting() {
        return self::$state_exiting;
    }
    
    private static function registerSubscribers() {
        /**
         * File got scanned to the end. If we're in "EXITING-State" do nothing,
         * else reactivate all NodeVisitors.
         */
        subscribe('endScanFile', function() {
            ScanInfo::parentFile();
        });
        
        
        // End of this scanning iteration reached, check if there are controlstructure paths left
        subscribe('endOfRun', function() {
            self::$state_exiting = false;
            self::$state_walking = false;
            
            $control_structs = &ScanInfo::getControlStructures();
            if(!empty($control_structs)) {
                // get last control structure
                /* @var $struct Obj_Controlstructure */
                $struct = null;
                while($struct == null) {
                    $struct = end($control_structs);
                    // if last path of structure has been gone in this iteration, delete this
                    // controlstructure and go to next higher level structure
                    if($struct->getCurrentPathNumber() == $struct->getNumPaths()-1) {
                        ScanInfo::removeLastControlStructure();
                        $struct = null;
                    }
                    else {
                        $struct->incCurrentPathNumber();
                    }
                    // no structures anymore
                    if(empty($control_structs)) {
                        break;
                    }
                }

                if($struct) {
                    $skipwalker = new NodeVisitor_WalkTo($struct->getNode(), $struct->getFileStack());
                    $tree = ScanInfo::getFileTree();
                    $file = $tree::getMainFile()->getPath();
                    // start scanning from beginning with NodeVisitor_WalkTo enabled -> walks to last found controlstructure
                    Scanner::walkFile($file, $skipwalker);
                }
            }
            
            if(empty($control_structs)) {
                $warnings = ScanInfo::getVulnerabilityList();
                self::$state_endofscan = true;
            }
        });
    }
    
    
    // --- Scanning methods ---------------------------------------------------------------
    /**
     * Get the real location of the file that shall be included.
     * This method uses get_include_path() to detect the file using the include paths
     * @param string $file
     * @return string|false
     */
    protected static function getFileLocation($file) {
        if(!is_string($file)) return false;
        
        $include_paths = explode(PATH_SEPARATOR, get_include_path());
        
        $filetree = ScanInfo::getFileTree();
        if($filetree) {
            chdir(dirname($filetree->getPath()));
        }
        
        $file_path = DIRECTORY_SEPARATOR. $file;
        
        foreach($include_paths as $inc_path) {
            $full_path = realpath($inc_path.$file_path);
            if(file_exists($full_path)) {
                return $full_path;
            }
            
        }
        
        // file not found
        return false;
    }
    
    
    
    
    
    /**
     * Starts the scanning process. Loops scanning while end of scan is not reached.
     * @param type $start_file
     * @return boolean
     */
    public static function startScan($start_file) {
        // get real file path
        if(!$file = self::getFileLocation($start_file)) {
            return false;
        }
        // add file to filetree
        ScanInfo::addFile($file, null);
        
        do {
            // start scanning first file
            self::scanFile();
            fire('endOfRun');
        } while(!self::$state_endofscan);
        
        // scan complete
        fire('endOfScan');
    }
    
    
    
    /**
     * Does not scan the file if it has been scanned already. Needed for include_once()
     * and require_once().
     * @param type $rel_file
     * @param type $node
     */
    public static function scanFileOnce($rel_file, $node) {
        //$filetree = ScanInfo::getFileTree();
        
    }
    
    /**
     * Start scanning a new file. This method:
     *  - detects the full path to the file tree
     *  - writes the new file to the file tree
     *  - parses the file
     *  - traverses the nodes
     * @param string $rel_file File to be included
     * @param PHPParser_Node $node Node of the include or require
     */
    public static function scanFile($rel_file=null, $node=null) {
        // get real file path
        if($rel_file) {
            if(!$file = self::getFileLocation($rel_file)) {
                ScanInfo::addFailedInclude(new Obj_FailedInclude($node)  );
                return false;
            }

            // add file to filetree
            ScanInfo::addFile($file, $node);
        }

        $parser = self::$parser;
        $nodeDumper = self::$dumper;
        $file = ScanInfo::getCurrentFilePath();
        
        // execute parsing and traversing
        try {
            // parse
            fire('beginParseFile', array($file));
            $stmts = $parser->parse(ScanInfo::getCurrentFileContent());
            fire('endParseFile', array($file, $stmts));


            // just dump the parsed file
            if(SCANNER_DUMP_TREE) {
                echo ($nodeDumper->dump($stmts))."\n\n\n";
            }
            else {
                // Scan File
                fire('beginScanFile', array($file));
                self::$traverser->traverse($stmts);
                fire('endScanFile', array($file));

            }
        } 
        catch (PHPParser_Error $e) {
            fire('parseError', array($file, $e->getMessage()));
        }
    }
    
    /**
     * This method gets called when return, exit or die get called in the function
     */
    public static function scanFileEnd($exit=false) {
        // Has not finished yet ("return", "die" or "exit" called) 
        self::$state_exiting = $exit ? true : false;
    }
    
    
    
    /**
     * Walk to a given node in the file without scanning the nodes until this node is set
     * @param string $file Path to the file that gets scanned
     * @param NodeVisitor_WalkTo From which node the scanning process shall be started.
     */
    public static function walkFile($file, NodeVisitor_WalkTo $nodevisitor) {
        self::walkFileEnd(); // Delete all existing NodeVisitor_WalkTos
        self::$state_walking = true;
        self::$traverser->addVisitor($nodevisitor);
        //$traverser->setActiveVisitors(array($idx, 0));
    }
    
    /**
     * This method gets called after the node we walked to was found. Deletes all NodeVisitor_WalkTo
     * and re-enable all nodevisitors.
     */
    public static function walkFileEnd() {
        self::$state_walking = false;
        self::$traverser->removeVisitor('NodeVisitor_WalkTo');
    }
    
    /**
     * Scan a given set of statements, i.e. to analyse functions or classes
     * @param string $identifier
     * @param array $stmts
     */
    public static function scanStatements($identifier, array $stmts) {
        
    }
    
    public static function scanStatementsEnd() {
        
    }
    
    
    // ---------------- Other methods/ helpers -----------------------------
    /**
     * Converts a PHParser_Node into prettified PHP code.
     * @param PHPParser_Node_Expr $node
     * @return string
     */
    public static function prettyPrintExpr(PHPParser_Node_Expr $node) {
        return self::$printer->prettyPrintExpr($node);
    }
    
    public static function printNode(PHPParser_Node $node) {
        return self::$printer->p($node);
    }

    /**
    * Returns a securified relative url path. File protocols and back paths (../) get removed.
    * @param type $url
    * @return type
    */
    public static function secureurl($url) {
        $i = 0;
        // remove chars that shouldnt be in a url
        $path = preg_replace('#[^[:alnum:]äöüß_+? .%/!\#()\-]#iu', '', $url);
        // remove http:// and ../
        while (($tmp = preg_replace('/(^|\/)((https?|ftp|mailto):\/\/|\.\.\/|\.\/)/isu', '/', $path)) != $path)
            $path = $tmp;
        while (@($path[$i++] == '/'));
        return trim(substr($path, ($i - 1)));
    }
}
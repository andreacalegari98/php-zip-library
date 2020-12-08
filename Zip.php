<?php 

class Zip {

    #region Properties

        // The FULL path with filename of the result zip file
        private $__zip_filepath;

        // The files to insert into zip
        private $__uploaded_files;

    #endregion

    #region Constructor-Destructors

        /** Constructor
         * 
         * @param $zip_result_path - The FULL path with filename of the result zip file
         */
        public function __construct($zip_result_path){
            $this->initProperties();

            // Set the filepath
            $this->__zip_filepath = $zip_result_path;
        }

        public function __destruct() {
        }

        /** Init all properties */
        private function initProperties() {
            $this->__zip_filepath = null;
            $this->__uploaded_files = array();
        }

    #endregion

    #region Public Methods

        /** Insert a file path or a whole directory
         * 
         * @param $input file path or directory path
         */
        public function add($input) {
            $this->checkMandatoryProperties();
            
            // If not exists, try to concat the doc root
            if(!file_exists($input))
                $input = str_replace("//", "/", $_SERVER["DOCUMENT_ROOT"] . "/" . $input);

            // If path exists
            if(file_exists($input)) {
                // Check if input is a file
                if(is_file($input)) {
                    // Push into uploaded
                    array_push($this->__uploaded_files, $input);
                }

                // Check if input is a dir
                elseif(is_dir($input)) {
                    // Get all dir files
                    $files = glob($input . "/*");

                    // Remove useless paths
                    if (($key = array_search(".", $files)) !== false) { unset($files[$key]); }
                    if (($key = array_search("..", $files)) !== false) { unset($files[$key]); }
                    if (($key = array_search(".DS_Store", $files)) !== false) { unset($files[$key]); }

                    // Reset array keys
                    $files = array_values($files);

                    // Push into uploaded
                    $this->__uploaded_files = array_merge($this->__uploaded_files, $files);
                }
            }
        }

        /** Generate the zip file */
        public function generate() {
            $this->checkMandatoryProperties(true);

            // Require the lib
            require_once "pclzip.lib.php";

            // Init PHP ZIP
            if(!$lib = new PclZip($this->__zip_filepath)) throw new Exception('Permission Denied or zlib can\'t be found');
            
			// Add file to zip using the PCLZIP
            $lib->add($this->__uploaded_files, PCLZIP_OPT_REMOVE_PATH, $this->getCommonPath());
        }

        /** Unzip file into folder
         * 
         * @param $target_dir the destination folder where unzip the file
        */
        public function unzip($target_dir) {

            // It exists, but it's not a directory
            if(file_exists($target_dir) && (!is_dir($target_dir))) throw new Exception("Target directory exists as a file not a directory");

            // It doesn't exist
            if(!file_exists($target_dir)) if(!mkdir($target_dir)) throw new Exception("Directory not found, and unable to create it");

            // Require the lib
            require_once "pclzip.lib.php";
            
            // Init PHP ZIP
            if(!$lib = new PclZip($this->__zip_filepath)) throw new Exception('Permission Denied or zlib can\'t be found');

            // Extract 
            if(!$lib->extract(PCLZIP_OPT_PATH, $target_dir)) throw new Exception("Unable to extract files");   
        }

    #endregion

    #region Private Methods

        /** Get the common path from the uploaded files
         * 
         * @return string the common path between files
         */
        private function getCommonPath() {
            $ret = null;
            $files = $this->__uploaded_files;

            // Only one file
            if(count($files) == 1) {
                // Remove the last part and get only the path
                $exp = explode("/", $files);
                array_pop($exp);                
            }
            // More files
            elseif(count($files) > 1) {
                $arrays = array();
                
                // Explode all files
                foreach ($files as $key => $file) {
                    array_push($arrays, explode("/", $file));
                }

                // Get intersect 
                $exp = call_user_func_array('array_intersect', $arrays);
            }

            // Implode and return
            return implode("/", $exp);
        }

        /** Check is all the mandatory properties are not null
         * 
         * @param $check_files if true check if there are files
         * 
         * @return Exception unique for all the errors
         */
        private function checkMandatoryProperties($check_files = false) {
            $errs = array();

            // Check file path
            if($this->IsNullOrEmpty($this->__zip_filepath))
                array_push($errs, "Result file path not insered");

            // Check files
            if($check_files && $this->IsNullOrEmpty($this->__uploaded_files))
                array_push($errs, "There's not file to insert into zip");

            if(count($errs) > 0) {
                $message = implode("\n", $errs);
                throw new Exception($message, 1);
            }
        }


        /** Check If variable is:
         * - string => null or empty
         * - array => without elements
         * - object => without properties
         * 
         * @return true if null or empty
         */
        private function IsNullOrEmpty($to_check) {
            if(is_null($to_check)) {
                return true;
            }
            elseif(is_int($to_check)) {
                return false;
            }
            elseif(is_string($to_check)) {
                $to_check = strip_tags(html_entity_decode($to_check));
                $to_check = preg_replace('/\s/', '', $to_check);
                return ($to_check == null || $to_check == "");
            }
            elseif(is_array($to_check)) {
                return (count($to_check) == 0);
            }
            elseif(is_object($to_check)) {
                return (count((array)$to_check) == 0);
            }
            elseif(is_bool($to_check)) {
                return false;
            }
            else {
                return ($to_check != null);
            }
        }
    #endregion

}



?>
<?php
    set_time_limit(0);
    error_reporting(E_ALL);
    ini_set('display_errors', true);
    
    /**
    
     * Save MySQL
    
     */
    
    class BackupMySQL extends mysqli {
    
      
    
      /**
    
       * folder the files to save
    
       * @var string
    
       */
    
      protected $dossier;
    
      
    
      /**
    
       * name file
    
       * @var string
    
       */
    
      protected $nom_fichier;
    
      
    
      /**
    
       * Ressource file GZip
    
       * @var ressource
    
       */
    
      protected $gz_fichier;
    
      
    
      
    
      /**
    
       * constructor
    
       * @param array $options
    
       */
    
      public function __construct($options = array()) {
    
        $default = array(
    
          'host' => ini_get('mysqli.default_host'),
    
          'username' => ini_get('mysqli.default_user'),
    
          'passwd' => ini_get('mysqli.default_pw'),
    
          'dbname' => '',
    
          'port' => ini_get('mysqli.default_port'),
    
          'socket' => ini_get('mysqli.default_socket'),
    
          // other options
    
          'dossier' => 'backupSQL/',
    
          'nbr_fichiers' => 5,
    
          'nom_fichier' => 'backup'
    
          );
    
        $options = array_merge($default, $options);
    
        extract($options);
    
        
    
        // Connexion DB
    
        @parent::__construct($host, $username, $passwd, $dbname, $port, $socket);
    
        if($this->connect_error) {
    
         // $this->message('connexion Error (' . $this->connect_errno . ') '. $this->connect_error);
    
          return;
    
        }
    
        
    
        // Controle the folder
    
        $this->dossier = $dossier;
    
        if(!is_dir($this->dossier)) {
    
          //$this->message('Folder Error &quot;' . htmlspecialchars($this->dossier) . '&quot;');
    
          return;
    
        }
    
        
    
        // Controle the file
    
        $this->nom_fichier = $nom_fichier . date('Ymd-His') . '.sql.gz';
    
        $this->gz_fichier = @gzopen($this->dossier . $this->nom_fichier, 'w');
    
        if(!$this->gz_fichier) {
    
          //$this->message('Files Error &quot;' . htmlspecialchars($this->nom_fichier) . '&quot;');
    
          return;
    
        }
    
            
        $this->sauvegarder();
    
        $this->purger_fichiers($nbr_fichiers);
    
      }
    
      
    
      /**
        
       * @param string $message HTML
    
       */
    
      protected function message($message = '&nbsp;') {
    
        echo '<p style="padding:0; margin:1px 10px; font-family:sans-serif;">'. $message .'</p>';
    
      }
    
      
    
      /**
        
       * @param string $string
    
       * @return string
    
       */
    
      protected function insert_clean($string) {
    
        // not change the order of tables !!!
    
        $s1 = array( "\\"	, "'"	, "\r", "\n", );
    
        $s2 = array( "\\\\"	, "''"	, '\r', '\n', );
    
        return str_replace($s1, $s2, $string);
    
      }
    
      
    
      /**
    
       * save tables
    
       */
    
      protected function sauvegarder() {
    
       // $this->message('Save...');
    
        
    
        $sql  = '--' ."\n";
    
        $sql .= '-- '. $this->nom_fichier ."\n";
    
        gzwrite($this->gz_fichier, $sql);
    
            
        $result_tables = $this->query('SHOW TABLE STATUS');
    
        if($result_tables && $result_tables->num_rows) {
    
          while($obj_table = $result_tables->fetch_object()) {
    
           // $this->message('- ' . htmlspecialchars($obj_table->{'Name'}));
    
            
    
            // DROP ...
    
            $sql  = "\n\n";
    
            $sql .= 'DROP TABLE IF EXISTS `'. $obj_table->{'Name'} .'`' .";\n";
    
    
    
            // CREATE ...
    
            $result_create = $this->query('SHOW CREATE TABLE `'. $obj_table->{'Name'} .'`');
    
            if($result_create && $result_create->num_rows) {
    
              $obj_create = $result_create->fetch_object();
    
              $sql .= $obj_create->{'Create Table'} .";\n";
    
              $result_create->free_result();
    
            }
    
    
    
            // INSERT ...
    
            $result_insert = $this->query('SELECT * FROM `'. $obj_table->{'Name'} .'`');
    
            if($result_insert && $result_insert->num_rows) {
    
              $sql .= "\n";
    
              while($obj_insert = $result_insert->fetch_object()) {
    
                $virgule = false;
    
                
    
                $sql .= 'INSERT INTO `'. $obj_table->{'Name'} .'` VALUES (';
    
                foreach($obj_insert as $val) {
    
                  $sql .= ($virgule ? ',' : '');
    
                  if(is_null($val)) {
    
                    $sql .= 'NULL';
    
                  } else {
    
                    $sql .= '\''. $this->insert_clean($val) . '\'';
    
                  }
    
                  $virgule = true;
    
                } // for
    
                
    
                $sql .= ')' .";\n";
    
                
    
              } // while
    
              $result_insert->free_result();
    
            }
    
            
    
            gzwrite($this->gz_fichier, $sql);
    
          } // while
    
          $result_tables->free_result();
    
        }
    
        gzclose($this->gz_fichier);
    
       // $this->message('<strong style="color:green;">' . htmlspecialchars($this->nom_fichier) . '</strong>');
    
        
    
      //  $this->message('Save termin&eacute;e !');
    
        
    
        //header("Location: http://cafe-latasse.com/".htmlspecialchars($this->nom_fichier)."");
    
      }
    
      
    
      /**
    
       * Purge old files
    
       * @param int $nbr_fichiers_max Number max of saves
    
       */
    
      protected function purger_fichiers($nbr_fichiers_max) {
    
       // $this->message();
    
       // $this->message('Purge old files...');
    
        $fichiers = array();
    
        
    
        // On get the name of the files gz
    
        if($dossier = dir($this->dossier)) {
    
          while(false !== ($fichier = $dossier->read())) {
    
            if($fichier != '.' && $fichier != '..') {
    
              if(is_dir($this->dossier . $fichier)) {
    
                // its a folder ( not a file )
    
                continue;
    
              } else {
    
                // we can just get the files last with ".gz"
    
                if(preg_match('/\.gz$/i', $fichier)) {
    
                  $fichiers[] = $fichier;
    
                }
    
              }
    
            }
    
          } // while
    
          $dossier->close();
    
        }
    
        
    
        // we delete the old files
    
        $nbr_fichiers_total = count($fichiers);
    
        if($nbr_fichiers_total >= $nbr_fichiers_max) {
    
          // Invers the order of files gz for not delete the last files
    
          rsort($fichiers);
    
          
    
          // Delete...
    
          for($i = $nbr_fichiers_max; $i < $nbr_fichiers_total; $i++) {
    
            // $this->message('<strong style="color:red;">' . htmlspecialchars($fichiers[$i]) . '</strong>');
    
            unlink($this->dossier . $fichiers[$i]);
    
          }
    
        }
    
        //$this->message('Purge termin&eacute;e !');
    
      }
    
      
    
    }



    
    // Class Instance ( to copy as much as necessary, but beware of the timeout )
    
    // Rq: for the parameters, take one or more keys from $default ( in the method __construct() )
    
    new BackupMySQL(array(
    
      'username' => 'root',
    
      'passwd' => '',
    
      'dbname' => 'newdev'
    
      ));
    
    
    
    //new BackupMySQL(array(
    
    //	'username' => 'root',
    
    //	'passwd' => 'root',
    
    //	'dbname' => 'mabase',
    
    //	'dossier' => './dossier2/'
    
    //	));

?>
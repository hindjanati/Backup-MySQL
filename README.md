# Backup-MySQL
==========================

This PHP file/class allows you to create compressed rolling backup sets of your MySQL databases whene the first login done.
<br> 
It will send a mail to your email attached with the names of the files that exist and inform you that notifies you that someone is login at the same moment
<br>
Files returned in GZip format are perfectly reusable in phpMyAdmin (or others).
This method does not require root or administrator access to your server.
You just need to have read/write rights on the database and in the storage space where you are going to save your files.

some of things you need to change
---------------------

```php
new BackupMySQL(array(
	'host' => 'localhost',
	'username' => 'root',
	'passwd' => 'your password',
	'dbname' => 'dbName'
	));
```

```php
$default = array(
  'nbr_fichiers' => 5, // <--------- How much files you want to stock
  ); 
 ```
 
 
 ```php
 foreach (new DirectoryIterator('backupSQL') as $fileInfo) { // <---- the name of the folder you want to stock the files in
    if($fileInfo->isDot()) continue;
    $namef .= $fileInfo->getFilename() . "<br>\n"; // <---- stock the name of files to an sting 
  }
  ```
``` 
  $name = "name"; 
  $to = "email received";  
  $subject = "subject";
  $body = "your message";
  $from = "your email";
  $password = "password";   
 ```
 
 ``` 
    $con = mysqli_connect('localhost', 'root', '', 'bdName'); <------ your database name
 ```

Options
-------

| Key | Description | Type | Default |
|------|-------------|------|---------|
| `host` | Server host MySQL | string | `ini_get('mysqli.default_host')` |
| `username` | Connection identifier | string | `ini_get('mysqli.default_user')` |
| `passwd` | Login Password | string | `ini_get('mysqli.default_pw')` |
| `dbname` | Name of the data base | string | `''` |
| `port` | connection port | string | `ini_get('mysqli.default_port')` |
| `socket` | Socket | string | `ini_get('mysqli.default_socket')` |
| `dossier` | Folder containing GZip archives | string | `'./'` |
| `nbr_fichiers` | Number of backups to keep | integer | `5` |
| `nom_fichier` | File name prefix for backup | string | `backup` |


To send an email from PHP . The solution is here :  https://github.com/PHPMailer/PHPMailer/

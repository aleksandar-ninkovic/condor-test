<?php

$params = [];
$required = ['user', 'password', 'database', 'filename'];
parse_str(implode('&', array_slice($argv, 1)), $params);
$missing = [];
foreach ($required as $param) {
    if(!isset($params[$param]) || $params[$param] == ''){
        $missing[] = $param;
    }
}

if (count($missing)) {
    echo "Missing required parameters: " . implode(', ', $missing) . "\n";
    exit;
}

$user = $params['user'];
$password = $params['password'];
$database = $params['database'];
$filename = $params['filename'];

$result = exec("mysqldump $database --password=$password --user=$user --single-transaction >$filename",$output);

if(!empty($output)) {
    echo "An error occurred during backup! Command output: \n";
    echo $output;
} else {
    echo "Database backup DONE\n";
}

echo "Compressing the backup file to $filename.tar.gz ...\n";

try
{
    if (file_exists($filename . '.tar.gz')) {
        unlink($filename . '.tar.gz');
    }
    $archive = new PharData($filename . '.tar');
    $archive->addFile($filename);
    $archive->compress(Phar::GZ);
    unlink($filename . '.tar');
    unlink($filename);
}
catch (Exception $e)
{
    echo "Compression operation failed! : " . $e . "\n";
    die();
}
echo "Backup file successfully compressed\n";
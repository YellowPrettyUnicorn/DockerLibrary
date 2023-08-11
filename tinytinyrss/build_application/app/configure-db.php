#!/usr/bin/env php
<?php

$confpath = '/var/www/config.php';

$config_replace = array();
$config_add = array();

// path to ttrss
$config_replace['SELF_URL_PATH'] = env('SELF_URL_PATH', 'http://localhost');

if (env_defined('_SKIP_SELF_URL_PATH_CHECKS')) {
    $config_add['_SKIP_SELF_URL_PATH_CHECKS'] = true;
}

// cookie lifetime
if (env_defined('SESSION_COOKIE_LIFETIME')) {
    $config_replace['SESSION_COOKIE_LIFETIME'] = getenv('SESSION_COOKIE_LIFETIME');
}

if (env_defined('PLUGINS')) {
    $config_replace['PLUGINS'] = getenv('PLUGINS');
}

// database configuration
if (getenv('DB_TYPE') !== false) {
    $config_replace['DB_TYPE'] = getenv('DB_TYPE');
} elseif (getenv('DB_PORT_5432_TCP_ADDR') !== false) {
    // postgres container linked
    $config_replace['DB_TYPE'] = 'pgsql';
    $eport = 5432;
} elseif (getenv('DB_PORT_3306_TCP_ADDR') !== false) {
    // mysql container linked
    $config_replace['DB_TYPE'] = 'mysql';
    $eport = 3306;
}

if (!empty($eport)) {
    $config_replace['DB_HOST'] = env('DB_PORT_' . $eport . '_TCP_ADDR');
    $config_replace['DB_PORT'] = env('DB_PORT_' . $eport . '_TCP_PORT');
} elseif (getenv('DB_PORT') === false) {
    error('The env DB_PORT does not exist. Make sure to run with "--link mypostgresinstance:DB"');
} elseif (is_numeric(getenv('DB_PORT')) && getenv('DB_HOST') !== false) {
    // numeric DB_PORT provided; assume port number passed directly
    $config_replace['DB_HOST'] = env('DB_HOST');
    $config_replace['DB_PORT'] = env('DB_PORT');

    if (empty($config_replace['DB_TYPE'])) {
        switch ($config_replace['DB_PORT']) {
            case 3306:
                $config_replace['DB_TYPE'] = 'mysql';
                break;
            case 5432:
                $config_replace['DB_TYPE'] = 'pgsql';
                break;
            default:
                error('Database on non-standard port ' . $config_replace['DB_PORT'] . ' and env DB_TYPE not present');
        }
    }
}

// database credentials for this instance
//   database name (DB_NAME) can be supplied or detaults to "ttrss"
//   database user (DB_USER) can be supplied or defaults to database name
//   database pass (DB_PASS) can be supplied or defaults to database user
$config_replace['DB_NAME'] = env('DB_NAME', 'ttrss');
$config_replace['DB_USER'] = env('DB_USER', $config_replace['DB_NAME']);
$config_replace['DB_PASS'] = env('DB_PASS', $config_replace['DB_USER']);

if (!dbcheck($config_replace)) {
    echo 'Database login failed, trying to create...' . PHP_EOL;
    // superuser account to create new database and corresponding user account
    //   username (SU_USER) can be supplied or defaults to "docker"
    //   password (SU_PASS) can be supplied or defaults to username

    $super = $config_replace;

    $super['DB_NAME'] = null;
    $super['DB_USER'] = env('DB_ENV_USER', 'docker');
    $super['DB_PASS'] = env('DB_ENV_PASS', $super['DB_USER']);
    
    $pdo = dbconnect($super);

    if ($super['DB_TYPE'] === 'mysql') {
        $pdo->exec('CREATE DATABASE ' . ($config_replace['DB_NAME']));
        $pdo->exec('GRANT ALL PRIVILEGES ON ' . ($config_replace['DB_NAME']) . '.* TO ' . $pdo->quote($config_replace['DB_USER']) . '@"%" IDENTIFIED BY ' . $pdo->quote($config_replace['DB_PASS']));
    } else {
        $pdo->exec('CREATE ROLE ' . ($config_replace['DB_USER']) . ' WITH LOGIN PASSWORD ' . $pdo->quote($config_replace['DB_PASS']));
        $pdo->exec('CREATE DATABASE ' . ($config_replace['DB_NAME']) . ' WITH OWNER ' . ($config_replace['DB_USER']));
    }

    unset($pdo);
    
    if (dbcheck($config_replace)) {
        echo 'Database login created and confirmed' . PHP_EOL;
    } else {
        error('Database login failed, trying to create login failed as well');
    }
}

$pdo = dbconnect($config_replace);
try {
    $pdo->query('SELECT 1 FROM ttrss_feeds');
    // reached this point => table found, assume db is complete
}
catch (PDOException $e) {
    echo 'Database table not found, applying schema... ' . PHP_EOL;
    $schema = file_get_contents('schema/ttrss_schema_' . $config_replace['DB_TYPE'] . '.sql');
    $schema = preg_replace('/--(.*?);/', '', $schema);
    $schema = preg_replace('/[\r\n]/', ' ', $schema);
    $schema = trim($schema, ' ;');
    foreach (explode(';', $schema) as $stm) {
        $pdo->exec($stm);
    }
    unset($pdo);
}

$contents = file_get_contents($confpath);
foreach ($config_replace as $name => $value) {
    $contents = preg_replace('/(define\s*\(\'' . $name . '\',\s*)(.*)(\);)/', '$1"' . $value . '"$3', $contents);
}

$contents .= "\n\n" . "// Added user specific defines" . "\n\n";
foreach ($config_add as $name => $value) {
    if (is_string($value)) {
        $value = "'$value'";
    }

    $contents .= "define('$name', $value);" . "\n";
}

file_put_contents($confpath, $contents);

function env($name, $default = null)
{
    $v = getenv($name) ?: $default;
    
    if ($v === null) {
        error('The env ' . $name . ' does not exist');
    }
    
    return $v;
}

function env_defined($name)
{
    $envVar = getenv($name);
    return $envVar !== false;
}

function error($text)
{
    echo 'Error: ' . $text . PHP_EOL;
    exit(1);
}

function dbconnect($config_replace)
{
    $map = array('host' => 'HOST', 'port' => 'PORT', 'dbname' => 'NAME');
    $dsn = $config_replace['DB_TYPE'] . ':';
    foreach ($map as $d => $h) {
        if (isset($config_replace['DB_' . $h])) {
            $dsn .= $d . '=' . $config_replace['DB_' . $h] . ';';
        }
    }
    $pdo = new \PDO($dsn, $config_replace['DB_USER'], $config_replace['DB_PASS']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

function dbcheck($config_replace)
{
    try {
        dbconnect($config_replace);
        return true;
    }
    catch (PDOException $e) {
        return false;
    }
}


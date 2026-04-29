<?php

/**
 * @package     AlejoASotelo
 * @subpackage  com_samcpserver
 * @author      Alejo A. Sotelo
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @link        alejosotelo.com.ar
 */

const PHP_TAB = "\t";

function usage($command)
{
  echo PHP_EOL;
  echo 'Usage: php ' . $command . ' [options]' . PHP_EOL;
  echo PHP_TAB . '[options]:' . PHP_EOL;
  echo PHP_TAB . PHP_TAB . '--name <name>:' . PHP_TAB . 'El nombre del plugin' . PHP_EOL;
  echo PHP_TAB . PHP_TAB . '--help:' . PHP_TAB . PHP_TAB . PHP_TAB . 'Show this help output' . PHP_EOL . PHP_EOL;
  echo PHP_TAB . PHP_TAB . 'Ejemplo: php build-plugin.php --name=plg_system_salibrarydompdf' . PHP_EOL;
  echo PHP_EOL;
}

if (version_compare(PHP_VERSION, '5.4', '<')) {
  echo "The build script requires PHP 5.4.\n";

  exit(1);
}

// Directorio raíz del proyecto
$root_dir = dirname(__DIR__);

$here = __DIR__;
$options = getopt('', array('help', 'name::', 'dest::'));

$name = isset($options['name']) ? $options['name'] : '';
$dest = isset($options['dest']) ?  $options['dest'] : $root_dir . '/dist';
$showHelp = isset($options['help']);

if ($showHelp || empty($name)) {
  usage($argv[0]);
  die;
}

// Directorio de origen
$source_dir = $root_dir . '/plugin/' . $name;

// Directorio de destino
$dest_dir = rtrim($dest, '/') . '/' . $name;

// Archivo ZIP de destino
$zip_file = $name  . '.zip';

// Lista de archivos y carpetas a excluir
$exclude = array(".git", ".gitignore", "node_modules", 'php-hot-reloader', 'phrwatcher.php', 'vendor', '.php-cs-fixer.cache', '.php-cs-fixer.dist.php');

if (!file_exists($source_dir)) {
  echo "El directorio $source_dir no existe." . PHP_EOL . PHP_EOL;
  exit(1);
}

if (file_exists($dest_dir)) {
  shell_exec("rm -rf $dest_dir");
  shell_exec('rm -rf ' . dirname($dest_dir) . '/' . $zip_file);
}

// Crear el directorio de destino si no existe
shell_exec("mkdir -p $dest_dir");

// Construir el comando rsync con las opciones necesarias
$options = "-a --delete";
foreach ($exclude as $ex) {
  $options .= " --exclude=" . $ex;
}

shell_exec("rsync $options $source_dir/ $dest_dir");

// if exists composer.json
if (file_exists($dest_dir . '/composer.json')) {
  shell_exec('composer install --no-dev --optimize-autoloader --no-interaction --working-dir=' . $dest_dir);
}

// Cambiar al directorio de destino
chdir(dirname($dest_dir));

// Comprimir el directorio de destino en un archivo ZIP
$result = shell_exec("zip -rq $zip_file $name ");

// Ejecutar el comando y mostrar el resultado
echo $result;

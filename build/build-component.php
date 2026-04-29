<?php
/**
 * @package     AlejoASotelo
 * @subpackage  com_base
 * @author      Alejo A. Sotelo
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @link        alejosotelo.com.ar
 */

// Directorio raíz del proyecto
$root_dir = dirname(__DIR__);

$component_name = 'com_samcpserver';

// Directorio de origen
$source_dir = $root_dir . '/component';

// Directorio de destino
$dest_dir = $root_dir . '/dist/' . $component_name;

// Archivo ZIP de destino
$zip_file = $component_name . '.zip';

// Lista de archivos y carpetas a excluir
$exclude = array(
    ".git", ".gitignore", ".gitkeep",
    "node_modules", "package.json", "package-lock.json",
    "admin/vendor",
    "site/vendor",
    "api/vendor", "tests", "phpunit.xml", ".travis.yml",
    "README.md", "CHANGELOG.md", ".editorconfig",
    ".vscode", ".idea", "*.log",
    ".php-cs-fixer.dist.php"
);

if (file_exists($dest_dir)) {
  shell_exec("rm -rf $dest_dir");
  shell_exec('rm -rf '.dirname($dest_dir) . '/' . $zip_file);
}

// Crear el directorio de destino si no existe
shell_exec("mkdir -p $dest_dir");

// Construir el comando rsync con las opciones necesarias
$options = "-a --delete";
foreach ($exclude as $ex) {
  $options .= " --exclude=" . $ex;
}
$command = "rsync $options $source_dir/ $dest_dir";

shell_exec($command);

// Instalar dependencias de Composer para producción en el directorio copiado
echo "Instalando dependencias de Composer para producción...\n";

// Instalar dependencias en admin/ del directorio copiado
$admin_dest_dir = $dest_dir . '/admin';
if (file_exists($admin_dest_dir . '/composer.json')) {
    chdir($admin_dest_dir);
    shell_exec("composer install --no-dev --optimize-autoloader --no-interaction");
    echo "Dependencias de Composer instaladas en admin/\n";
} else {
    echo "No se encontró composer.json en admin/, omitiendo instalación de dependencias\n";
}

// Instalar dependencias en site/ del directorio copiado
$site_dest_dir = $dest_dir . '/site';
if (file_exists($site_dest_dir . '/composer.json')) {
    chdir($site_dest_dir);
    shell_exec("composer install --no-dev --optimize-autoloader --no-interaction");
    echo "Dependencias de Composer instaladas en site/\n";
} else {
    echo "No se encontró composer.json en site/, omitiendo instalación de dependencias\n";
}

// Instalar dependencias en api/ del directorio copiado
$api_dest_dir = $dest_dir . '/api';
if (file_exists($api_dest_dir . '/composer.json')) {
    chdir($api_dest_dir);
    shell_exec("composer install --no-dev --optimize-autoloader --no-interaction");
    echo "Dependencias de Composer instaladas en api/\n";
} else {
    echo "No se encontró composer.json en api/, omitiendo instalación de dependencias\n";
}

// Instalar dependencias en vendor/ del directorio copiado
$vendor_dest_dir = $dest_dir . '/vendor';
if (file_exists($vendor_dest_dir . '/composer.json')) {
    chdir($vendor_dest_dir);
    shell_exec("composer install --no-dev --optimize-autoloader --no-interaction");
    echo "Dependencias de Composer instaladas en vendor/\n";
} else {
    echo "No se encontró composer.json en vendor/, omitiendo instalación de dependencias\n";
}

// Volver al directorio raíz
chdir($root_dir);

// Cambiar al directorio de destino
chdir(dirname($dest_dir));


// Comprimir el directorio de destino en un archivo ZIP
$command = "zip -rq $zip_file $component_name";
$result = shell_exec($command);

// Ejecutar el comando y mostrar el resultado
echo $result;

// Mensaje de finalización
if (file_exists($zip_file)) {
    echo "\n✅ Construcción completada exitosamente!\n";
    echo "📦 Archivo creado: " . dirname($dest_dir) . '/' . $zip_file . "\n";
    echo "📊 Tamaño del archivo: " . filesize(dirname($dest_dir) . '/' . $zip_file) . " bytes\n";
} else {
    echo "\n❌ Error: No se pudo crear el archivo ZIP\n";
}
?>

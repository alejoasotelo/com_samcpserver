<?php

const PHP_TAB = "\t";

function usage($command)
{
	echo PHP_EOL;
    echo 'Usage: php ' . $command . ' [options]' . PHP_EOL;
	echo PHP_TAB . '[options]:' . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '--from <from>:' . PHP_TAB . PHP_TAB . 'El tag de la versión desde la cual se van a obtener los commits (ex: `1.12.0`, `1.13.0`)' . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '--version <version>:' . PHP_TAB . 'La versión para agregar en el changelog' . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '--token <token>:' . PHP_TAB . PHP_TAB . 'GitHub token para acceder a la API (opcional, se puede usar GITHUB_TOKEN env var)' . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '--repo <repo>:' . PHP_TAB . PHP_TAB . 'Repositorio de GitHub en formato owner/repo (default: alejoasotelo/com_samcpserver)' . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '--help:' . PHP_TAB . PHP_TAB . PHP_TAB . 'Show this help output' . PHP_EOL . PHP_EOL;
    echo PHP_TAB . PHP_TAB . 'Ejemplo: php build_changelog.php --from=1.12.0 --version=1.13.0 --token=ghp_xxxxx' . PHP_EOL;
	echo PHP_EOL;
}

if (version_compare(PHP_VERSION, '5.4', '<'))
{
	echo "The build script requires PHP 5.4.\n";

	exit(1);
}

$time = time();

// Set path to git binary (e.g., /usr/local/git/bin/git or /usr/bin/git)
ob_start();
passthru('which git', $systemGit);
$systemGit = trim(ob_get_clean());

if (empty($systemGit)) {
    ob_start();
    passthru('where git', $systemGit);
    $systemGit = trim(ob_get_clean());
}

if (empty($systemGit))
{
	die('Install Git');
}

// Make sure file and folder permissions are set correctly
umask(022);

// Shortcut the paths to the repository root and build folder
$repo = dirname(__DIR__);
$here = __DIR__;

// Set paths for the build packages
$tmp      = $here . '/tmp';
$fullpath = $tmp . '/' . $time;

/**
 * Obtiene los pull requests fusionados entre dos tags usando la API de GitHub
 * 
 * @param string $repo Repository in format owner/repo
 * @param string $fromTag Tag desde el cual obtener los PRs
 * @param string $toTag Tag hasta el cual obtener los PRs (default: HEAD)
 * @param string $token GitHub token para autenticación
 * @return array Array de pull requests con su información
 */
function getPullRequestsBetweenTags($repo, $fromTag, $toTag = 'HEAD', $token = '')
{
	global $systemGit;
	
	// Obtener commits entre tags
	chdir(dirname(__DIR__));
	ob_start();
	system('"'.$systemGit . '" log --pretty=format:"%H" ' . $fromTag . '...' . $toTag);
	$commits = explode("\n", trim(ob_get_clean()));
	
	if (empty($commits[0])) {
		return [];
	}
	
	$pullRequests = [];
	$seenPRs = [];
	
	foreach ($commits as $commit) {
		// Buscar el PR asociado a este commit
		$prNumber = getPRNumberFromCommit($repo, $commit, $token);
		
		if ($prNumber && !isset($seenPRs[$prNumber])) {
			$prData = getPullRequestData($repo, $prNumber, $token);
			if ($prData) {
				$pullRequests[] = $prData;
				$seenPRs[$prNumber] = true;
			}
		}
	}
	
	return $pullRequests;
}

/**
 * Obtiene el número de PR asociado a un commit usando la API de GitHub
 * 
 * @param string $repo Repository in format owner/repo
 * @param string $commit SHA del commit
 * @param string $token GitHub token
 * @return int|null Número del PR o null si no se encuentra
 */
function getPRNumberFromCommit($repo, $commit, $token)
{
	$url = "https://api.github.com/repos/{$repo}/commits/{$commit}/pulls";
	$headers = ['Accept: application/vnd.github+json'];
	
	if (!empty($token)) {
		$headers[] = 'Authorization: Bearer ' . $token;
	}
	
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_USERAGENT, 'BullVial-Changelog-Builder');
	
	$response = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	
	if ($httpCode === 200) {
		$prs = json_decode($response, true);
		if (!empty($prs) && isset($prs[0]['number'])) {
			return $prs[0]['number'];
		}
	}
	
	return null;
}

/**
 * Obtiene la información completa de un pull request
 * 
 * @param string $repo Repository in format owner/repo
 * @param int $prNumber Número del PR
 * @param string $token GitHub token
 * @return array|null Datos del PR o null si no se encuentra
 */
function getPullRequestData($repo, $prNumber, $token)
{
	$url = "https://api.github.com/repos/{$repo}/pulls/{$prNumber}";
	$headers = ['Accept: application/vnd.github+json'];
	
	if (!empty($token)) {
		$headers[] = 'Authorization: Bearer ' . $token;
	}
	
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_USERAGENT, 'BullVial-Changelog-Builder');
	
	$response = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	
	if ($httpCode === 200) {
		$pr = json_decode($response, true);
		
		// Extraer labels
		$labels = [];
		if (isset($pr['labels']) && is_array($pr['labels'])) {
			foreach ($pr['labels'] as $label) {
				$labels[] = $label['name'];
			}
		}
		
		return [
			'number' => $pr['number'],
			'title' => $pr['title'],
			'labels' => $labels,
			'html_url' => $pr['html_url'],
			'merged_at' => $pr['merged_at'] ?? null
		];
	}
	
	return null;
}

/**
 * Categoriza los pull requests por sus labels
 * 
 * @param array $pullRequests Array de pull requests
 * @return array Array con PRs categorizados
 */
function categorizePullRequests($pullRequests)
{
	$categorized = [
		'enhancement' => [],
		'bug' => [],
		'other' => []
	];
	
	foreach ($pullRequests as $pr) {
		$labels = $pr['labels'];
		
		// Solo fusionados
		if (empty($pr['merged_at'])) {
			continue;
		}
		
		if (in_array('enhancement', $labels)) {
			$categorized['enhancement'][] = $pr;
		} elseif (in_array('bug', $labels)) {
			$categorized['bug'][] = $pr;
		} elseif (!in_array('documentation', $labels)) {
			// Solo agregar a "otros" si no es documentación
			$categorized['other'][] = $pr;
		}
	}
	
	return $categorized;
}

/**
 * Genera el contenido markdown del changelog
 * 
 * @param array $categorized PRs categorizados
 * @param string $version Versión del release
 * @param string $repo Repository en formato owner/repo
 * @param string $fromTag Tag desde
 * @param string $toTag Tag hasta
 * @return string Contenido markdown
 */
function generateChangelog($categorized, $version, $repo, $fromTag, $toTag = 'HEAD')
{
	$totalPRs = count($categorized['enhancement']) + count($categorized['bug']) + count($categorized['other']);
	
	$releasePage = "https://github.com/{$repo}/releases/tag/{$toTag}";
	$changelog = "https://github.com/{$repo}/compare/{$fromTag}...{$toTag}";

	$data = "## 👀 Información release\n\n";
	$data .= "* 🧰 **{$totalPRs}**+ Pull Requests se han fusionado\n";
	$data .= "* 🌎 [Release page]({$releasePage})\n";
	$data .= "* 👀 [Full Changelog]({$changelog})\n\n";
	
	// Mejoras
	if (!empty($categorized['enhancement'])) {
		$data .= "## 🚀 Mejoras\n\n";
		foreach ($categorized['enhancement'] as $pr) {
			$data .= "* #{$pr['number']}: {$pr['title']}\n";
		}
		$data .= "\n";
	}
	
	// Correcciones de errores
	if (!empty($categorized['bug'])) {
		$data .= "## 🐛 Correciones de errores\n\n";
		foreach ($categorized['bug'] as $pr) {
			$data .= "* #{$pr['number']}: {$pr['title']}\n";
		}
		$data .= "\n";
	}
	
	// Otros
	if (!empty($categorized['other'])) {
		$data .= "## 📝 Otros\n\n";
		foreach ($categorized['other'] as $pr) {
			$data .= "* #{$pr['number']}: {$pr['title']}\n";
		}
		$data .= "\n";
	}
	
	return $data;
}

// Parse input options
$options = getopt('', array('help', 'from::', 'version::', 'dest::', 'token::', 'repo::'));

$from = isset($options['from']) ? $options['from'] : '';
$version = isset($options['version']) ? $options['version'] : '';
$dest = isset($options['dest']) ? $options['dest'] : $repo . '/CHANGELOG.md';
$token = isset($options['token']) ? $options['token'] : (getenv('GITHUB_TOKEN') ?: '');
$repository = isset($options['repo']) ? $options['repo'] : 'alejoasotelo/com_samcpserver';
$showHelp = isset($options['help']);

if ($showHelp)
{
	usage($argv[0]);
	die;
}

if (empty($from)){
	usage($argv[0]);
    die;
}

// Obtener el tag actual (HEAD)
chdir($repo);
ob_start();
system('"'.$systemGit . '" describe --tags --abbrev=0 2>/dev/null');
$currentTag = trim(ob_get_clean());

if (empty($currentTag)) {
	$currentTag = 'HEAD';
}

// Si se especifica version, usar como tag actual
if (!empty($version)) {
	$currentTag = $version;
}

echo "Obteniendo pull requests entre {$from} y {$currentTag}...\n";

// Obtener pull requests
$pullRequests = getPullRequestsBetweenTags($repository, $from, $currentTag, $token);

if (empty($pullRequests)) {
	echo "No se encontraron pull requests entre {$from} y {$currentTag}\n";
	echo "Generando changelog desde commits...\n";
	
	// Fallback al método anterior basado en commits
	ob_start();
	system('"'.$systemGit . '" log --pretty=oneline HEAD...' . $from, $commits);
	$commits = explode("\n", trim(ob_get_clean()));
	
	$data = '# Versión'.(!empty($version) ? ' ' . $version : '').' - '.date('d/m/Y');
	$data .= "\n\n";
	
	foreach ($commits as &$commit) {
		$parts = explode(' ', $commit);
		unset($parts[0]);
		$commit = implode(' ', array_values($parts));
		
		if (strpos($commit, '[*]') === false && 
			strpos($commit, '[+]') === false && 
			strpos($commit, '[-]') === false && 
			strpos($commit, '[x]') === false) 
		{
			continue;
		}
		
		$data .= '- ' .  $commit."\n";
	}
} else {
	echo "Se encontraron " . count($pullRequests) . " pull requests\n";
	
	// Categorizar PRs
	$categorized = categorizePullRequests($pullRequests);
	
	// Generar changelog
	$data = generateChangelog($categorized, $version, $repository, $from, $currentTag);
}

$filename = $dest;

if (file_exists($filename)) {
	$content = file_get_contents($filename);
	$data .= "\n".$content;
}

file_put_contents($filename, $data);

echo "Changelog generado exitosamente en {$filename}\n";
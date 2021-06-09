#!/usr/bin/env php
<?php

$options = getopt('hv', ['help', 'verbose', 'type::']);

// Check if we're running in the right environment
if (!exec('command -v docker-compose')) {
	printf("  \033[1;31mIt looks like you're trying to run this inside the docker container.\033[0m".PHP_EOL);
	printf("  \033[1;31mThis script is meant to be executed from your local computer / host machine.\033[0m".PHP_EOL);
	printf("  \033[1;31mFor more information, try running this script with --help.\033[0m".PHP_EOL);
	printf(PHP_EOL);
	exit();
}

// Display help if requested
if (isset($options['h']) || isset($options['help'])) {
	printf("\033[0;33mDescription:\033[0m".PHP_EOL);
	printf('  Testing script for the Koalati app platform.'.PHP_EOL);
	printf('  When executed, this script temporarily switches your environment to testing mode, prepares your database, and runs every unit, functional and E2E test available.'.PHP_EOL);
	printf("  \033[1;36mImportant: This script is meant to be executed from your local computer / host machine.\033[0m".PHP_EOL);
	printf(PHP_EOL);
	printf("\033[0;33mOptions:\033[0m".PHP_EOL);
	printf("\033[0;32m  -h, --help                                              Displays the help for this script.\033[0m".PHP_EOL);
	printf("\033[0;32m      --type[=frontend,backend,e2e,unit,functional]       Defines the types of tests that will run. \033[1;30m(Ex.: \"--type=frontend,unit\" will only run unit tests for frontend components)\033[0m".PHP_EOL);
	printf("\033[0;32m  -v, --verbose                                           Shows the output of every command executed.\033[0m".PHP_EOL);
	printf(PHP_EOL);
	exit();
}

// Utility functions
/**
 * Parses the `type` CLI option and returns the desired types as an array of flags.
 *
 * @return array<string,bool>
 */
function parseTypeOption(): array
{
	global $options;

	$availableTypes = ['frontend', 'backend', 'unit', 'functional', 'e2e'];
	$selectedTypes = explode(',', $options['type'] ?: implode(',', $availableTypes));
	$types = [];

	foreach ($availableTypes as $type) {
		$types[$type] = in_array($type, $selectedTypes);
	}

	if (!$types['unit'] && !$types['functional']) {
		$types['unit'] = true;
		$types['functional'] = true;
	}

	if (!$types['backend'] && !$types['frontend'] && !$types['e2e']) {
		$types['backend'] = true;
		$types['frontend'] = true;
		$types['e2e'] = true;
	}

	foreach ($selectedTypes as $type) {
		if (!isset($types[$type])) {
			printf("  \033[1;31mInvalid test type provided to --type:.\033[0m".PHP_EOL);
			printf("  \033[1;31m   %s\033[0m".PHP_EOL, $type);
			printf("  \033[1;31mFor more information, try running this script with --help.\033[0m".PHP_EOL);
			printf(PHP_EOL);
			exit();
		}
	}

	return $types;
}

function runCommand(string $description, string $command, bool $outputAnyway = false): void
{
	global $totalRuntime;

	$startTime = microtime(true);

	printf($description);
	exec($command.' 2>&1', $output, $statusCode);

	$runtime = microtime(true) - $startTime;
	$totalRuntime += $runtime;
	$runtimeOutput = sprintf(" \033[1;30m(%ss)\033[0m", round($runtime, 2));

	if ($statusCode != 0) {
		echo '❌'.$runtimeOutput.PHP_EOL;
		echo implode('', array_map(fn ($line) => "\t".$line.PHP_EOL, $output));
		throw new Exception();
	} else {
		echo '✅'.$runtimeOutput.PHP_EOL;

		if ($outputAnyway) {
			echo implode('', array_map(fn ($line) => "\t".$line.PHP_EOL, $output));
		}
	}
}

// Define globals and constants that will be used at runtime
define('ROOT_DIR', __DIR__.'/../../');
$hasFailed = false;
$totalRuntime = 0;
$types = parseTypeOption();
$verboseMode = (isset($options['v']) || isset($options['verbose']));

// Processing starts here
printf('Switching to test .env file... ');
rename(ROOT_DIR.'.env.local', ROOT_DIR.'.env.local.tmp');
copy(ROOT_DIR.'.env.test', ROOT_DIR.'.env.local');
echo '✅'.PHP_EOL;

try {
	runCommand(
		"Creating test database if it doesn't exist... ",
		'docker-compose exec -T php ./bin/console --env=test doctrine:database:create --if-not-exists --no-interaction',
		$verboseMode
	);
	runCommand(
		'Wiping test database... ',
		'docker-compose exec -T php ./bin/console --env=test doctrine:schema:drop --full-database --force',
		$verboseMode
	);
	runCommand(
		'Running database migrations... ',
		'docker-compose exec -T php ./bin/console --env=test doctrine:migrations:migrate --no-interaction',
		$verboseMode
	);
	runCommand(
		'Generating fixtures... ',
		'docker-compose exec -T php ./bin/console --env=test doctrine:fixtures:load -n',
		$verboseMode
	);

	if ($types['backend']) {
		if ($types['unit'] && $types['functional']) {
			runCommand(
				'Running full PHPUnit... ',
				'docker-compose exec -T php ./bin/phpunit',
				$verboseMode
			);
		} elseif ($types['unit']) {
			runCommand(
				'Running full PHPUnit (unit tests only)... ',
				'docker-compose exec -T php ./bin/phpunit --testsuite unit',
				$verboseMode
			);
		} elseif ($types['functional']) {
			runCommand(
				'Running full PHPUnit (functional tests only)... ',
				'docker-compose exec -T php ./bin/phpunit --testsuite functional',
				$verboseMode
			);
		}
	}

	if ($types['frontend']) {
		if ($types['unit']) {
			runCommand(
				'Running JS unit tests... ',
				'npm run test:unit',
				$verboseMode
			);
		}
	}

	if ($types['e2e']) {
		runCommand(
			'Running end-to-end tests... ',
			'npm run test:e2e',
			$verboseMode
		);
	}
} catch (Exception $e) {
	$hasFailed = true;
} finally {
	echo PHP_EOL;
	printf('Restoring original .env.local file... ');
	unlink(ROOT_DIR.'.env.local');
	rename(ROOT_DIR.'.env.local.tmp', ROOT_DIR.'.env.local');
	echo '✅'.PHP_EOL;
}

if ($hasFailed) {
	printf(PHP_EOL."\033[31m❌ An error has occured! Check out the information above for more details.\033[0m".PHP_EOL);
} else {
	printf(PHP_EOL."\033[32m✅ Completed successfully!\033[0m".PHP_EOL);
}

printf("\033[1;30mTotal runtime: %ss\033[0m".PHP_EOL, round($totalRuntime, 2));

exit($hasFailed ? 1 : 0);

<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
	->in([
		__DIR__ . '/api',
		__DIR__ . '/backoffice',
		__DIR__ . '/common',
		__DIR__ . '/console',
		__DIR__ . '/frontoffice',
		__DIR__ . '/tests',
	])
	->exclude([
		'runtime',
		'tmp',
		'web',
	])
	->name('*.php')
	->ignoreDotFiles(true)
	->ignoreVCS(true);

return (new Config())
	->setRiskyAllowed(false)
	->setIndent("\t")
	->setLineEnding("\n")
	->setFinder($finder)
	->setRules([
		'@PER-CS' => true,
		'array_syntax' => [
			'syntax' => 'short',
		],
		'blank_line_before_statement' => [
			'statements' => [
				'break',
				'case',
				'continue',
				'if',
				'return',
				'throw',
				'try',
			],
		],
		'concat_space' => [
			'spacing' => 'none',
		],
		'function_declaration' => [
			'closure_fn_spacing' => 'one',
			'closure_function_spacing' => 'one',
		],
		'method_argument_space' => [
			'on_multiline' => 'ensure_fully_multiline',
		],
		'no_extra_blank_lines' => [
			'tokens' => [
				'break',
				'case',
				'continue',
				'curly_brace_block',
				'extra',
				'parenthesis_brace_block',
				'return',
				'square_brace_block',
				'switch',
				'throw',
				'use',
			],
		],
		'ordered_imports' => [
			'imports_order' => [
				'class',
				'function',
				'const',
			],
			'sort_algorithm' => 'alpha',
		],
		'single_import_per_statement' => true,
		'trailing_comma_in_multiline' => [
			'elements' => [
				'arguments',
				'arrays',
				'parameters',
			],
		],
	]);

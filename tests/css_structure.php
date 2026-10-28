<?php

declare(strict_types=1);

$root = dirname(__DIR__);

$requiredFiles = [
	'common/assets/css/layouts/base.css',
	'common/assets/css/layouts/fonts.css',
	'common/assets/css/components/primitives.css',
	'frontoffice/assets/css/layouts/main.css',
	'frontoffice/assets/css/components/poster.css',
	'frontoffice/assets/css/views/catalog.css',
	'frontoffice/assets/css/views/movie.css',
	'frontoffice/assets/css/views/session.css',
	'frontoffice/assets/css/views/checkout.css',
	'frontoffice/assets/css/views/ticket.css',
	'backoffice/assets/css/layouts/main.css',
	'backoffice/assets/css/components/forms.css',
	'backoffice/assets/css/components/tables.css',
	'backoffice/assets/css/views/rooms.css',
	'backoffice/assets/css/views/orders.css',
	'backoffice/assets/css/views/settings.css',
];

$removedFiles = [
	'frontoffice/assets/css/views/home.css',
	'backoffice/assets/css/components/management.css',
	'backoffice/assets/css/views/management.css',
];

foreach ($requiredFiles as $file) {
	if (!is_file($root.'/'.$file)) {
		fwrite(STDERR, "Missing split stylesheet: {$file}\n");
		exit(1);
	}
}

foreach ($removedFiles as $file) {
	if (is_file($root.'/'.$file)) {
		fwrite(STDERR, "God stylesheet still present: {$file}\n");
		exit(1);
	}
}

echo "CSS structure test passed.\n";

<?php

namespace frontoffice\controllers;

use yii\filters\VerbFilter;
use yii\web\Controller;

/** All demo pages are read-only; forms are intercepted in the browser. */
abstract class PreviewController extends Controller
{
	public function behaviors(): array
	{
		return [
			'verbs' => [
				'class' => VerbFilter::class,
				'actions' => [
					'*' => ['GET', 'HEAD'],
				],
			],
		];
	}
}

<?php

namespace frontoffice\controllers;

use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

/** All demo pages are read-only; forms are intercepted in the browser. */
abstract class PreviewController extends Controller
{
	public function behaviors(): array
	{
		return [
			'access' => [
				'class' => AccessControl::class,
				'rules' => [
					[
						'allow' => true,
						'roles' => ['frontoffice.view'],
					],
				],
			],
			'verbs' => [
				'class' => VerbFilter::class,
				'actions' => [
					'*' => ['GET', 'HEAD'],
				],
			],
		];
	}
}

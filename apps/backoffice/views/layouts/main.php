<?php

use backoffice\assets\AppAsset;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var string $content */

AppAsset::register($this);
?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">

<head>
	<meta charset="<?= Yii::$app->charset ?>">
	<meta
		name="viewport"
		content="width=device-width, initial-scale=1">

	<?= Html::csrfMetaTags() ?>

	<title><?= Html::encode($this->title) ?></title>

	<?php $this->head() ?>
</head>

<body>
	<?php $this->beginBody() ?>

	<div class="backoffice-shell">
		<?= $this->render('../components/sidebar') ?>

		<div class="workspace">
			<?= $this->render('../components/header') ?>

			<?= $content ?>
		</div>
	</div>

	<?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>
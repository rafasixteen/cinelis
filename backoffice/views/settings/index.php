<?php

use common\widgets\Button;

/** @var yii\web\View $this */
?>

<main id="main-content" class="page">
	<?= $this->render('../components/page-heading', [
		'title' => 'Definições',
		'description' => 'Informações do cinema e preferências da operação.',
	]) ?>
	<form method="post" data-demo-form novalidate>
		<div class="panel form-grid">
			<h2 class="field--full">CineLis Lisboa</h2>
			<?= $this->render('../components/field', [
				'name' => 'venue',
				'label' => 'Nome do cinema',
				'value' => 'CineLis Lisboa',
			]) ?>

			<?= $this->render('../components/field', [
				'name' => 'email',
				'label' => 'Email de contacto',
				'type' => 'email',
				'value' => 'ola@cinelis.pt',
			]) ?>

			<?= $this->render('../components/field', [
				'name' => 'address',
				'label' => 'Morada',
				'value' => 'Avenida da Liberdade, Lisboa',
				'class' => 'field--full',
			]) ?>

			<?= $this->render('../components/field', [
				'name' => 'price',
				'label' => 'Preço base do bilhete (€)',
				'type' => 'number',
				'value' => '7.50',
				'options' => ['step' => '0.50'],
			]) ?>

			<?= $this->render('../components/field', [
				'name' => 'timezone',
				'label' => 'Fuso horário',
				'items' => ['Europe/Lisbon' => 'Europe/Lisbon'],
			]) ?>
		</div>
		<div class="form-actions">
			<?= Button::widget([
				'label' => 'Guardar definições',
				'options' => ['type' => 'submit'],
			]) ?>
		</div>
	</form>
	<section class="panel preferences">
		<div>
			<h2>Aparência</h2>
			<p>Alterne entre o tema claro e o tema escuro.</p>
		</div>
		<?= Button::widget([
			'label' => 'Alternar tema',
			'variant' => 'secondary',
			'icon' => 'sun-moon',
			'options' => ['data-theme-toggle' => true],
		]) ?>
	</section>
</main>

<?php

/** @var yii\web\View $this */
/** @var array $record */
/** @var bool $isNew */
?>

<main id="main-content" class="page">
	<?= $this->render('../components/page-heading', [
		'title' => $isNew ? 'Adicionar utilizador' : 'Editar utilizador',
		'section' => 'Utilizadores',
		'description' => $isNew ? 'Defina os dados e o perfil do colaborador.' : 'Equipa / '.$record['name'],
	]) ?>
	<form method="post" data-demo-form novalidate>
		<div class="panel form-grid">
			<?= $this->render('../components/field', [
				'name' => 'name',
				'label' => 'Nome completo',
				'value' => $record['name'],
				'options' => ['autocomplete' => 'name'],
			]) ?>

			<?= $this->render('../components/field', [
				'name' => 'email',
				'label' => 'Email',
				'type' => 'email',
				'value' => $record['email'],
				'options' => ['autocomplete' => 'email'],
			]) ?>

			<?= $this->render('../components/field', [
				'name' => 'role',
				'label' => 'Perfil',
				'value' => $record['role'],
				'items' => [
					'Admin' => 'Admin',
					'Funcionário' => 'Funcionário',
				],
			]) ?>

			<?= $this->render('../components/field', [
				'name' => 'status',
				'label' => 'Estado',
				'value' => $record['status'],
				'items' => [
					'Ativo' => 'Ativo',
					'Inativo' => 'Inativo',
				],
			]) ?>

			<?= $this->render('../components/field', [
				'name' => 'password',
				'label' => $isNew ? 'Palavra-passe' : 'Nova palavra-passe',
				'type' => 'password',
				'options' => ['autocomplete' => 'new-password'],
				'hint' => 'Formulário demonstrativo. Nenhum dado será guardado.',
			]) ?>
		</div>
		<?= $this->render('../components/form-actions', [
			'cancel' => ['/users/index'],
			'label' => $isNew ? 'Criar utilizador' : 'Guardar utilizador',
		]) ?>
	</form>
</main>

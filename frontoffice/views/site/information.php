<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $page */

$this->title = [
	'contact' => 'Contacto',
	'help' => 'Ajuda',
	'privacy' => 'Privacidade',
][$page];
?>

<header class="page-heading">
	<h1><?= Html::encode($this->title) ?>
	</h1>
	<p>O seu lugar no cinema.</p>
</header>
<section class="panel information">
	<?php if ($page === 'contact'): ?>
		<h2>Fale connosco</h2>
		<p>Tem uma dúvida sobre a programação ou uma sessão? Deixe a sua mensagem.</p>
		<form data-demo-form novalidate method="post" action="<?= Url::to(
			['/site/contact'],
		) ?>
">
			<div class="ui-field">
				<label for="contact-name">Nome</label>
				<input class="ui-input" id="contact-name" autocomplete="name">
			</div>
			<div class="ui-field">
				<label for="contact-email">Email</label>
				<input class="ui-input" id="contact-email" type="email" autocomplete="email">
			</div>
			<div class="ui-field">
				<label for="contact-message">Mensagem</label>
				<textarea class="ui-input" id="contact-message" rows="5"></textarea>
			</div>
			<button class="ui-button ui-button--primary" type="submit" data-demo-action>Enviar mensagem</button>
		</form>

	<?php elseif ($page === 'help'): ?>
		<h2>Como funciona</h2>
		<details open>
			<summary>Como escolher uma sessão?</summary>
			<p>
				Explore os
				<?= Html::a(
					'filmes em exibição',
					['/movie/index'],
				) ?>
,
				escolha um dia e uma hora, e consulte o mapa de lugares.
			</p>
		</details>
		<details>
			<summary>Os bilhetes são reais?</summary>
			<p>Esta é uma demonstração do CineLis. Os lugares, pagamentos, contas e bilhetes são exemplos e não são guardados.</p>
		</details>
		<details>
			<summary>Onde encontro os meus bilhetes?</summary>
			<p>
				Visite
				<?= Html::a(
					'Os meus bilhetes',
					['/ticket/index'],
				) ?>
para explorar os exemplos de bilhetes.
			</p>
		</details>

	<?php else: ?>
		<h2>Privacidade nesta demonstração</h2>
		<p>Os filmes, sessões, contas e compras apresentados são dados fictícios. Os formulários de demonstração não enviam nem guardam os dados introduzidos.</p>
		<p>A preferência de tema é guardada apenas no seu navegador. Esta aplicação usa cookies técnicos necessários ao funcionamento do Yii.</p>
		<p>
			Para esclarecer uma dúvida, consulte a
			<?= Html::a(
				'ajuda',
				['/site/help'],
			) ?>
ou a página de
			<?= Html::a(
				'contacto',
				['/site/contact'],
			) ?>
.
		</p>

	<?php endif ?>
</section>

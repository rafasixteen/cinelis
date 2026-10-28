<?php

/** @var int $step */
?>

<ol class="steps" aria-label="Etapas da compra">
	<?php foreach ([
		1 => 'Lugares',
		2 => 'Pagamento',
		3 => 'Bilhete',
	] as $number => $label): ?>
	<li <?= $number === $step ? 'aria-current="step" class="is-active"' : '' ?>
>
			<?= $number ?>
		<span><?= $label ?>
		</span>
		</li>

	<?php endforeach ?>
</ol>

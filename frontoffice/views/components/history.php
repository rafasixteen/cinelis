<?php

use frontoffice\data\Catalog;
use yii\helpers\Html;

?>

<div class="table-wrap">
	<table class="history-table">
		<caption class="sr-only">Histórico de compras de demonstração</caption>
		<thead>
			<tr>
				<th scope="col">Data</th>
				<th scope="col">Filme / bilhetes</th>
				<th scope="col">Total</th>
				<th scope="col">Estado</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach (array_slice(array_values(Catalog::movies()), 0, 4) as $index => $movie): ?>
				<tr>
					<td>0<?= $index + 1 ?>
/10/2026</td>
					<td><?= Html::encode($movie['title']) ?>
· 2 bilhetes</td>
					<td>15,00 €</td>
					<td>
						<span class="ui-badge ui-badge--success">Pago · exemplo</span>
					</td>
				</tr>

			<?php endforeach ?>
		</tbody>
	</table>
</div>
<div class="history-cards">
	<?php foreach (array_slice(array_values(Catalog::movies()), 0, 4) as $index => $movie): ?>
		<article class="panel">
			<div>
				<h3><?= Html::encode($movie['title']) ?>
				</h3>
				<p class="muted">0<?= $index + 1 ?>
out. · 2 bilhetes</p>
			</div>
			<span>15,00 €</span>
		</article>

	<?php endforeach ?>
</div>
<p class="note history-count">4 compras · 8 bilhetes · dados de demonstração</p>

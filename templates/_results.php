<?php
/**
 * @var array $results from results table
 */
?>
<ul>
    <?php foreach($results as $result): ?>
    <li>
        <h3><?= $result['name'] ?></h3>
        Рейтинг: <?= $result['rating'] ?>
        <br>
        <?= $result['description'] ?>
    </li>
    <?php endforeach ?>
</ul>

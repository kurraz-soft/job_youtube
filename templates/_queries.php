<?php
/**
 * @var array $queries (results from db)
 */
?>
<ul>
    <?php foreach($queries as $v): ?>
    <li><a href="/?r=get-data&id=<?= $v['id'] ?>" class="link-query"><?= htmlspecialchars($v['text']) ?></a></li>
    <?php endforeach ?>
</ul>
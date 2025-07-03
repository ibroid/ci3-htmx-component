<?php
/** @var string $state_id */
/** @var string $component */
/** @var string $csrf_name */
/** @var string $csrf_hash */
/** @var string $error_html */
/** @var string $loading_html */
/** @var string $slot */
?>

<div id="<?= $state_id ?>" hx-target="this" hx-swap="outerHTML" class="htmx-component-wrapper">

    <?= $loading_html ?>

    <input type="hidden" name="state_id" value="<?= $state_id ?>">
    <input type="hidden" name="component" value="<?= $component ?>">
    <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">

    <?= $error_html ?>

    <?= $slot ?>
</div>

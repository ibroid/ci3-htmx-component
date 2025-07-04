<div id="<?= $wrapper_id ?>"
    class="htmx-component-wrapper"
    hx-target="#<?= $state_id ?>"
    hx-swap="outerHTML">

    <div class="htmx-indicator"><?= $loading_html ?? 'Memuat...' ?></div>

    <input type="hidden" name="state_id" value="<?= $state_id ?>">
    <input type="hidden" name="component" value="<?= $component ?>">
    <input type="hidden" name="<?= $csrf_name ?>" value="<?= $csrf_hash ?>">

    <?php if (!empty($error_html)): ?>
        <?= $error_html ?>
    <?php endif; ?>

    <div id="<?= $state_id ?>">
        <?= $slot ?>
    </div>

</div>
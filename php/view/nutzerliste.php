<?php if (!isset($nutzer)) $nutzer = []; ?>

<main>
    <h2>Nutzerübersicht</h2>
    <div>Hinweis: Diese Seite ist nur für Administratoren vorgesehen.</div>

    <div class="nutzerliste">
        <?php foreach ($nutzer as $person): ?>
            <div class="nutzer-karte">
                <div class="nutzer-karte-inhalt">
                    <div><strong>Benutzername:</strong> <?= htmlspecialchars($person['benutzername']) ?></div>
                    <div><strong>E-Mail:</strong> <?= htmlspecialchars($person['email']) ?></div>
                    <div><strong>Registriert am:</strong> <?= htmlspecialchars($person['registriert'] ?? '-') ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>
<?php if (!isset($nutzer)) $nutzer = []; ?>

<main>
    <h2>Nutzerübersicht</h2>
    <p>Hinweis: Diese Seite ist nur für Administratoren vorgesehen.</p>

    <div class="nutzerliste">
        <?php foreach ($nutzer as $person): ?>
            <div class="nutzer-karte">
                <div class="nutzer-karte-inhalt">
                    <p><strong>Benutzername:</strong> <?= htmlspecialchars($person['benutzername']) ?></p>
                    <p><strong>E-Mail:</strong> <?= htmlspecialchars($person['email']) ?></p>
                    <p><strong>Registriert am:</strong> <?= htmlspecialchars($person['registriert'] ?? '-') ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

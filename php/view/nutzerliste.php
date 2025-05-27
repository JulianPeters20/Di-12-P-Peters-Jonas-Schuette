<?php if (!isset($nutzer)) $nutzer = []; ?>

<main>
    <h2>Nutzerübersicht</h2>
    <p>Hinweis: Diese Seite ist nur für Administratoren vorgesehen.</p>

    <ul class="nutzerliste">
        <?php foreach ($nutzer as $person): ?>
            <li class="nutzer-karte">
                <div class="nutzer-karte-inhalt">
                    <dl>
                        <dt>Benutzername:</dt>
                        <dd><?= htmlspecialchars($person['benutzername']) ?></dd>
                        <dt>E-Mail:</dt>
                        <dd><?= htmlspecialchars($person['email']) ?></dd>
                        <dt>Registriert am:</dt>
                        <dd><?= htmlspecialchars($person['registriert'] ?? '-') ?></dd>
                    </dl>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</main>
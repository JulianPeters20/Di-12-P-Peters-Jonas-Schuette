<?php if (!isset($nutzer)) $nutzer = []; ?>

<main>
    <h2>Nutzerübersicht</h2>
    <div>Hinweis: Diese Seite ist nur für Administratoren vorgesehen.</div>

    <div class="nutzerliste">
        <?php foreach ($nutzer as $person): ?>
            <div class="nutzer-karte">
                <div class="nutzer-karte-inhalt">
                    <div>
                        <strong>Benutzername:</strong>
                        <?= htmlspecialchars($person->benutzername) ?>
                        <?php if (!empty($person->istAdmin)): ?>
                            <span style="color: green;">(Admin)</span>
                        <?php endif; ?>
                    </div>
                    <div><strong>E-Mail:</strong> <?= htmlspecialchars($person->email) ?></div>
                    <div><strong>Registriert am:</strong> <?= htmlspecialchars($person->registrierungsDatum ?? '-') ?></div>

                    <?php if ((int)$person->id === (int)($_SESSION['nutzerId'] ?? -1)): ?>
                        <div class="rezept-aktion" style="margin-top: 10px; color: #888;">
                            (eigener Account)
                        </div>
                    <?php elseif (!empty($person->istAdmin)): ?>
                        <div class="rezept-aktion" style="margin-top: 10px; color: #888;">
                            (Administrator - nicht löschbar)
                        </div>
                    <?php else: ?>
                        <div class="rezept-aktion" style="margin-top: 10px;">
                            <a href="index.php?page=nutzer-loeschen&id=<?= $person->id ?>"
                               class="btn"
                               onclick="return confirm('Möchtest du diesen Nutzer wirklich löschen?');">
                                Nutzer löschen
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>
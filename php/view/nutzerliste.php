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
                        <?= htmlspecialchars($person['Benutzername']) ?>
                        <?php if (!empty($person['IstAdmin'])): ?>
                            <span style="color: green;">(Admin)</span>
                        <?php endif; ?>
                    </div>
                    <div><strong>E-Mail:</strong> <?= htmlspecialchars($person['Email']) ?></div>
                    <div><strong>Registriert am:</strong> <?= htmlspecialchars($person['RegistrierungsDatum'] ?? '-') ?></div>

                    <?php if ((int)$person['NutzerID'] !== (int)($_SESSION['nutzerId'] ?? -1)): ?>
                        <div class="rezept-aktion" style="margin-top: 10px;">
                            <a href="index.php?page=nutzer-loeschen&id=<?= $person['NutzerID'] ?>"
                               class="btn"
                               onclick="return confirm('Möchtest du diesen Nutzer wirklich löschen?');">
                                Nutzer löschen
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="rezept-aktion" style="margin-top: 10px; color: #888;">
                            (eigener Account)
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

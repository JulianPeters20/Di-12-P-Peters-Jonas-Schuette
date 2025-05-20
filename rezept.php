<!DOCTYPE html>
<html lang="de">
<head>
  <link rel="stylesheet" href="css/style.css">
  <meta charset="UTF-8">
  <title>Broke & Hungry - Rezeptansicht</title>
</head>
<body>

<?php include_once 'header.php'; ?>
  <main>
    <h2>Nudeln mit Pesto</h2>

    <p><strong>Kategorie:</strong> Vegetarisch</p>
    <p><strong>Verfasser:</strong> julia@example.com</p>
    <p><strong>Veröffentlicht am:</strong> 21.04.2025</p>

    <figure>
      <img src="images/pesto.jpg" alt="Teller mit Nudeln und grünem Pesto" width="300">
      <figcaption>So sieht das fertige Gericht aus.</figcaption>
    </figure>

    <h3>Zutaten</h3>
    <ul class="ohne-punkte">
      <li>200g Spaghetti</li>
      <li>2 EL Pesto (z.B. Basilikum)</li>
      <li>Salz</li>
    </ul>

    <h3>Zubereitung</h3>
    <p>
      Die Nudeln in gesalzenem Wasser al dente kochen. Danach abgießen, zurück in den Topf geben und das Pesto unterrühren. Warm servieren.
    </p>

    <h3>Küchenutensilien</h3>
      <ul class="ohne-punkte">
        <li>Kochtopf</li>
        <li>Sieb</li>
        <li>Esslöffel</li>
      </ul>

    <hr>

    <section>
        <h3>Kommentare zum Rezept</h3>
      
        <article>
          <h4>Kommentar von max@example.com</h4>
          <p><strong>max@example.com</strong> schrieb am 21.04.2025:</p>
          <p>Sehr einfach und lecker! Habe noch geriebenen Käse dazugegeben.</p>
        </article>
      
        <article>
          <h4>Kommentar von lena@beispiel.de</h4>
          <p><strong>lena@beispiel.de</strong> schrieb am 22.04.2025:</p>
          <p>Funktioniert auch gut mit Vollkornnudeln. Danke fürs Teilen!</p>
        </article>
      </section>

    <p><em>(Kommentarfunktion wird später technisch umgesetzt)</em></p>

    <hr>

    <p>
        <button> <a href="rezept-neu.php" class="btn"> Rezept bearbeiten </a></button>
      <button>Rezept löschen</button>
    </p>
  </main>

  <?php include_once 'footer.php'; ?>
</body>
</html>

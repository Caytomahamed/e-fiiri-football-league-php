
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Premier League Table</title>
  <link rel="stylesheet" href="css/index.css?v=<?php echo time(); ?>" type="text/css">
    <script src="index.js" defer></script>
</head>
<body id="body">
  <div class="container">
    <h1>The Somaliland Football Championship</h1>

    <div class="container-menu">
       <button class="menu-btn active" data-target="match-details">Matches</button>
      <button class="menu-btn" data-target="standings">Standings</button>
      <button class="menu-btn" data-target="players">Players</button>
    </div>


    <!-- Content Sections -->
    <section id="match-details" class="tab-content active">
        <h2>Match Details</h2>
        <?php require_once './fixture.php'?>
      </section>

      <section id="standings" class="tab-content">
        <h2>Standings</h2>
        <?php require_once './league.php'?>
      <div class="table-info">
        <p class="top">Top League</p>
        <p class="relegation">Relegation Zone</p>
      </div>

    </section>

    <section id="players" class="tab-content">
      <h2>Players</h2>
       <?php require_once './PlayerPage.php'?>
    </section>
  </div>
  </div>

</body>
</html>

 <!-- background-color: #1f2937;
 /* background-color: #19212c; */ -->

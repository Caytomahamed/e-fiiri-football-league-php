<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php");
    exit;
}

// Admin is logged in, display sensitive information or actions
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/admin.css">
    <script src="index.js" defer></script>
</head>
<body>
    <div class="dashboard">
        <div class="sidebar">
            <img src="images/Stadium.svg" alt="">
             <a href="./admin.php"><button class="dash-btn active">
              Standing Table</button></a>
              <a href="./FixturesAdmin.php">
                <button class="dash-btn" >
                    Fixtures
                </button>
            </a>
              <a href="./TeamsAndPlayers.php">
                <button class="dash-btn" >
                    Teams & Players
                </button>
            </a>

              <a href="./logout.php" class="logout">
                <button class="dash-btn" >
                    Logout
                </button>
            </a>


        </div>

        <div class="dashboard-content">
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

            <section id="standings" class="tab-content" >
            <h2>Standings</h2>
            <?php require_once './league.php'?>
            <div class="table-info">
                <p class="top">Top League</p>
                <p class="relegation">Relegation Zone</p>
            </div>
             <div style="height:100px">
             </div>
            </section>

            <section id="players" class="tab-content">
            <h2>Players</h2>
                <?php require_once './PlayerPage.php'?>
                 <div style="height:100px">
             </div>
            </section>
        </div>

    </div>

</body>
</html>

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
    <link rel="stylesheet" href="css/form.css">
</head>

<body>
    <?php require_once "matchesFunctions.php"?>
    <div class="dashboard">
        <div class="sidebar">
            <img src="images/Stadium.svg" alt="">
            <a href="./admin.php"><button class="dash-btn ">
                    Standing Table</button></a>

            <a href="./FixturesAdmin.php">
                <button class="dash-btn active">
                    Fixtures
                </button>
            </a>
            <a href="./TeamsAndPlayers.php">
                <button class="dash-btn">
                    Teams & Players
                </button>
            </a>

            <a href="./logout.php" class="logout">
                <button class="dash-btn">
                    Logout
                </button>
            </a>
        </div>

        <div class="dashboard-content">
            <h1>Management Fixtures</h1>
            <div class="lives" style="display:flex;width:1000px;">
                <div class="img"></div>
                <div class="live-score">
                    <h3 class="fixture-title ">Live Game Scores Updates🥅</h3>
                    <form method="post">
                        <input type="hidden" name="form_type" value="score_update">
                        <input type="text" name="fixtureId" placeholder="FixtureId" required>
                        <input type="text" name="homeScore" placeholder="HomeScore" required>
                        <input type="text" name="awayScore" placeholder="AwayScore" required>
                        <input type="submit" value="Score Update">
                    </form>
                </div>
            </div>

            <div class="lives" style="display:flex;width:1000px; margin-top:80px;">
                <div class="live-score">
                    <h3 class="fixture-title ">End Games Updates League🏆</h3>
                    <p style="margin-bottom:10px;">teamId[1,2,3,4,5,6,7,8,9,10] <a href="./TeamsAndPlayers.php"
                            style="color:red">more about teams</a></p>
                    <form method="post">
                        <input type="hidden" name="form_type" value="league_update">
                        <input type="text" name="teamId" placeholder="teamId" required>
                        <input type="text" name="goalsFor" placeholder="goalsFor" required>
                        <input type="text" name="goalsAgainst" placeholder="goalsAgainst" required>
                        <input type="submit" value="League Update">
                    </form>
                </div>
                <div class="img2"></div>
            </div>

            <div class="lives" style="display:flex;width:1000px; margin-top:80px;">
                <div class="img3"></div>
                <div class="live-score">
                    <h3 class="fixture-title ">Generate Fixtures All A Season</h3>
                    <form method="post">
                        <input type="hidden" name="form_type" value="generate_fixture">
                        <input type="date" name="startDate" placeholder="startDate" required>
                        <input type="submit" value="Generate fixture">
                    </form>
                </div>
            </div>

            <div style="height:100px">
            </div>
        </div>

    </div>
    <script src="index.js"></script>
</body>

</html>
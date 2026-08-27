<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: home.php");
    exit;
}

require_once "AutoLoader.php";

$standingTable = new StandingTable();
$teamCount = count($standingTable->getTeams());
$standingTable->closeConnection();

$playersClass = new Players();
$playerCount = count($playersClass->getPlayers());
$playersClass->closeConnection();

$competitionClass = new Competition();
$competitionStats = $competitionClass->getCompetitionFixtureStats();
$competitionClass->closeConnection();

$competitionCount = count($competitionStats);
$typeCounts = ['LEAGUE' => 0, 'GROUP_KNOCKOUT' => 0, 'CUP' => 0];
$totalFixtures = 0;
$scheduledCompetitions = 0;
foreach ($competitionStats as $c) {
    $typeCounts[$c['TYPE']]++;
    $totalFixtures += intval($c['fixtureCount']);
    if (intval($c['fixtureCount']) > 0) {
        $scheduledCompetitions++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/admin.css">
    <script src="index.js" defer></script>
</head>

<body>
    <div class="dashboard">
        <div class="sidebar">
            <img src="images/Stadium.svg" alt="">
            <a href="./admin.php">
                <button class="dash-btn active">
                    <i class="fa fa-tachometer"></i> Dashboard
                </button>
            </a>
            <a href="./FixturesAdmin.php">
                <button class="dash-btn">
                    <i class="fa fa-calendar"></i> Fixtures
                </button>
            </a>
            <a href="./TeamsAdmin.php">
                <button class="dash-btn">
                    <i class="fa fa-shield"></i> Teams
                </button>
            </a>
            <a href="./TeamsAndPlayers.php">
                <button class="dash-btn">
                    <i class="fa fa-user"></i> Players
                </button>
            </a>
            <a href="./CompetitionsAdmin.php">
                <button class="dash-btn">
                    <i class="fa fa-trophy"></i> Competitions
                </button>
            </a>

            <a href="./logout.php" class="logout">
                <button class="dash-btn">
                    <i class="fa fa-sign-out"></i> Logout
                </button>
            </a>
        </div>

        <div class="dashboard-content">
            <h1>Dashboard</h1>

            <div class="stat-cards">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $teamCount; ?></div>
                    <div class="stat-label">Teams</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $playerCount; ?></div>
                    <div class="stat-label">Players</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $competitionCount; ?></div>
                    <div class="stat-label">Competitions</div>
                    <div class="stat-detail">
                        <?php echo $typeCounts['LEAGUE']; ?> League ·
                        <?php echo $typeCounts['GROUP_KNOCKOUT']; ?> Group+Knockout ·
                        <?php echo $typeCounts['CUP']; ?> Cup
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $totalFixtures; ?></div>
                    <div class="stat-label">Fixtures scheduled</div>
                    <div class="stat-detail"><?php echo $scheduledCompetitions; ?> of <?php echo $competitionCount; ?> competitions have a schedule</div>
                </div>
            </div>

            <div class="admin-panel">
                <h3 class="fixture-title">Quick Actions</h3>
                <div class="action-cards">
                    <a class="action-card" href="./FixturesAdmin.php">
                        <div class="action-icon"><i class="fa fa-calendar"></i></div>
                        <div class="action-title">Fixtures</div>
                        <div class="action-desc">Generate schedules, add ties, update scores and standings.</div>
                    </a>
                    <a class="action-card" href="./TeamsAdmin.php">
                        <div class="action-icon"><i class="fa fa-shield"></i></div>
                        <div class="action-title">Teams</div>
                        <div class="action-desc">Create, edit, or remove teams.</div>
                    </a>
                    <a class="action-card" href="./TeamsAndPlayers.php">
                        <div class="action-icon"><i class="fa fa-user"></i></div>
                        <div class="action-title">Players</div>
                        <div class="action-desc">Add players and browse the roster.</div>
                    </a>
                    <a class="action-card" href="./CompetitionsAdmin.php">
                        <div class="action-icon"><i class="fa fa-trophy"></i></div>
                        <div class="action-title">Competitions</div>
                        <div class="action-desc">Create competitions, assign teams, initialize standings.</div>
                    </a>
                </div>
            </div>

            <div style="height:100px">
            </div>
        </div>

    </div>

</body>

</html>

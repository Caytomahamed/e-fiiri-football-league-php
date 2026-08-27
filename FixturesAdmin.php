<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: home.php");
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/form.css">
</head>

<body>
    <?php
require_once "matchesFunctions.php";
require_once 'classes/Competition.php';
require_once 'classes/StandingTable.php';
require_once 'classes/Matches.php';

$competitionClass = new Competition();
$competitions = $competitionClass->getAllCompetitions();
$competitionClass->closeConnection();

$leagueCompetitions = array_values(array_filter($competitions, function ($c) {
    return $c['TYPE'] === 'LEAGUE';
}));
$knockoutCompetitions = array_values(array_filter($competitions, function ($c) {
    return $c['TYPE'] === 'GROUP_KNOCKOUT' || $c['TYPE'] === 'CUP';
}));

$standingTable = new StandingTable();
$teams = $standingTable->getTeams();
$standingTable->closeConnection();

$matchesClass = new Matches();
$fixturePickerList = $matchesClass->getFixturesForAdminPicker();
$matchesClass->closeConnection();

$competitionClass2 = new Competition();
$competitionFixtureStats = $competitionClass2->getCompetitionFixtureStats();
$competitionClass2->closeConnection();

$totalFixtures = 0;
$scheduledCompetitions = 0;
foreach ($competitionFixtureStats as $c) {
    $totalFixtures += intval($c['fixtureCount']);
    if (intval($c['fixtureCount']) > 0) {
        $scheduledCompetitions++;
    }
}
$competitionCount = count($competitionFixtureStats);
?>
    <div class="dashboard">
        <div class="sidebar">
            <img src="images/Stadium.svg" alt="">
            <a href="./admin.php">
                <button class="dash-btn">
                    <i class="fa fa-tachometer"></i> Dashboard
                </button>
            </a>
            <a href="./FixturesAdmin.php">
                <button class="dash-btn active">
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
            <h1>Fixtures Management</h1>

            <div class="stat-cards">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $competitionCount; ?></div>
                    <div class="stat-label">Competitions</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $scheduledCompetitions; ?> / <?php echo $competitionCount; ?></div>
                    <div class="stat-label">Have a schedule generated</div>
                    <div class="stat-detail"><?php echo $competitionCount - $scheduledCompetitions; ?> still need fixtures</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $totalFixtures; ?></div>
                    <div class="stat-label">Total fixtures scheduled</div>
                </div>
            </div>

            <div class="action-cards">
                <button type="button" class="action-card" data-modal-open="scoreUpdateModal">
                    <div class="action-icon"><i class="fa fa-futbol-o"></i></div>
                    <div class="action-title">Update Score</div>
                    <div class="action-desc">Record a final or live score, with penalties if the tie went that far.</div>
                </button>
                <button type="button" class="action-card" data-modal-open="standingsUpdateModal">
                    <div class="action-icon"><i class="fa fa-trophy"></i></div>
                    <div class="action-title">Update Standings</div>
                    <div class="action-desc">Apply a match result's goals for/against to a team's standings row.</div>
                </button>
                <button type="button" class="action-card" data-modal-open="generateSeasonModal">
                    <div class="action-icon"><i class="fa fa-calendar"></i></div>
                    <div class="action-title">Generate Round-Robin Season</div>
                    <div class="action-desc">Wipe and reschedule a League-format competition's fixtures.</div>
                </button>
                <button type="button" class="action-card" data-modal-open="addKnockoutFixtureModal">
                    <div class="action-icon"><i class="fa fa-sitemap"></i></div>
                    <div class="action-title">Add Group / Knockout Fixture</div>
                    <div class="action-desc">Add one group-stage match or knockout tie at a time.</div>
                </button>
            </div>

            <div class="modal-overlay" id="scoreUpdateModal">
                <div class="admin-panel">
                    <button type="button" class="modal-close" data-modal-close>&times;</button>
                    <h3 class="fixture-title">Live Game Score Update🥅</h3>
                    <form method="post">
                        <input type="hidden" name="form_type" value="score_update">

                        <label for="fixtureId">Fixture</label>
                        <select id="fixtureId" name="fixtureId" required>
                            <?php foreach ($fixturePickerList as $f) { ?>
                            <option value="<?php echo $f['FIXTURE_ID']; ?>">
                                <?php echo htmlspecialchars($f['competitionName']); ?><?php echo $f['ROUND_NAME'] ? ' — ' . htmlspecialchars($f['ROUND_NAME']) : ''; ?><?php echo $f['GROUP_NAME'] ? ' (Group ' . htmlspecialchars($f['GROUP_NAME']) . ')' : ''; ?>:
                                <?php echo htmlspecialchars($f['homeTeam']); ?> vs <?php echo htmlspecialchars($f['awayTeam']); ?>
                                (<?php echo $f['MATCH_DATE']; ?>)<?php echo $f['HOME_SCORE'] !== null ? ' — ' . $f['HOME_SCORE'] . '-' . $f['AWAY_SCORE'] : ''; ?>
                            </option>
                            <?php } ?>
                        </select>

                        <label for="homeScore">Home score</label>
                        <input id="homeScore" type="text" name="homeScore" placeholder="Home score" required>

                        <label for="awayScore">Away score</label>
                        <input id="awayScore" type="text" name="awayScore" placeholder="Away score" required>

                        <label for="homePens">Penalties (only if decided on pens)</label>
                        <input id="homePens" type="text" name="homePens" placeholder="Home penalties">
                        <input type="text" name="awayPens" placeholder="Away penalties">

                        <input type="submit" value="Update Score">
                    </form>
                </div>
            </div>

            <div class="modal-overlay" id="standingsUpdateModal">
                <div class="admin-panel">
                    <button type="button" class="modal-close" data-modal-close>&times;</button>
                    <h3 class="fixture-title">Update Standings After a Match🏆</h3>
                    <p class="panel-hint">Adds one match's goals for/against to a team's running standings row.</p>
                    <form method="post">
                        <input type="hidden" name="form_type" value="league_update">

                        <label for="luCompetition">Competition</label>
                        <select id="luCompetition" name="competitionId" required>
                            <?php foreach ($competitions as $c) { ?>
                            <option value="<?php echo $c['COMPETITION_ID']; ?>"><?php echo htmlspecialchars($c['NAME']); ?></option>
                            <?php } ?>
                        </select>

                        <label for="luTeam">Team</label>
                        <select id="luTeam" name="teamId" required>
                            <?php foreach ($teams as $t) { ?>
                            <option value="<?php echo $t['TEAM_ID']; ?>"><?php echo htmlspecialchars($t['TEAM_NAME']); ?></option>
                            <?php } ?>
                        </select>

                        <label for="luGroup">Group name (only for Group + Knockout)</label>
                        <input id="luGroup" type="text" name="groupName" placeholder="e.g. A">

                        <label for="goalsFor">Goals for</label>
                        <input id="goalsFor" type="text" name="goalsFor" placeholder="Goals for" required>

                        <label for="goalsAgainst">Goals against</label>
                        <input id="goalsAgainst" type="text" name="goalsAgainst" placeholder="Goals against" required>

                        <input type="submit" value="Update Standings">
                    </form>
                </div>
            </div>

            <div class="modal-overlay" id="generateSeasonModal">
                <div class="admin-panel">
                    <button type="button" class="modal-close" data-modal-close>&times;</button>
                    <h3 class="fixture-title">Generate a Round-Robin Season</h3>
                    <p class="panel-hint">Only for League-format competitions. Wipes and reschedules that competition's fixtures.</p>
                    <form method="post">
                        <input type="hidden" name="form_type" value="generate_fixture">

                        <label for="genCompetition">Competition</label>
                        <select id="genCompetition" name="competitionId" required>
                            <?php foreach ($leagueCompetitions as $c) { ?>
                            <option value="<?php echo $c['COMPETITION_ID']; ?>"><?php echo htmlspecialchars($c['NAME']); ?></option>
                            <?php } ?>
                        </select>

                        <label for="startDate">Season start date</label>
                        <input id="startDate" type="date" name="startDate" required>

                        <input type="submit" value="Generate Fixtures">
                    </form>
                </div>
            </div>

            <div class="modal-overlay" id="addKnockoutFixtureModal">
                <div class="admin-panel">
                    <button type="button" class="modal-close" data-modal-close>&times;</button>
                    <h3 class="fixture-title">Add Group / Knockout Fixture</h3>
                    <p class="panel-hint">Add one tie at a time for a Group + Knockout or Cup competition — a group-stage match, or a knockout round.</p>
                    <form method="post">
                        <input type="hidden" name="form_type" value="add_knockout_fixture">

                        <label for="kfCompetition">Competition</label>
                        <select id="kfCompetition" name="competitionId" required>
                            <?php foreach ($knockoutCompetitions as $c) { ?>
                            <option value="<?php echo $c['COMPETITION_ID']; ?>"><?php echo htmlspecialchars($c['NAME']); ?></option>
                            <?php } ?>
                        </select>

                        <label for="kfGroup">Group name (leave blank for knockout ties)</label>
                        <input id="kfGroup" type="text" name="groupName" placeholder="e.g. A">

                        <label for="kfRound">Round name</label>
                        <input id="kfRound" type="text" name="roundName" list="roundNameOptions" placeholder="e.g. Quarterfinal" required>
                        <datalist id="roundNameOptions">
                            <option value="Group Stage">
                            <option value="Preliminary Round">
                            <option value="Round 1">
                            <option value="Round 2">
                            <option value="1/16 Finals">
                            <option value="1/8 Finals">
                            <option value="Quarterfinal">
                            <option value="Semifinal">
                            <option value="Final">
                        </datalist>

                        <label for="kfOrder">Round order (sort order — 1, 2, 3…)</label>
                        <input id="kfOrder" type="number" name="roundOrder" placeholder="e.g. 1" required>

                        <label for="kfHome">Home team</label>
                        <select id="kfHome" name="homeTeamId" required>
                            <?php foreach ($teams as $t) { ?>
                            <option value="<?php echo $t['TEAM_ID']; ?>"><?php echo htmlspecialchars($t['TEAM_NAME']); ?></option>
                            <?php } ?>
                        </select>

                        <label for="kfAway">Away team</label>
                        <select id="kfAway" name="awayTeamId" required>
                            <?php foreach ($teams as $t) { ?>
                            <option value="<?php echo $t['TEAM_ID']; ?>"><?php echo htmlspecialchars($t['TEAM_NAME']); ?></option>
                            <?php } ?>
                        </select>

                        <label for="kfDate">Match date</label>
                        <input id="kfDate" type="date" name="matchDate" required>

                        <label for="kfVenue">Venue</label>
                        <input id="kfVenue" type="text" name="venue" placeholder="Venue" required>

                        <input type="submit" value="Add Fixture">
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

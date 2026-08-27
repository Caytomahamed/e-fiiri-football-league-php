<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: home.php");
    exit;
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
    <link rel="stylesheet" href="css/form.css">
</head>

<body>
    <?php
require_once "matchesFunctions.php";
require_once 'classes/Competition.php';
require_once 'classes/StandingTable.php';

$competition = new Competition();
$competitions = $competition->getAllCompetitions();
$regions = $competition->getDistinctRegions();
$competition->closeConnection();

$standingTable = new StandingTable();
$teams = $standingTable->getTeams();
$standingTable->closeConnection();
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
                <button class="dash-btn active">
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
            <h1>Competitions Management</h1>

            <div class="action-cards">
                <button type="button" class="action-card" data-modal-open="createCompetitionModal">
                    <div class="action-icon"><i class="fa fa-plus-circle"></i></div>
                    <div class="action-title">Create Competition</div>
                    <div class="action-desc">Set up a new league, group + knockout, or cup competition.</div>
                </button>
                <button type="button" class="action-card" data-modal-open="addTeamToCompetitionModal">
                    <div class="action-icon"><i class="fa fa-users"></i></div>
                    <div class="action-title">Add Team to Competition</div>
                    <div class="action-desc">Assign a team to a competition, and a group if it has one.</div>
                </button>
                <button type="button" class="action-card" data-modal-open="initStandingsModal">
                    <div class="action-icon"><i class="fa fa-list-ol"></i></div>
                    <div class="action-title">Initialize Standings</div>
                    <div class="action-desc">Create zeroed standings rows for every team in a competition.</div>
                </button>
            </div>

            <div class="modal-overlay" id="createCompetitionModal">
                <div class="admin-panel">
                    <button type="button" class="modal-close" data-modal-close>&times;</button>
                    <h3 class="fixture-title">Create Competition</h3>
                    <p class="panel-hint">A competition is one season of one tournament — a round-robin league, a group stage + knockout, or a straight knockout cup.</p>
                    <form method="post">
                        <input type="hidden" name="form_type" value="create_competition">

                        <label for="name">Name</label>
                        <input id="name" type="text" name="name" placeholder="e.g. Hargeysa First Division 2024/25" required>

                        <label for="type">Format</label>
                        <select id="type" name="type" required>
                            <option value="LEAGUE">League (round robin)</option>
                            <option value="GROUP_KNOCKOUT">Group stage + Knockout</option>
                            <option value="CUP">Cup (knockout)</option>
                        </select>

                        <label for="season">Season</label>
                        <input id="season" type="text" name="season" placeholder="e.g. 2024/25" required>

                        <label for="regionSelect">Region</label>
                        <select id="regionSelect" name="region" onchange="document.getElementById('regionOther').style.display = this.value === '__other__' ? 'block' : 'none';">
                            <option value="">National / no region</option>
                            <?php foreach ($regions as $r) { ?>
                            <option value="<?php echo htmlspecialchars($r); ?>"><?php echo htmlspecialchars($r); ?></option>
                            <?php } ?>
                            <option value="__other__">Other (new region)…</option>
                        </select>
                        <input id="regionOther" type="text" name="regionOther" placeholder="New region name" style="display:none;">

                        <label for="divisionLevel">Division</label>
                        <select id="divisionLevel" name="divisionLevel">
                            <option value="">None (Cup / Champions League)</option>
                            <option value="1">1st Division</option>
                            <option value="2">2nd Division</option>
                            <option value="3">3rd Division</option>
                        </select>

                        <input type="submit" value="Create Competition">
                    </form>
                </div>
            </div>

            <div class="modal-overlay" id="addTeamToCompetitionModal">
                <div class="admin-panel">
                    <button type="button" class="modal-close" data-modal-close>&times;</button>
                    <h3 class="fixture-title">Add Team to Competition</h3>
                    <p class="panel-hint">Group name only applies to Group + Knockout competitions (e.g. A, B, C…).</p>
                    <form method="post">
                        <input type="hidden" name="form_type" value="add_team_to_competition">

                        <label for="ctCompetition">Competition</label>
                        <select id="ctCompetition" name="competitionId" required>
                            <?php foreach ($competitions as $c) { ?>
                            <option value="<?php echo $c['COMPETITION_ID']; ?>"><?php echo htmlspecialchars($c['NAME']); ?></option>
                            <?php } ?>
                        </select>

                        <label for="ctTeam">Team</label>
                        <select id="ctTeam" name="teamId" required>
                            <?php foreach ($teams as $t) { ?>
                            <option value="<?php echo $t['TEAM_ID']; ?>"><?php echo htmlspecialchars($t['TEAM_NAME']); ?></option>
                            <?php } ?>
                        </select>

                        <label for="ctGroup">Group name (optional)</label>
                        <input id="ctGroup" type="text" name="groupName" placeholder="e.g. A">

                        <input type="submit" value="Add Team">
                    </form>
                </div>
            </div>

            <div class="modal-overlay" id="initStandingsModal">
                <div class="admin-panel">
                    <button type="button" class="modal-close" data-modal-close>&times;</button>
                    <h3 class="fixture-title">Initialize Standings</h3>
                    <p class="panel-hint">Creates a zeroed standings row for every team currently assigned to this competition. Run this once after adding teams, before recording results.</p>
                    <form method="post">
                        <input type="hidden" name="form_type" value="init_standings">

                        <label for="isCompetition">Competition</label>
                        <select id="isCompetition" name="competitionId" required>
                            <?php foreach ($competitions as $c) { ?>
                            <option value="<?php echo $c['COMPETITION_ID']; ?>"><?php echo htmlspecialchars($c['NAME']); ?></option>
                            <?php } ?>
                        </select>

                        <input type="submit" value="Initialize Standings">
                    </form>
                </div>
            </div>

            <div class="admin-panel">
                <h3 class="fixture-title">Existing Competitions</h3>
                <div class="admin-table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Season</th>
                                <th>Region</th>
                                <th>Division</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($competitions as $c) { ?>
                            <tr>
                                <td><?php echo $c['COMPETITION_ID']; ?></td>
                                <td><?php echo htmlspecialchars($c['NAME']); ?></td>
                                <td><?php echo $c['TYPE']; ?></td>
                                <td><?php echo htmlspecialchars($c['SEASON']); ?></td>
                                <td><?php echo htmlspecialchars($c['REGION'] ?? '-'); ?></td>
                                <td><?php echo $c['DIVISION_LEVEL'] ?? '-'; ?></td>
                                <td>
                                    <div class="row-actions">
                                        <button type="button" class="edit-action" title="Edit"
                                            onclick='openEditModal("editCompetitionModal", <?php echo json_encode([
                                                "competitionId" => $c["COMPETITION_ID"],
                                                "name" => $c["NAME"],
                                                "type" => $c["TYPE"],
                                                "season" => $c["SEASON"],
                                                "regionOther" => $c["REGION"],
                                                "divisionLevel" => (string) ($c["DIVISION_LEVEL"] ?? ''),
                                            ]); ?>)'>
                                            <i class="fa fa-pencil"></i>
                                        </button>
                                        <form method="post" onsubmit="return confirm('Delete <?php echo htmlspecialchars(addslashes($c['NAME'])); ?> and all of its fixtures/standings? This can\'t be undone.');" style="display:inline;">
                                            <input type="hidden" name="form_type" value="delete_competition">
                                            <input type="hidden" name="competitionId" value="<?php echo $c['COMPETITION_ID']; ?>">
                                            <button type="submit" class="delete-action" title="Delete"><i class="fa fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div style="height:100px">
            </div>
        </div>

    </div>

    <!-- Shared Edit Competition modal, filled by openEditModal() from the clicked row -->
    <div class="modal-overlay" id="editCompetitionModal">
        <div class="admin-panel">
            <button type="button" class="modal-close" data-modal-close>&times;</button>
            <h3 class="fixture-title">Edit Competition</h3>
            <form method="post">
                <input type="hidden" name="form_type" value="update_competition">
                <input type="hidden" name="competitionId">

                <label for="editName">Name</label>
                <input id="editName" type="text" name="name" required>

                <label for="editType">Format</label>
                <select id="editType" name="type" required>
                    <option value="LEAGUE">League (round robin)</option>
                    <option value="GROUP_KNOCKOUT">Group stage + Knockout</option>
                    <option value="CUP">Cup (knockout)</option>
                </select>

                <label for="editSeason">Season</label>
                <input id="editSeason" type="text" name="season" required>

                <label for="editRegionOther">Region</label>
                <select name="region" style="display:none;"><option value="__other__" selected>Other</option></select>
                <input id="editRegionOther" type="text" name="regionOther" placeholder="Region (blank for national)">

                <label for="editDivisionLevel">Division</label>
                <select id="editDivisionLevel" name="divisionLevel">
                    <option value="">None (Cup / Champions League)</option>
                    <option value="1">1st Division</option>
                    <option value="2">2nd Division</option>
                    <option value="3">3rd Division</option>
                </select>

                <input type="submit" value="Save Changes">
            </form>
        </div>
    </div>

    <script src="index.js"></script>
</body>

</html>

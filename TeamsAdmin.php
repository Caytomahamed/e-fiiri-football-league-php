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
require_once 'classes/StandingTable.php';

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
                <button class="dash-btn active">
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
            <h1>Teams Management</h1>

            <div class="modal-trigger">
                <button class="btn-primary" data-modal-open="createTeamModal"><i class="fa fa-plus"></i> Create Team</button>
            </div>

            <div class="admin-panel">
                <h3 class="fixture-title">All Teams</h3>
                <div class="admin-table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Team Id</th>
                                <th>Team name</th>
                                <th>Stadium</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($teams as $team) { ?>
                            <tr>
                                <td><?php echo $team['TEAM_ID']; ?></td>
                                <td>
                                    <div>
                                        <img src="images/teams/<?php echo explode(' ', $team['TEAM_NAME'])[0]; ?>.svg" alt="team logo" onerror="this.style.display='none'">
                                        <p><?php echo htmlspecialchars($team['TEAM_NAME']); ?></p>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($team['HOME_STADIUM']); ?></td>
                                <td>
                                    <div class="row-actions">
                                        <button type="button" class="edit-action" title="Edit"
                                            onclick='openEditModal("editTeamModal", <?php echo json_encode([
                                                "teamId" => $team["TEAM_ID"],
                                                "teamName" => $team["TEAM_NAME"],
                                                "homeStadium" => $team["HOME_STADIUM"],
                                            ]); ?>)'>
                                            <i class="fa fa-pencil"></i>
                                        </button>
                                        <form method="post" onsubmit="return confirm('Delete <?php echo htmlspecialchars(addslashes($team['TEAM_NAME'])); ?>? This can\'t be undone.');" style="display:inline;">
                                            <input type="hidden" name="form_type" value="delete_team">
                                            <input type="hidden" name="teamId" value="<?php echo $team['TEAM_ID']; ?>">
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

            <div style="height:100px"></div>
        </div>
    </div>

    <!-- Create Team modal -->
    <div class="modal-overlay" id="createTeamModal">
        <div class="admin-panel">
            <button type="button" class="modal-close" data-modal-close>&times;</button>
            <h3 class="fixture-title">Create Team</h3>
            <form method="post">
                <input type="hidden" name="form_type" value="create_team">

                <label for="teamName">Team name</label>
                <input id="teamName" type="text" name="teamName" placeholder="e.g. Gaashaan FC (Awdal)" required>

                <label for="homeStadium">Home stadium</label>
                <input id="homeStadium" type="text" name="homeStadium" placeholder="e.g. Boorama Stadium" required>

                <input type="submit" value="Create Team">
            </form>
        </div>
    </div>

    <!-- Shared Edit Team modal, filled by openEditModal() from the clicked row -->
    <div class="modal-overlay" id="editTeamModal">
        <div class="admin-panel">
            <button type="button" class="modal-close" data-modal-close>&times;</button>
            <h3 class="fixture-title">Edit Team</h3>
            <form method="post">
                <input type="hidden" name="form_type" value="update_team">
                <input type="hidden" name="teamId">

                <label for="editTeamName">Team name</label>
                <input id="editTeamName" type="text" name="teamName" required>

                <label for="editHomeStadium">Home stadium</label>
                <input id="editHomeStadium" type="text" name="homeStadium" required>

                <input type="submit" value="Save Changes">
            </form>
        </div>
    </div>

    <script src="index.js"></script>
</body>

</html>

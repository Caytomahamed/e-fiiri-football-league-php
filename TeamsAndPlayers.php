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
require_once 'classes/Players.php';

$standingTable = new StandingTable();
$Teams = $standingTable->getTeams();
$standingTable->closeConnection();

$playersClass = new Players();
$playerList = $playersClass->getPlayers();
$playersClass->closeConnection();
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
                <button class="dash-btn active">
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
            <h1>Players Management</h1>

            <div class="modal-trigger">
                <button class="btn-primary" data-modal-open="addPlayerModal"><i class="fa fa-plus"></i> Add Player</button>
            </div>

            <div class="admin-panel">
                <h3 class="fixture-title">Player Roster</h3>
                <div class="admin-table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Position</th>
                                <th>Club</th>
                                <th>Nationality</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($playerList) > 0) {
                                foreach ($playerList as $player) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars(trim($player['FirstName'] . ' ' . $player['MiddleName'] . ' ' . $player['LastName'])); ?></td>
                                <td><?php echo htmlspecialchars($player['PlayerPosition']); ?></td>
                                <td><?php echo htmlspecialchars($player['ClubName']); ?></td>
                                <td><?php echo htmlspecialchars($player['Nationality']); ?></td>
                            </tr>
                            <?php }
                            } else { ?>
                            <tr>
                                <td colspan="4">No players found.</td>
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

    <!-- Add Player modal -->
    <div class="modal-overlay" id="addPlayerModal">
        <div class="admin-panel">
            <button type="button" class="modal-close" data-modal-close>&times;</button>
            <h3 class="fixture-title">Add New Player Into Team🚶‍♂️</h3>
            <form method="post">
                <input type="hidden" name="form_type" value="add_player">

                <label for="team_id">Team</label>
                <select id="team_id" name="team_id" required>
                    <?php foreach ($Teams as $team) { ?>
                    <option value="<?php echo $team['TEAM_ID']; ?>"><?php echo htmlspecialchars($team['TEAM_NAME']); ?></option>
                    <?php } ?>
                </select>

                <label for="fname">First name</label>
                <input id="fname" type="text" name="fname" placeholder="First Name" required>

                <label for="mname">Middle name</label>
                <input id="mname" type="text" name="mname" placeholder="Middle Name">

                <label for="lname">Last name</label>
                <input id="lname" type="text" name="lname" placeholder="Last Name" required>

                <label for="dob">Date of birth</label>
                <input id="dob" type="date" name="dob" required>

                <label for="position">Position</label>
                <input id="position" type="text" name="position" placeholder="Position" required>

                <label for="weight">Weight (kg)</label>
                <input id="weight" type="number" name="weight" placeholder="Weight" required>

                <label for="height">Height (cm)</label>
                <input id="height" type="number" name="height" placeholder="Height" required>

                <label for="nationality">Nationality</label>
                <input id="nationality" type="text" name="nationality" placeholder="Nationality" required>

                <label for="kit_number">Kit number</label>
                <input id="kit_number" type="number" name="kit_number" placeholder="Kit Number" required>

                <input type="submit" value="Add Player">
            </form>
        </div>
    </div>

    <script src="index.js"></script>
</body>

</html>

<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php");
    exit;
}

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
    <?php
require_once "matchesFunctions.php";
require_once 'classes/StandingTable.php';
// Create instances of classes
$standingTable = new StandingTable();

// Get standing table
$Teams = $standingTable->getTeams();

?>
    <div class="dashboard">
        <div class="sidebar">
            <img src="images/Stadium.svg" alt="">
            <a href="./admin.php"><button class="dash-btn ">
                    Standing Table</button></a>

            <a href="./FixturesAdmin.php">
                <button class="dash-btn ">
                    Fixtures
                </button>
            </a>
            <a href="./TeamsAndPlayers.php">
                <button class="dash-btn active">
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
            <h1>Teams and Players Management</h1>
            <div class="lives" style="display:flex;width:1000px;">
                <div class="live-score">
                    <h3 class="fixture-title ">Add New Player Into Team🚶‍♂️</h3>
                    <form method="post" style="width:400px;">
                        <input type="hidden" name="form_type" value="add_player">
                        <input type="number" name="team_id" placeholder="Team ID" required>
                        <input type="text" name="fname" placeholder="First Name" required>
                        <input type="text" name="mname" placeholder="Middle Name">
                        <input type="text" name="lname" placeholder="Last Name" required>
                        <input type="date" name="dob" placeholder="Date of Birth" required>
                        <input type="text" name="position" placeholder="Position" required>
                        <input type="number" name="weight" placeholder="Weight" required>
                        <input type="number" name="height" placeholder="Height" required>
                        <input type="text" name="nationality" placeholder="Nationality" required>
                        <input type="number" name="kit_number" placeholder="Kit Number" required>
                        <input type="submit" value="Add Player">
                    </form>
                </div>
                <div style="margin-left:40px; margin-top:10px">
                    <h3 class="fixture-title ">Team information</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Team Id</th>
                                <th>Team name</th>
                                <th>Stadium</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                            foreach ($Teams as $team) {
                            echo '<tr>
                                <td>' . $team['TEAM_ID'] . '</td>
                                <td><div>
                                <img src="images/teams/' . explode(' ', $team["TEAM_NAME"])[0] . '.svg" alt="team logo">
                                <p>' . $team["TEAM_NAME"] . '</p>
                                </div></td>
                                <td>' . $team['HOME_STADIUM'] . '</td>
                                </tr>';
                            }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div style="height:100px">
            </div>
        </div>

    </div>
    <script src="index.js"></script>
</body>

</html>
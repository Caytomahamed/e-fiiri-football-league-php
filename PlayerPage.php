<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php
require_once 'classes/Players.php';

// Create instances of classes
$players = new Players();

// Get players
$playerList = $players->getPlayers();
// var_dump($playerList);

$players->closeConnection();
?>
          <div class="players-con">
        <?php
if (count($playerList) > 0) {
    foreach ($playerList as $player) {
        // print_r($player)
        ?>
        <div class="player">
          <h3><?php echo $player['FirstName'] . ' ' . $player['MiddleName'] . ' ' . $player['LastName']; ?></h3>
          <p><?php echo $player['PlayerPosition']; ?></p>
          <div class="team">
            <img src="images/teams/<?php echo explode(' ', $player['ClubName'])[0]; ?>.svg" alt="team logo">
            <p><?php echo $player['ClubName']; ?></p>
          </div>
          <p><?php echo $player['Nationality']; ?></p>
        </div>

            <?php
}
} else {
    echo "No Player was found.";
}
?>
      </div>
</body>
</html>
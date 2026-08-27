<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Premier League Table</title>
  <link rel="stylesheet" href="css/index.css?v=<?php echo time(); ?>" type="text/css">
  <link rel="stylesheet" href="css/form.css" type="text/css">
    <script src="index.js" defer></script>
</head>
<body id="body">
  <div class="container">
    <div class="account-bar">
      <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) { ?>
      <a href="./admin.php"><button>Admin Dashboard</button></a>
      <a href="./logout.php"><button>Logout</button></a>
      <?php } else { ?>
      <button data-modal-open="loginModal">Login</button>
      <?php } ?>
    </div>

    <h1>The Somaliland Football Championship</h1>

    <?php require_once './competitionSelector.php' ?>

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

  <div class="modal-overlay<?php echo isset($_GET['login_error']) ? ' open' : ''; ?>" id="loginModal">
    <div class="modal-box">
      <button type="button" class="modal-close" data-modal-close>&times;</button>
      <h2 style="margin-bottom: 16px;">Admin Login</h2>
      <?php if (isset($_GET['login_error'])) { ?>
      <p style="color: #ff5c5c; margin-bottom: 10px;">Invalid email or password</p>
      <?php } ?>
      <form method="post" action="Login.php">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="submit" value="Login">
      </form>
    </div>
  </div>

</body>
</html>

 <!-- background-color: #1f2937;
 /* background-color: #19212c; */ -->

# Gridlock

A tic-tac-toe app for a PHP, Apache, and MySQL (LAMP) server. Players create accounts, start multiplayer games with a six-character invite code, or play solo against the medium-strength Gridlock AI. Wins, losses, draws, and the leaderboard are stored in MySQL.

## Requirements

- PHP 8.1 or newer with PDO MySQL enabled
- MySQL 8.0+ or MariaDB 10.5+
- Apache (or another PHP-capable web server)

## Setup

1. Copy the project files into a web-served directory.
2. Import `schema.sql` into MySQL. The script creates the `multi_tictactoe` database and its tables.
3. Configure the PHP process environment with database credentials:

   ```text
   DB_HOST=localhost
   DB_NAME=multi_tictactoe
   DB_USER=your_app_user
   DB_PASS=your_app_password
   ```

   Grant that MySQL user access to the `multi_tictactoe` database. `config.php` defaults to a local root account for development; set the environment variables for any deployment.
4. Serve the directory through Apache with PHP enabled, and open `index.php` in a browser. HTTPS is recommended; session cookies are marked secure automatically when served over HTTPS.

No build step or third-party PHP packages are required. Keep `schema.sql` outside public access in production if your web server exposes SQL files for download.

### Existing installations

Back up your database, then run `upgrade-single-player.sql` once against the existing `multi_tictactoe` database before deploying the updated PHP files. This adds the game-mode and winner-symbol columns while keeping existing games as multiplayer.

## Play

Create an account or sign in, then select **Start a game** and share the displayed code with another signed-in player, or choose **Play against AI** for a solo game. In either mode, X starts. The AI plays as O, taking an immediate win when available, blocking an immediate player win, and otherwise preferring the center and corners. The server validates moves and updates stats when a game ends. AI games count toward player stats and the leaderboard; the leaderboard ranks players by wins, with games played as the tiebreaker.

Game moves are validated and serialized in MySQL transactions. The client polls for updates, so the app works on ordinary PHP hosting without a separate websocket server.

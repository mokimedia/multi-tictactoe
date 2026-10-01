# Gridlock

A tic-tac-toe and American checkers app for a PHP, Apache, and MySQL (LAMP) server. Players create accounts, start multiplayer games with a six-character invite code, or play solo against Gridlock AI at easy, medium, or hard difficulty. The interface is available in English, German, French, and Spanish. Wins, losses, draws, and the leaderboard are stored in MySQL.

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

Back up your database, then run the migration matching your current schema once against the existing `multi_tictactoe` database before deploying the updated PHP files:

- If you have only multiplayer mode, run `upgrade-single-player.sql`, then `upgrade-ai-difficulty.sql`, `upgrade-checkers.sql`, and `upgrade-abort.sql`.
- If you have single-player mode but no AI difficulty selector, run `upgrade-ai-difficulty.sql`, then `upgrade-checkers.sql` and `upgrade-abort.sql`.
- If you already have selectable AI difficulty, run `upgrade-checkers.sql`, then `upgrade-abort.sql`.

Existing games keep their multiplayer mode and use medium as the default AI difficulty.

## Play

Choose **Tic-tac-toe** or **Checkers** in the game selector, then select **Start a game** and share the code with another signed-in player, or choose an AI difficulty and select **Play against AI**. Checkers follows American rules: an 8×8 board, forward-moving men, kings that move both ways, mandatory captures, and continued jumps when available. Easy AI plays randomly, medium uses capture and promotion tactics, and hard looks ahead for replies. Tic-tac-toe AI difficulty works as before. The server validates moves and updates stats when a game ends. AI games count toward player stats and the leaderboard. Use the language selector to switch between English, German, French, and Spanish; the selection is remembered in your browser.

Game moves are validated and serialized in MySQL transactions. The client polls for updates, so the app works on ordinary PHP hosting without a separate websocket server.

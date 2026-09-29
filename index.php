<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#11131a">
    <title>Gridlock — Multiplayer Tic-Tac-Toe</title>
    <link rel="stylesheet" href="styles.css">
    <script src="app.js" defer></script>
</head>
<body>
    <main class="page-shell">
        <header class="site-header">
            <a class="brand" href="./" aria-label="Gridlock home"><span class="brand-mark">G</span> GRIDLOCK</a>
            <span class="header-note">THE CLASSIC, HEAD TO HEAD</span>
        </header>

        <section id="auth-view" class="auth-layout" aria-labelledby="welcome-title" hidden>
            <div class="intro">
                <p class="eyebrow">A LITTLE FRIENDLY COMPETITION</p>
                <h1 id="welcome-title">Make your<br>next move.</h1>
                <p class="intro-copy">Start a game, share your code, and see who gets three in a row. Your record follows you from game to game.</p>
                <div class="mini-board" aria-hidden="true"><span>X</span><span></span><span>O</span><span></span><span>X</span><span></span><span>O</span><span></span><span>X</span></div>
            </div>
            <div class="auth-card">
                <div class="auth-tabs" role="tablist" aria-label="Account action">
                    <button class="tab-button is-active" id="login-tab" type="button" role="tab" aria-selected="true">Sign in</button>
                    <button class="tab-button" id="register-tab" type="button" role="tab" aria-selected="false">Create account</button>
                </div>
                <form id="auth-form">
                    <label for="username">Username</label>
                    <input id="username" name="username" autocomplete="username" minlength="3" maxlength="20" pattern="[A-Za-z0-9_]{3,20}" required>
                    <p class="field-hint">3–20 letters, numbers, or underscores</p>
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" minlength="10" maxlength="72" required>
                    <p class="field-hint">At least 10 characters</p>
                    <button class="button button-primary auth-submit" type="submit">Sign in <span aria-hidden="true">→</span></button>
                    <p id="auth-message" class="message" role="alert"></p>
                </form>
            </div>
        </section>

        <section id="app-view" hidden>
            <div class="dashboard-heading">
                <div><p class="eyebrow">YOUR GAME ROOM</p><h1>Welcome back, <span id="welcome-name"></span></h1></div>
                <button id="logout-button" class="button button-quiet" type="button">Sign out</button>
            </div>

            <div class="dashboard-grid">
                <section class="panel game-panel" aria-labelledby="game-room-title">
                    <div class="panel-heading"><div><p class="eyebrow">PLAY NOW</p><h2 id="game-room-title">Game room</h2></div><span id="game-status" class="status-pill">No game selected</span></div>
                    <div class="game-actions">
                        <button id="create-game" class="button button-primary" type="button">＋ Start a game</button>
                        <button id="create-ai-game" class="button button-outline" type="button">Play against AI</button>
                        <form id="join-form" class="join-form">
                            <label class="visually-hidden" for="game-code">Game code</label>
                            <input id="game-code" name="code" placeholder="ENTER CODE" maxlength="6" autocomplete="off" required>
                            <button class="button button-outline" type="submit">Join game</button>
                        </form>
                    </div>
                    <p id="game-message" class="message" role="status"></p>
                    <div id="game-card" class="game-card" hidden>
                        <div class="game-info"><span id="game-info-label" class="eyebrow">GAME CODE</span><strong id="current-code" class="game-code"></strong><button id="copy-code" class="copy-button" type="button">Copy</button></div>
                        <p id="turn-message" class="turn-message" aria-live="polite"></p>
                        <div id="board" class="board" role="group" aria-label="Tic-tac-toe board">
                            <button type="button" data-cell="0" aria-label="Top left"></button><button type="button" data-cell="1" aria-label="Top middle"></button><button type="button" data-cell="2" aria-label="Top right"></button>
                            <button type="button" data-cell="3" aria-label="Middle left"></button><button type="button" data-cell="4" aria-label="Center"></button><button type="button" data-cell="5" aria-label="Middle right"></button>
                            <button type="button" data-cell="6" aria-label="Bottom left"></button><button type="button" data-cell="7" aria-label="Bottom middle"></button><button type="button" data-cell="8" aria-label="Bottom right"></button>
                        </div>
                    </div>
                    <div class="recent-games">
                        <h3>Your games</h3>
                        <div id="game-list" class="game-list"><p class="empty-state">Your games will show up here.</p></div>
                    </div>
                </section>

                <aside class="side-column">
                    <section class="panel stats-panel" aria-labelledby="stats-title">
                        <div class="panel-heading"><div><p class="eyebrow">THE SCORE SO FAR</p><h2 id="stats-title">Your stats</h2></div></div>
                        <div class="stats-grid">
                            <div><strong id="stat-played">0</strong><span>Played</span></div>
                            <div><strong id="stat-wins">0</strong><span>Wins</span></div>
                            <div><strong id="stat-losses">0</strong><span>Losses</span></div>
                            <div><strong id="stat-draws">0</strong><span>Draws</span></div>
                        </div>
                    </section>
                    <section class="panel leaderboard-panel" aria-labelledby="leaderboard-title">
                        <div class="panel-heading"><div><p class="eyebrow">TOP PLAYERS</p><h2 id="leaderboard-title">Leaderboard</h2></div><span class="leaderboard-icon" aria-hidden="true">↗</span></div>
                        <div id="leaderboard"></div>
                    </section>
                </aside>
            </div>
        </section>
        <footer><span>GRIDLOCK</span><span>Good games are just one move away.</span></footer>
    </main>
</body>
</html>

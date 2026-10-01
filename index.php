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
            <a class="brand" href="./" aria-label="Gridlock home" data-i18n-aria-label="home"><span class="brand-mark">G</span> GRIDLOCK</a>
            <div class="header-tools">
                <span class="header-note" data-i18n="tagline">THE CLASSIC, HEAD TO HEAD</span>
                <label class="visually-hidden" for="language-select" data-i18n="language">Language</label>
                <select id="language-select" class="language-select" aria-label="Language">
                    <option value="en">English</option>
                    <option value="de">Deutsch</option>
                    <option value="fr">Français</option>
                    <option value="es">Español</option>
                </select>
            </div>
        </header>

        <section id="auth-view" class="auth-layout" aria-labelledby="welcome-title" hidden>
            <div class="intro">
                <p class="eyebrow" data-i18n="auth_eyebrow">A LITTLE FRIENDLY COMPETITION</p>
                <h1 id="welcome-title" data-i18n="welcome_title">Make your<br>next move.</h1>
                <p class="intro-copy" data-i18n="intro_copy">Start a game, share your code, and see who gets three in a row. Your record follows you from game to game.</p>
                <div class="mini-board" aria-hidden="true"><span>X</span><span></span><span>O</span><span></span><span>X</span><span></span><span>O</span><span></span><span>X</span></div>
            </div>
            <div class="auth-card">
                <div class="auth-tabs" role="tablist" aria-label="Account action">
                    <button class="tab-button is-active" id="login-tab" type="button" role="tab" aria-selected="true" data-i18n="sign_in">Sign in</button>
                    <button class="tab-button" id="register-tab" type="button" role="tab" aria-selected="false" data-i18n="create_account">Create account</button>
                </div>
                <form id="auth-form">
                    <label for="username" data-i18n="username">Username</label>
                    <input id="username" name="username" autocomplete="username" minlength="3" maxlength="20" pattern="[A-Za-z0-9_]{3,20}" required>
                    <p class="field-hint" data-i18n="username_hint">3–20 letters, numbers, or underscores</p>
                    <label for="password" data-i18n="password">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" minlength="10" maxlength="72" required>
                    <p class="field-hint" data-i18n="password_hint">At least 10 characters</p>
                    <button class="button button-primary auth-submit" type="submit"><span data-i18n="sign_in">Sign in</span><span aria-hidden="true">→</span></button>
                    <p id="auth-message" class="message" role="alert"></p>
                </form>
            </div>
        </section>

        <section id="app-view" hidden>
            <div class="dashboard-heading">
                <div><p class="eyebrow" data-i18n="your_game_room">YOUR GAME ROOM</p><h1><span data-i18n="welcome_back">Welcome back,</span> <span id="welcome-name"></span></h1></div>
                <button id="logout-button" class="button button-quiet" type="button" data-i18n="sign_out">Sign out</button>
            </div>

            <div class="dashboard-grid">
                <section class="panel game-panel" aria-labelledby="game-room-title">
                    <div class="panel-heading"><div><p class="eyebrow" data-i18n="play_now">PLAY NOW</p><h2 id="game-room-title" data-i18n="game_room">Game room</h2></div><button id="game-status" class="status-pill" type="button" data-i18n="no_game" disabled>No game selected</button></div>
                    <div class="game-actions">
                        <label class="visually-hidden" for="game-kind" data-i18n="choose_game">Choose game</label>
                        <select id="game-kind" class="game-kind-select" aria-label="Choose game">
                            <option value="tic_tac_toe" data-i18n="tic_tac_toe">Tic-tac-toe</option>
                            <option value="checkers" data-i18n="checkers">Checkers</option>
                        </select>
                        <button id="create-game" class="button button-primary" type="button"><span aria-hidden="true">＋</span> <span data-i18n="start_game">Start a game</span></button>
                        <div class="ai-options">
                            <label class="visually-hidden" for="ai-difficulty" data-i18n="ai_difficulty">AI difficulty</label>
                            <select id="ai-difficulty" aria-label="AI difficulty">
                                <option value="easy" data-i18n="easy">Easy</option>
                                <option value="medium" selected data-i18n="medium">Medium</option>
                                <option value="hard" data-i18n="hard">Hard</option>
                            </select>
                            <button id="create-ai-game" class="button button-outline" type="button"><span data-i18n="play_ai">Play against AI</span></button>
                        </div>
                        <form id="join-form" class="join-form">
                            <label class="visually-hidden" for="game-code" data-i18n="game_code">Game code</label>
                            <input id="game-code" name="code" placeholder="ENTER CODE" data-i18n-placeholder="enter_code" maxlength="6" autocomplete="off" required>
                            <button class="button button-outline" type="submit" data-i18n="join_game">Join game</button>
                        </form>
                    </div>
                    <p id="game-message" class="message" role="status"></p>
                    <section id="game-instructions" class="game-instructions" aria-labelledby="instructions-title" hidden>
                        <h3 id="instructions-title" data-i18n="checkers_rules_title">How to play Checkers</h3>
                        <ul>
                            <li data-i18n="checkers_rule_move">Men move one diagonal square forward; kings move one diagonal square in either direction.</li>
                            <li data-i18n="checkers_rule_capture">Jump an adjacent opponent to capture it. Captures are mandatory.</li>
                            <li data-i18n="checkers_rule_chain">If another capture is available after a jump, continue with the same piece.</li>
                            <li data-i18n="checkers_rule_king">A man reaching the far edge becomes a king.</li>
                            <li data-i18n="checkers_rule_win">Capture all opposing pieces or leave your opponent with no legal moves to win.</li>
                        </ul>
                    </section>
                    <div id="game-card" class="game-card" hidden>
                        <div class="game-info"><span id="game-info-label" class="eyebrow" data-i18n="game_code_caps">GAME CODE</span><strong id="current-code" class="game-code"></strong><button id="copy-code" class="copy-button" type="button" data-i18n="copy">Copy</button></div>
                        <p id="turn-message" class="turn-message" aria-live="polite"></p>
                        <div id="board" class="board" role="group" aria-label="Tic-tac-toe board" data-i18n-aria-label="board_label">
                            <button type="button" data-cell="0" aria-label="Top left" data-i18n-aria-label="top_left"></button><button type="button" data-cell="1" aria-label="Top middle" data-i18n-aria-label="top_middle"></button><button type="button" data-cell="2" aria-label="Top right" data-i18n-aria-label="top_right"></button>
                            <button type="button" data-cell="3" aria-label="Middle left" data-i18n-aria-label="middle_left"></button><button type="button" data-cell="4" aria-label="Center" data-i18n-aria-label="center"></button><button type="button" data-cell="5" aria-label="Middle right" data-i18n-aria-label="middle_right"></button>
                            <button type="button" data-cell="6" aria-label="Bottom left" data-i18n-aria-label="bottom_left"></button><button type="button" data-cell="7" aria-label="Bottom middle" data-i18n-aria-label="bottom_middle"></button><button type="button" data-cell="8" aria-label="Bottom right" data-i18n-aria-label="bottom_right"></button>
                        </div>
                        <div id="checkers-board" class="checkers-board" role="group" aria-label="Checkers board" data-i18n-aria-label="checkers_board_label" hidden></div>
                    </div>
                    <div class="recent-games">
                        <h3 data-i18n="your_games">Your games</h3>
                        <div id="game-list" class="game-list"><p class="empty-state" data-i18n="games_empty">Your games will show up here.</p></div>
                    </div>
                </section>

                <aside class="side-column">
                    <section class="panel stats-panel" aria-labelledby="stats-title">
                        <div class="panel-heading"><div><p class="eyebrow" data-i18n="score_so_far">THE SCORE SO FAR</p><h2 id="stats-title" data-i18n="your_stats">Your stats</h2></div></div>
                        <div class="stats-grid">
                            <div><strong id="stat-played">0</strong><span data-i18n="played">Played</span></div>
                            <div><strong id="stat-wins">0</strong><span data-i18n="wins">Wins</span></div>
                            <div><strong id="stat-losses">0</strong><span data-i18n="losses">Losses</span></div>
                            <div><strong id="stat-draws">0</strong><span data-i18n="draws">Draws</span></div>
                        </div>
                    </section>
                    <section class="panel leaderboard-panel" aria-labelledby="leaderboard-title">
                        <div class="panel-heading"><div><p class="eyebrow" data-i18n="top_players">TOP PLAYERS</p><h2 id="leaderboard-title" data-i18n="leaderboard">Leaderboard</h2></div><span class="leaderboard-icon" aria-hidden="true">↗</span></div>
                        <div id="leaderboard"></div>
                    </section>
                </aside>
            </div>
        </section>
        <footer><span>GRIDLOCK</span><span data-i18n="footer_tagline">Good games are just one move away.</span></footer>
    </main>
</body>
</html>

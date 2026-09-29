const authView = document.querySelector('#auth-view');
const appView = document.querySelector('#app-view');
const authForm = document.querySelector('#auth-form');
const authMessage = document.querySelector('#auth-message');
const gameMessage = document.querySelector('#game-message');
const boardButtons = [...document.querySelectorAll('#board button')];

let csrf = '';
let authMode = 'login';
let selectedGameId = null;
let selectedGameStatus = null;
let refreshTimer = null;

async function api(action, payload = {}, method = 'POST') {
    const options = { method, headers: { 'Content-Type': 'application/json' } };
    if (method === 'GET') {
        options.headers = {};
    } else {
        options.body = JSON.stringify({ ...payload, csrf, action });
    }

    const response = await fetch(method === 'GET' ? `api.php?action=${encodeURIComponent(action)}` : 'api.php', options);
    const responseText = await response.text();
    let result;
    try {
        result = JSON.parse(responseText);
    } catch {
        const contentType = response.headers.get('content-type') || 'unknown content type';
        throw new Error(`The server returned an invalid response (HTTP ${response.status}, ${contentType}). Check the PHP server logs and confirm api.php is being served by PHP.`);
    }

    if (!response.ok) {
        throw new Error(result.error || 'The request could not be completed.');
    }
    return result;
}

function setMessage(element, text, isError = false) {
    element.textContent = text;
    element.classList.toggle('is-error', isError);
}

function showAuth() {
    clearInterval(refreshTimer);
    authView.hidden = false;
    appView.hidden = true;
}

function showApp() {
    authView.hidden = true;
    appView.hidden = false;
}

function setAuthMode(mode) {
    authMode = mode;
    const isLogin = mode === 'login';
    document.querySelector('#login-tab').classList.toggle('is-active', isLogin);
    document.querySelector('#register-tab').classList.toggle('is-active', !isLogin);
    document.querySelector('#login-tab').setAttribute('aria-selected', String(isLogin));
    document.querySelector('#register-tab').setAttribute('aria-selected', String(!isLogin));
    document.querySelector('#password').autocomplete = isLogin ? 'current-password' : 'new-password';
    document.querySelector('.auth-submit').innerHTML = isLogin
        ? 'Sign in <span aria-hidden="true">→</span>'
        : 'Create account <span aria-hidden="true">→</span>';
    setMessage(authMessage, '');
}

function renderDashboard(data) {
    document.querySelector('#welcome-name').textContent = data.user.username;
    document.querySelector('#stat-played').textContent = data.user.games_played;
    document.querySelector('#stat-wins').textContent = data.user.wins;
    document.querySelector('#stat-losses').textContent = data.user.losses;
    document.querySelector('#stat-draws').textContent = data.user.draws;

    const gameList = document.querySelector('#game-list');
    gameList.replaceChildren();
    if (data.games.length === 0) {
        const empty = document.createElement('p');
        empty.className = 'empty-state';
        empty.textContent = 'Your games will show up here.';
        gameList.append(empty);
    }
    data.games.forEach((game) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'game-list-item';
        button.classList.toggle('is-selected', game.id === selectedGameId);
        const title = document.createElement('span');
        title.textContent = game.opponent
            ? `vs. ${game.opponent}`
            : 'Waiting for a player';
        const meta = document.createElement('span');
        meta.textContent = game.mode === 'single_player'
            ? `single player · ${game.status}`
            : `${game.code} · ${game.status}`;
        button.append(title, meta);
        button.addEventListener('click', () => selectGame(game.id));
        gameList.append(button);
    });

    const leaderboard = document.querySelector('#leaderboard');
    leaderboard.replaceChildren();
    if (data.leaderboard.length === 0) {
        const empty = document.createElement('p');
        empty.className = 'empty-state';
        empty.textContent = 'Play a game to appear on the leaderboard.';
        leaderboard.append(empty);
    } else {
        data.leaderboard.forEach((player, index) => {
            const row = document.createElement('div');
            row.className = 'leaderboard-row';
            const rank = document.createElement('span');
            rank.className = 'rank';
            rank.textContent = String(index + 1).padStart(2, '0');
            const name = document.createElement('span');
            name.className = 'leader-name';
            name.textContent = player.username;
            const wins = document.createElement('span');
            wins.className = 'leader-wins';
            wins.textContent = `${player.wins} W`;
            row.append(rank, name, wins);
            leaderboard.append(row);
        });
    }
}

async function refreshDashboard() {
    const data = await api('dashboard');
    renderDashboard(data);
}

function renderGame(game) {
    document.querySelector('#game-card').hidden = false;
    const isSinglePlayer = game.mode === 'single_player';
    document.querySelector('#game-info-label').textContent = isSinglePlayer ? 'OPPONENT' : 'GAME CODE';
    document.querySelector('#current-code').textContent = isSinglePlayer ? 'GRIDLOCK AI' : game.code;
    document.querySelector('#current-code').classList.toggle('game-opponent', isSinglePlayer);
    document.querySelector('#copy-code').hidden = isSinglePlayer;
    const status = document.querySelector('#game-status');
    status.textContent = game.status === 'waiting' ? 'Waiting for opponent' : game.status === 'finished' ? 'Game over' : 'In progress';
    status.classList.toggle('is-live', game.status === 'active');

    let turnMessage;
    if (game.status === 'waiting') {
        turnMessage = 'Share this code with a friend to play as O. You are X.';
    } else if (game.status === 'finished') {
        turnMessage = game.winner
            ? (game.winner === document.querySelector('#welcome-name').textContent ? 'You win!' : `${game.winner} wins!`)
            : 'It’s a draw!';
    } else if (isSinglePlayer) {
        turnMessage = 'Your turn — you are X.';
    } else {
        turnMessage = game.turn === game.your_symbol
            ? `Your turn — you are ${game.your_symbol}.`
            : `${game.opponent || 'Your opponent'} is thinking…`;
    }
    document.querySelector('#turn-message').textContent = turnMessage;

    boardButtons.forEach((button, index) => {
        const value = game.board[index];
        button.textContent = value === '-' ? '' : value;
        button.classList.toggle('mark-x', value === 'X');
        button.classList.toggle('mark-o', value === 'O');
        button.disabled = game.status !== 'active' || game.turn !== game.your_symbol || value !== '-';
    });

    const previousStatus = selectedGameStatus;
    selectedGameStatus = game.status;
    if (previousStatus !== null && previousStatus !== game.status) {
        refreshDashboard().catch((error) => setMessage(gameMessage, error.message, true));
    }
}

async function selectGame(gameId) {
    selectedGameId = gameId;
    try {
        const result = await api('state', { game_id: gameId });
        renderGame(result.game);
        await refreshDashboard();
        clearInterval(refreshTimer);
        refreshTimer = setInterval(() => {
            api('state', { game_id: selectedGameId })
                .then(({ game }) => renderGame(game))
                .catch((error) => setMessage(gameMessage, error.message, true));
        }, 2000);
    } catch (error) {
        setMessage(gameMessage, error.message, true);
    }
}

async function startApp() {
    showApp();
    await refreshDashboard();
    const list = document.querySelector('#game-list');
    const firstGame = list.querySelector('.game-list-item');
    if (firstGame) {
        firstGame.click();
    }
}

document.querySelector('#login-tab').addEventListener('click', () => setAuthMode('login'));
document.querySelector('#register-tab').addEventListener('click', () => setAuthMode('register'));

authForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    const formData = new FormData(authForm);
    try {
        await api(authMode === 'login' ? 'login' : 'register', {
            username: formData.get('username'),
            password: formData.get('password'),
        });
        authForm.reset();
        await startApp();
    } catch (error) {
        setMessage(authMessage, error.message, true);
    }
});

document.querySelector('#logout-button').addEventListener('click', async () => {
    try {
        await api('logout');
        selectedGameId = null;
        selectedGameStatus = null;
        document.querySelector('#game-card').hidden = true;
        showAuth();
    } catch (error) {
        setMessage(gameMessage, error.message, true);
    }
});

document.querySelector('#create-game').addEventListener('click', async () => {
    try {
        const { game } = await api('create');
        selectedGameStatus = null;
        setMessage(gameMessage, 'Game created. Share the code to invite a player.');
        await refreshDashboard();
        await selectGame(game.id);
    } catch (error) {
        setMessage(gameMessage, error.message, true);
    }
});

document.querySelector('#create-ai-game').addEventListener('click', async () => {
    try {
        const { game } = await api('create_ai');
        selectedGameStatus = null;
        setMessage(gameMessage, 'Game started. You are X; Gridlock AI is O.');
        await refreshDashboard();
        await selectGame(game.id);
    } catch (error) {
        setMessage(gameMessage, error.message, true);
    }
});

document.querySelector('#join-form').addEventListener('submit', async (event) => {
    event.preventDefault();
    const code = document.querySelector('#game-code').value.trim().toUpperCase();
    try {
        const { game } = await api('join', { code });
        document.querySelector('#game-code').value = '';
        selectedGameStatus = null;
        setMessage(gameMessage, 'You joined the game. Good luck!');
        await refreshDashboard();
        await selectGame(game.id);
    } catch (error) {
        setMessage(gameMessage, error.message, true);
    }
});

boardButtons.forEach((button) => {
    button.addEventListener('click', async () => {
        try {
            const { game } = await api('move', {
                game_id: selectedGameId,
                cell: Number(button.dataset.cell),
            });
            renderGame(game);
        } catch (error) {
            setMessage(gameMessage, error.message, true);
            if (selectedGameId) {
                api('state', { game_id: selectedGameId })
                    .then(({ game }) => renderGame(game))
                    .catch((refreshError) => setMessage(gameMessage, refreshError.message, true));
            }
        }
    });
});

document.querySelector('#copy-code').addEventListener('click', async () => {
    try {
        await navigator.clipboard.writeText(document.querySelector('#current-code').textContent);
        document.querySelector('#copy-code').textContent = 'Copied!';
        setTimeout(() => { document.querySelector('#copy-code').textContent = 'Copy'; }, 1500);
    } catch {
        setMessage(gameMessage, 'Could not copy the code. Select it to copy manually.', true);
    }
});

(async function initialize() {
    try {
        const session = await api('me', {}, 'GET');
        csrf = session.csrf;
        if (session.authenticated) {
            await startApp();
        } else {
            showAuth();
        }
    } catch {
        showAuth();
        setMessage(authMessage, 'Could not connect to the game server. Check your PHP and database configuration.', true);
    }
}());

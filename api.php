<?php
declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('log_errors', '1');

require_once __DIR__ . '/config.php';

ini_set('session.use_strict_mode', '1');
session_set_cookie_params([
    'httponly' => true,
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Strict',
]);
session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function respond(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_THROW_ON_ERROR);
    exit;
}

function request_data(): array
{
    try {
        $data = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        respond(['error' => 'Request body must be valid JSON.'], 400);
    }

    if (!is_array($data)) {
        respond(['error' => 'Request body must be a JSON object.'], 400);
    }

    return $data;
}

function current_user(): ?array
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $statement = db()->prepare('SELECT id, username, games_played, wins, losses, draws FROM users WHERE id = ?');
    $statement->execute([$_SESSION['user_id']]);
    $user = $statement->fetch();

    if (!$user) {
        unset($_SESSION['user_id']);
        return null;
    }

    return $user;
}

function require_user(): array
{
    $user = current_user();
    if (!$user) {
        respond(['error' => 'Please sign in to continue.'], 401);
    }

    return $user;
}

function verify_csrf(array $data): void
{
    $token = $data['csrf'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
        respond(['error' => 'Your session has expired. Refresh the page and try again.'], 403);
    }
}

function valid_username(mixed $value): ?string
{
    if (!is_string($value)) {
        return null;
    }

    $username = trim($value);
    return preg_match('/^[A-Za-z0-9_]{3,20}$/D', $username) ? $username : null;
}

function valid_password(mixed $value): bool
{
    return is_string($value) && strlen($value) >= 10 && strlen($value) <= 72;
}

function game_code(): string
{
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $code = '';
    for ($index = 0; $index < 6; $index++) {
        $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
    }

    return $code;
}

function winning_symbol(string $board): ?string
{
    $lines = [[0, 1, 2], [3, 4, 5], [6, 7, 8], [0, 3, 6], [1, 4, 7], [2, 5, 8], [0, 4, 8], [2, 4, 6]];
    foreach ($lines as $line) {
        $symbol = $board[$line[0]];
        if ($symbol !== '-' && $symbol === $board[$line[1]] && $symbol === $board[$line[2]]) {
            return $symbol;
        }
    }

    return null;
}

function choose_ai_move(string $board): int
{
    foreach (['O', 'X'] as $symbol) {
        foreach (str_split($board) as $cell => $value) {
            if ($value !== '-') {
                continue;
            }

            $candidate = $board;
            $candidate[$cell] = $symbol;
            if (winning_symbol($candidate) === $symbol) {
                return $cell;
            }
        }
    }

    foreach ([4, 0, 2, 6, 8, 1, 3, 5, 7] as $cell) {
        if ($board[$cell] === '-') {
            return $cell;
        }
    }

    throw new LogicException('No open square is available for the AI.');
}

function game_state(int $gameId, int $userId): array
{
    $statement = db()->prepare(
        'SELECT g.id, g.code, g.game_type, g.player_x_id, g.player_o_id, g.board, g.turn, g.status,
                g.winner_id, g.winner_symbol,
                x.username AS player_x, o.username AS player_o
         FROM games g
         JOIN users x ON x.id = g.player_x_id
         LEFT JOIN users o ON o.id = g.player_o_id
         WHERE g.id = ?',
    );
    $statement->execute([$gameId]);
    $game = $statement->fetch();

    if (!$game) {
        respond(['error' => 'Game not found.'], 404);
    }

    $gameType = $game['game_type'] ?? 'multiplayer';
    if ((int) $game['player_x_id'] !== $userId && (int) ($game['player_o_id'] ?? 0) !== $userId) {
        respond(['error' => 'Game not found.'], 404);
    }

    $yourSymbol = (int) $game['player_x_id'] === $userId ? 'X' : 'O';
    $opponent = $gameType === 'single_player'
        ? 'Gridlock AI'
        : ($yourSymbol === 'X' ? $game['player_o'] : $game['player_x']);
    $winnerSymbol = $game['winner_symbol']
        ?? ($game['winner_id'] === null
            ? null
            : ((int) $game['winner_id'] === (int) $game['player_x_id'] ? 'X' : 'O'));

    return [
        'id' => (int) $game['id'],
        'code' => $game['code'],
        'mode' => $gameType,
        'board' => str_split($game['board']),
        'status' => $game['status'],
        'turn' => $game['turn'],
        'your_symbol' => $yourSymbol,
        'opponent' => $opponent,
        'winner' => $winnerSymbol === null
            ? null
            : ($gameType === 'single_player' && $winnerSymbol === 'O'
                ? 'Gridlock AI'
                : ($winnerSymbol === 'X' ? $game['player_x'] : $game['player_o'])),
    ];
}

if (!isset($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET' && $action === 'me') {
    $user = current_user();
    respond([
        'authenticated' => $user !== null,
        'user' => $user ? ['id' => (int) $user['id'], 'username' => $user['username']] : null,
        'csrf' => $_SESSION['csrf'],
    ]);
}

if ($method !== 'POST') {
    respond(['error' => 'Method not allowed.'], 405);
}

$data = request_data();
verify_csrf($data);

try {
    switch ($data['action'] ?? '') {
        case 'register':
            $username = valid_username($data['username'] ?? null);
            $password = $data['password'] ?? null;
            if (!$username || !valid_password($password)) {
                respond(['error' => 'Use a 3–20 character username (letters, numbers, underscores) and a password of 10–72 characters.'], 422);
            }

            try {
                $statement = db()->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
                $statement->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
            } catch (PDOException $exception) {
                if ($exception->getCode() === '23000') {
                    respond(['error' => 'That username is already taken.'], 409);
                }
                throw $exception;
            }

            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) db()->lastInsertId();
            respond(['user' => ['id' => $_SESSION['user_id'], 'username' => $username]]);

        case 'login':
            $username = valid_username($data['username'] ?? null);
            $password = $data['password'] ?? null;
            if (!$username || !is_string($password)) {
                respond(['error' => 'Enter a valid username and password.'], 422);
            }

            $statement = db()->prepare('SELECT id, username, password_hash FROM users WHERE username = ?');
            $statement->execute([$username]);
            $user = $statement->fetch();
            if (!$user || !password_verify($password, $user['password_hash'])) {
                respond(['error' => 'Incorrect username or password.'], 401);
            }

            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            respond(['user' => ['id' => (int) $user['id'], 'username' => $user['username']]]);

        case 'logout':
            $_SESSION = [];
            session_regenerate_id(true);
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            respond(['ok' => true]);

        case 'dashboard':
            $user = require_user();
            $statement = db()->prepare(
                'SELECT g.id, g.code, g.game_type, g.status, g.updated_at, x.username AS player_x, o.username AS player_o
                 FROM games g
                 JOIN users x ON x.id = g.player_x_id
                 LEFT JOIN users o ON o.id = g.player_o_id
                 WHERE g.player_x_id = ? OR g.player_o_id = ?
                 ORDER BY g.updated_at DESC
                 LIMIT 20',
            );
            $statement->execute([$user['id'], $user['id']]);
            $games = array_map(static fn (array $game): array => [
                'id' => (int) $game['id'],
                'code' => $game['code'],
                'mode' => $game['game_type'],
                'status' => $game['status'],
                'opponent' => $game['game_type'] === 'single_player'
                    ? 'Gridlock AI'
                    : ($game['player_x'] === $user['username'] ? $game['player_o'] : $game['player_x']),
            ], $statement->fetchAll());

            $leaderboard = db()->query(
                'SELECT username, games_played, wins, losses, draws
                 FROM users
                 WHERE games_played > 0
                 ORDER BY wins DESC, games_played DESC, username ASC
                 LIMIT 10',
            )->fetchAll();

            respond([
                'user' => [
                    'username' => $user['username'],
                    'games_played' => (int) $user['games_played'],
                    'wins' => (int) $user['wins'],
                    'losses' => (int) $user['losses'],
                    'draws' => (int) $user['draws'],
                ],
                'games' => $games,
                'leaderboard' => $leaderboard,
            ]);

        case 'create':
            $user = require_user();
            $statement = db()->prepare(
                'INSERT INTO games (code, player_x_id) VALUES (?, ?)',
            );
            for ($attempt = 0; ; $attempt++) {
                if ($attempt >= 5) {
                    respond(['error' => 'Could not create a unique game code. Please try again.'], 503);
                }
                try {
                    $statement->execute([game_code(), $user['id']]);
                    break;
                } catch (PDOException $exception) {
                    if ($exception->getCode() !== '23000') {
                        throw $exception;
                    }
                }
            }
            respond(['game' => game_state((int) db()->lastInsertId(), (int) $user['id'])], 201);

        case 'create_ai':
            $user = require_user();
            $statement = db()->prepare(
                "INSERT INTO games (code, player_x_id, game_type, status) VALUES (?, ?, 'single_player', 'active')",
            );
            for ($attempt = 0; ; $attempt++) {
                if ($attempt >= 5) {
                    respond(['error' => 'Could not create a unique game. Please try again.'], 503);
                }
                try {
                    $statement->execute([game_code(), $user['id']]);
                    break;
                } catch (PDOException $exception) {
                    if ($exception->getCode() !== '23000') {
                        throw $exception;
                    }
                }
            }
            respond(['game' => game_state((int) db()->lastInsertId(), (int) $user['id'])], 201);

        case 'join':
            $user = require_user();
            $code = strtoupper(trim(is_string($data['code'] ?? null) ? $data['code'] : ''));
            if (!preg_match('/^[A-HJ-NP-Z2-9]{6}$/D', $code)) {
                respond(['error' => 'Enter a valid 6-character game code.'], 422);
            }

            $connection = db();
            $connection->beginTransaction();
            $statement = $connection->prepare('SELECT id, player_x_id, status FROM games WHERE code = ? FOR UPDATE');
            $statement->execute([$code]);
            $game = $statement->fetch();
            if (!$game || $game['status'] !== 'waiting') {
                $connection->rollBack();
                respond(['error' => 'That game is unavailable. Check the code or start a new game.'], 404);
            }
            if ((int) $game['player_x_id'] === (int) $user['id']) {
                $connection->rollBack();
                respond(['error' => 'You cannot join your own game.'], 409);
            }

            $statement = $connection->prepare(
                "UPDATE games SET player_o_id = ?, status = 'active', updated_at = CURRENT_TIMESTAMP WHERE id = ?",
            );
            $statement->execute([$user['id'], $game['id']]);
            $connection->commit();
            respond(['game' => game_state((int) $game['id'], (int) $user['id'])]);

        case 'state':
            $user = require_user();
            $gameId = filter_var($data['game_id'] ?? null, FILTER_VALIDATE_INT);
            if (!$gameId || $gameId < 1) {
                respond(['error' => 'Invalid game.'], 422);
            }
            respond(['game' => game_state($gameId, (int) $user['id'])]);

        case 'move':
            $user = require_user();
            $gameId = filter_var($data['game_id'] ?? null, FILTER_VALIDATE_INT);
            $cell = filter_var($data['cell'] ?? null, FILTER_VALIDATE_INT);
            if (!$gameId || $gameId < 1 || $cell === false || $cell < 0 || $cell > 8) {
                respond(['error' => 'Invalid move.'], 422);
            }

            $connection = db();
            $connection->beginTransaction();
            $statement = $connection->prepare('SELECT * FROM games WHERE id = ? FOR UPDATE');
            $statement->execute([$gameId]);
            $game = $statement->fetch();
            if (!$game || ((int) $game['player_x_id'] !== (int) $user['id'] && (int) ($game['player_o_id'] ?? 0) !== (int) $user['id'])) {
                $connection->rollBack();
                respond(['error' => 'Game not found.'], 404);
            }
            if ($game['status'] !== 'active') {
                $connection->rollBack();
                respond(['error' => 'This game is not accepting moves.'], 409);
            }

            $gameType = $game['game_type'] ?? 'multiplayer';
            if ($gameType === 'single_player' && $game['turn'] === 'O') {
                $connection->rollBack();
                respond(['error' => 'Wait for the AI to make its move.'], 409);
            }

            $expectedPlayer = $game['turn'] === 'X' ? (int) $game['player_x_id'] : (int) $game['player_o_id'];
            if ($expectedPlayer !== (int) $user['id']) {
                $connection->rollBack();
                respond(['error' => 'It is not your turn.'], 409);
            }

            $board = $game['board'];
            if ($board[$cell] !== '-') {
                $connection->rollBack();
                respond(['error' => 'That square is already taken.'], 409);
            }

            $symbol = $game['turn'];
            $board[$cell] = $symbol;
            $winnerSymbol = winning_symbol($board);
            $drawn = $winnerSymbol === null && !str_contains($board, '-');
            if ($gameType === 'single_player' && $winnerSymbol === null && !$drawn) {
                $board[choose_ai_move($board)] = 'O';
                $winnerSymbol = winning_symbol($board);
                $drawn = $winnerSymbol === null && !str_contains($board, '-');
            }

            $status = ($winnerSymbol !== null || $drawn) ? 'finished' : 'active';
            $winnerId = $winnerSymbol === null
                ? null
                : ($winnerSymbol === 'X' ? (int) $game['player_x_id'] : ($game['player_o_id'] === null ? null : (int) $game['player_o_id']));
            $nextTurn = $gameType === 'single_player' ? 'X' : ($symbol === 'X' ? 'O' : 'X');
            $statement = $connection->prepare(
                'UPDATE games
                 SET board = ?, turn = ?, status = ?, winner_id = ?, winner_symbol = ?, updated_at = CURRENT_TIMESTAMP
                 WHERE id = ?',
            );
            $statement->execute([$board, $nextTurn, $status, $winnerId, $winnerSymbol, $gameId]);

            if ($status === 'finished') {
                if ($gameType === 'single_player') {
                    $statement = $connection->prepare(
                        'UPDATE users
                         SET games_played = games_played + 1,
                             wins = wins + ?,
                             losses = losses + ?,
                             draws = draws + ?
                         WHERE id = ?',
                    );
                    $statement->execute([
                        $winnerSymbol === 'X' ? 1 : 0,
                        $winnerSymbol === 'O' ? 1 : 0,
                        $drawn ? 1 : 0,
                        $game['player_x_id'],
                    ]);
                } elseif ($drawn) {
                    $statement = $connection->prepare(
                        'UPDATE users SET games_played = games_played + 1, draws = draws + 1 WHERE id IN (?, ?)',
                    );
                    $statement->execute([$game['player_x_id'], $game['player_o_id']]);
                } else {
                    $loserId = $expectedPlayer === (int) $game['player_x_id']
                        ? (int) $game['player_o_id']
                        : (int) $game['player_x_id'];
                    $statement = $connection->prepare(
                        'UPDATE users
                         SET games_played = games_played + 1,
                             wins = wins + IF(id = ?, 1, 0),
                             losses = losses + IF(id = ?, 1, 0)
                         WHERE id IN (?, ?)',
                    );
                    $statement->execute([$winnerId, $loserId, $game['player_x_id'], $game['player_o_id']]);
                }
            }

            $connection->commit();
            respond(['game' => game_state($gameId, (int) $user['id'])]);

        default:
            respond(['error' => 'Unknown action.'], 404);
    }
} catch (Throwable $exception) {
    if (isset($connection) && $connection instanceof PDO && $connection->inTransaction()) {
        $connection->rollBack();
    }
    error_log((string) $exception);
    respond(['error' => 'Something went wrong. Please try again.'], 500);
}

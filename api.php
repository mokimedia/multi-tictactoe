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

function minimax_score(string $board, string $turn, int $depth): int
{
    $winner = winning_symbol($board);
    if ($winner === 'O') {
        return 10 - $depth;
    }
    if ($winner === 'X') {
        return $depth - 10;
    }
    if (!str_contains($board, '-')) {
        return 0;
    }

    $scores = [];
    foreach (str_split($board) as $cell => $value) {
        if ($value !== '-') {
            continue;
        }
        $candidate = $board;
        $candidate[$cell] = $turn;
        $scores[] = minimax_score($candidate, $turn === 'O' ? 'X' : 'O', $depth + 1);
    }

    return $turn === 'O' ? max($scores) : min($scores);
}

function choose_ai_move(string $board, string $difficulty): int
{
    $available = [];
    foreach (str_split($board) as $cell => $value) {
        if ($value === '-') {
            $available[] = $cell;
        }
    }
    if ($available === []) {
        throw new LogicException('No open square is available for the AI.');
    }

    if ($difficulty === 'easy') {
        return $available[random_int(0, count($available) - 1)];
    }

    foreach (['O', 'X'] as $symbol) {
        foreach ($available as $cell) {
            if ($board[$cell] !== '-') {
                continue;
            }

            $candidate = $board;
            $candidate[$cell] = $symbol;
            if (winning_symbol($candidate) === $symbol) {
                return $cell;
            }
        }
    }

    if ($difficulty === 'hard') {
        $bestMove = $available[0];
        $bestScore = PHP_INT_MIN;
        foreach ($available as $cell) {
            $candidate = $board;
            $candidate[$cell] = 'O';
            $score = minimax_score($candidate, 'X', 1);
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMove = $cell;
            }
        }
        return $bestMove;
    }

    foreach ([4, 0, 2, 6, 8, 1, 3, 5, 7] as $cell) {
        if ($board[$cell] === '-') {
            return $cell;
        }
    }

    throw new LogicException('AI could not select a move.');
}

function initial_checkers_board(): string
{
    $board = str_repeat('-', 64);
    for ($row = 0; $row < 3; $row++) {
        for ($column = 0; $column < 8; $column++) {
            if (($row + $column) % 2 === 1) {
                $board[$row * 8 + $column] = 'x';
            }
        }
    }
    for ($row = 5; $row < 8; $row++) {
        for ($column = 0; $column < 8; $column++) {
            if (($row + $column) % 2 === 1) {
                $board[$row * 8 + $column] = 'o';
            }
        }
    }

    return $board;
}

function checkers_piece_side(string $piece): ?string
{
    return match (strtolower($piece)) {
        'x' => 'X',
        'o' => 'O',
        default => null,
    };
}

function checkers_moves(string $board, string $side, ?int $forcedPiece = null): array
{
    $captures = [];
    $steps = [];
    $directions = $side === 'X' ? [1] : [-1];

    for ($from = 0; $from < 64; $from++) {
        $piece = $board[$from];
        if (checkers_piece_side($piece) !== $side || ($forcedPiece !== null && $from !== $forcedPiece)) {
            continue;
        }

        $row = intdiv($from, 8);
        $column = $from % 8;
        $pieceDirections = ctype_upper($piece) ? [-1, 1] : $directions;
        foreach ($pieceDirections as $rowDirection) {
            foreach ([-1, 1] as $columnDirection) {
                $nextRow = $row + $rowDirection;
                $nextColumn = $column + $columnDirection;
                $landingRow = $row + 2 * $rowDirection;
                $landingColumn = $column + 2 * $columnDirection;

                if ($landingRow >= 0 && $landingRow < 8 && $landingColumn >= 0 && $landingColumn < 8) {
                    $middle = ($row + $rowDirection) * 8 + $nextColumn;
                    $to = $landingRow * 8 + $landingColumn;
                    $opponent = checkers_piece_side($board[$middle]);
                    if ($opponent !== null && $opponent !== $side && $board[$to] === '-') {
                        $captures[] = ['from' => $from, 'to' => $to, 'capture' => $middle];
                    }
                }

                if ($forcedPiece === null && $nextRow >= 0 && $nextRow < 8 && $nextColumn >= 0 && $nextColumn < 8) {
                    $to = $nextRow * 8 + $nextColumn;
                    if ($board[$to] === '-') {
                        $steps[] = ['from' => $from, 'to' => $to, 'capture' => null];
                    }
                }
            }
        }
    }

    return $captures !== [] ? $captures : $steps;
}

function play_checkers_move(string $board, array $move): array
{
    $piece = $board[$move['from']];
    $board[$move['from']] = '-';
    $board[$move['to']] = $piece;
    if ($move['capture'] !== null) {
        $board[$move['capture']] = '-';
    }

    $row = intdiv($move['to'], 8);
    $promoted = ($piece === 'x' && $row === 7) || ($piece === 'o' && $row === 0);
    if ($promoted) {
        $board[$move['to']] = strtoupper($piece);
    }

    return ['board' => $board, 'promoted' => $promoted];
}

function choose_checkers_ai_move(string $board, string $difficulty, ?int $forcedPiece = null): array
{
    $moves = checkers_moves($board, 'O', $forcedPiece);
    if ($moves === []) {
        throw new LogicException('No legal checkers move is available for the AI.');
    }
    if ($difficulty === 'easy') {
        return $moves[random_int(0, count($moves) - 1)];
    }

    $ranked = [];
    foreach ($moves as $move) {
        $result = play_checkers_move($board, $move);
        $piece = $result['board'][$move['to']];
        $row = intdiv($move['to'], 8);
        $score = ($move['capture'] !== null ? 12 : 0)
            + ($result['promoted'] ? 10 : 0)
            + (7 - $row)
            + (ctype_upper($piece) ? 3 : 0)
            + (random_int(0, 10) / 100);

        if ($difficulty === 'hard') {
            $playerReplies = checkers_moves($result['board'], 'X');
            foreach ($playerReplies as $reply) {
                $replyResult = play_checkers_move($result['board'], $reply);
                $remainingAiPieces = 0;
                foreach (str_split($replyResult['board']) as $pieceAfterReply) {
                    if (checkers_piece_side($pieceAfterReply) === 'O') {
                        $remainingAiPieces++;
                    }
                }
                $opponentCapture = $reply['capture'] !== null ? 15 : 0;
                $promotionThreat = $replyResult['promoted'] ? 4 : 0;
                $score = min($score, $score - $opponentCapture - $promotionThreat + $remainingAiPieces);
            }
        }

        $ranked[] = ['move' => $move, 'score' => $score];
    }

    usort($ranked, static fn (array $left, array $right): int => $right['score'] <=> $left['score']);
    return $ranked[0]['move'];
}

function game_state(int $gameId, int $userId): array
{
    $statement = db()->prepare(
        'SELECT g.id, g.code, g.game_kind, g.game_type, g.ai_difficulty, g.player_x_id, g.player_o_id,
                g.board, g.turn, g.status, g.forced_piece,
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
    $gameKind = $game['game_kind'] ?? 'tic_tac_toe';
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
        'game_kind' => $gameKind,
        'difficulty' => $game['ai_difficulty'] ?? 'medium',
        'board' => str_split($game['board']),
        'forced_piece' => $game['forced_piece'] === null ? null : (int) $game['forced_piece'],
        'legal_moves' => $gameKind === 'checkers'
            ? checkers_moves(
                $game['board'],
                $yourSymbol,
                $game['forced_piece'] === null ? null : (int) $game['forced_piece'],
            )
            : [],
        'status' => $game['status'],
        'turn' => $game['turn'],
        'your_symbol' => $yourSymbol,
        'winner_symbol' => $winnerSymbol,
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
                'SELECT g.id, g.code, g.game_kind, g.game_type, g.ai_difficulty, g.status, g.updated_at,
                        x.username AS player_x, o.username AS player_o
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
                'game_kind' => $game['game_kind'] ?? 'tic_tac_toe',
                'mode' => $game['game_type'],
                'difficulty' => $game['ai_difficulty'] ?? 'medium',
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
            $gameKind = $data['game_kind'] ?? 'tic_tac_toe';
            if (!is_string($gameKind) || !in_array($gameKind, ['tic_tac_toe', 'checkers'], true)) {
                respond(['error' => 'Choose a valid game.'], 422);
            }
            $initialBoard = $gameKind === 'checkers' ? initial_checkers_board() : '---------';
            $statement = db()->prepare(
                'INSERT INTO games (code, player_x_id, game_kind, board) VALUES (?, ?, ?, ?)',
            );
            for ($attempt = 0; ; $attempt++) {
                if ($attempt >= 5) {
                    respond(['error' => 'Could not create a unique game code. Please try again.'], 503);
                }
                try {
                    $statement->execute([game_code(), $user['id'], $gameKind, $initialBoard]);
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
            $gameKind = $data['game_kind'] ?? 'tic_tac_toe';
            if (!is_string($gameKind) || !in_array($gameKind, ['tic_tac_toe', 'checkers'], true)) {
                respond(['error' => 'Choose a valid game.'], 422);
            }
            $difficulty = $data['difficulty'] ?? 'medium';
            if (!is_string($difficulty) || !in_array($difficulty, ['easy', 'medium', 'hard'], true)) {
                respond(['error' => 'Choose a valid AI difficulty.'], 422);
            }
            $statement = db()->prepare(
                "INSERT INTO games (code, player_x_id, game_kind, game_type, ai_difficulty, board, status)
                 VALUES (?, ?, ?, 'single_player', ?, ?, 'active')",
            );
            for ($attempt = 0; ; $attempt++) {
                if ($attempt >= 5) {
                    respond(['error' => 'Could not create a unique game. Please try again.'], 503);
                }
                try {
                    $board = $gameKind === 'checkers' ? initial_checkers_board() : '---------';
                    $statement->execute([game_code(), $user['id'], $gameKind, $difficulty, $board]);
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

        case 'abort':
            $user = require_user();
            $gameId = filter_var($data['game_id'] ?? null, FILTER_VALIDATE_INT);
            if (!$gameId || $gameId < 1) {
                respond(['error' => 'Invalid game.'], 422);
            }

            $connection = db();
            $connection->beginTransaction();
            $statement = $connection->prepare('SELECT player_x_id, player_o_id, status FROM games WHERE id = ? FOR UPDATE');
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

            $statement = $connection->prepare(
                "UPDATE games SET status = 'aborted', forced_piece = NULL, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
            );
            $statement->execute([$gameId]);
            $connection->commit();
            respond(['game' => game_state($gameId, (int) $user['id'])]);

        case 'move':
            $user = require_user();
            $gameId = filter_var($data['game_id'] ?? null, FILTER_VALIDATE_INT);
            if (!$gameId || $gameId < 1) {
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

            $gameKind = $game['game_kind'] ?? 'tic_tac_toe';
            $board = $game['board'];
            $winnerSymbol = null;
            $drawn = false;
            $nextTurn = $game['turn'] === 'X' ? 'O' : 'X';
            $forcedPiece = null;

            if ($gameKind === 'checkers') {
                $from = filter_var($data['from'] ?? null, FILTER_VALIDATE_INT);
                $to = filter_var($data['to'] ?? null, FILTER_VALIDATE_INT);
                if ($from === false || $from < 0 || $from > 63 || $to === false || $to < 0 || $to > 63) {
                    $connection->rollBack();
                    respond(['error' => 'Invalid move.'], 422);
                }

                $legalMoves = checkers_moves($board, $game['turn'], $game['forced_piece'] === null ? null : (int) $game['forced_piece']);
                $selectedMove = null;
                foreach ($legalMoves as $move) {
                    if ($move['from'] === $from && $move['to'] === $to) {
                        $selectedMove = $move;
                        break;
                    }
                }
                if ($selectedMove === null) {
                    $connection->rollBack();
                    respond([
                        'error' => $game['forced_piece'] !== null
                            ? 'Continue capturing with the same piece.'
                            : (array_filter($legalMoves, static fn (array $move): bool => $move['capture'] !== null)
                                ? 'A capture is available and must be taken.'
                                : 'Invalid move.'),
                    ], 409);
                }

                $playedMove = play_checkers_move($board, $selectedMove);
                $board = $playedMove['board'];
                $side = $game['turn'];
                $opponent = $side === 'X' ? 'O' : 'X';
                $opponentPieces = 0;
                foreach (str_split($board) as $piece) {
                    if (checkers_piece_side($piece) === $opponent) {
                        $opponentPieces++;
                    }
                }

                if ($opponentPieces === 0 || checkers_moves($board, $opponent) === []) {
                    $winnerSymbol = $side;
                } elseif ($selectedMove['capture'] !== null && !$playedMove['promoted']
                    && checkers_moves($board, $side, $to) !== []) {
                    $nextTurn = $side;
                    $forcedPiece = $to;
                }

                if ($winnerSymbol === null && $gameType === 'single_player' && $nextTurn === 'O') {
                    do {
                        $aiMove = choose_checkers_ai_move($board, $game['ai_difficulty'] ?? 'medium', $forcedPiece);
                        $aiResult = play_checkers_move($board, $aiMove);
                        $board = $aiResult['board'];
                        $forcedPiece = null;
                        $playerPieces = 0;
                        foreach (str_split($board) as $piece) {
                            if (checkers_piece_side($piece) === 'X') {
                                $playerPieces++;
                            }
                        }
                        if ($playerPieces === 0 || checkers_moves($board, 'X') === []) {
                            $winnerSymbol = 'O';
                            break;
                        }
                        $forcedMoves = $aiMove['capture'] !== null && !$aiResult['promoted']
                            ? checkers_moves($board, 'O', $aiMove['to'])
                            : [];
                        if ($forcedMoves === []) {
                            $nextTurn = 'X';
                            break;
                        }
                        $forcedPiece = $aiMove['to'];
                    } while (true);
                }
            } else {
                $cell = filter_var($data['cell'] ?? null, FILTER_VALIDATE_INT);
                if ($cell === false || $cell < 0 || $cell > 8 || $board[$cell] !== '-') {
                    $connection->rollBack();
                    respond(['error' => 'Invalid move.'], 422);
                }

                $symbol = $game['turn'];
                $board[$cell] = $symbol;
                $winnerSymbol = winning_symbol($board);
                $drawn = $winnerSymbol === null && !str_contains($board, '-');
                if ($gameType === 'single_player' && $winnerSymbol === null && !$drawn) {
                    $board[choose_ai_move($board, $game['ai_difficulty'] ?? 'medium')] = 'O';
                    $winnerSymbol = winning_symbol($board);
                    $drawn = $winnerSymbol === null && !str_contains($board, '-');
                }
                $nextTurn = $gameType === 'single_player' ? 'X' : ($symbol === 'X' ? 'O' : 'X');
            }

            $status = ($winnerSymbol !== null || $drawn) ? 'finished' : 'active';
            $winnerId = $winnerSymbol === null
                ? null
                : ($winnerSymbol === 'X' ? (int) $game['player_x_id'] : ($game['player_o_id'] === null ? null : (int) $game['player_o_id']));
            $statement = $connection->prepare(
                'UPDATE games
                 SET board = ?, turn = ?, status = ?, winner_id = ?, winner_symbol = ?, forced_piece = ?,
                     updated_at = CURRENT_TIMESTAMP
                 WHERE id = ?',
            );
            $statement->execute([$board, $nextTurn, $status, $winnerId, $winnerSymbol, $forcedPiece, $gameId]);

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

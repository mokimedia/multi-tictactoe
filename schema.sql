CREATE DATABASE IF NOT EXISTS multi_tictactoe
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE multi_tictactoe;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(20) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    games_played INT UNSIGNED NOT NULL DEFAULT 0,
    wins INT UNSIGNED NOT NULL DEFAULT 0,
    losses INT UNSIGNED NOT NULL DEFAULT 0,
    draws INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS games (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    code CHAR(6) CHARACTER SET ascii COLLATE ascii_bin NOT NULL UNIQUE,
    player_x_id BIGINT UNSIGNED NOT NULL,
    player_o_id BIGINT UNSIGNED NULL,
    game_kind ENUM('tic_tac_toe', 'checkers') NOT NULL DEFAULT 'tic_tac_toe',
    game_type ENUM('multiplayer', 'single_player') NOT NULL DEFAULT 'multiplayer',
    ai_difficulty ENUM('easy', 'medium', 'hard') NOT NULL DEFAULT 'medium',
    board VARCHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT '---------',
    turn CHAR(1) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'X',
    status ENUM('waiting', 'active', 'finished', 'aborted') NOT NULL DEFAULT 'waiting',
    winner_id BIGINT UNSIGNED NULL,
    winner_symbol CHAR(1) CHARACTER SET ascii COLLATE ascii_bin NULL,
    forced_piece TINYINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_games_player_x FOREIGN KEY (player_x_id) REFERENCES users (id),
    CONSTRAINT fk_games_player_o FOREIGN KEY (player_o_id) REFERENCES users (id),
    CONSTRAINT fk_games_winner FOREIGN KEY (winner_id) REFERENCES users (id),
    INDEX idx_games_player_x (player_x_id, updated_at),
    INDEX idx_games_player_o (player_o_id, updated_at)
) ENGINE=InnoDB;

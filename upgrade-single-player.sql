USE multi_tictactoe;

ALTER TABLE games
    ADD COLUMN game_type ENUM('multiplayer', 'single_player') NOT NULL DEFAULT 'multiplayer' AFTER player_o_id,
    ADD COLUMN winner_symbol CHAR(1) CHARACTER SET ascii COLLATE ascii_bin NULL AFTER winner_id;

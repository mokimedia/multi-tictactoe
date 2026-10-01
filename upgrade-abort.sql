ALTER TABLE games
    MODIFY status ENUM('waiting', 'active', 'finished', 'aborted') NOT NULL DEFAULT 'waiting';

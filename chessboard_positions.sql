CREATE DATABASE kings_escape;
USE kings_escape;

CREATE TABLE chessboard_positions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    move_number INT NOT NULL,
    piece_type VARCHAR(10) NOT NULL,
    position_x INT NOT NULL,
    position_y INT NOT NULL,
    is_enemy BOOLEAN NOT NULL
);
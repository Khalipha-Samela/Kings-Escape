# The King's Escape 🏰♔

A web-based chess puzzle game where you guide the king to safety while avoiding randomly generated enemy pieces.

---

## Game Overview

The King's Escape is an interactive chess puzzle where players must navigate their king from the top-left corner (0,0) to the bottom-right exit point (7,7) on an 8x8 chessboard. After each move, 2-3 enemy pieces randomly appear on the board, creating obstacles that the king cannot move through.

---

## Features

- **Interactive Chessboard**: Beautiful 8x8 grid with alternating light and dark squares
- **Real-time Gameplay**: Move the king by clicking highlighted valid squares
- **Dynamic Enemy Generation**: 2-3 enemies spawn randomly after each move
- **Win/Lose Conditions**: 
  - Win: Reach the exit point at (7,7)
  - Lose: King becomes trapped with no valid moves
- **Move Tracking**: Real-time display of move count and king position
- **Responsive Design**: Works on both desktop and mobile devices

---

## How to Play

1. The king (♔) starts at position (0,0) - top-left corner
2. Click on any highlighted square to move the king (one square in any direction)
3. After each move, enemy pieces (♟) will appear on random empty squares
4. Avoid enemy pieces - the king cannot move through them
5. Reach the blue exit point (⛩) at (7,7) to win the game
6. If the king has no valid moves, the game ends in defeat

---

## Tech Stack

- **Backen** – PHP with MySQL database
- **Frontend** – HTML5, CSS3, JavaScript (ES6+)  
- **Database** – MySQL with PDO for secure database operations
- **Styling** - CSS Grid and Flexbox for responsive layout
- **AJAX** - For seamless game interactions without page reloads

---

## Installation

### Prerequisites
- PHP 7.0 or higher
- MySQL/MariaDB
- Web server (Apache, Nginx, or built-in PHP server)

### Setup Instructions

1. **Clone the repository**
   ```bash
   git clone https://github.com/Khalipha-Samela/Kings-Escape.git
   cd kings-escape

2. **Database Setup**
- Import the chessboard_positions.sql file into your MySQL database
- The SQL file will create the necessary database and table structure

3. **Configuration**
- Update database credentials in index.php if different from defaults:

```php
define('DB_HOST','localhost');
define('DB_NAME','kings_escape');
define('DB_USER','root');
define('DB_PASS','');
```

4. **Run the Application**

**Localhost (XAMPP/WAMP)**
Place the project in your server’s htdocs/ or www/ folder:
- http://localhost/kings-escape

---

## 🚀 Live Demo

🔗 **Live Version:** [http://kingsescape.atwebpages.com/]  
🔗 **View the project here:** [https://github.com/Khalipha-Samela/Kings-Escape.git]

---

## 📱 Preview

![alt text](image.png)

---

## 📄 License
This project is open-source.
You are free to modify and expand it for your own use, portfolio, or learning.


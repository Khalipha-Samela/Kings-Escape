<?php
define('DB_HOST','localhost');
define('DB_NAME','kings_escape');
define('DB_USER','root');
define('DB_PASS','');

class KingsEscape {
    private $db;
    private $boardSize = 8;
    private $kingStart = [0,0];
    private $exitPoint = [7,7];

    public function __construct() {
        $this->connectDB();
        session_start();
        if(!isset($_SESSION['game_started'])){
            $this->initializeGame();
            $_SESSION['game_started']=true;
            $_SESSION['move_number']=0;
            $_SESSION['king_position']=$this->kingStart;
        }
    }

    private function connectDB(){
        try{
            $this->db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
            $this->db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        }catch(PDOException $e){
            die("DB connection failed: ".$e->getMessage());
        }
    }

    // Initialize or reset the game
    private function initializeGame() {
        try {
            // Clear all previous positions and reset auto-increment
            $this->db->exec("TRUNCATE TABLE chessboard_positions");

            // Reset session variables
            $_SESSION['game_started'] = true;
            $_SESSION['move_number'] = 0;
            $_SESSION['king_position'] = $this->kingStart;
            $_SESSION['exit_point'] = $this->exitPoint;

            // Place the king at starting position
            $this->placePiece(0, "King", $this->kingStart[0], $this->kingStart[1], false);

        } catch(PDOException $e) {
            error_log("PDO Exception in initializeGame: " . $e->getMessage());
            die("Failed to initialize game.");
        }
    }

    public function makeMove($newKingPos){
        $currentPos = $_SESSION['king_position'];

        // Validate move
        $dx = abs($newKingPos[0]-$currentPos[0]);
        $dy = abs($newKingPos[1]-$currentPos[1]);
        if(($dx>1)||($dy>1)||($dx==0 && $dy==0)) return "invalid";
        if($this->isEnemyAt($newKingPos[0],$newKingPos[1])) return "invalid";

        $_SESSION['move_number']++;
        $moveNumber = $_SESSION['move_number'];

        // Generate 2-3 enemies
        $enemyPositions=[];
        $numEnemies = rand(2,3);
        $attempts = 0;
        while(count($enemyPositions)<$numEnemies && $attempts<50){
            $x=rand(0,7);
            $y=rand(0,7);

            // Only place enemy on empty squares, not the king or exit
            if (($x != $newKingPos[0] || $y != $newKingPos[1]) &&
                ($x != $this->exitPoint[0] || $y != $this->exitPoint[1]) &&
                !$this->isEnemyAt($x, $y)) {
                $enemyPositions[] = [$x, $y];
            }
            $attempts++;
        }
        foreach($enemyPositions as $pos){
            $this->placePiece($moveNumber,"Enemy",$pos[0],$pos[1],true);
        }

         // Move king: UPDATE existing king row instead of inserting new
        $stmt = $this->db->prepare("
            UPDATE chessboard_positions
            SET position_x = ?, position_y = ?, move_number = ?
            WHERE piece_type = 'King' AND is_enemy = 0
        ");
        $stmt->execute([$newKingPos[0], $newKingPos[1], $moveNumber]);
        $_SESSION['king_position'] = $newKingPos;

        // Check win
        if($newKingPos==$this->exitPoint) return "win";

        // Check if king is trapped
        if(empty($this->getPossibleKingMovesArray())) return false;

        return true;
    }

    public function getPossibleKingMovesArray(){
        $kingPos=$_SESSION['king_position'];
        $dirs=[[-1,-1],[-1,0],[-1,1],[0,-1],[0,1],[1,-1],[1,0],[1,1]];
        $moves=[];
        foreach($dirs as $d){
            $x=$kingPos[0]+$d[0];
            $y=$kingPos[1]+$d[1];
            if($x>=0 && $x<8 && $y>=0 && $y<8 && !$this->isEnemyAt($x,$y)){
                $moves[]=['x'=>$x,'y'=>$y];
            }
        }
        return $moves;
    }

    private function isEnemyAt($x,$y){
        $stmt=$this->db->prepare("SELECT COUNT(*) FROM chessboard_positions WHERE position_x=? AND position_y=? AND is_enemy=1");
        $stmt->execute([$x,$y]);
        return $stmt->fetchColumn()>0;
    }

    private function placePiece($moveNumber,$type,$x,$y,$isEnemy){
        $stmt=$this->db->prepare("INSERT INTO chessboard_positions (move_number,piece_type,position_x,position_y,is_enemy) VALUES (?,?,?,?,?)");
        $stmt->execute([$moveNumber,$type,$x,$y,$isEnemy?1:0]);
    }

    public function getBoardState(){
        $stmt=$this->db->prepare("SELECT * FROM chessboard_positions WHERE move_number<=? ORDER BY id ASC");
        $stmt->execute([$_SESSION['move_number']]);
        $pieces=$stmt->fetchAll(PDO::FETCH_ASSOC);
        $board=array_fill(0,8,array_fill(0,8,null));
        foreach($pieces as $p){
            $board[$p['position_x']][$p['position_y']]=['type'=>$p['piece_type'],'isEnemy'=>(bool)$p['is_enemy']];
        }
        return $board;
    }

    public function resetGame(){
        $this->initializeGame();
    }

    public function getKingPosition(){ return $_SESSION['king_position']; }
    public function getMoveNumber(){ return $_SESSION['move_number']; }
}

$game=new KingsEscape();

// Handle AJAX move
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['move'])){
    $x=(int)$_POST['x'];
    $y=(int)$_POST['y'];
    $result=$game->makeMove([$x,$y]);
    echo json_encode([
        'board'=>$game->getBoardState(),
        'kingPos'=>$game->getKingPosition(),
        'moveNumber'=>$game->getMoveNumber(),
        'validMoves'=>$game->getPossibleKingMovesArray(),
        'result'=>$result
    ]);
    exit;
}

// Handle reset
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['reset'])){
    $game->resetGame();
    echo json_encode([
        'board'=>$game->getBoardState(),
        'kingPos'=>$game->getKingPosition(),
        'moveNumber'=>$game->getMoveNumber(),
        'validMoves'=>$game->getPossibleKingMovesArray(),
        'result'=>'reset'
    ]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The King's Escape</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>The King's Escape</h1>
            <p>Guide your king to safety while avoiding enemy pieces!</p>
        </header>
        
        <div class="game-info">
            <div class="info-box">
                <h3>Move</h3>
                <p id="move-counter">0</p>
            </div>
            <div class="info-box">
                <h3>King Position</h3>
                <p id="king-position">(0, 0)</p>
            </div>
            <div class="info-box">
                <h3>Exit Point</h3>
                <p id="exit-point">(7, 7)</p>
            </div>
        </div>
        
        <div class="game-content">
            <div class="chessboard-container">
                <div class="chessboard" id="chessboard">
                    <!-- Chessboard will be generated by JavaScript -->
                </div>
            </div>
            
            <div class="controls-container">
                <div class="controls">
                    <h2>Game Controls</h2>
                    <button class="btn btn-reset" id="btn-reset">Reset Game</button>
            
                    <div id="message" class="message"></div>
                    
                    <div class="instructions">
                        <h3>How to Play:</h3>
                        <p>1. The king (♔) starts at the top-left corner.</p>
                        <p>2. Click on any highlighted square to move the king.</p>
                        <p>3. Enemy pieces will automatically appear after each move.</p>
                        <p>4. Reach the blue exit point at the bottom-right to win!</p>
                        <p>5. Avoid enemy pieces - the king cannot move through them.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
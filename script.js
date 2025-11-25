document.addEventListener('DOMContentLoaded', function () {
    const chessboard = document.getElementById('chessboard');
    const moveCounter = document.getElementById('move-counter');
    const kingPosition = document.getElementById('king-position');
    const exitPoint = document.getElementById('exit-point');
    const btnReset = document.getElementById('btn-reset');
    const message = document.getElementById('message');

    let board = [], kingPos = [0, 0], moveNumber = 0, gameOver = false;

    // Render the chessboard UI
    function renderBoard() {
        chessboard.innerHTML = '';
        for (let i = 0; i < 8; i++) {
            for (let j = 0; j < 8; j++) {
                const square = document.createElement('div');
                square.classList.add('square', (i + j) % 2 === 0 ? 'white' : 'black');
                square.dataset.x = i;
                square.dataset.y = j;

                // Exit point
                if (i === 7 && j === 7) {
                    square.classList.add('exit');
                    square.innerHTML = '⛩';
                }

                // Pieces (case-insensitive check)
                if (board[i][j]) {
                    const type = board[i][j].type.toLowerCase();
                    if (type === 'king') square.innerHTML = '<span class="king">♔</span>';
                    if (type === 'enemy') square.innerHTML = '<span class="enemy">♟</span>';
                }

                square.addEventListener('click', handleSquareClick);
                chessboard.appendChild(square);
            }
        }
        updateGameInfo();
    }

    // Update game info display
    function updateGameInfo() {
        moveCounter.textContent = moveNumber;
        kingPosition.textContent = `(${kingPos[0]}, ${kingPos[1]})`;
        exitPoint.textContent = '(7, 7)';
    }

    // Highlight valid moves received from backend
    function highlightPossibleMoves(validMoves) {
        const squares = document.querySelectorAll('.square');
        squares.forEach(sq => sq.classList.remove('highlighted'));
        if (!validMoves) return;
        validMoves.forEach(move => {
            const sq = document.querySelector(`.square[data-x="${move.x}"][data-y="${move.y}"]`);
            if (sq) sq.classList.add('highlighted');
        });
    }

    // Handle square click
    function handleSquareClick(e) {
        if (gameOver) return;
        const x = parseInt(e.target.dataset.x);
        const y = parseInt(e.target.dataset.y);

        fetch('index.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `move=1&x=${x}&y=${y}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.result === 'invalid') {
                message.textContent = '⚠️ Invalid move!';
                message.className = 'message invalid';
                return;
            }

            board = data.board;
            kingPos = data.kingPos;
            moveNumber = data.moveNumber;

            renderBoard();
            highlightPossibleMoves(data.validMoves);

            if (data.result === 'win') {
                gameOver = true;
                message.textContent = `🥳 Congratulations! The king escaped in ${moveNumber} moves!`;
                message.className = 'message win';
            } else if (data.result === false) {
                gameOver = true;
                message.textContent = '😢 Game over! The king is trapped!';
                message.className = 'message lose';
            } else {
                message.textContent = '';
                message.className = 'message';
            }
        });
    }

    // Reset game
    btnReset.addEventListener('click', () => {
        fetch('index.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'reset=1'
        })
        .then(res => res.json())
        .then(data => {
            board = data.board;
            kingPos = data.kingPos;
            moveNumber = data.moveNumber;
            gameOver = false;
            message.textContent = '';
            message.className = 'message';
            renderBoard();
            highlightPossibleMoves(data.validMoves);
        });
    });

    // Initial board render
    fetch('index.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'reset=1'
    })
    .then(res => res.json())
    .then(data => {
        board = data.board;
        kingPos = data.kingPos;
        moveNumber = data.moveNumber;
        renderBoard();
        highlightPossibleMoves(data.validMoves);
    });
});

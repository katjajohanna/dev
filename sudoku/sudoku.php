<?php
include("SudokuRobot.php");

$sudoku1 = array(
	array(4, 8, 9,  5, null, null,  null, 7, null),
	array(3, 5, null,  null, 7, null,  null, null, 9),
	array(null, null, null,  null, 4, 9,  null, null, null),

	array(null, null, 5,  1, null, null,  null, null, 6),
	array(null, 7, 4,  3, null, 2,  1, 9, null),
	array(1, null, null,  null, null, 7,  5, null, null),

	array(null, null, null,  4, 2, null,  null, null, null),
	array(5, null, null,  null, 9, null,  null, 8, 1),
	array(null, 1, null,  null, null, 5,  9, 6, 4),
);

$sudoku1Solution = array(
	array(4, 8, 9,  5, 1, 3,  6, 7, 2),
	array(3, 5, 2,  6, 7, 8,  4, 1, 9),
	array(7, 6, 1,  2, 4, 9,  8, 3, 5),

	array(9, 3, 5,  1, 8, 4,  7, 2, 6),
	array(6, 7, 4,  3, 5, 2,  1, 9, 8),
	array(1, 2, 8,  9, 6, 7,  5, 4, 3),

	array(8, 9, 6,  4, 2, 1,  3, 5, 7),
	array(5, 4, 3,  7, 9, 6,  2, 8, 1),
	array(2, 1, 7,  8, 3, 5,  9, 6, 4),
);

$sudoku2 = array(
	array(null, null, null,   3, 1, 8,   9, null, null),
	array(null, null, null,   2, 5, 6,   null, 3, 1),
	array(3, null, null,   null, null, null,   6, null, null),

	array(null, 4, null,   8, 6, 1,   null, 9, 3),
	array(null, 5, null,   null, null, null,   null, 1, null),
	array(1, 9, null,   5, 4, 3,   null, 6, null),

	array(null, null, 1,   null, null, null,   null, null, 2),
	array(7, 6, null,   1, 8, 4,   null, null, null),
	array(null, null, 4,   7, 2, 5,   null, null, null)
);

$sudoku2Solution = array(
	array(4, 2, 6,   3, 1, 8,   9, 7, 5),
	array(8, 7, 9,   2, 5, 6,   4, 3, 1),
	array(3, 1, 5,   4, 9, 7,   6, 2, 8),

	array(2, 4, 7,   8, 6, 1,   5, 9, 3),
	array(6, 5, 3,   9, 7, 2,   8, 1, 4),
	array(1, 9, 8,   5, 4, 3,   2, 6, 7),

	array(5, 8, 1,   6, 3, 9,   7, 4, 2),
	array(7, 6, 2,   1, 8, 4,   3, 5, 9),
	array(9, 3, 4,   7, 2, 5,   1, 8, 6),
);

$sudoku3 = array(
	array(null, null, null,   5, null, null,   3, null, 9),
	array(null, 3, null,   null, null, null,   null, 5, 2),
	array(null, 6, 9,   3, null, null,   null, 1, null),

	array(null, null, null,   null, null, 7,   null, null, 6),
	array(3, 2, null,   null, 8, null,   null, 9, 7),
	array(9, null, null,   4, null, null,   null, null, null),

	array(null, 4, null,   null, null, 2,   9, 7, null),
	array(1, 5, null,   null, null, null,   null, 4, null),
	array(8, null, 2,   null, null, 1,   null, null, null),
);

$sudoku3Solution = array(
	array(2, 1, 4,   5, 7, 6,   3, 8, 9),
	array(7, 3, 8,   9, 1, 4,   6, 5, 2),
	array(5, 6, 9,   3, 2, 8,   7, 1, 4),

	array(4, 8, 5,   2, 9, 7,   1, 3, 6),
	array(3, 2, 6,   1, 8, 5,   4, 9, 7),
	array(9, 7, 1,   4, 6, 3,   8, 2, 5),

	array(6, 4, 3,   8, 5, 2,   9, 7, 1),
	array(1, 5, 7,   6, 3, 9,   2, 4, 8),
	array(8, 9, 2,   7, 4, 1,   5, 6, 3),
);

$chosenSudokus = array(1, 2, 3);

foreach ($chosenSudokus as $chosenSudoku) {
	$robot = new SudokuRobot(${"sudoku{$chosenSudoku}"});

	//loop these until no empties are found or no cells are filled
	do {
		$emptyFilled = false;

		while ($uniqueFilled = $robot->fillUniqueEmpties()) {
			if ($uniqueFilled) {
				$emptyFilled = true;
			}
		}

		//Lets try to add numbers to empty cells
		$emptyFilled = false;
		for ($tryNumber = 1; $tryNumber <= 9; $tryNumber++) {
			if ($robot->fillWithNumber($tryNumber)) {
				$emptyFilled = true;
			}
		}

		//Lets try what number would fit in empty cell
		if ($robot->fillEmptyCells()) {
			$emptyFilled = true;
		}
	} while ($emptyFilled);

	$equal = true;
	foreach ($robot->getSudoku() as $rowId => $row) {
		$diff = array_diff($row, ${"sudoku{$chosenSudoku}Solution"}[$rowId]);
		if (count($diff) != 0) {
			$equal = false;
			continue;
		}
	}

	if ($equal) {
		echo "\n{$chosenSudoku}. sudoku ready";
	} else {
		echo "\nCouldn't finish {$chosenSudoku}. sudoku";
	}
}
<?php

class SudokuRobot
{
	private $_sudoku;
	private $_blockTotal = 45;
	private $_blockCoordinates = array();

	public function __construct($sudoku)
	{
		$this->_sudoku = $sudoku;

		for ($row = 0; $row < 9; $row++) {
			for ($column = 0; $column < 3; $column++) {
				if     ($row < 3) { $this->_setBlockCoordinate("r0c0", $row, $column); }
				elseif ($row < 6) { $this->_setBlockCoordinate("r1c0", $row, $column); }
				else              { $this->_setBlockCoordinate("r2c0", $row, $column); }
			}

			for ($column = 3; $column < 6; $column++) {
				if     ($row < 3) { $this->_setBlockCoordinate("r0c1", $row, $column); }
				elseif ($row < 6) { $this->_setBlockCoordinate("r1c1", $row, $column); }
				else              { $this->_setBlockCoordinate("r2c1", $row, $column); }
			}

			for ($column = 6; $column < 9; $column++) {
				if     ($row < 3) { $this->_setBlockCoordinate("r0c2", $row, $column); }
				elseif ($row < 6) { $this->_setBlockCoordinate("r1c2", $row, $column); }
				else              { $this->_setBlockCoordinate("r2c2", $row, $column); }
			}
		}
	}

	private function _setBlockCoordinate($position, $row, $column)
	{
		$this->_blockCoordinates[$position][] = array("row" => $row, "column" => $column);
	}

	public function getSudoku()
	{
		return $this->_sudoku;
	}

	/*
	Fills in cell in block, if it is the only null in that block.
	Returns true if one or more cells were filled, false otherwise
	*/
	public function fillUniqueEmpties()
	{
		$emptyFilled = false;

		//go through rows
		foreach ($this->_sudoku as $rowId => $row) {
			$nullKeys = array_keys($row, null);

			if (count($nullKeys) == 1) {
				$missingNumber = $this->_getMissingNumberFromBlock($row);
				$this->setNumber($rowId, $nullKeys[0], $missingNumber);
				$emptyFilled = true;
			}
		}

		//go through columns
		foreach ($this->_getColumns() as $columnId => $column) {
			$nullKeys = array_keys($column, null);

			if (count($nullKeys) == 1) {
				$missingNumber = $this->_getMissingNumberFromBlock($column);
				$this->setNumber($nullKeys[0], $columnId, $missingNumber);
				$emptyFilled = true;
			}
		}

		//go through sub-squares
		foreach ($this->_getSubSquares() as $position => $block) {
			$nullKeys = array_keys($block, null);

			if (count($nullKeys) == 1) {
				$missingNumber = $this->_getMissingNumberFromBlock($block);
				$row = $this->_blockCoordinates[$position][$nullKeys[0]]["row"];
				$column = $this->_blockCoordinates[$position][$nullKeys[0]]["column"];

				$this->setNumber($row, $column, $missingNumber);
				$emptyFilled = true;
			}
		}

		return $emptyFilled;
	}

	private function _getMissingNumberFromBlock($block)
	{
		$total = 0;

		foreach ($block as $cell) {
			$total += $cell;
		}

		return $this->_blockTotal - $total;
	}

	/*
	Fills cell with number, if it is the only cell for given number
	Returns true if one or more cells were filled, false otherwise
	*/
	public function fillWithNumber($tryNumber)
	{
		$emptyFilled = false;

		foreach ($this->_sudoku as $rowId => $row) {
			//Loading columns and blocks everytime because there might be updates to cells
			$columns = $this->_getColumns();
			$blocks = $this->_getSubSquares();

			if (in_array($tryNumber, $row)) {
				continue;
			}

			$nullKeys = array_keys($row, null);
			$possiblePositions = array();

			foreach ($nullKeys as $nullKey) {
				if (in_array($tryNumber, $columns[$nullKey])) {
					continue;
				}

				$blockPosition = $this->_getSubSquarePositionByCoordinates($rowId, $nullKey);

				if (in_array($tryNumber, $blocks[$blockPosition])) {
					continue;
				}

				$possiblePositions[] = $nullKey;
			}

			if (count($possiblePositions) == 1) {
				$this->setNumber($rowId, $possiblePositions[0], $tryNumber);
				$emptyFilled = true;
			} else {
				//Check if some possible position is the only position for this number in that block
				foreach ($possiblePositions as $possiblePosition) {
					if ($this->_isOnlyCellForNumber($tryNumber, $possiblePosition, $rowId)) {
						$this->setNumber($rowId, $possiblePosition, $tryNumber);
						$emptyFilled = true;
						continue;
					}
				}
			}
		}

		return $emptyFilled;
	}

	private function _isOnlyCellForNumber($tryNumber, $possiblePosition, $rowId)
	{
		$sudoku = $this->getSudoku();
		$columns = $this->_getColumns();
		$blocks = $this->_getSubSquares();

		//Get null keys for column of this possible position
		//Check if number can fit in some of null keys
		$nullKeys = array_keys($columns[$possiblePosition], null);

		foreach ($nullKeys as $nullKey) {
			if ($nullKey == $rowId) {
				continue; //We don't want to double-check the position in question, because of course it is a place for number!
			}

			//check row and sub-square
			$blockPosition = $this->_getSubSquarePositionByCoordinates($rowId, $possiblePosition);

			if (!in_array($tryNumber, $sudoku[$nullKey]) && !in_array($tryNumber, $blocks[$blockPosition])) {
				return false; //Number is not found in row or block, so this is not the only cell for number
			}
		}

		//Get null keys for sub-square of this possible position
		//Check if number can fit in some of null keys
		$blockPosition = $this->_getSubSquarePositionByCoordinates($rowId, $possiblePosition);
		$nullKeys = array_keys($blocks[$blockPosition], null);

		$positionInTest = $this->_getBlockCellByRowAndColumn($blockPosition, $rowId, $possiblePosition);

		foreach ($nullKeys as $nullKey) {
			if ($nullKey == $positionInTest) {
				continue; //We don't want to double-check the position in question, because of course it is a place for number!
			}

			$blockRowId = $this->_blockCoordinates[$blockPosition][$nullKey]["row"];
			$blockColumnId = $this->_blockCoordinates[$blockPosition][$nullKey]["column"];

			//check row and column
			if (!in_array($tryNumber, $sudoku[$blockRowId]) && !in_array($tryNumber, $columns[$blockColumnId])) {
				return false; //Number is not found in row or block, so this is not the only cell for number
			}
		}

		return true;
	}

	private function _getBlockCellByRowAndColumn($blockPosition, $rowId, $columnId)
	{
		foreach ($this->_blockCoordinates[$blockPosition] as $blockCell => $coordinates) {
			if ($coordinates["row"] == $rowId && $coordinates["column"] == $columnId) {
				return $blockCell;
			}
		}
	}

	/*
	Fills empty cell with number that is the only number fitting that cell
	Returns true if one or more cells were filled, false otherwise
	*/
	public function fillEmptyCells()
	{
		$emptyFilled = false;

		foreach ($this->_sudoku as $rowId => $row) {
			$columns = $this->_getColumns();
			$blocks = $this->_getSubSquares();
			$nullKeys = array_keys($row, null);
			$possiblePositions = array();

			foreach ($nullKeys as $nullKey) {
				for ($tryNumber = 1; $tryNumber <= 9; $tryNumber++) {
					if (in_array($tryNumber, $row)) {
						continue;
					}

					if (in_array($tryNumber, $columns[$nullKey])) {
						continue;
					}

					$blockPosition = $this->_getSubSquarePositionByCoordinates($rowId, $nullKey);

					if (in_array($tryNumber, $blocks[$blockPosition])) {
						continue;
					}

					$possiblePositions[] = $tryNumber;
				}

				if (count($possiblePositions) == 1) {
					$this->setNumber($rowId, $nullKey, $possiblePositions[0]);
					$emptyFilled = true;
				}
			}
		}

		return $emptyFilled;
	}

	public function setNumber($row, $column, $number)
	{
		$this->_sudoku[$row][$column] = $number;
	}

	private function _getSubSquares()
	{
		foreach ($this->_sudoku as $lineIndex => $row) {
			foreach ($row as $position => $cell) {
				if     ($lineIndex <= 2) { $row = "r0"; }
				elseif ($lineIndex <= 5) { $row = "r1"; }
				else 					 { $row = "r2"; }

				if     ($position <= 2) { $column = "c0"; }
				elseif ($position <= 5) { $column = "c1"; }
				else                    { $column = "c2"; }

				$blocks["{$row}{$column}"][] = $cell;
			}
		}

		return $blocks;
	}

	private function _getColumns()
	{
		$columns = array();

		for ($column = 0; $column < 9; $column++) {
			$block = array();

			for ($row = 0; $row < 9; $row++) {
				$block[] = $this->_sudoku[$row][$column];
			}

			$columns[] = $block;
		}

		return $columns;
	}

	private function _getSubSquarePositionByCoordinates($rowId, $columnId)
	{
		if     ($rowId <= 2) { $blockRowId = "r0"; }
		elseif ($rowId <= 5) { $blockRowId = "r1"; }
		else 			     { $blockRowId = "r2"; }

		if     ($columnId <= 2) { $blockColumnId = "c0"; }
		elseif ($columnId <= 5) { $blockColumnId = "c1"; }
		else                    { $blockColumnId = "c2"; }

		return "{$blockRowId}{$blockColumnId}";
	}
}
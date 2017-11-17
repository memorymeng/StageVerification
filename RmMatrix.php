<?php

declare(strict_types = 1);

class RmMatrix
{
    private $mIsValid;
    private $mRows;
    private $mColumns;
    private $mName;
    private $mMatrix;

    public function __construct(int $row = 1, int $column = 1, string $name = 'unknown')
    {
        $this->mIsValid = false;
        $this->mRows = $row;
        $this->mColumns = $column;
        $this->mName = $name;
        $this->setupMatrix();
    }

    public function __destruct()
    {
        $this->deleteMatrix();
    }

    private function setupMatrix(): bool
    {
        $this->mMatrix = [];
        if (($this->mRows > 0) && ($this->mColumns > 0)) {
            for ($i = 0; $i < $this->mRows; $i ++) {
                for ($j = 0; $j < $this->mColumns; $j ++) {
                    $this->mMatrix[$i][$j] = 0.0;
                }
            }
            $this->mIsValid = true;
            // var_dump(count($this->_matrix));
            return true;
        } else {
            return false;
        }
    }

    private function deleteMatrix()
    {
        $this->mMatrix = null;
        $this->mRows = 0;
        $this->mColumns = 0;
        $this->mIsValid = true;
    }

    private function hasValidContent(): bool
    {
        return $this->mIsValid;
    }

    private function getMMatrix(): array
    {
        return $this->mMatrix;
    }

    private function getRows(): int
    {
        return $this->mRows;
    }

    private function getMColumns(): int
    {
        return $this->mColumns;
    }

    public function printMatrix()
    {
        //echo 'name = ', $this->name, ', valid = ', ($this->isValid), '<br>';
        var_dump($this->mMatrix);
    }

    private function dotProduct(RmMatrix &$a, RmMatrix &$b): RmMatrix
    {
        $rows = $a->getRows();
        $columns = $b->getMColumns();
        $product = new RmMatrix($rows, $columns, 'product');

        if ($a->hasValidContent() && $b->hasValidContent()) {
            if ($a->getMColumns() === $b->getRows()) {
                $n = $a->getMColumns();
                for ($i = 0; $i < $rows; $i ++) {
                    for ($j = 0; $j < $columns; $j ++) {
                        $productValue = 0.0;
                        for ($k = 0; $k < $n; $k ++) {
                            $productValue += 1.0 * $a->mMatrix[$i][$k] * $b->mMatrix[$k][$j];
                        }
                        $product->mMatrix[$i][$j] = 1.0 * $productValue;
                    }
                }
            } else {
                throw new Exception('ERROR: a.col != b.row matrix multiplication cannot be done\n');
            }
        } else {
            $product->deleteMatrix();
            throw new Exception('ERROR: matrix a or b is not valid for dotProduct\n');
        }
        return $product;
    }

    private function transpose(RmMatrix &$m): RmMatrix
    {
        $rows = $m->getMColumns();
        $columns = $m->getRows();
        $mT = new RmMatrix($rows, $columns, 'transpose');
        //mT._name = "transpose";

        if ($m->hasValidContent()) {
            for ($i = 0; $i < $rows; $i++) {
                for ($j = 0; $j < $columns; $j++) {
                    $mT->mMatrix[$i][$j] = 1.0 * $m->mMatrix[$j][$i];
                }
            }
        } else {
            $mT->deleteMatrix();
            throw new Exception('ERROR: matrix is not valid for transpose\n');
        }


        return $mT;
    }

    public static function getCoefficients(array $px, array $py, int $powN): array
    {
        $beta = new RmMatrix();
        if (count($px) === count($py)) {
            //$numOfSample = count($px);
            $X = $beta->getPartialDerivativeX($px, $powN);
            $y = $beta->getPartialDerivativeY($px, $py, $powN);
            $beta = $beta->gaussianElimination($X, $y);
            if (!$beta->hasValidContent()) {
                throw new Exception("ERROR: Unable to get the result with Gaussian Elimination!\nPlease check the source samples\n");
            }
        } else {
            throw new Exception('ERROR: x.count != y.count coe cannot be calculated\n');
        }

        $coefficients = [];
        for ($i=0;$i<$beta->getRows();$i++) {
            array_push($coefficients, $beta->mMatrix[$i][0]);
        }

        return $coefficients;
    }

    private function getPartialDerivativeX(array $px, int $powN): RmMatrix
    {
        $numOfSample = count($px);
        $rows = $powN + 1;
        $cols = $powN + 1;
        $X = new RmMatrix($rows, $cols, 'X');
        //X._name = "X";

        $X->mMatrix[0][0] = $numOfSample;
        for ($n = 0; $n < $numOfSample; $n++) {
            for ($sum = 1; $sum <= 2 * $powN; $sum++) {
                for ($i = 0; $i < $rows; $i++) {
                    for ($j = 0; $j < $cols; $j++) {
                        if ($sum === ($i + $j)) {
                            $X->mMatrix[$i][$j] += 1.0 * pow($px[$n], $sum);
                        }
                    }
                }
            }
        }
        return $X;
    }

    private function getPartialDerivativeY(array $px, array $py, int $powN): RmMatrix
    {
        $y = null;
        if (count($px) === count($py)) {
            $numOfSample = count($px);
            $rows = $powN + 1;
            $cols = 1;
            $y = new RmMatrix($rows, $cols, 'y');
            for ($n = 0; $n < $numOfSample; $n++) {
                for ($i = 0; $i < $rows; $i++) {
                    $y->mMatrix[$i][0] += 1.0 * pow($px[$n], $i) * $py[$n];
                }
            }
        } else {
            throw new Exception('ERROR: x.count != y.count PartialDerivativeY cannot be calculated\n');
        }
        return $y;
    }

    private function gaussianElimination(RmMatrix &$X, RmMatrix &$y): RmMatrix
    {
        $numOfCoe = $X->getRows();
        $tempX = clone $X;
        $beta = clone $y;
        $beta->mName = 'beta';

        if ($X->hasValidContent() && $y->hasValidContent()) {
//            $i = $j = $k = $l = 0;
//            $t = 0.0;
            for ($k = 1; $k <= $numOfCoe; $k++) {
                for ($l = $k; $l <= $numOfCoe; $l++) {
                    if (abs($tempX->mMatrix[$l - 1][$k - 1]) > 0) {
                        break;
                    } elseif ($l == $numOfCoe) {
                        $beta->deleteMatrix();
                        $beta->mName = 'gaussianEliminationError';
                        return $beta;
                    }
                }
                if ($l != $k) {
                    for ($j = $k; $j <= $numOfCoe; $j++) {
                        $t = $tempX->mMatrix[$k - 1][$j - 1];
                        $tempX->mMatrix[$k - 1][$j - 1] = 1.0 * $tempX->mMatrix[$l - 1][$j - 1];
                        $tempX->mMatrix[$l - 1][$j - 1] = 1.0 * $t;
                    }
                    $t = 1.0 * $beta->mMatrix[$k - 1][0];
                    $beta->mMatrix[$k - 1][0] = 1.0 * $beta->mMatrix[$l - 1][0];
                    $beta->mMatrix[$l - 1][0] = 1.0 * $t;
                }
                $t = 1.0 / $tempX->mMatrix[$k - 1][$k - 1];
                for ($j = $k + 1; $j <= $numOfCoe; $j++) {
                    $tempX->mMatrix[$k - 1][$j - 1] = 1.0 * $t * $tempX->mMatrix[$k - 1][$j - 1];
                }
                $beta->mMatrix[$k - 1][0] *= 1.0 * $t;
                for ($i = $k + 1; $i <= $numOfCoe; $i++) {
                    for ($j = $k + 1; $j <= $numOfCoe; $j++) {
                        $tempX->mMatrix[$i - 1][$j - 1] -= 1.0 * $tempX->mMatrix[$i - 1][$k - 1] * $tempX->mMatrix[$k - 1][$j - 1];
                    }
                    $beta->mMatrix[$i - 1][0] -= 1.0 * $tempX->mMatrix[$i - 1][$k - 1] * $beta->mMatrix[$k - 1][0];
                }
            }

            for ($i = $numOfCoe - 1; $i >= 1; $i--) {
                for ($j = $i + 1; $j <= $numOfCoe; $j++) {
                    $beta->mMatrix[$i - 1][0] -= 1.0 * $tempX->mMatrix[$i - 1][$j - 1] * $beta->mMatrix[$j - 1][0];
                }
            }
        } else {
            $beta->deleteMatrix();
            throw new Exception('ERROR: X or y is not valid, gaussianElimination cannot be calculated\n');
        }
        return $beta;
    }
}
//$Q = [
//    0,
//    29.58,
//    49.93,
//    68.78,
//    79.84,
//    89.59,
//    94.46,
//    98.52,
//    108.09,
//    121.47,
//    140.22
//];
//$H = [
//    5.58,
//    5.13,
//    4.71,
//    4.3,
//    3.99,
//    3.62,
//    3.4,
//    3.21,
//    2.68,
//    1.7,
//    0.08
//];
//$P = [
//    0.0596994770014751,
//    0.0680296365830763,
//    0.0763597961646775,
//    0.0833015958160118,
//    0.0846899557462787,
//    0.0860783156765455,
//    0.0860783156765455,
//    0.0860783156765455,
//    0.0867199557462786,
//    0.0845848759554781,
//    0.0833015958160118
//
//
//];
//$rm = RmMatrix::getCoefficients($Q, $H, 5);
//var_dump($rm);
////$rm = RmMatrix::getCoefficients($Q, $P, 5);
//

<?php 

class Set implements \ArrayAccess {

    public $length;
    protected $data;
    protected $stub = 1; // this is used as the value in the assoc array, we are disinterested in this

    public function __construct(Array $arr) {
        $this->length = count($arr);
        foreach ($arr as $item) {
        	// We are using a pointer to save on memory
            $this->data[$item] = &$this->stub;
        }
    }

    public function has($item) {
        return isset($this->data[$item]);
    }

    public function add($item) {
        $this->data[$item] = &$this->stub;
        $this->length = count($this->data);
    }

    public function delete($item) {
        unset($this->data[$item]);
        $this->length = count($this->data);
    }

    public function clear() {
        $this->data = [];
        $this->length = 0;
    }

    public function values() {
        return array_keys($this->data);
    }

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->data[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->data[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }
}

class Grundy {

    public $result = 0;
    public $line = '';
    public $matrix = array();
    public $grundy = array();
    public $arr = array();
    public $segmentTree = array();


    private function constructMinSegmentTree($low, $high, $pos)
    {
        if($low == $high){
            $this->segmentTree[$pos] = $this->arr[$low];
            return;
        }
        $mid = intval(($low + $high)/2);
        $this->constructMinSegmentTree($low, $mid, (2 * $pos) + 1);
        $this->constructMinSegmentTree($mid + 1, $high, (2 * $pos) + 2);
        $this->segmentTree[$pos] = $this->segmentTree[(2*$pos)+1] ^ $this->segmentTree[(2*$pos)+2];
    }

    private function updateSegmentTree($index, $delta, $low, $high, $pos) 
    { 
        //if index to be updated is less than low or higher than high just return.
        if($index < $low || $index > $high){
            return;
        }
        
        //if low and high become equal, then index will be also equal to them and update
        //that value in segment tree at pos
        if($low == $high){
            $this->segmentTree[$pos] = $delta;
            return;
        }
        //otherwise keep going left and right to find index to be updated 
        //and then update current tree position if min of left or right has
        //changed.
        $mid = intval(($low + $high)/2);
        $this->updateSegmentTree($index, $delta, $low, $mid, (2 * $pos) + 1);
        $this->updateSegmentTree($index, $delta, $mid + 1, $high, (2 * $pos) + 2);
        $this->segmentTree[$pos] = $this->segmentTree[(2*$pos)+1] ^ $this->segmentTree[(2*$pos) + 2];
    }

    private function rangeQuery($low, $high, $qlow, $qhigh, $pos)
    {
        if($qlow <= $low && $qhigh >= $high){
            return $this->segmentTree[$pos];
        }
        if($qlow > $high || $qhigh < $low){
            return 0;
        }
        $mid = intval(($low+$high)/2);
        return $this->rangeQuery($low, $mid, $qlow, $qhigh, $pos * 2 + 1) ^ $this->rangeQuery($mid + 1, $high, $qlow, $qhigh, $pos * 2 + 2);
    }

    public function createSegmentTree() 
    {
        $nextPowOfTwo = pow(2, 16);
        $this->segmentTree = array_fill(0, $nextPowOfTwo * 2 - 1, 0);
        
        $this->constructMinSegmentTree(0, count($this->arr) - 1, 0);
        return $this->segmentTree;
    }

    /*
		function - to check if rectangle on vertices $a, $b, $c ,d from matrix $m is all fullfill with all 1's 
		if not return -1 if yes exchange all 1's to 0's and return 16 bit representation of that number
	*/
    public function check($a, $b, $c, $d, $m) 
    {
		$mm = $m;
	   
		for($i=$a; $i<=$c;$i++) {
			for($j=$b; $j<=$d;$j++) {
				if($m[$i][$j]==0) {
					return -1;
				} 
				else $mm[$i][$j] = 0;
			}
		}
		
		$s="0000000000000000";
		for($i=0; $i<4;$i++) {
			for($j=0; $j<4;$j++) {
				$s[$i*4+$j] = $mm[$i][$j]+'0';
			}
			   
		}
		return bindec($s);
	}

	/*
		function - calculates moves for number $n, the moves express next allowable moves from binary matrix $n  
	*/
    public function calculateMoves(&$moves, $n) 
    {
		$s = str_pad(decbin($n), 16, "0", STR_PAD_LEFT);
		$m = array_fill(0, 4, array_fill(0, 4, -1));
		for($i=0; $i<4;$i++) 
			for($j=0; $j<4;$j++) 
				$m[$i][$j] = $s[$i*4+$j]-'0';
			
		for($a=0; $a<4;$a++) {
			for($b=0; $b<4;$b++) {
				for($c=$a; $c<4; $c++) {
					for($d=$b; $d<4; $d++) {
						$k = $this->check($a,$b,$c,$d,$m);
						if($k!=-1) {
							$moves[] = $k;
						}
					}
				}
			}
		}
		return $moves;
	}
	
	public function calculateMex($set)
	{
		$mex = 0;
		while ($set->has($mex))
			$mex++;
		return $mex;
	}

	public function calculateGrundy($n)
	{
	 
		if ($this->Grundy[$n] != -1)
			return $this->Grundy[$n];
	 
		$set = new Set([]);
		$moves = array();
		$moves = $this->calculateMoves($moves, $n);
		
		foreach($moves as $item) {
			$set->add($this->calculateGrundy($item));
		}
	   
		$this->Grundy[$n] = $this->calculateMex($set);
		return $this->Grundy[$n];
	}
    
    

    

    

    public function init() {
        $test = 65535; // 65535;
        $this->Grundy[0] = 0;
        $this->Grundy[1] = 1;
        $this->Grundy[2] = 1;
        $this->Grundy[3] = 2;
        $this->Grundy[4] = 1;
        $this->Grundy[5] = 0;
        $this->Grundy[$test] = $this->calculateGrundy($test);  // top-down DP approach to fulfill all the table with grundy numbers
        //var_dump($this->Grundy);
        $t = stream_get_line(STDIN, 20000000000, PHP_EOL);
        $numbers = explode(' ', stream_get_line(STDIN, 20000000000, PHP_EOL));
        $this->line = '';
        // $this->matrix = array();
        for ($i=0; $i<$t; $i++) {
            for ($j=0; $j<$numbers[0]; $j++) {
                for ($k=0; $k<5; $k++) {
                    if ($k !== 4) {
                        $this->line .= stream_get_line(STDIN, 100, PHP_EOL);
                    } else {
                        stream_get_line(STDIN, 100, PHP_EOL);
                    }
                }
                $this->arr[$j] = $this->Grundy[bindec($this->line)];
                // $this->matrix[] = str_split($this->line);
                $this->line = '';
            }
            $this->createSegmentTree(); // DODANE !!!

            for ($q=0; $q<$numbers[1]; $q++) { 
                $query = explode(' ',stream_get_line(STDIN, 100, PHP_EOL));
                switch ($query[0]) {
                    case '1':
                        $l = --$query[1];
                        $r = --$query[2];
                        if ($this->rangeQuery(0, $numbers[0] - 1, $l, $r, 0)) {
                            echo 'Pishty'.PHP_EOL;
                        } else {
                            echo 'Lotsy'.PHP_EOL;
                        }
                    break;
                    case '2':
                        $pos = $query[1];
                        $id = --$pos;
                        for ($k=0; $k<4; $k++) {
                            $this->line .= stream_get_line(STDIN, 100, PHP_EOL);
                        }
                        $this->updateSegmentTree($id, $this->Grundy[bindec($this->line)], 0, $numbers[0] - 1, 0);
                        // $this->matrix[$pos] = str_split($this->line);
                        $this->line = '';
                    break;
                }
            }
        }
    }

    public function __construct() {
        $this->Grundy = array_fill(0, 65536, -1);
    }

}


$grundyClass = new Grundy();
$grundyClass->init();
?>
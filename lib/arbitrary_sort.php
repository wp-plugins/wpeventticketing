<?php

class arbitrarySort
{
	public $sort;

	public function __construct($sort)
	{
		$this->sort = $sort;
	}

	public function cmp($a, $b)
    {
        if (is_numeric($a[$this->sort]) && is_numeric($b[$this->sort]))
        {
			if ($a[$this->sort] == $b[$this->sort])
			{
				return 0;
			}
			return ($a[$this->sort] < $b[$this->sort]) ? -1 : 1;
    	}
		else
		{
			return strcasecmp($a[$this->sort], $b[$this->sort]);
		}
	}
}

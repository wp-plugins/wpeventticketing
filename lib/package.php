<?php

require_once WP_EVENT_TICKETING_LIB_DIR . 'ticket.php';

class package
{
	public $packageId;
	public $packageName;
	public $tickets;
	public $ticketQuantity;
	public $expireStart;
	public $expireEnd;
	public $price;
	public $packageQuantity;
	public $packageDescription;
	public $orderDetails;
	public $coupon;
	public $active;

	function __construct($tickets = array())
	{
		$this->tickets = $tickets;
		$this->active = false;
	}

	public function displayForm()
	{
		echo '<div id="eventPackage" class="wrap">';
		echo '<h2>'.$this->displayName().'</h2>';
		echo '<div class="packageName">Package Display Name<input type="text" name="packageDisplayName" value="' . $this->packageName . '"></div>';
		echo '<div class="packageDescription">Package Description<textarea name="packageDescription">' . $this->packageDescription . '</textarea></div>';
		echo '<div>';
		echo '<h2>Included Tickets</h2>';
		if (is_array($this->tickets))
		{
			echo '<ul>';
			foreach ($this->tickets as $o)
			{
				echo '<li>';
				echo $o->displayName() . ' X <input name="packageTicketQuantity" size="2" type="text" value="' . $this->ticketQuantity . '">&nbsp;&nbsp;';
				echo '<a href="#" onclick="javascript:document.ticketAddToPackage.del.value=\'' . $o->ticketId . '\'; document.ticketAddToPackage.submit();return false;">Reset</a>';
				echo '</li>';
			}
			echo '</ul>';
		}
		echo '<h2>Package Expiration Dates</h2>';
		echo '<div>Start: <input type="text" name="packageExpireStart" id="expireStart" value="' . $this->expireStart . '"> End: <input type="text" name="packageExpireEnd" id="expireEnd" value="' . $this->expireEnd . '"></div>';
		echo '</div>';
		echo '<h2>Package Cost</h2>';
		echo '<div>'.eventTicketingSystem::getCurrencySymbol().'<input type="text" name="packagePrice" value="' . $this->price . '" size="5"></div>';
		//echo '</div>';
		echo '<h2>Package Quantity</h2>';
		echo '<div>Quantity: <input type="text" name="packageQuantity" value="' . $this->packageQuantity . '" size="3"><br /><p style="font-style: italic;">How many of this package to sell? Leave blank for no limit</p></div>';
		echo '</div>';
	}

	public function setActive($active)
	{
		if($active && $this->validatePackage())
		{
			$this->active = true;
		}
		else
		{
			$this->active = false;
		}
	}

	public function validatePackage()
	{
		if(count($this->tickets) == 0)
			return false;
		
		return true;
	}

	public function setPackageId($id)
	{
		$this->packageId = $id;
	}

	public function setExpire($dates)
	{
		$this->expireStart = $dates["start"];
		$this->expireEnd = $dates["end"];
	}

	public function displayName()
	{
		return ($this->packageName == '' ? 'Unnamed' : $this->packageName);
	}

	public function setDisplayName($display)
	{
		$this->packageName = $display;
	}

	public function setPackageDescription($desc)
	{
		$this->packageDescription = $desc;
	}

	public function addTicket($ticket)
	{
		$this->tickets[$ticket->ticketId] = $ticket;
	}

	public function setTicketQuantity($num)
	{
		$this->ticketQuantity = $num;
	}

	public function setPackagePrice($price)
	{
		$this->price = $price;
	}

	public function setPackageQuantity($q)
	{
		$this->packageQuantity = $q;
	}

	public function delTicket($ticketId)
	{
		unset($this->tickets[$ticketId]);
		
		if(count($this->tickets) == 0)
		{
			$this->setActive(false);
		}
	}

	public function validDates()
	{
		if (current_time('timestamp') > strtotime($this->expireStart) && current_time('timestamp') < strtotime($this->expireEnd." +1 day"))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function setOrderDetails($p)
	{
		$this->orderDetails = $p;
	}
	
	public function setCoupon($coupon)
	{
		$this->coupon = $coupon;
	}
}

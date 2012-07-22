<?php

class ticket
{
	public $ticketName;
	public $ticketOptions;
	public $ticketId;
	public $final;
	public $soldTime;

	function __construct($ticketOptions = array())
	{
		$this->ticketOptions = $ticketOptions;
		$this->final = false;
	}

	function __clone()
	{
		foreach ($this->ticketOptions as $k => $v)
		{
			$this->ticketOptions[$k] = clone $v;
		}
	}

	public function displayName()
	{
		return ($this->ticketName == '' ? 'Unnamed' : $this->ticketName);
	}

	public function displayFull()
	{
		echo '<div id="eventTicket">';
		echo '<div class="ticketName">' . ($this->ticketName == '' ? 'Unnamed' : $this->ticketName) . '</div>';
		/*
				  echo '<div>';
				  if(is_array($this->ticketOptions))
				  {
					  foreach ($this->ticketOptions as $o)
					  {
						  echo '<div>'.$o->displayName.'</div>';
					  }
				  }
				  echo '</div>';
		*/
		echo '</div>';

	}

	public function displayForm()
	{
		echo '<div id="eventTicket">';
		echo '<div class="ticketName">Ticket Display Name&nbsp;<input name="ticketDisplayName" type="text" value="' . $this->ticketName . '"></div>';
		echo '<div>';
		//echo '<h3>Ticket Options</h3>';
		if (is_array($this->ticketOptions))
		{

			echo "<table class='widefat'>";
			echo "<thead>";
			echo "<tr>";
			echo "<th>New Ticket Options</th>";
			echo "<th>Actions</th>";
			echo "<tbody>";
			foreach ($this->ticketOptions as $o)
			{
				echo "<tr>";
				echo '<td>' . $o->displayName . '</td>';
				echo '<td><a href="#" onclick="javascript:document.ticketOptionAddToTicket.del.value=\'' . $o->optionId . '\'; document.ticketOptionAddToTicket.submit();return false;">Delete</a></td>';
			}

			echo "</tr>";
			echo "</tbody>";
			echo "</tr>";
			echo "</thead>";
			echo "</table>";
			echo "<br />";

		}
		echo '</div>';
	}

	public function addOption($option)
	{
		$this->ticketOptions[$option->optionId] = $option;
	}

	public function delOption($optionId)
	{
		unset($this->ticketOptions[$optionId]);
	}

	public function setDisplayName($display)
	{
		$this->ticketName = $display;
	}

	public function setTicketId($id)
	{
		$this->ticketId = $id;
	}
	public function setSoldTime($timeStamp)
	{
		$this->soldTime = $timeStamp;
	}
}

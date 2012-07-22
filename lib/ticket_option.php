<?php

class ticketOption
{
	public $displayName;
	public $displayType;
	public $options;
	public $required;
	public $value;
	public $optionId;

	function __construct($display = NULL, $displayType = NULL, $options = NULL, $required = true)
	{
		$this->displayName = $display;
		$this->displayType = $displayType;
		$this->options = $options;
		$this->required = $required;
	}

	public function displayForm()
	{
		ob_start();
		switch ($this->displayType)
		{
			case "text":
				echo '<input class="ticket-option-text" id="text-' . $this->optionId . '" name="ticketOption[' . $this->optionId . ']" value="' . $this->value . '">';
				break;
			case "checkbox":
				echo '<input class="ticket-option-checkbox" id="checkbox-' . $this->optionId . '" name="ticketOption[' . $this->optionId . ']" ' . ($this->value ? "CHECKED" : "") . '>';
				break;
			case "dropdown":
				echo '<select class="ticket-option-dropdown" id="dropdown-' . $this->optionId . '" name="ticketOption[' . $this->optionId . ']">';
				foreach ($this->options as $o)
				{
					echo '<option ' . ($o == $this->value ? "selected" : "") . '>' . $o . '</option>';
				}
				echo '</select>';
				break;
			case "multidropdown":
				echo '<select size=5 MULTIPLE class="ticket-option-multidropdown" id="multidropdown-' . $this->optionId . '" name="ticketOption[' . $this->optionId . '][]">';
				foreach ($this->options as $o)
				{
					echo '<option ' . (in_array($o,$this->value) ? "selected" : "") . '>' . $o . '</option>';
				}
				echo '</select>';
				break;
		}
		return ob_get_clean();
	}

	public function displayValue()
	{
		echo $this->value;
	}

	public function display()
	{
		echo $this->displayName;
	}

	public function setOptionId($id)
	{
		$this->optionId = $id;
	}
}

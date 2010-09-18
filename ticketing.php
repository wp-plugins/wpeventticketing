<?php
/*
Plugin Name: Event Ticketing System
Plugin URI: http://9seeds.com/
Description: Sell tickets for an event
Author: 9seeds
Version: .1
Author URI: http://9seeds.com/
*/

register_activation_hook(__FILE__, array("eventTicketingSystem", "activate"));
register_deactivation_hook(__FILE__, array("eventTicketingSystem", "deactivate"));
add_action('admin_init', array("eventTicketingSystem", "adminscripts"));
add_action('wp_print_styles', array("eventTicketingSystem", "frontendscripts"));
add_action('admin_menu', array("eventTicketingSystem", "options"));
add_shortcode('eventTicketing', array("eventTicketingSystem", 'shortcode'));
add_action('template_redirect', array("eventTicketingSystem", "paypal"));

class eventTicketingSystem
{
	function activate()
	{
		//Set up default options

		$data = unserialize(file_get_contents(WP_PLUGIN_DIR . '/' . plugin_basename(dirname(__FILE__)) . '/defaults.ser', serialize($out)));

		if (!get_option("eventTicketingSystem"))
		{
			add_option("eventTicketingSystem", $data);
		}
	}

	function deactivate()
	{
		delete_option("eventTicketingSystem");
	}

	function adminscripts()
	{
		$pluginurl = WP_PLUGIN_URL . '/' . plugin_basename(dirname(__FILE__));
		wp_enqueue_script('eventticketingscript', $pluginurl . '/js/ticketing.js', array('jquery'));
		wp_enqueue_script('datepicker', $pluginurl . '/js/jquery.ui.datepicker.js', array('eventticketingscript', 'jquery-ui-core'));
		wp_enqueue_style('datepickercss', $pluginurl . '/css/ui.all.css');
	}

	function frontendscripts()
	{
		$pluginurl = WP_PLUGIN_URL . '/' . plugin_basename(dirname(__FILE__));
		wp_enqueue_style('eventticketingstyle', $pluginurl . '/css/ticketing.css');
	}

	function options()
	{
		//add_options_page('Ticketing Options', 'Event Ticketing', 'manage_options', 'eventTicketingSystem', array("eventTicketingSystem","control"));
		add_menu_page('Ticketing', 'Ticketing', 'activate_plugins', 'eventticketing', array("eventTicketingSystem", "ticketHelp"), WP_PLUGIN_URL . '/' . plugin_basename(dirname(__FILE__)) . '/images/calendar_full.png');
		add_submenu_page('eventticketing', 'Ticket Options', 'Ticket Options', 'activate_plugins', 'ticketoptions', array('eventTicketingSystem', 'ticketOptionsControl'));
		add_submenu_page('eventticketing', 'Tickets', 'Tickets', 'activate_plugins', 'tickettickets', array('eventTicketingSystem', 'ticketTicketsControl'));
		add_submenu_page('eventticketing', 'Packages', 'Packages', 'activate_plugins', 'ticketpackages', array('eventTicketingSystem', 'ticketPackagesControl'));
		add_submenu_page('eventticketing', 'Coupons', 'Coupons', 'activate_plugins', 'ticketcoupons', array('eventTicketingSystem', 'ticketCouponsControl'));
		add_submenu_page('eventticketing', 'Attendance', 'Attendance', 'activate_plugins', 'ticketevents', array('eventTicketingSystem', 'ticketEventsControl'));
		add_submenu_page('eventticketing', 'Paypal', 'Paypal', 'activate_plugins', 'ticketpaypal', array('eventTicketingSystem', 'ticketPaypalControl'));
		add_submenu_page('eventticketing', 'Messages', 'Messages', 'activate_plugins', 'ticketmessages', array('eventTicketingSystem', 'ticketMessagesControl'));
	}

	function ticketHelp()
	{
		global $wpdb;
		$o = get_option("eventTicketingSystem");
		$packages = $wpdb->get_results("select option_value from {$wpdb->options} where option_name like 'package_%'");
		if (is_array($packages))
		{
			foreach ($packages as $k => $v)
			{
				$v = unserialize($v->option_value);
				$package[$v->displayName()]['count']++;
				$package[$v->displayName()]['money'] += $v->price;
				foreach ($v->tickets as $t)
				{
					$ticket[$t->displayName()]['count']++;
					$attendee[$t->displayName()][] = $t;
				}
			}
		}
		$coupons = $wpdb->get_results("select option_value from {$wpdb->options} where option_name like 'coupon_%'");
		//echo '<pre>'.print_r($coupons,true).'</pre>';
		if (is_array($coupons))
		{
			foreach ($coupons as $k => $v)
			{
				$v = unserialize($v->option_value);
				foreach ($v["items"] as $c)
				{
					$coupon[$c["name"]]['count'] += $c["quantity"];
					$coupon[$c["name"]]['money'] += $o["packageProtos"][$c["packageid"]]->price;
				}
			}
		}

		$pTotal = $cTotal = 0;


		echo '<div id="ticket_help" class="wrap">';
		echo '<div id="ticket_sales_left">';
		echo '<div id="icon-users" class="icon32"></div><h2>Event Ticketing</h2>';
		//counts table
		echo "<table class='widefat'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th>Package</th>";
		echo "<th>Sold</th>";
		echo "<th>Revenue</th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		if (is_array($package))
		{
			$total = 0;
			foreach ($package as $k => $v)
			{
				$total += $v["money"];
				echo "<tr>";
				echo '<td>' . $k . '</td>';
				echo '<td>' . $v["count"] . '</td>';
				echo '<td>$' . number_format($v["money"], 2) . '</td>';
				echo "</tr>";
			}
			$pTotal = $total;
			echo '<tr><td>Total Package Revenue</td><td>&nbsp;</td><td>$' . number_format($pTotal, 2) . '</td></tr>';
		}
		echo "</tbody>";
		echo "<thead>";
		echo "<tr>";
		echo "<th>Coupon</th>";
		echo "<th>Used</th>";
		echo "<th>Discounted</th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		if (is_array($coupon))
		{
			$total = 0;
			foreach ($coupon as $k => $v)
			{
				$total += $v["money"];
				echo "<tr>";
				echo '<td>' . $k . '</td>';
				echo '<td>' . $v["count"] . '</td>';
				echo '<td>($' . $v["money"] . ')</td>';
				echo "</tr>";
			}
			$cTotal = $total;
		}
		echo '<tr><td><strong>Total Revenue</strong></td><td>&nbsp;</td><td><strong>$' . number_format(($pTotal - $cTotal), 2) . '</strong></td></tr>';
		echo "</tbody>";
		echo "<thead>";
		echo "<tr>";
		echo "<th>Ticket</th>";
		echo "<th>Sold</th>";
		echo "<th>Remaining</th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		if (is_array($ticket))
		{
			$total = 0;
			foreach ($ticket as $k => $v)
			{
				$total += $v["count"];
				echo "<tr>";
				echo '<td>' . $k . '</td>';
				echo '<td>' . $v["count"] . '</td>';
				echo '<td>&nbsp;</td>';
				echo "</tr>";
			}
			echo "<tr>";
			echo '<td>Total</td>';
			echo '<td>' . $total . '</td>';
			echo '<td>' . ($o["eventAttendance"] - $total) . '</td>';
			echo "</tr>";
		}
		echo "</tbody>";
		echo "</table>";
		//end counts table
		echo '</div>';
		echo '<div id="attendeeGraph">';
		echo '<img src="http://chart.apis.google.com/chart?chs=300x150&cht=p3&chd=s:Mx&chdl=Sold|Left&chp=0.628&chl=' . $total . '|' . ($o["eventAttendance"] - $total) . '&chtt=Attendance">';
		echo '</div>';
		if (is_array($attendee))
		{
			echo '<div id="ticket_sales_bottom">';
			echo '<div id="icon-users" class="icon32"></div><h2>Attendees</h2>';
			foreach ($attendee as $ticketType => $v)
			{
				//filthy hack to display ticket info quickly
				//should be moved into ticket and ticketOption objects
				foreach ($v as $ticket)
				{
					foreach ($ticket->ticketOptions as $o)
					{
						$th[$ticketType][$o->displayName] = $o->displayName;
						$trtmp[$o->displayName] = $o->value;
					}
					$tr[] = $trtmp;
				}
			}
			foreach ($th as $k => $v)
			{
				echo "<table class='widefat'>";
				echo "<thead>";
				echo "<tr>";
				echo '<th>&nbsp;</th>';
				foreach ($v as $header)
				{
					$headerkey[] = $header;
					echo '<th>' . $header . '</th>';
				}
				echo "</tr>";
				echo "</thead>";
				echo '<tbody>';
				$c = 0;
				foreach ($tr as $data)
				{
					$c++;
					echo '<tr>';
					echo '<td>' . $c . '</td>';
					foreach ($headerkey as $key)
					{
						echo '<td>' . (strlen($data[$key]) ? $data[$key] : "&nbsp;") . '</td>';
					}
					echo '</tr>';
				}
				echo '</tbody>';
				echo '</table>';
			}
		}
		/*
		echo '<ul>';
        echo '<li><h2>Step 1:</h2><br />Create all the options you want for each ticket. This is the information you would ask of each person attending your event such as personal info (name/address/phone) or shirt size or meal preference</li>';
        echo '<li><h2>Step 2:</h2><br />Create a ticket and attach the options you want for that ticket. <strong>Add the options in the order you want them displayed on the form when someone purchases a ticket</strong><p style="font-style: italic;"><strong>Example:</strong> Create two types of tickets which are the same where one doesn\'t ask for shirt size since a shirt isn\'t included</p></li>';
        echo '<li><h2>Step 3:</h2><br />Create a package and attach a ticket to it. This is where you determine the price for the event.<p style="font-style: italic;"><strong>Example:</strong> You have defined a single standard ticket called Regular. Attach Regular to the package with a quantity of 1, give it a price which is $10 less than full admission and give it an active date which will end a month before the event and a quantity of 50. With this you have created an early bird ticket which will expire either a month before the event occurs or when 50 of them are sold, whichever comes first<p style="font-style: italic;"><strong>Example:</strong> Create a package and attach Regular to the package with a quantity of 4 and give this package a price of $500. This would be like a sponsorship package where you are bundling some free tickets to go along with a sponsorship</li>';
        echo '<li><h2>Step 4:</h2><br />Set your maximum event attendance and whether or not you want to display remaining tickets on the ticket form. This number supercedes all the package quantities if they are set. At no point will you sell more than this many tickets to the event.</li>';
        echo '<li><h2>Step 5:</h2><br />Set your maximum paypal info. None of this is going to work if you cannot get paid. Follow <a href="https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_api_NVPAPIBasics#id084E30I30RO">these instructions at Paypal</a> to get your API signature</li>';
        echo '</ul>';
		 */
		echo '</div></div>';
	}

	function ticketOptionsControl()
	{
		//echo "<pre>";print_r($_REQUEST); echo "</pre>";
		$o = get_option("eventTicketingSystem");
		if (wp_verify_nonce($_POST['ticketOptionAddNonce'], plugin_basename(__FILE__)))
		{
			$_REQUEST = array_map('stripslashes_deep', $_REQUEST);

			if (is_numeric($_REQUEST["edit"]))
			{
				$ticketOption = $o["ticketOptions"][$_REQUEST["edit"]];
			}
			elseif (is_numeric($_REQUEST["del"]))
			{
				unset($o["ticketOptions"][$_REQUEST["del"]]);
				update_option("eventTicketingSystem", $o);
				$ticketOption = new ticketOption();
			}
			else
			{
				if (is_numeric($_REQUEST["update"]))
				{
					unset($o["ticketOptions"][$_REQUEST["update"]]);
					$nextId = $_REQUEST["update"];
				}
				else
				{
					$nextId = ((int) max(array_keys($o["ticketOptions"]))) + 1;
				}
				if ($_REQUEST["ticketOptionDisplayType"] != "dropdown")
				{
					$_REQUEST["ticketOptionDrop"] = NULL;
				}
				$o["ticketOptions"][$nextId] = new ticketOption($_REQUEST["ticketOptionDisplay"], $_REQUEST["ticketOptionDisplayType"], $_REQUEST["ticketOptionDrop"]);
				$o["ticketOptions"][$nextId]->setOptionId($nextId);
				update_option("eventTicketingSystem", $o);

				$ticketOption = new ticketOption();
			}
		}
		else
		{
			$ticketOption = new ticketOption();
		}
		echo "<div id='ticket_wrapper_1'>";
		echo "<div id='ticket_options'>";
		echo "<div class='wrap'>";
		echo "<div id='icon-users' class='icon32'></div><h2>Options</h2>";
		if (is_array($o["ticketOptions"]))
		{

			echo "<table class='widefat'>";
			echo "<thead>";
			echo "<tr>";
			echo "<th>Ticket Options</th>";
			echo "<th>Actions</th>";
			echo "</tr>";
			echo "</thead>";
			echo "<tbody>";
			foreach ($o["ticketOptions"] as $k => $v)
			{
				echo "<tr>";
				echo '<td>' . $v->displayName . '</td>';
				echo '<td><a href="#" onclick="javascript:document.ticketOptionAdd.update.value=\'\';document.ticketOptionAdd.del.value=\'\';document.ticketOptionAdd.edit.value=\'' . $v->optionId . '\';document.ticketOptionAdd.submit();return false;">Edit</a>&nbsp;|&nbsp;<a href="#" onclick="javascript:document.ticketOptionAdd.update.value=\'\';document.ticketOptionAdd.edit.value=\'\';document.ticketOptionAdd.del.value=\'' . $v->optionId . '\';if (confirm(\'Are you sure you want to delete this option?\')) document.ticketOptionAdd.submit();return false;">Delete</a></td>';
			}

			echo "</tr>";
			echo "</tbody>";
			echo "</table>";
			echo "</div></div>";
		}

		echo "<div id='ticket_new_options'>";
		echo '<form method="post" action="" name="ticketOptionAdd">
            <input type="hidden" name="ticketOptionAddNonce" id="ticketOptionAddNonce" value="' . wp_create_nonce(plugin_basename(__FILE__)) . '" />
			<input type="hidden" name="del" value="" />
			<input type="hidden" name="edit" value="" />
			<input type="hidden" name="update" value="' . $ticketOption->optionId . '" />
			
			<div class="wrap">
			<div id="icon-users" class="icon32"></div><h2>Create New Option</h2>
			</div>
			<div id="inputTicketOptionDisplay">
				Display Name: <input type="text" name="ticketOptionDisplay" value="' . $ticketOption->displayName . '">
			</div>
			<div id="inputTicketOptionDisplayType">
				Option Type: <select name="ticketOptionDisplayType" id="ticketoptionselect">
				<option ' . ($ticketOption->displayType == "text" ? "SELECTED" : "") . '>text</option>
				<option ' . ($ticketOption->displayType == "dropdown" ? "SELECTED" : "") . '>dropdown</option>
				</select>
			</div>';
		echo '<div id="optionvalsdiv">';
		if (is_array($ticketOption->options) && !empty($ticketOption->options))
		{
			$c = 0;
			foreach ($ticketOption->options as $option)
			{
				$c++;
				echo '<div id="input' . $c . '" style="margin-bottom:4px;" class="clonedInput">';
				echo 'Value: <input type="text" name="ticketOptionDrop[' . $c . ']" id="ticketOptionDrop' . $c . '" value="' . $option . '"/>';
				echo '</div>';
			}
		}
		else
		{
			echo '<div id="input1" style="margin-bottom:4px;" class="clonedInput">';
			echo 'Value: <input type="text" name="ticketOptionDrop[1]" id="ticketOptionDrop1" />';
			echo '</div>';
		}

		echo '<div>
			<p class="submit"><input type="button" id="btnAdd" value="add another option value" />
			<input type="button" id="btnDel" value="remove last option value" /></p>
		</div></div>';
		if (is_numeric($_REQUEST["edit"]) && is_numeric($ticketOption->optionId))
		{
			echo '<div>
				<p class="submit"><input type="submit" class="button-primary" name="submitbutt" value="Update Ticket Option: ' . $ticketOption->displayName . '"></p>
			</div>';
		}
		else
		{
			echo '<div>
				<input type="submit" class="button-primary" name="submitbutt" value="Add Ticket Option">
			

			</div>';
		}
		echo '</form>';
		echo '</div>';
		echo '</div>';
	}

	function ticketTicketsControl()
	{
		//echo "<pre>";print_r($_REQUEST); echo "</pre>";
		$o = get_option("eventTicketingSystem");

		if (wp_verify_nonce($_POST['ticketOptionAddToTicketNonce'], plugin_basename(__FILE__)))
		{
			if (is_numeric($_REQUEST["ticketId"]))
			{
				if (is_numeric($_REQUEST["add"]))
				{
					$o["ticketProtos"][$_REQUEST["ticketId"]]->addOption($o["ticketOptions"][$_REQUEST["add"]]);
					update_option("eventTicketingSystem", $o);
				}
				elseif (is_numeric($_REQUEST["del"]))
				{
					$o["ticketProtos"][$_REQUEST["ticketId"]]->delOption($_REQUEST["del"]);
					update_option("eventTicketingSystem", $o);
				}

				$ticketProto = $o["ticketProtos"][$_REQUEST["ticketId"]];
			}
		}

		if (wp_verify_nonce($_POST['ticketEditNonce'], plugin_basename(__FILE__)))
		{
			if ($_REQUEST["add"] == 1)
			{
				if (is_array($o["ticketProtos"]) && !empty($o["ticketProtos"]))
				{
					$nextId = ((int) max(array_keys($o["ticketProtos"]))) + 1;
				}
				else
				{
					$nextId = 0;
				}

				$o["ticketProtos"][$nextId] = new ticket();
				$o["ticketProtos"][$nextId]->setTicketId($nextId);

				update_option("eventTicketingSystem", $o);

				$ticketProto = $o["ticketProtos"][$nextId];
			}
			elseif (is_numeric($_REQUEST["del"]))
			{
				unset($o["ticketProtos"][$_REQUEST["del"]]);
				update_option("eventTicketingSystem", $o);
			}
			elseif (is_numeric($_REQUEST["edit"]))
			{
				$ticketProto = $o["ticketProtos"][$_REQUEST["edit"]];
			}
			elseif (is_numeric($_REQUEST["update"]))
			{
				$o["ticketProtos"][$_REQUEST["update"]]->setDisplayName($_REQUEST["ticketDisplayName"]);
				update_option("eventTicketingSystem", $o);
			}
		}

		echo "<div id='ticket_wrapper_2'>";
		echo "<div id='ticket_holder_left'>";
		echo "<div class='wrap'>";
		echo "<div id='icon-users' class='icon32'></div><h2>Tickets</h2>";

		if (is_array($o["ticketProtos"]))
		{

			echo "<table class='widefat'>";
			echo "<thead>";
			echo "<tr>";
			echo "<th>Existing Tickets</th>";
			echo "<th>Actions</th>";
			echo "</tr>";
			echo "</thead>";
			echo "<tbody>";
			foreach ($o["ticketProtos"] as $k => $v)
			{
				echo "<tr>";
				echo '<td>' . $v->displayName() . '</td>';
				echo '<td><a href="#" onclick="javascript:document.ticketEdit.edit.value=\'' . $v->ticketId . '\'; document.ticketEdit.submit();return false;">Edit</a>&nbsp;|&nbsp;<a href="#" onclick="javascript:document.ticketEdit.del.value=\'' . $v->ticketId . '\';if (confirm(\'Are you sure you want to delete this ticket? THIS CANNOT BE UNDONE\')) document.ticketEdit.submit();return false;">Delete</a>';

			}

			echo "</tr>";
			echo "</tbody>";
			echo "</table>";

		}
		echo "</div></div>";
		echo "<div id='ticket_holder_right'>";
		echo '<form method="post" action="" name="ticketOptionAddToTicket">
		<input type="hidden" name="ticketOptionAddToTicketNonce" id="ticketOptionAddToTicketNonce" value="' . wp_create_nonce(plugin_basename(__FILE__)) . '" />
		<input type="hidden" name="ticketId" value="' . $ticketProto->ticketId . '" />
		<input type="hidden" name="add" value="" />
		<input type="hidden" name="del" value="" />
		</form>';
		echo '<form method="post" action="" name="ticketEdit">
		<input type="hidden" name="ticketEditNonce" id="ticketEditNonce" value="' . wp_create_nonce(plugin_basename(__FILE__)) . '" />
		<input type="hidden" name="update" value="' . $ticketProto->ticketId . '">
		<input type="hidden" name="add" value="" />
		<input type="hidden" name="edit" value="" />
		<input type="hidden" name="del" value="" />';
		if (is_array($o["ticketOptions"]) && is_numeric($ticketProto->ticketId))
		{

			echo "<div class='wrap'>";
			echo "<div id='icon-users' class='icon32'></div><h2>Ticket Options</h2>";
			echo "</div>";
			echo "<table class='widefat'>";
			echo "<thead>";
			echo "<tr>";
			echo "<th>Ticket Options</th>";
			echo "<th>Actions</th>";
			echo "</tr>";
			echo "</thead>";
			echo "<tbody>";
			foreach ($o["ticketOptions"] as $k => $v)
			{
				echo "<tr>";
				echo '<td>' . $v->displayName . '</td>';
				echo '<td><a href="#" onclick="javascript:document.ticketOptionAddToTicket.add.value=\'' . $v->optionId . '\'; document.ticketOptionAddToTicket.submit();return false;">Add To New Ticket Below</a></td>';
			}

			echo "</tr>";
			echo "</tbody>";
			echo "</table>";

			$ticketProto->displayForm();
			echo '<div><input type="submit" class="button-primary" name="submitbutt" value="Save Ticket"></div>';
		}
		else
		{
			echo '<div class="wrap"><h2>Create New Ticket</h2></div><br /><a href="#" class="button" onclick="javascript:document.ticketEdit.add.value=\'1\'; document.ticketEdit.submit();return false;">Add New Ticket</a>';
		}
		echo "</div>";
		echo '</form>';
		echo "</div></div>";
	}

	function ticketPackagesControl()
	{
		//echo "<pre>";print_r($_REQUEST); echo "</pre>";
		$o = get_option("eventTicketingSystem");

		if (wp_verify_nonce($_POST['ticketAddToPackageNonce'], plugin_basename(__FILE__)))
		{
			if (is_numeric($_REQUEST["packageId"]))
			{
				if (is_numeric($_REQUEST["add"]))
				{
					$o["packageProtos"][$_REQUEST["packageId"]]->addTicket($o["ticketProtos"][$_REQUEST["add"]]);
					update_option("eventTicketingSystem", $o);
				}
				elseif (is_numeric($_REQUEST["del"]))
				{
					$o["packageProtos"][$_REQUEST["packageId"]]->delTicket($_REQUEST["del"]);
					update_option("eventTicketingSystem", $o);
				}

				$packageProto = $o["packageProtos"][$_REQUEST["packageId"]];
			}
		}

		if (wp_verify_nonce($_POST['packageEditNonce'], plugin_basename(__FILE__)))
		{
			if ($_REQUEST["add"] == 1)
			{
				if (is_array($o["packageProtos"]) && !empty($o["packageProtos"]))
				{
					$nextId = ((int) max(array_keys($o["packageProtos"]))) + 1;
				}
				else
				{
					$nextId = 0;
				}

				$o["packageProtos"][$nextId] = new package();
				$o["packageProtos"][$nextId]->setPackageId($nextId);
				update_option("eventTicketingSystem", $o);

				$packageProto = $o["packageProtos"][$nextId];
			}
			elseif (is_numeric($_REQUEST["del"]))
			{
				unset($o["packageProtos"][$_REQUEST["del"]]);
				update_option("eventTicketingSystem", $o);
			}
			elseif (is_numeric($_REQUEST["edit"]))
			{
				$packageProto = $o["packageProtos"][$_REQUEST["edit"]];
			}
			elseif (is_numeric($_REQUEST["update"]))
			{
				$_REQUEST = array_map('stripslashes_deep', $_REQUEST);
				$o["packageProtos"][$_REQUEST["update"]]->setDisplayName($_REQUEST["packageDisplayName"]);
				$o["packageProtos"][$_REQUEST["update"]]->setExpire(array("start" => $_REQUEST["packageExpireStart"], "end" => $_REQUEST["packageExpireEnd"]));
				$o["packageProtos"][$_REQUEST["update"]]->setPackagePrice($_REQUEST["packagePrice"]);
				$o["packageProtos"][$_REQUEST["update"]]->setTicketQuantity($_REQUEST["packageTicketQuantity"] < 1 ? 1 : $_REQUEST["packageTicketQuantity"]);
				$o["packageProtos"][$_REQUEST["update"]]->setPackageQuantity($_REQUEST["packageQuantity"]);
				$o["packageProtos"][$_REQUEST["update"]]->setPackageDescription($_REQUEST["packageDescription"]);

				update_option("eventTicketingSystem", $o);
			}
		}
		echo "<div id='ticket_wrapper_2'>";
		echo "<div id='ticket_holder_left'>";
		echo "<div class='wrap'>";
		echo "<div id='icon-users' class='icon32'></div><h2>Packages</h2>";
		if (is_array($o["packageProtos"]))
		{

			echo "<table class='widefat'>";
			echo "<thead>";
			echo "<tr>";
			echo "<th>Existing Packages</th>";
			echo "<th>Actions</th>";
			echo "</tr>";
			echo "</thead>";
			echo "<tbody>";
			foreach ($o["packageProtos"] as $k => $v)
			{
				echo "<tr>";
				echo '<td>' . $v->displayName() . '</td>';
				echo '<td><a href="#" onclick="javascript:document.packageEdit.edit.value=\'' . $v->packageId . '\'; document.packageEdit.submit();return false;">Edit</a>&nbsp;|&nbsp;<a href="#" onclick="javascript:document.packageEdit.del.value=\'' . $v->packageId . '\';if (confirm(\'Are you sure you want to delete this package? THIS CANNOT BE UNDONE\')) document.packageEdit.submit();return false;">Delete</a></td>';
			}

			echo "</tr>";
			echo "</tbody>";
			echo "</table>";

		}
		echo "</div></div>";
		echo "<div id='ticket_holder_right'>";
		echo '<form method="post" action="" name="ticketAddToPackage">
		<input type="hidden" name="ticketAddToPackageNonce" id="ticketAddToPackageNonce" value="' . wp_create_nonce(plugin_basename(__FILE__)) . '" />
		<input type="hidden" name="packageId" value="' . $packageProto->packageId . '" />
		<input type="hidden" name="add" value="" />
		<input type="hidden" name="del" value="" />
		</form>';
		echo '<form method="post" action="" name="packageEdit">
		<input type="hidden" name="packageEditNonce" id="packageEditNonce" value="' . wp_create_nonce(plugin_basename(__FILE__)) . '" />
		<input type="hidden" name="update" value="' . $packageProto->packageId . '">
		<input type="hidden" name="add" value="" />
		<input type="hidden" name="edit" value="" />
		<input type="hidden" name="del" value="" />';
		if (is_array($o["ticketProtos"]) && is_numeric($packageProto->packageId))
		{
			if (empty($packageProto->tickets))
			{
				echo '<div class="wrap"><h2>Pick the type of ticket for this package</h2></div>';

				echo "<table class='widefat'>";
				echo "<thead>";
				echo "<tr>";
				echo "<th>Existing Tickets</th>";
				echo "<th>Actions</th>";
				echo "</tr>";
				echo "</thead>";
				echo "<tbody>";
				foreach ($o["ticketProtos"] as $k => $v)
				{
					echo "<tr>";
					echo '<td>' . $v->displayName() . '</td>';
					echo '<td><a href="#" onclick="javascript:document.ticketAddToPackage.add.value=\'' . $v->ticketId . '\'; document.ticketAddToPackage.submit();return false;">Add Ticket To Package</a></td>';
				}

				echo "</tr>";
				echo "</tbody>";
				echo "</table>";

			}
			else
			{
				$packageProto->displayForm();

				echo '<input type="submit" class="button-primary" name="submitbutt" value="Save Package">';
			}
		}
		else
		{
			echo '<div class="wrap"><h2>Create New Package</h2></div><br /><a href="#" class="button" onclick="javascript:document.packageEdit.add.value=\'1\'; document.packageEdit.submit();return false;">Add New Package</a>';
		}

		echo '</form>';
		echo '</div>';
		echo '</div>';

	}

	function ticketEventsControl()
	{
		//echo "<pre>";print_r($_REQUEST); echo "</pre>";
		$o = get_option("eventTicketingSystem");
		echo '<div id="ticket_events">';
		echo "<div class='wrap'>";
		echo '<div id="icon-users" class="icon32"></div><h2>Event Attendance Maximum</h2>';
		echo '<form method="post" action="" name="eventAttendance">
		<input type="hidden" name="eventAttendanceNonce" id="eventAttendanceNonce" value="' . wp_create_nonce(plugin_basename(__FILE__)) . '" />
		<input type="hidden" name="edit" value="" />';
		if (wp_verify_nonce($_POST['eventAttendanceNonce'], plugin_basename(__FILE__)))
		{
			if ($_REQUEST["edit"] == 1)
			{
				echo '<div id="ticket_events_edit">';
				echo '<table><tr><td>Maximum Attendance</td><td><input type="text" value="' . $o["eventAttendance"] . '" name="eventAttendanceMax" size="4" /></td></tr>';
				echo '<tr><td>Display Totals in Form</td><td><input type="checkbox" value="1" name="displayPackageQuantity" ' . ($o["displayPackageQuantity"] == 1 ? "checked" : "") . '></td></tr>';
				echo '<tr><td colspan="2"><input type="submit" class="button-primary" name="submitbutt" value="Update Total Ticket Quantity" /></td></tr></table>';
				echo '</div>';
			}
			if (is_numeric($_REQUEST["eventAttendanceMax"]))
			{
				$o["eventAttendance"] = $_REQUEST["eventAttendanceMax"];
				$o["displayPackageQuantity"] = $_REQUEST["displayPackageQuantity"];
				update_option("eventTicketingSystem", $o);
			}
		}
		echo '</form>';
		echo "</div>";

		echo "<table class='widefat'>";
		echo "<thead>";
		echo "<tr>";
		echo "<th>Total Tickets To Sell</th>";
		echo "<th>Actions</th>";
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";

		echo "<tr>";
		echo '<td>' . $o["eventAttendance"] . '</td>';
		echo '<td><a href="#" onclick="javascript:document.eventAttendance.edit.value=\'1\'; document.eventAttendance.submit();return false;">Edit</a></td>';

		echo "</tr>";
		echo "</tbody>";
		echo "</table>";

		echo "</div>";
	}

	function ticketPaypalControl()
	{
		$o = get_option("eventTicketingSystem");

		echo '<div class="wrap">';

		if (wp_verify_nonce($_POST['ticketPaypalNonce'], plugin_basename(__FILE__)))
		{
			$o["paypalInfo"] = array(
				"paypalAPIUser" => trim($_REQUEST["paypalAPIUser"]),
				"paypalAPIPwd" => trim($_REQUEST["paypalAPIPwd"]),
				"paypalAPISig" => trim($_REQUEST["paypalAPISig"]),
				"paypalEnv" => trim($_REQUEST["paypalEnv"])
			);
			update_option("eventTicketingSystem", $o);
			echo '<div id="message" class="updated"><p>Paypal settings have been saved.</p></div>';
		}

		echo '<div id="icon-users" class="icon32"></div><h2>Paypal Settings</h2>';
		echo '<form method="post" action="">
			<input type="hidden" name="ticketPaypalNonce" id="ticketPaypalNonce" value="' . wp_create_nonce(plugin_basename(__FILE__)) . '" />
			
			

			<table class="form-table">			
			<tr valign="top" id="tags">
				<th scope="row"><label for="paypalEnv">Environment: </label></th>
				<td><select id="paypalEnv" name="paypalEnv"/>
					<option value="live" ' . ($o["paypalInfo"]["paypalEnv"] == "live" ? "selected" : "") . '>Live</option>
					<option value="sandbox" ' . ($o["paypalInfo"]["paypalEnv"] == "sandbox" ? "selected" : "") . '>Sandbox (for testing)</option>
				</select></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="paypalAPIUser">API User: </label></th>
				<td><input id="paypalAPIUser" type="text" maxlength="110" size="45" name="paypalAPIUser" value="' . $o["paypalInfo"]["paypalAPIUser"] . '" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="paypalAPIPwd">API Password: </label></th>
				<td><input id="paypalAPIPwd" type="text" maxlength="110" size="24" name="paypalAPIPwd" value="' . $o["paypalInfo"]["paypalAPIPwd"] . '" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="paypalAPISig">API Signature: </label></th>
				<td><input id="paypalAPISig" type="text" maxlength="110" size="75" name="paypalAPISig" value="' . $o["paypalInfo"]["paypalAPISig"] . '" /></td>
				
			</tr>
			<tr valign="top">
				
				<td><input class="button-primary" type="submit" name="submitbutton" value="Save Paypal Info" id="submitbutton"/></td>
			</tr>
			
			</table>
			
		</form>';

		echo '</div>';
	}

	function ticketCouponsControl()
	{
		$o = get_option("eventTicketingSystem");

		if (wp_verify_nonce($_POST['couponEditNonce'], plugin_basename(__FILE__)))
		{
			//echo '<pre>'.print_r($_REQUEST,true).'</pre>';
			if (is_numeric($_REQUEST["packageId"]) && strlen($_REQUEST["couponCode"]) && $_REQUEST["submitbutt"] == 'Save Coupon')
			{
				$o["coupons"][$_REQUEST["couponCode"]] = array("couponCode" => $_REQUEST["couponCode"], "packageId" => $_REQUEST["packageId"], "used" => $_REQUEST["couponUsed"]);
				update_option("eventTicketingSystem", $o);
				echo '<div id="message" class="updated"><p>Coupon saved.</p></div>';
			}
			if ($_REQUEST["add"] == 1)
			{
				$coupon = array("couponCode" => '', "packageId" => '', "used" => '');
			}
			if (strlen($_REQUEST["edit"]))
			{
				$coupon = $o["coupons"][$_REQUEST["edit"]];

			}
			if (strlen($_REQUEST["del"]))
			{
				unset($o["coupons"][$_REQUEST["couponCode"]]);
				update_option("eventTicketingSystem", $o);
			}
		}

		echo "<div id='ticket_wrapper_2'>";
		echo "<div id='ticket_holder_left'>";
		echo "<div class='wrap'>";
		echo "<div id='icon-users' class='icon32'></div><h2>Coupons</h2>";
		if (is_array($o["coupons"]))
		{
			echo "<table class='widefat'>";
			echo "<thead>";
			echo "<tr>";
			echo "<th>Existing Coupons</th>";
			echo "<th>For Package</th>";
			echo "<th>Used</th>";
			echo "<th>Actions</th>";
			echo "</tr>";
			echo "</thead>";
			echo "<tbody>";
			foreach ($o["coupons"] as $packageId => $v)
			{
				echo "<tr>";
				echo '<td>' . $v["couponCode"] . '</td>';
				echo '<td>' . $o["packageProtos"][$v["packageId"]]->displayName() . '</td>';
				echo '<td>' . ($v["used"] ? "Yes" : "No") . '</td>';
				echo '<td><a href="#" onclick="javascript:document.couponEdit.edit.value=\'' . $v["couponCode"] . '\'; document.couponEdit.submit();return false;">Edit</a>&nbsp;|&nbsp;<a href="#" onclick="javascript:document.couponEdit.del.value=\'' . $v["couponCode"] . '\';if (confirm(\'Are you sure you want to delete this coupon\')) document.couponEdit.submit();return false;">Delete</a></td>';
				echo "</tr>";
			}

			echo "</tbody>";
			echo "</table>";
		}
		echo "</div>";
		echo "</div>";
		echo "<div id='ticket_holder_right'>";
		echo '<form method="post" action="" name="couponEdit">
		<input type="hidden" name="couponEditNonce" id="couponEditNonce" value="' . wp_create_nonce(plugin_basename(__FILE__)) . '" />
		<input type="hidden" name="add" value="" />
		<input type="hidden" name="edit" value="" />
		<input type="hidden" name="del" value="" />';
		if (isset($coupon))
		{
			if (strlen($coupon["couponCode"]))
			{
				echo '<div class="wrap"><h2>Update Coupon</h2></div>';
			}
			else
			{
				echo '<div class="wrap"><h2>Add Coupon</h2></div>';
			}
			echo '<table class="form-table">';
			echo '<tr><td>Coupon Code</td><td><input name="couponCode" value="' . $coupon["couponCode"] . '"></td></tr>';
			echo '<tr><td>For Package</td><td>
				<select name="packageId">';
			foreach ($o["packageProtos"] as $pk => $pv)
			{
				echo '<option value="' . $pk . '" ' . ($coupon["packageId"] == $pk ? "selected" : "") . '>' . $pv->displayName() . '</option>';
			}
			echo '</select></td></tr>';
			echo '<tr><td>Used</td><td><input type="checkbox" name="couponUsed" value="1" ' . ($coupon["used"] == 1 ? "checked" : "") . '></td></tr>';
			echo '<tr><td colspan="2"><input type="submit" class="button" name="submitbutt" value="Save Coupon"></td></tr>';
			echo '</table>';
		}
		else
		{
			echo '<div class="wrap"><h2>Create New Coupon</h2></div><br /><a href="#" class="button" onclick="javascript:document.couponEdit.add.value=\'1\'; document.couponEdit.submit();return false;">Add New Coupon</a>';
		}
		echo '</form>';
		echo '</div>';
		echo '</div>';
	}

	function ticketMessagesControl()
	{
		$o = get_option("eventTicketingSystem");

		echo '<div class="wrap">';

		if (wp_verify_nonce($_POST['ticketMessagesNonce'], plugin_basename(__FILE__)))
		{
			$o["messages"] = array(
				"messageEventName" => trim(stripslashes($_REQUEST["messageEventName"])),
				"messageThankYou" => trim(stripslashes($_REQUEST["messageThankYou"])),
				"messageEmailFromName" => trim(stripslashes($_REQUEST["messageEmailFromName"])),
				"messageEmailFromEmail" => trim(stripslashes($_REQUEST["messageEmailFromEmail"])),
				"messageEmailBody" => trim(stripslashes($_REQUEST["messageEmailBody"])),
				"messageEmailSubj" => trim(stripslashes($_REQUEST["messageEmailSubj"])),
				"messageEmailBcc" => trim(stripslashes($_REQUEST["messageEmailBcc"])),
			);
			update_option("eventTicketingSystem", $o);
			echo '<div id="message" class="updated"><p>Message settings have been saved.</p></div>';
		}

		echo '<div id="icon-users" class="icon32"></div><h2>Email Message</h2>';
		echo '<form method="post" action="">
			<input type="hidden" name="ticketMessagesNonce" id="ticketMessagesNonce" value="' . wp_create_nonce(plugin_basename(__FILE__)) . '" />
			<table class="form-table">			
			<tr valign="top">
				<th scope="row"><label for="messageEventName">Event Name:</label></th>
				<td><input id="messageEventName" type="text" name="messageEventName" size="80" value="' . $o["messages"]["messageEventName"] . '"></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="messageThankYou">Thank You Page:</label></th>
				<td><textarea id="messageThankYou" name="messageThankYou" rows="10" cols="80"/>' . $o["messages"]["messageThankYou"] . '</textarea></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="messageEmailFromName">Email From Name:</label></th>
				<td><input id="messageEmailFromName" type="text" name="messageEmailFromName" size="40" value="' . $o["messages"]["messageEmailFromName"] . '"></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="messageEmailFromEmail">Email From Email:</label></th>
				<td><input id="messageEmailFromEmail" type="text" name="messageEmailFromEmail" size="40" value="' . $o["messages"]["messageEmailFromEmail"] . '"></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="messageEmailSubj">Email Subject:</label></th>
				<td><input id="messageEmailSubj" type="text" name="messageEmailSubj" size="80" value="' . $o["messages"]["messageEmailSubj"] . '"></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="messageEmailBody">Email Body:</label></th>
				<td><textarea id="messageEmailBody" name="messageEmailBody" rows="10" cols="80"/>' . $o["messages"]["messageEmailBody"] . '</textarea></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="messageEmailBcc">Email to BCC on orders:</label></th>
				<td><input id="messageEmailBcc" type="text" name="messageEmailBcc" size="40" value="' . $o["messages"]["messageEmailBcc"] . '"></td>
			</tr>
			<tr valign="top">
				<td><input class="button-primary" type="submit" name="submitbutton" value="Save Messages" id="submitbutton"/></td>
			</tr>
			</table>
			</form>';
		echo '</div>';

	}

	function shortcode()
	{
		$o = get_option("eventTicketingSystem");
		/*	
		foreach($o as $k => $v)
		{
			if(in_array($k, array('ticketOptions',
			'ticketProtos',
			'packageProtos',
			'eventAttendance',
			'messages',
			'displayPackageQuantity')))
			$out[$k] = $v;
		}
        $path = WP_PLUGIN_DIR . '/' . plugin_basename(dirname(__FILE__)) . '/ticketing.ser';
		var_dump(file_put_contents($path,serialize($out)));
		exit;
        */
		ob_start();

		//return redirect from paypal
		//token=EC-4DR89227KU882313S&PayerID=5SYRSDFCC4Z56
		if (isset($_REQUEST["token"]) && isset($_REQUEST["PayerID"]) && strlen($_REQUEST["token"]) == 20 && strlen($_REQUEST["PayerID"]) == 13)
		{
			//get order details to send to paypal...again
			$order = get_option("paypal_" . $_REQUEST["token"]);
			$total = number_format($order["total"], 2);
			$item = $order["items"];

			include(WP_PLUGIN_DIR . '/' . plugin_basename(dirname(__FILE__)) . '/lib/nvp.php');
			include(WP_PLUGIN_DIR . '/' . plugin_basename(dirname(__FILE__)) . '/lib/paypal.php');
			$p = $o["paypalInfo"];

			$method = "DoExpressCheckoutPayment";
			$cred = array("apiuser" => $p["paypalAPIUser"], "apipwd" => $p["paypalAPIPwd"], "apisig" => $p["paypalAPISig"]);
			$env = $p["paypalEnv"];
			$nvp = array('PAYMENTREQUEST_0_AMT' => $total,
			             'TOKEN' => $_REQUEST["token"],
			             "PAYERID" => $_REQUEST["PayerID"],
			             "PAYMENTREQUEST_0_PAYMENTACTION" => 'Sale',
			             "PAYMENTREQUEST_0_CURRENCYCODE" => 'USD',
			);
			foreach ($item as $k => $i)
			{
				//$nvp['L_PAYMENTREQUEST_0_NAME' . $k] = $i["name"];
				$nvp['L_PAYMENTREQUEST_0_NAME' . $k] = $o["messages"]["messageEventName"] . ": Registration";
				$nvp['L_PAYMENTREQUEST_0_DESC' . $k] = $i["desc"];
				$nvp['L_PAYMENTREQUEST_0_AMT' . $k] = $i["price"];
				$nvp['L_PAYMENTREQUEST_0_QTY' . $k] = $i["quantity"];
			}
			$nvp['PAYMENTREQUEST_0_ITEMAMT'] = $total;

			$nvpStr = nvp($nvp);

			$resp = PPHttpPost($method, $nvpStr, $cred, $env);
			if (isset($resp["ACK"]) && $resp["ACK"] == 'Success')
			{
				if (!isset($o["packageQuantities"]["totalTicketsSold"]))
				{
					$o["packageQuantities"]["totalTicketsSold"] = 0;
				}
				if (!isset($o["packageQuantities"][$_REQUEST["packageId"]]))
				{
					$o["packageQuantities"][$_REQUEST["packageId"]] = 0;
				}
				foreach ($order["items"] as $i)
				{
					for ($x = 1; $x <= $i["quantity"]; $x++)
					{
						$packageHash = md5(microtime() . $i["packageid"]);
						$package = clone $o["packageProtos"][$i["packageid"]];
						$package->setPackageId($packageHash);

						//get ticket proto from package and wipe proto from package
						$t = array_shift($package->tickets);

						for ($y = 1; $y <= $package->ticketQuantity; $y++)
						{
							//create tickets and attach them to real package
							$ticketHash = md5(microtime() . $t->ticketId);
							$ticket = clone $o["ticketProtos"][$t->ticketId];
							$ticket->setTicketid($ticketHash);
							$package->addTicket($ticket);
							add_option("ticket_" . $ticketHash, $packageHash);
							$o["packageQuantities"]["totalTicketsSold"]++;
							$tickethashes[] = $ticketHash;
						}
						$o["packageQuantities"][$i["packageid"]]++;
						update_option("eventTicketingSystem", $o);
						add_option("package_" . $packageHash, $package);
					}
				}
				echo '<div class="info">' . $o["messages"]["messageThankYou"] . '</div>';
				echo '<div class="info">';
				echo '<p>Your ticket ID(s) follow. If you have bought tickets for other people send them one of the links below so they can enter their information for the event, otherwise just click the link below to fill out your information for the event</p>';
				echo '<ul>';
				$c = 0;
				$emaillinks = "\r\n";
				foreach ($tickethashes as $hash)
				{
					$c++;
					$url = get_permalink() . '?tickethash=' . $hash;
					$href = '<a href="' . $url . '">' . $url . '</a>';
					$emaillinks .= 'Ticket ' . $c . ': ' . $url . "\r\n";
					echo '<li>Ticket ' . $c . ': ' . $href . '</li>';

				}
				echo '</ul>';
				echo '</div>';
				$to = 'To: ' . $order["name"] . ' <' . $order["email"] . '>' . "\r\n";
				$headers = 'From: ' . $o["messages"]["messageEmailFromName"] . ' <' . $o["messages"]["messageEmailFromEmail"] . '>' . "\r\n";
				$headers .= 'Bcc: ' . $o["messages"]["messageEmailBcc"] . "\r\n";
				wp_mail($to, $o["messages"]["messageEmailSubj"], $o["messages"]["messageEmailBody"] . $emaillinks, $headers);
			}
			else
			{
				echo '<div class="ticketingerror">There was an error from PayPal<br />Error: <strong>' . urldecode($resp["L_LONGMESSAGE0"]) . '</strong></div>';
			}
		}
		elseif (wp_verify_nonce($_REQUEST['couponSubmitNonce'], plugin_basename(__FILE__)) && $_REQUEST["couponCode"] && is_numeric($_REQUEST["packageId"]))
		{
			$order = get_option('coupon_' . $_REQUEST["couponCode"]);
			if (!isset($o["packageQuantities"]["totalTicketsSold"]))
			{
				$o["packageQuantities"]["totalTicketsSold"] = 0;
			}
			if (!isset($o["packageQuantities"][$_REQUEST["packageId"]]))
			{
				$o["packageQuantities"][$_REQUEST["packageId"]] = 0;
			}

			foreach ($order["items"] as $i)
			{
				for ($x = 1; $x <= $i["quantity"]; $x++)
				{
					$packageHash = md5(microtime() . $i["packageid"]);
					$package = clone $o["packageProtos"][$i["packageid"]];
					$package->setPackageId($packageHash);

					//get ticket proto from package and wipe proto from package
					$t = array_shift($package->tickets);

					for ($y = 1; $y <= $package->ticketQuantity; $y++)
					{
						//create tickets and attach them to real package
						$ticketHash = md5(microtime() . $t->ticketId);
						$ticket = clone $o["ticketProtos"][$t->ticketId];
						$ticket->setTicketid($ticketHash);
						$package->addTicket($ticket);
						add_option("ticket_" . $ticketHash, $packageHash);
						$o["packageQuantities"]["totalTicketsSold"]++;
						$tickethashes[] = $ticketHash;
					}
					$o["packageQuantities"][$i["packageid"]]++;
					update_option("eventTicketingSystem", $o);
					add_option("package_" . $packageHash, $package);
				}
			}
			echo '<div class="info">' . $o["messages"]["messageThankYou"] . '</div>';
			echo '<div class="info">';
			echo '<p>Your ticket ID(s) follow. If you have bought tickets for other people send them one of the links below so they can enter their information for the event, otherwise just click the link below to fill out your information for the event</p>';
			echo '<ul>';
			$c = 0;
			$emaillinks = "\r\n";
			foreach ($tickethashes as $hash)
			{
				$c++;
				$url = get_permalink() . '?tickethash=' . $hash;
				$href = '<a href="' . $url . '">' . $url . '</a>';
				$emaillinks .= 'Ticket ' . $c . ': ' . $url . "\r\n";
				echo '<li>Ticket ' . $c . ': ' . $href . '</li>';

			}
			echo '</ul>';
			echo '</div>';
			$to = 'To: ' . $order["name"] . ' <' . $order["email"] . '>' . "\r\n";
			$headers = 'From: ' . $o["messages"]["messageEmailFromName"] . ' <' . $o["messages"]["messageEmailFromEmail"] . '>' . "\r\n";
			$headers .= 'Bcc: ' . $o["messages"]["messageEmailBcc"] . "\r\n";
			wp_mail($to, $o["messages"]["messageEmailSubj"], $o["messages"]["messageEmailBody"] . $emaillinks, $headers);

			$o["coupons"][$_REQUEST["couponCode"]]["used"] = true;
			update_option('eventTicketingSystem', $o);
		}
		elseif (isset($_REQUEST["tickethash"]) && strlen($_REQUEST["tickethash"]) == 32)
		{
			//ticket form recieved
			if (wp_verify_nonce($_POST['ticketInformationNonce'], plugin_basename(__FILE__)))
			{
				$_REQUEST = array_map('stripslashes_deep', $_REQUEST);
				//echo '<pre>'.print_r($_REQUEST,true).'</pre>';
				$ticketHash = $_REQUEST["tickethash"];
				$packageHash = $_REQUEST["packagehash"];
				$package = get_option('package_' . $packageHash);
				if ($package instanceof package)
				{
					foreach ($_REQUEST["ticketOption"] as $oid => $oval)
					{
						$package->tickets[$ticketHash]->ticketOptions[$oid]->value = $oval;
					}
					//echo '<pre>'.print_r($package->tickets,true).'</pre>';
					$package->tickets[$ticketHash]->final = true;
					update_option('package_' . $packageHash, $package);

					echo '<div>Your ticket has been saved</div>';
				}
				//save ticket info
				//mark as final?
				//say see you soon
			}

			//pull ticketinfo
			//display ticket form (filling in if this is already been finished)
			$ticketHash = $_REQUEST["tickethash"];
			$packageHash = get_option('ticket_' . $ticketHash);
			$package = get_option('package_' . $packageHash);
			if ($package instanceof package)
			{
				$ticket = $package->tickets[$ticketHash];

				//echo '<pre>'.print_r($ticket,true).'</pre>';
				echo '<form name="ticketInformation" method="post" action="">';
				echo '<table>';
				echo '<input type="hidden" name="ticketInformationNonce" id="ticketInformationNonce" value="' . wp_create_nonce(plugin_basename(__FILE__)) . '" />';
				echo '<input type="hidden" name ="tickethash" value="' . $ticketHash . '" />';
				echo '<input type="hidden" name ="packagehash" value="' . $packageHash . '" />';
				foreach ($ticket->ticketOptions as $option)
				{
					echo '<tr><td>' . $option->displayName . ':</td><td>' . $option->displayForm() . '</tr>';
				}
				echo '<tr><td colspan="2"><input type="submit" name="submitbutt" value="Save Ticket Information"></td></tr>';
				echo '</table>';
				echo '</form>';
			}
			else
			{
				echo  '<div class="ticketingerror">Your tickethash appears to be incorrect. Please check your link and try again</div>';
			}
		}
		else
		{
			//This will catch any errors thrown in the paypal() method.
			//Have to use session because paypal() has to happen quite early to allow for the paypal redirect
			if (strlen($_SESSION["ticketingError"]))
			{
				echo '<div class="ticketingerror">' . $_SESSION["ticketingError"] . '</div>';
				unset($_SESSION["ticketingError"]);
			}
			echo '<form action="" method="post">';
			echo '<input type="hidden" name="packagePurchaseNonce" id="packagePurchaseNonce" value="' . wp_create_nonce(plugin_basename(__FILE__)) . '" />';
			echo '<div>Please enter a name and email address for your confirmation and tickets</div>';
			echo '<div>Name: <input name="packagePurchaseName" size="25" value="' . $_REQUEST["packagePurchaseName"] . '"> Email: <input name="packagePurchaseEmail" size="25" value="' . $_REQUEST["packagePurchaseEmail"] . '"></div>';
			echo '<div id="packages">';
			echo '<table>';
			echo '<tr><th>Quantitiy</th><th>Price</th>';
			if ($o["displayPackageQuantity"])
			{
				echo '<th>Quantity Remaining</th>';
			}
			echo '<th>Description</th></tr>';
			foreach ($o["packageProtos"] as $k => $v)
			{
				//determine remaining tickets so we don't display selectors that allow too many tickets to be sold
				//overall attendance max takes precendece over individual package quantity limitation
				$totalRemaining = $o["eventAttendance"] - $o["packageQuantities"]["totalTicketsSold"];
				if ($v->packageQuantity)
				{
					$packageRemaining = $v->packageQuantity - $o["packageQuantities"][$v->packageId];
					$packageCounter = ($packageRemaining * $v->ticketQuantity) < $totalRemaining ? $packageRemaining : floor($totalRemaining / $v->ticketQuantity);
					$packageCounter = $packageCounter > 10 ? 10 : $packageCounter;
				}
				else
				{
					$packageRemaining = floor($totalRemaining / $v->ticketQuantity);
					$packageCounter = $packageRemaining > 10 ? 10 : $packageRemaining;
				}

				if ($packageCounter > 0 && $v->validDates())
				{
					echo '<tr>';
					echo '<td><select name="packagePurchase[' . $v->packageId . ']">';
					for ($i = 0; $i <= $packageCounter; $i++)
					{
						echo '<option>' . $i . '</option>';
					}
					echo '</select></td>';
					echo '<td>$' . number_format($v->price, "2") . '</td>';
					if ($o["displayPackageQuantity"])
					{
						echo '<td>' . $packageRemaining . ' left</td>';
					}
					echo '<td><div class="packagename">' . $v->packageName . '</div><div class="packagedescription">' . $v->packageDescription . '</div></td>';
					echo '</tr>';
				}
			}
			echo '<tr><td>Coupon Code</td><td colspan="' . ($o["displayPackageQuantity"] == 1 ? "3" : "2") . '"><input class="input" name="couponCode"><input type="submit" name="couponSubmitButton" value="Apply Coupon"></td></tr>';
			echo '<tr><td colspan="2"><input type="image" src="https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif"></td><td id="purchaseinfo" colspan="' . ($o["displayPackageQuantity"] == 1 ? "2" : "1") . '">Chose your tickets and pay for them at PayPal. You will fill in your ticket information after your purchase is completed</td></tr>';
			echo '</table>';
			echo '</div>';
			echo '</form>';
		}
		return (ob_get_clean());
	}

	function paypal()
	{
		$o = get_option("eventTicketingSystem");
		//check order and build for later retrieval
		if (wp_verify_nonce($_POST['packagePurchaseNonce'], plugin_basename(__FILE__)))
		{
			if (!check_email_address($_REQUEST["packagePurchaseEmail"]))
			{
				$_SESSION["ticketingError"] = 'Please enter a name and email address';
				return (false);
			}
			if (strlen($_REQUEST["couponSubmitButton"]))
			{
				if (is_array($o["coupons"][$_REQUEST["couponCode"]]) && $o["coupons"][$_REQUEST["couponCode"]]["used"] == false && is_numeric($o["coupons"][$_REQUEST["couponCode"]]["packageId"]))
				{
					$packageId = $o["coupons"][$_REQUEST["couponCode"]]["packageId"];
					$couponCode = $_REQUEST["couponCode"];
					$couponSubmitNonce = wp_create_nonce(plugin_basename(__FILE__));
					$item[] = array("quantity" => 1,
					                "name" => $o["packageProtos"][$packageId]->displayName(),
					                "desc" => $o["packageProtos"][$packageId]->packageDescription,
					                "price" => 0,
					                "packageid" => $packageId);
					add_option('coupon_' . $couponCode, array("items" => $item, "email" => $_REQUEST["packagePurchaseEmail"], "name" => $_REQUEST["packagePurchaseName"]));
					header('Location: ' . get_permalink() . '?packageId=' . $packageId . '&couponCode=' . $couponCode . '&couponSubmitNonce=' . $couponSubmitNonce);
					exit;
				}
				else
				{
					$_SESSION["ticketingError"] = 'Invalid Coupon Entered';
					return (false);
				}
			}

			$somethingpurchased = $total = 0;
			foreach ($_REQUEST["packagePurchase"] as $packageId => $quantity)
			{
				if ($quantity > 0)
				{
					$somethingpurchased = 1;
					$total += $o["packageProtos"][$packageId]->price * $quantity;
					$item[] = array("quantity" => $quantity,
					                "name" => $o["packageProtos"][$packageId]->displayName(),
					                "desc" => $o["packageProtos"][$packageId]->packageDescription,
					                "price" => $o["packageProtos"][$packageId]->price,
					                "packageid" => $packageId
					);
				}
			}

			//was something purchased?
			if ($somethingpurchased == 0)
			{
				$_SESSION["ticketingError"] = 'You did not chose a quantity on any of the tickets. Please chose how many tickets you want';
			}
			else
			{
				//check to see if value is $0 due to a discount code or something
				//do something here

				//looks like we got a submit with some values, let's redirect to paypal
				include(WP_PLUGIN_DIR . '/' . plugin_basename(dirname(__FILE__)) . '/lib/nvp.php');
				include(WP_PLUGIN_DIR . '/' . plugin_basename(dirname(__FILE__)) . '/lib/paypal.php');
				$returnsite = get_permalink();
				$total = number_format($total, 2);
				$p = $o["paypalInfo"];

				if (is_array($item))
				{
					$method = "SetExpressCheckout";
					$cred = array("apiuser" => $p["paypalAPIUser"], "apipwd" => $p["paypalAPIPwd"], "apisig" => $p["paypalAPISig"]);
					$env = $p["paypalEnv"];
					$nvp = array('PAYMENTREQUEST_0_AMT' => $total,
					             "RETURNURL" => $returnsite,
					             "CANCELURL" => $returnsite,
					             "PAYMENTREQUEST_0_PAYMENTACTION" => 'Sale',
					             "PAYMENTREQUEST_0_CURRENCYCODE" => 'USD',
					);
					foreach ($item as $k => $i)
					{
						//$nvp['L_PAYMENTREQUEST_0_NAME' . $k] = $i["name"];
						$nvp['L_PAYMENTREQUEST_0_NAME' . $k] = $o["messages"]["messageEventName"] . ": Registration";
						$nvp['L_PAYMENTREQUEST_0_DESC' . $k] = $i["desc"];
						$nvp['L_PAYMENTREQUEST_0_AMT' . $k] = $i["price"];
						$nvp['L_PAYMENTREQUEST_0_QTY' . $k] = $i["quantity"];
					}
					$nvp['PAYMENTREQUEST_0_ITEMAMT'] = $total;

					$nvpStr = nvp($nvp);

					//echo '<pre>'.print_r($nvp,true).'</pre>';

					$resp = PPHttpPost($method, $nvpStr, $cred, $env);
					//echo '<pre>'.print_r($httpParsedResponseAr,true).'</pre>';
					if (isset($resp["ACK"]) && $resp["ACK"] == 'Success')
					{
						//store some vals and redirect
						//echo '<pre>'.print_r($resp,true).'</pre>';
						$token = urldecode($resp["TOKEN"]);
						add_option('paypal_' . $token, array("total" => $total, "items" => $item, "paid" => false, "email" => $_REQUEST["packagePurchaseEmail"], "name" => $_REQUEST["packagePurchaseName"]));
						if ("sandbox" === $env || "beta-sandbox" === $env)
						{
							$payPalURL = "https://www.$env.paypal.com/webscr&cmd=_express-checkout&token=$token";
						}
						else
						{
							$payPalURL = "https://www.paypal.com/webscr&cmd=_express-checkout&token=$token";
						}
						header("Location: $payPalURL");
						exit;
					}
					else
					{
						$_SESSION["ticketingError"] = 'There was an error from PayPal<br />Error: <strong>' . urldecode($resp["L_LONGMESSAGE0"]) . '</strong>';
					}
				}
				else
				{
					$_SESSION["ticketingError"] = 'No items were found. Please go back and chose how many tickets you want';
				}
			}
		}
	}
}

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

class ticket
{
	public $ticketName;
	public $ticketOptions;
	public $ticketId;
	public $final;

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
		return ($this->ticketName == '' ? 'Unamed' : $this->ticketName);
	}

	public function displayFull()
	{
		echo '<div id="eventTicket">';
		echo '<div class="ticketName">' . ($this->ticketName == '' ? 'Unamed' : $this->ticketName) . '</div>';
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
}

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

	function __construct($tickets = array())
	{
		$this->tickets = $tickets;
	}

	public function displayForm()
	{
		echo '<div id="eventPackage" class="wrap">';
		echo '<h2>New Package</h2>';
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
		echo '<div>$<input type="text" name="packagePrice" value="' . $this->price . '" size="5"></div>';
		//echo '</div>';
		echo '<h2>Package Quantity</h2>';
		echo '<div>Quantity: <input type="text" name="packageQuantity" value="' . $this->packageQuantity . '" size="3"><br /><p style="font-style: italic;">How many of this package to sell? Leave blank for no limit</p></div>';
		echo '</div>';
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
		return ($this->packageName == '' ? 'Unamed' : $this->packageName);
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
	}

	public function validDates()
	{
		if (time() > strtotime($this->expireStart) && time() < strtotime($this->expireEnd))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}

function check_email_address($email)
{
	// First, we check that there's one @ symbol, and that the lengths are right
	if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email))
	{
		// Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
		return false;
	}
	// Split it into sections to make life easier
	$email_array = explode("@", $email);
	$local_array = explode(".", $email_array[0]);
	for ($i = 0; $i < sizeof($local_array); $i++)
	{
		if (!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i]))
		{
			return false;
		}
	}
	if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1]))
	{ // Check if domain is IP. If not, it should be valid domain name
		$domain_array = explode(".", $email_array[1]);
		if (sizeof($domain_array) < 2)
		{
			return false; // Not enough parts to domain
		}
		for ($i = 0; $i < sizeof($domain_array); $i++)
		{
			if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i]))
			{
				return false;
			}
		}
	}
	return true;
}

?>
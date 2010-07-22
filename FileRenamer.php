<?php

if (!@$GLOBALS['framework']) {
	$window = new FileShredder();
	Gtk::main();
}
else {
	echo 'Error in loading the required files.';
}

class FileShredder extends GtkWindow {

	private $menu, $file_label, $selected_file, $file_label_default;
	
	// Create the main window
	function __construct($parent = null) {
		parent::__construct();
		$this->selected_file = "";
		$this->file_label_default = "Open a file to delete	(Ctrl+O)";
		$this->set_size_request(400, 60);
		$this->connect_simple('destroy', array('gtk', 'main_quit'));
		$this->set_title(__CLASS__);
		$this->set_position(Gtk::WIN_POS_CENTER);
		$this->add($vbox = new GtkVBox());
		$accel_group = new GtkAccelGroup();
		$this->add_accel_group($accel_group);
		$menu_definition = array('_File' => array('_Open|O', '<hr>', 'E_xit'),'_Help' => array('_About|H'));		
		// define menu definition
		$this->menu = new Menu($vbox, $menu_definition, $accel_group);

		// display title
		$button = new GtkButton('_Delete File');
		$button->connect('clicked', array($this, 'on_delete_file'));

		$this->file_label = new GtkLabel($this->file_label_default);
		$vbox->pack_start($hbox = new GtkHBox());
		$hbox->pack_start($this->file_label);
		$hbox->pack_start($button, false, false, 0);

		$this->show_all();
	}

	// Update the label to selected file.
	function file_choosen($text) {
		$this->selected_file = $text;
		$this->file_label->set_text($text);
	}
	
	// Actual delete functionality
	function delete_file() {
		$data_len = filesize($this->selected_file);
		$fp = fopen($this->selected_file, "wb");
		$str = 'This file was deleted using File Shredder. :) Visit:  http://github.com/nayanshah/FileShredder/\n' . $data_len;
		$binary_null = pack("x");
		for($i=$data_len; $i>0; $i--)
			$str .= $binary_null;
		fwrite($fp, $str);
		fclose($fp);
		unlink($this->selected_file);
		return true;
	}
	
	// Event handler for Delete button 
	function on_delete_file($button) {
		if($this->selected_file == "") {
			$this->on_no_file_selected($button);
		}
		else {
			$this->on_delete_confirm($button);
		}
	}
	
	// Delete confirmation dialog.
	function on_delete_confirm() {
		$dialog = new GtkDialog('CAUTION!', $this, 0, array( Gtk::STOCK_OK, Gtk::RESPONSE_OK, Gtk::STOCK_CANCEL, Gtk::RESPONSE_CANCEL));

		$hbox = new GtkHBox(false, 8);
		$hbox->set_border_width(8);
		$stock = GtkImage::new_from_stock(
		Gtk::STOCK_DIALOG_QUESTION,
		Gtk::ICON_SIZE_DIALOG);
		$confirm_text1 = new GtkLabel('Are you sure you want to proceed ?');
		$confirm_text2 = new GtkLabel('Note: This process is irreversible.');
		$confirm_text3 = new GtkLabel($this->selected_file);
		$confirm_text3->modify_fg(Gtk::STATE_NORMAL, GdkColor::parse("#ff0000"));
		$vbox2 = new GtkVBox();
		
		$vbox2->pack_start($confirm_text1);
		$vbox2->pack_start($confirm_text2);
		$vbox2->pack_start($confirm_text3);
		$hbox->pack_start($stock, false, false, 0);
		$hbox->pack_start($vbox2, false, false, 0);
		$dialog->vbox->pack_start($hbox, false, false, 0);
		
		$dialog->show_all();
		$response = $dialog->run();

		if ($response == Gtk::RESPONSE_OK) {
			$resp = $this->delete_file();
			if($resp) {
				$this->on_delete_successful();
				$this->selected_file = "";
				$this->file_label->set_text($this->file_label_default);
			}
			else {
				$this->selected_file = "";
				$this->file_label->set_text('Unable to delete the specified file.');
			}
		}
		$dialog->destroy();
	}
	
	function on_no_file_selected() {
		$dialog = new GtkMessageDialog($this, Gtk::DIALOG_DESTROY_WITH_PARENT,
		Gtk::MESSAGE_INFO, Gtk::BUTTONS_OK, 'No file selected. Please click Open from the File menu or Ctrl+O to open a file.');
		$dialog->run();
		$dialog->destroy();
	}

	function on_delete_successful() {
		$dialog = new GtkMessageDialog($this, Gtk::DIALOG_DESTROY_WITH_PARENT,
		Gtk::MESSAGE_INFO, Gtk::BUTTONS_OK, 'The file has been permanently deleted.
Its data cannot be recovered by any means.');
		$dialog->run();
		$dialog->destroy();
	}

	function about_dialog() {
		$message = '
		File Shredder v0.1
			
1. Click Open to select a file to delete.
2. Click the Delete button to PERMANENTLY delete the file.

Copyright 2010.  Nayan Shah (nayanmange@gmail.com) ';
		$dialog = new GtkMessageDialog($this, Gtk::DIALOG_DESTROY_WITH_PARENT,
		Gtk::MESSAGE_INFO, Gtk::BUTTONS_OK, $message);
		$dialog->run();
		$dialog->destroy();
	}

}

// class Menu
class Menu {
	var $prev_keyval = 0;
	var $prev_state = 0;
	var $prev_keypress = '';

	// let user choose a file with a file chooser dialog
	public static function _Open() { // note 1
		global $window;
		$dialog = new GtkFileChooserDialog("File Open", null, 
		Gtk::FILE_CHOOSER_ACTION_OPEN, 
		array(Gtk::STOCK_OK, Gtk::RESPONSE_OK, Gtk::STOCK_CANCEL, Gtk::RESPONSE_CANCEL), null); // note 2
		$dialog->show_all();
		if ($dialog->run() == Gtk::RESPONSE_OK) {
			$selected_file = $dialog->get_filename(); // note 3
			echo '';
			$window->file_choosen($selected_file);
		}
		$dialog->destroy();
	}
	
	public static function _About() {
		global $window;
		$window->about_dialog();
	}

	function Menu($vbox, $menu_definition, $accel_group) {
		$this->menu_definition = $menu_definition;
		$menubar = new GtkMenuBar();
		$vbox->pack_start($menubar, 0, 0);
		foreach($menu_definition as $toplevel => $sublevels) {
			$top_menu = new GtkMenuItem($toplevel);
			$menubar->append($top_menu);
			$menu = new GtkMenu();
			$top_menu->set_submenu($menu);

			// let's ask php-gtk to tell us when user press the 2nd Alt key
			$menu->connect('key-press-event', array(&$this, 'on_menu_keypress'),
			$toplevel);
			//*
			foreach($sublevels as $submenu) {
				if (strpos("$submenu", '|') === false) {
					$accel_key = '';
				} else {
					list($submenu, $accel_key) = explode('|', $submenu);
				}
				if ($submenu=='<hr>') {
					$menu->append(new GtkSeparatorMenuItem());
				} 
				else {
					$submenu2 = str_replace('_', '', $submenu);
					$submenu2 = str_replace(' ', '_', $submenu2);
					$stock_image_name = 'Gtk::STOCK_'.strtoupper($submenu2);
					if (defined($stock_image_name)) {
						$menu_item = new GtkImageMenuItem(
						constant($stock_image_name));
					} else {
						$menu_item = new GtkMenuItem($submenu);
					}
					if ($accel_key!='') {
						$menu_item->add_accelerator("activate",
						$accel_group, ord($accel_key), Gdk::CONTROL_MASK, 1);
					}

					$menu->append($menu_item);
					$menu_item->connect('activate',
					array(&$this, 'on_menu_select'));
					$this->menuitem[$toplevel][$submenu] = $menu_item;
				}
				//                }
				
			}
			// */			
		}
	}

	// process menu item selection
	function on_menu_select($menu_item) {
		$item = $menu_item->child->get_label();
		if (method_exists($this, $item)) $this->$item();
		if ($item=='E_xit') Gtk::main_quit();
	}

	// processing of menu keypress
	function on_menu_keypress($menu, $event, $toplevel) {
		if (!$event->state & Gdk::MOD1_MASK) return false;
		// get the ascii equivalent of the keypress
		$keypress = '';
		if ($event->keyval<255) {
			$keypress = chr($event->keyval); // ascii equivalent
			$keypress = strtolower($keypress); // convert to lowercase
		}
		$match = 0; // flag to see if there's a match
		foreach($this->menu_definition[$toplevel] as $submenu) {
			if (!preg_match("/.*_([a-zA-Z0-9]).*/", "$submenu", $matches))
			continue;
			$key2 = strtolower($matches[1]);
			if ($keypress==$key2) {
				if (strpos("$submenu", '|') === false) {
					$accel_key = '';
				} else {
					list($submenu, $accel_key) = explode('|', $submenu);
				}
				$menuitem = $this->menuitem[$toplevel][$submenu];
				$menuitem->activate();
				$menu->popdown();
				$match = 1;
				break;
			}
		}
		return $match;
	}
}

?>
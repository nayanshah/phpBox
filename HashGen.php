#!C:/xampp/php/
<?php

if (!@$GLOBALS['framework'] && class_exists('HashGen')) {
	$window = new HashGen();
	Gtk::main();
}
else {
	$arg = count($argv);
	if($arg<2 || $arg>3) {
		echo 'Usage :
php HashGen.php file/string [hash_name]
php HashGen.php hello MD5\n';
	}
	else {
		$isFile = file_exists($argv[1]);
		if($arg==2) {
		$result = '
MD5 : '.calculate_hash($argv[1], 'MD5', $isFile).'
SHA1 : '.calculate_hash($argv[1], 'SHA1', $isFile).'
';
		} 
		else {
		$result = '
'.strtoupper($argv[2]).' : '.calculate_hash($argv[1], strtoupper($argv[2]), $isFile).'
';
		} 
	}
	echo $result;
	exit;
}

function calculate_hash($string, $hash = 'MD5', $isFile = false) {
	$str = '';
	if($isFile) {
		switch($hash) {
			case 'SHA1' :
				$str = sha1_file($string);
				break;
			case 'MD5' :
			default :
				$str = md5_file($string);
				break;
		}
	}
	else {
		switch($hash) {
			case 'SHA1' :
				$str = sha1($string);
				break;
			case 'MD5' :
			default :
				$str = md5($string);
				break;
		}		
	}
	return $str;
}

class HashGen extends GtkWindow {

	private $menu, $hash_label, $selected_file, $hash_label_default;
	
	// Create the main window
	function __construct($parent = null) {
		parent::__construct();
		$this->selected_file = "";
		$this->hash_label_default = "Open a file (Ctrl+O)  OR  Type a sentence (Ctrl+T)";
		$this->set_size_request(400, 60);
		$this->connect_simple('destroy', array('gtk', 'main_quit'));
		$this->set_title(__CLASS__);
		$this->set_position(Gtk::WIN_POS_CENTER);
		$this->add($vbox = new GtkVBox());
		$accel_group = new GtkAccelGroup();
		$this->add_accel_group($accel_group);
		$menu_definition = array('_File' => array('_Open|O', '_Type|T', '<hr>', 'E_xit'),'_Help' => array('_About|H'));		
		// define menu definition
		$this->menu = new Menu($vbox, $menu_definition, $accel_group);

		// display title
		$button = new GtkButton('_Get Hash');
		$button->connect('clicked', array($this, 'calculate'));

		$this->hash_label = new GtkLabel($this->hash_label_default);
		$vbox->pack_start($hbox = new GtkHBox());
		$hbox->pack_start($this->hash_label);
		$hbox->pack_start($button, false, false, 0);

		$this->show_all();
	}

	// Update the label to selected file.
	function file_choosen($text) {
		$this->selected_file = $text;
		$this->hash_label->set_text($text);
	}
	
	// Event handlers for the two buttons
	function calculate($button) {
		if($this->selected_file == "") {
			$this->on_no_input($button);
			return false;
		}
		$isFile = file_exists($this->selected_file);
		$md5 = calculate_hash($this->selected_file, 'MD5', $isFile);
		$sha1 = calculate_hash($this->selected_file, 'SHA1', $isFile);
		if($isFile) {
			$result = 'Hash values of given file:
MD5 : '.$md5.'
SHA1 : '.$sha1;
		}
		else {
			$result = 'Hash values of given string:
MD5 : '.$md5.'
SHA1 : '.$sha1;
		}
		$this->display_hash($result);
		return true;
	}

    function enter_text() {
        $dialog = new GtkDialog('Enter text', $this, 0, array( Gtk::STOCK_OK, Gtk::RESPONSE_OK, Gtk::STOCK_CANCEL, Gtk::RESPONSE_CANCEL));
		$hbox = new GtkHBox(false);
		$dialog->vbox->pack_start($hbox, false, false, 0);
        $label = new GtkLabel('String :');
        $entry = new GtkEntry();
        $hbox->pack_start($label);
        $hbox->pack_start($entry);
        $dialog->show_all();
        $response = $dialog->run();
        if ($response == Gtk::RESPONSE_OK) {
            $this->hash_label->set_text($entry->get_text());
            $this->selected_file=$entry->get_text();
        }
        $dialog->destroy();
    }
	
	function on_no_input() {
		$dialog = new GtkMessageDialog($this, Gtk::DIALOG_DESTROY_WITH_PARENT,
		Gtk::MESSAGE_INFO, Gtk::BUTTONS_OK, 'No input given!!
To open a file (Ctrl+O)  OR  to type in a sentence (Ctrl+T)');
		$dialog->run();
		$dialog->destroy();
	}

	function display_hash($result) {
		$dialog = new GtkMessageDialog($this, Gtk::DIALOG_DESTROY_WITH_PARENT,
		Gtk::MESSAGE_INFO, Gtk::BUTTONS_OK, $result);
		$dialog->run();
		$dialog->destroy();
	}

	function about_dialog() {
		$message = '
		HashGen v0.1
			
1. To select a file click Open.
2. To enter a string click Type.
3. Click Hash to get the hashes.

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
	
	public static function _Type() {
		global $window;
		$window->enter_text();	
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
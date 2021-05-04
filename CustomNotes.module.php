<?php namespace ProcessWire;

class CustomNotes extends Process implements Module, ConfigurableModule {

    public static function getModuleinfo() {
    return [
        'title' => 'CustomNotes',
        'summary' => 'Put your custom notes into an admin page, with some settings to show it.',
        'author' => 'Mauro Romano, NewMediaConsulting',
        'version' => 100,
        'permission' => 'view-notes',
        'autoload' => 'template=admin',
        'singular' => true
    ];
    }

    public function init() {}

    public function ready() {
    $this->addHookAfter("ProcessPageEdit::buildForm", function($event) {
    $form = $event->return;
    $page = $event->process->getPage();

    if ($this->enable_module == 0){
        if ($this->button){

        if(!in_array($page->template->id, $this->tpls_sticky)) return;
        if(!in_array($page->id, $this->pages_sticky)) return;

        $divPos = $this->v_position.':'.$this->ver.'%';
        $aPos = $this->h_position.':'.$this->hor.'%';
        $button  = '<div class="container" style="position:sticky;'.$divPos.';z-index:10;">';
        $button .= '<a title="'.$this->linkLabel().'" href="'.wire('urls')->admin.'page/custom-notes/" class="sticky-button ui-button ui-state-default '.$this->view_mode_sticky.'" style="position:absolute;'.$aPos.';">';
        $button .= '<i class="fa fa-list"></i> '. $this->linkLabel();
        $button .= '</a>';
        $button .= '</div>';
        $form->prependMarkup($button);
        $event->return = $form;
        }
    }  
    });

    $this->addHookAfter("ProcessPageEdit::buildForm", function($event) {
    $form = $event->return;
    $page = $event->process->getPage();

    if ($this->enable_module == 0){
        if ($this->note){

            if(!in_array($page->template->id, $this->tpls_note)) return;
            if(!in_array($page->id, $this->pages_note)) return;

        // @BernhardBaumrock Tip: Adding link into field note. Where it all begins! ;)
        foreach ($this->fields_note as $field_note){
            if($f = $form->get($field_note)) {
                $f->notes  = '';
                if (!trim($this->custom_link)){
                $f->notes .= " <a href='".wire('urls')->admin."page/custom-notes/' class='".$this->view_mode_note."'>".$this->linkLabel()."</a>";
                } else {
                $f->notes .= " <a href='".wire('urls')->admin."page/custom-notes/' class='".$this->view_mode_note."'>".$this->linkLabel()."</a>";
                }
                $f->entityEncodeText = false;
            }
            }
        }
    }    
    });

    }

    public function linkLabel() {
        // get user language to determine which language to output 
        if($this->wire('languages')) {
            $userLanguage = $this->wire('user')->language;
            $langIdSuffix = $userLanguage->isDefault() ? '' : "__$userLanguage->id";
        } else {
            $langIdSuffix = '';
        }
    
    $linkLabel = $this->get('custom_link'.$langIdSuffix);

    return $linkLabel;

    }

	/*
        Called only when the module is installed
        Creates a new page and a new permission. 
	*/
	public function ___install() {

        $tp = new Page();
            $tp->template = 'admin';
            $tp->parent = 'page';
        // $tp->parent = $this->page_parent;
        $tp->name = 'custom-notes';
        $tp->process = $this;
        // we will make the page title the same as our module title
        $info = self::getModuleInfo();
        $tp->title = $info['title'];
        $tp->save();
        $this->message("Created Page: {$tp->path}");
        
        // Set module permission
        if (count(wire('pages')->find('name=view-notes, parent.name=permissions')) < 1) {
        $permission = wire('permissions')->add('view-notes');
        $permission->title = 'View Custom Notes Page';
        $permission->save();
        $this->message("Created Permission: {$permission->name}");
        }

        //save default module configurations on install
        wire('modules')->saveModuleConfigData($this, self::getDefaultData());

	}

  	/* 
	    Called only when the module is uninstalled
	    Delete the created page and permission. 
	*/
	public function ___uninstall() {
    // find the page we installed, locating it by the process field (which has the module ID)
    // it would probably be sufficient just to locate by name, but this is just to be extra sure.
    $moduleID = $this->modules->getModuleID($this); 
    $pages = $this->pages->find("template=admin, process=".$moduleID); 
    if(count($pages)) {
        foreach ($pages as $page){
                // if we found the page, let the user know and delete it
                $this->message("Deleting Page: {$page->path}"); 
                $page->delete();
        }
    }
    // Let user know that we are deleting Permission
    $permission = wire('permissions')->get('view-notes');
		$this->message("Deleting Permission: {$permission->name}"); 
    $permission->delete();
	}


    public function ___execute() {
    // get user language to determine which language to output 
    if($this->wire('languages')) {
        $userLanguage = $this->wire('user')->language;
        $langIdSuffix = $userLanguage->isDefault() ? '' : "__$userLanguage->id";
    } else {
        $langIdSuffix = '';
    }

    // Custom Text
    $fieldML = 'custom_text'.$langIdSuffix;
    $out = $this->$fieldML; 

    // Textformatter Usage
    if (!is_array(wire('modules')->get('CustomNotes')->textformatters)) wire('modules')->get('CustomNotes')->textformatters = array(wire('modules')->get('CustomNotes')->textformatters);
    foreach (wire('modules')->get('CustomNotes')->textformatters as $tf){
        $tFM = wire('modules')->getModuleInfo($tf);
        $mName = $tFM['name'];
        wire('modules')->$mName->format($out);
    }

    // Edit Button to module (only for Superusers)
    if (wire('user')->isSuperuser()){
        if ($this->editing == 1){
        $out .= '<div class="g-py-25"><a title="Open new page to edit this notes" target="_blank" class="ui-button ui-state-default" href="'.wire('urls')->admin.'module/edit?name='.$this.'&collapse_info=1">Edit</a></div>';
        }
    }

    // Custom Stylesheet load
    if (trim($this->css_file)){
        $out .= '<link rel="stylesheet" type="text/css" href="'.$this->css_file.'" />';
    }    

    // Custom Inline JS
    if (($this->custom_js)&&($this->notstupid)){
    $out .= '<script>'.$this->custom_js.'</script>';
    }

    return $out;

    } 

    public function __construct() {
        $this->wire('config')->scripts->add(wire('urls')->$this.'CustomNotes.js');
    }

}

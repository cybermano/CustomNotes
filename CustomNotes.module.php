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


    // Sets Default data 
    public static function getDefaultData() {	

        $content = '<h1>Welcome to CustomNotes.</h1><p>Here you can put everything you want. If installed, you can alsp choose a textformatters.</p><ul><li>First of all, set the main content for your notes.</li><li>Then go to Setting Tab e start to play! ;)</li></ul><p>Here some example stuff</p><table border="0" cellpadding="5" cellspacing="0" class="single-row table table-striped" style="width:100%"><tbody><tr><td>PW Forums</td><td><a class="g-text-underline--none--hover" href="https://processwire.com/talk/" rel="nofollow noreferrer noopener" target="_blank">https://processwire.com/talk/</a></td></tr><tr><td>PW Cheatsheet</td><td><a class="g-text-underline--none--hover" href="https://cheatsheet.processwire.com/" rel="nofollow noreferrer noopener" target="_blank">https://cheatsheet.processwire.com/</a></td></tr></tbody></table><p>https://www.youtube.com/watch?v=IHqnLQy9R1A&amp;t=1s</p>';

        return array(
                'custom_text' => $content,
                'custom_link' => 'Link',
                'v_position' => 'top',
                'ver' => 90,
                'h_position' => 'right',
                'hor' => 5,
        );
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


    public static function getModuleConfigInputfields(array $data) { 
    // JQuery Tab loading
    wire('modules')->get('JqueryWireTabs');
    $thisModule = wire('modules')->get('CustomNotes');
    $thisPage = wire('pages')->get('name=custom-notes');
    
    if (wire('modules')->get('CustomNotes')->enable_module == 0) { 
        $thisPage->status(1);
        $thisPage->save();
    } else {
        $thisPage->status(2049);
        $thisPage->save();
    }

    $inputfields = new InputfieldWrapper();

    // TAB CONTENT
    $tab = new InputfieldWrapper();
        $tab->attr('title', 'Content');
        $tab->attr('class', 'CN-WireTab');

        $markup = $thisModule->modules->get('InputfieldMarkup');
        $tab->add($markup);

        // Custom Text
        $field = wire('modules')->get('InputfieldCKEditor');
        $field->name = 'custom_text';
        $field->label = __("Custom Text");
        $field->useLanguages = true;
        $field->rows = 12;	
        if(isset($data['custom_text'])) $field->value = $data['custom_text'];
        $tab->add($field); 

        // TextFormatters
        $field = wire('modules')->get('InputfieldAsmSelect');
        $field->name = 'textformatters';
        $field->icon = 'font';
        $field->label = __("Text Formatters");
        $field->description = __(" If you select more than one, drag them into the order they should be applied.");
        $field->notes = __("Due to the CKEditor field, only appropriate Textformatters are enabled");
        $field->columnWidth = 33;
        $textFmts = wire('modules')->find('name*=textformatter');
        foreach ($textFmts as $textF){
            $tFInfo = wire('modules')->getModuleInfo($textF);
            // Due to the CKEditor field, only appropriate Textformatters are enabled
            if(($tFInfo['name']=='TextformatterVideoEmbed')||($tFInfo['name']=='TextformatterEntities')){
                $field->addOption($tFInfo['id'],$tFInfo['title']);
            }
        }
        if(isset($data['textformatters'])) $field->value = $data['textformatters'];
        $tab->add($field); 

        // Custom link
        $field = wire('modules')->get('InputfieldText');
        $field->name = 'custom_link';
        $field->label = __("Custom text for link into Field Note");
        $field->description = __("The link will be shown as a red link into the field notes.");
        $field->placeholder = 'Link';
        $thisModule->custom_link ? $thisLink = '```['.$thisModule->custom_link.']('.wire('urls')->admin.'page/custom-notes/)```' : $thisLink = 'Default value is: ```[Link]('.wire('urls')->admin.'page/custom-notes/)```';
        $field->notes = __("$thisLink");
        $field->columnWidth = 33;
        $field->useLanguages = true;
        if(isset($data['custom_link'])) $field->value = $data['custom_link'];
        $tab->add($field);     

        // Inline Style
        $field = wire('modules')->get('InputfieldText');
        $field->name = 'css_file';
        $field->label = __("Custom CSS file to load");
        $field->columnWidth = 34;
        $field->placeholder = '/site/templates/styles/yourfile.css';
        $field->description = 'Put the url to your stylesheet in the format of **/site/templates/styles/yourfile.css**';
        if(isset($data['css_file'])) $field->value = $data['css_file'];
        $tab->add($field);    

    $inputfields->add($tab);

    // TAB SETTINGS
    $tab = new InputfieldWrapper();
        $tab->attr('title', 'Settings');
        $tab->attr('class', 'CN-WireTab');

        $markup = $thisModule->modules->get('InputfieldMarkup');
        $markup->label = 'Settings';
        $tab->add($markup);

        // ENABLE/DISABLE MODULE
        $field = wire('modules')->get('InputfieldCheckbox');
        $field->name = 'enable_module';
        $field->columnWidth = 33;
        $field->label = __("Hide Custom Notes page");
        $field->notes = __("Leave unchecked to show the page (default status).");
        $field->icon = "on";
        $field->attr('checked', empty($data['enable_module']) ? '' : 'checked');
        $tab->add($field);

        //Append Edit button
        $field = wire('modules')->get('InputfieldCheckbox');
        $field->name = 'editing';
        $field->columnWidth = 33;
        $field->label = __("Editable notes (only for Superusers)");
        $field->notes = __("An edit button will be appended at the end of the page to quickly jump into module editing.");
        $field->icon = "edit";
        $field->value = 0;
        $field->attr('checked', empty($data['editing']) ? '' : 'checked');
        $tab->add($field);

        // Permisssions
        $f = wire('modules')->get('InputfieldAsmSelect');
        $f->name = 'role_permissions';
        $f->icon = 'users';
        $f->columnWidth = 34;
        $f->label = __("Add Permission to Roles");
        $f->description = __("Select the roles to assign view permission for the page of this module");
        $f->notes = __("Superuser and Guest are automatically excluded from this select.");
        $allRoles = wire('roles')->find('sort=id, name!=guest|superuser');
        foreach($allRoles as $oneRole){
            $f->addOption($oneRole->id,$oneRole->name);
        }  
        if(isset($data['role_permissions'])) $f->value = $data['role_permissions'];
        $tab->add($f);

        // Set permission to selected roles on save and reload
        $allRoles = wire('modules')->get('CustomNotes')->role_permissions;
        if(!is_array($allRoles)) $allRoles = array();
        foreach ($allRoles as $oneRole){
            if (!in_array('view-notes', wire('roles')->get($oneRole)->permissions->getArray())){
                wire('roles')->get($oneRole)->addPermission("view-notes");
                wire('roles')->get($oneRole)->save();
                wire('modules')->get('CustomNotes')->message(wire('roles')->get($oneRole)->name.' ha permesso di tipo: view-notes');
            } else {
                wire('modules')->get('CustomNotes')->message(wire('roles')->get($oneRole)->name.' non ha ancora permesso di tipo: view-notes');
            }        
        }
        

        // Getting Global Permissions and Setted Permission by module
        $rolesWithPermission = wire('roles')->find('permissions=view-notes');
        $allRoles = wire('modules')->get('CustomNotes')->role_permissions;
        // Leave permission only to selected Roles
        foreach($rolesWithPermission as $rwp){
            if (!in_array($rwp->id, $allRoles) || (empty($allRoles)) ){
            wire('roles')->get($rwp->id)->removePermission('view-notes');
            wire('roles')->get($rwp->id)->save();
            } 
        }

        // Custom inline JS
        $field = wire('modules')->get('InputfieldTextarea');
        $field->name = 'custom_js';
        $field->label = __("Custom inline JS");
        $field->placeholder = 'Your JS code, without html tags';
        $field->notes = 'JS to append to your HTML code, setted into module.
        **N.B.** Write your js code **without** ```<script></script>``` tags.
        After saving your code, a checkbox will appear to enable/disable JS.';
        $field->collapsed = 2;
        $field->rows = 12;
        if(isset($data['custom_js'])) $field->value = $data['custom_js'];
        $tab->add($field); 

        // CHECKBOX "I'm not a stupid!"
        if(array_key_exists('custom_js', $data)){
        if (trim($data['custom_js'])){
        $field3 = wire('modules')->get('InputfieldCheckbox');
        $field3->name = 'notstupid';
        $field3->columnWidth = 100;
        $field3->label = __("**I'm not a stupid**, my JS is safe for my site!");
        $field3->icon = "exclamation";
        $field3->set("themeColor", "warning");
        $field3->set("themeOffset", "s");
        $field3->value = 0;
        $field3->attr('checked', empty($data['notstupid']) ? '' : 'checked');
        $tab->add($field3);
        }
        }

        // CHECKBOX for ENABLING/DISABILING FIELD NOTE
        $field2 = wire('modules')->get('InputfieldCheckbox');
        $field2->name = 'note';
        $field2->columnWidth = 100;
        $field2->label = __("Enable Field Note with Link");
        $field2->description = __("Create simple link into bottom field notes in the templates+fields that you'll select.");
        $field2->notes = __("This will add an **a.pw-panel** link");
        $field2->icon = "link";
        $field2->value = 0;
        $field2->attr('checked', empty($data['note']) ? '' : 'checked');
        $tab->add($field2);

        if (wire('modules')->get('CustomNotes')->note == 1){
            // View Mode Select
            $field = wire('modules')->get('InputfieldSelect');
            $field->name = 'view_mode_note';
            $field->icon = 'eye';
            $field->columnWidth = 25;
            $field->label = __("View Mode (Field Note)");
            $field->notes = __("Select how to open the link.
            Leave blank to open as a default page.");
            $field->addOption('pw-modal', 'Modal Popup');
            $field->addOption('pw-panel', 'PW Panel');
            if(isset($data['view_mode_note'])) $field->value = $data['view_mode_note'];
            $tab->add($field); 

            // template to apply FIELD NOTE LINK
            $field = wire('modules')->get('InputfieldAsmSelect');
            $field->name = 'tpls_note';
            $field->icon = 'cubes';
            $field->columnWidth = 25;
            $field->label = __("Templates to apply field note");
            $field->notes = __("Save before page select (to filter pages by selected templates).");
            $tpls = wire('templates')->find('sort=template.name');
            foreach($tpls as $tpl){
            $field->addOption($tpl->id,$tpl->name);
            }    
            if(isset($data['tpls_note'])) $field->value = $data['tpls_note'];
            $tab->add($field); 

            // pages to apply FIELD NOTE LINK
            if (array_key_exists('tpls_note', $data)){
            $field = wire('modules')->get('InputfieldAsmSelect');
            $field->name = 'pages_note';
            $field->icon = 'file';
            $field->columnWidth = 25;
            $field->label = __("Pages to apply field note");
            $field->notes = __("Save before field select.
            Published pages (```hasParent!=2```): to modify, change selectors into module.");
            $pgs = wire('pages')->find('sort=title, hasParent!=2');
            foreach($pgs as $pg){
                if (in_array($pg->template->id, wire('modules')->get('CustomNotes')->tpls_note)){
                    $field->addOption($pg->id,$pg->title);
                }    
            }
            if(isset($data['pages_note'])) $field->value = $data['pages_note'];
            $tab->add($field); 
            }

            // fields to apply FIELD NOTE LINK
            if (array_key_exists('pages_note', $data)){
            $field = wire('modules')->get('InputfieldAsmSelect');
            $field->name = 'fields_note';
            $field->icon = 'cube';
            $field->columnWidth = 25;
            $field->label = __("Field to apply Field Note");
            $flds = wire('fields')->find('sort=title');
            foreach($flds as $fld){
                foreach(wire('modules')->get('CustomNotes')->pages_note as $pg_note){
                    if (wire('pages')->get($pg_note)->hasField($fld->name)){
                        $field->addOption($fld->name);
                    }    
                }    
            }
            if(isset($data['fields_note'])) $field->value = $data['fields_note'];
            $tab->add($field); 
            }

        } // end check for field note link

        // CHECKBOX for ENABLING/DISABILING STICKY BUTTON
        $field1 = wire('modules')->get('InputfieldCheckbox');
        $field1->name = 'button';
        $field1->columnWidth = 100;
        $field1->label = __("Enable Sticky Button");
        $field1->description = __("Create a ui-button with sticky position in templates+pages that you'll select forward.");
        $field1->notes = __("This will add a **div.container > a.sticky-button** with a ```\$form->prependMarkup(\$button)``` method");
        $field1->icon = "sticky-note";
        $field1->value = 0;
        $field1->attr('checked', empty($data['button']) ? '' : 'checked');
        $tab->add($field1);


        if (wire('modules')->get('CustomNotes')->button == 1){
            // View Mode Select
            $field = wire('modules')->get('InputfieldSelect');
            $field->name = 'view_mode_sticky';
            $field->icon = 'eye';
            $field->columnWidth = 33;
            $field->label = __("View Mode (Sticky Button)");
            $field->notes = __("Select how to open the link.
            Leave blank to open as a default page.");
            $field->addOption('pw-modal', 'Modal Popup');
            $field->addOption('pw-panel', 'PW Panel');
            if(isset($data['view_mode_sticky'])) $field->value = $data['view_mode_sticky'];
            $tab->add($field); 

            // template to apply sticky button
            $field = wire('modules')->get('InputfieldAsmSelect');
            $field->name = 'tpls_sticky';
            $field->icon = 'cubes';
            $field->columnWidth = 33;
            $field->label = __("Templates to apply sticky button");
            $field->notes = __("Save before page select (to filter pages by selected templates).");
            $tpls = wire('templates')->find('sort=template.name');
            foreach($tpls as $tpl){
            $field->addOption($tpl->id,$tpl->name);
            }    
            if(isset($data['tpls_sticky'])) $field->value = $data['tpls_sticky'];
            $tab->add($field); 

            // pages to apply sticky button
            if (array_key_exists('tpls_sticky', $data)){
            $field = wire('modules')->get('InputfieldAsmSelect');
            $field->name = 'pages_sticky';
            $field->icon = 'file';
            $field->columnWidth = 34;
            $field->label = __("Pages to apply sticky button");
            $field->notes = __("Published pages (```hasParent!=2```).
            To modify that, change selectors into module config.");
            $pgs = wire('pages')->find('sort=title, hasParent!=2');
            foreach($pgs as $pg){
                if (in_array($pg->template->id, wire('modules')->get('CustomNotes')->tpls_sticky)){
                    $field->addOption($pg->id,$pg->title);
                }    
            }
            if(isset($data['pages_sticky'])) $field->value = $data['pages_sticky'];
            $tab->add($field); 
            }

        } // End check for button link

        // Position settings group
        $wrapPos = new InputfieldWrapper();
        $setPos = wire('modules')->get('InputfieldFieldset');
        $setPos->label = __('Button Position');
        $setPos->description = 'CSS rule for positions with percentage values.
        Verticlas refers to ```div.container```, horizontals refers to ```a.sticky-button```';
        $setPos->collapsed = 1;       
        $setPos->icon = "arrows";
            // Radio buttons
            $field = wire('modules')->get("InputfieldRadios");
            $field->name = "v_position";
            $field->label = __("Vertical Position");
            $field->notes = __("Suggested **top**");
            $field->columnWidth = 25;
            $field->addOption('top');
            $field->addOption('bottom');
            if (!isset($data['v_position'])) $field->value = 'Bottom';  
            if (isset($data['v_position'])) $field->value = $data['v_position'];  
            $setPos->append($field);
        $wrapPos->add($setPos);

            $perY = wire('modules')->get('InputfieldInteger');
            $perY->name = 'ver'; 
            $perY->label = 'Vertical deviation'; 
            $perY->notes = __("Suggested **90**. Don't digit ```%```.");
            $perY->icon = __('arrows-v'); 
            $perY->columnWidth = 25;
            if (isset($data['ver'])) $perY->value = $data['ver'];
            $setPos->append($perY);
        $wrapPos->add($setPos);

            $field = wire('modules')->get("InputfieldRadios");
            $field->name = "h_position";
            $field->label = __("Horizontal Position");
            $field->notes = __("Suggested **right**");
            $field->columnWidth = 25;
            $field->addOption('left');
            $field->addOption('right');
            if (!isset($data['h_position'])) $field->value = 'Right';  
            if (isset($data['h_position'])) $field->value = $data['h_position'];  
            $setPos->append($field);
        $wrapPos->add($setPos);

            $perX = wire('modules')->get('InputfieldInteger');
            $perX->name = 'hor'; 
            $perX->label = 'Horizontal deviation'; 
            $perX->notes = __("Suggested **5** Don't digit ```%```.");
            $perX->icon = __('arrows-h'); 
            $perX->columnWidth = 25;
            if (isset($data['hor'])) $perX->value = $data['hor'];
            $setPos->append($perX);
        $wrapPos->add($setPos);

        $tab->append($wrapPos);        

    $inputfields->add($tab);

    return $inputfields;

    }

}
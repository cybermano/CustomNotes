# CustomNotes
Processwire module to create a custom notes page into backend.
It works also with multilanguage.

## On Install
1. This module create a page under your admin tree with the "page" parent, with CustomNotes title;
2. Also create a new permission called **view-notes** that it will be assigned to those not superuser, if you want let them see the notes.

## Using the module
### CONTENTS
1. At starting, a CKEditor could be populated with your note. Then you can choose a textformatter of the installed ones;
2. Link label to notes is customizable;
3. A custom css could be setted.

### SETTINGS
1. Hide/show checkbox will set the page hidden or published;
2. Editable notes checkbox add a button only for superuser to quickly jump into module config;
3. Role select to add the view permission to non superuser;
4. Custom inline JS let you append script to execute (for more security, there is an additional checkbox to flag to enable it);
5. Enabling the mode to show the page: as a link into a specific field (selecting template -> page -> field), or as a sticky button in a page editing (selecting template -> page). You can choose to show as modal or as side panel (or as page if none selected). 
6. Sticky button also has its position settings.


### References
The starting point is this Processwire Forum thread: [https://processwire.com/talk/topic/25435-custom-notes-former-list-of-allergens/?tab=comments#comment-213278](https://processwire.com/talk/topic/25435-custom-notes-former-list-of-allergens/?tab=comments#comment-213278)

### Version
This is the first vrsion stable. 
Tested on some pw sites (PW 3.0.148 -> 3.0.170) and major debug on local machine (PW 3.0.165)

#### Disclaimer
This is my first "public" module, and most important my first developing work so be careful to use in prod sites: test before on developing or debugging. 

#### Download
Download the zip ad uncompress into /site/modules/ then refresh your module admin page.
If you would download the files one by one, put the module into a directory into /site/modules/ and the relative JS into a subfolder with the same name of the JS.

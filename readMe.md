# Copyright Guardian
This extension manages the copyright details of images and other media and offers a convienient way of displaying these automatically and directly on the page on which the media is used. In addition, a list of all media used and the associated copyright information can be output.

Functions in a nutshell:
* Maintain copyright information easily and centrally in the backend
* Add picture agencies as data records
* Automatic output of copyright information in the title of the media files used
* Output of a list of all media files used in a page with complete copyright information
* Media files inherited from parent pages are also taken into account
* Works with pages-table, tt_content-table and EXT:news and can be customized via TypoScript

## Installation
Just install the extension and include the typoscript.

## Configuration
You should create a system-folder in which you create data records for media sources (propably mostly agencies).
For each uploaded file you can then add a creator and select the corresponding media source using the meta-information of the file in the backend.

If you use a file with added copyright information, this information will be automatically added to the title of the file when rendered in the frontend.
This way the copyright information is always visible when hovering over the media file.

You can also add a plugin to the footer of the page which renders a complete list of all media used on the page with all relevant copyright information.
This also works for media that is inherited from a parent page.

It is also possible to use this plugin as USER-Object:
```
lib.siteDefault {

    mediaSources = USER
    mediaSources {
        userFunc = TYPO3\CMS\Extbase\Core\Bootstrap->run
        extensionName = CopyrightGuardian
        pluginName = MediaSource
        vendorName = Madj2k
        controller = MediaSource
        switchableControllerActions {
        // Again: Controller-Name and Action
            MediaSource {
                1 = importParentPage
            }
        }

        view =< plugin.copyrightguardian.view
        persistence =< plugin.copyrightguardian.persistence
        settings =< plugin.copyrightguardiansettings
    }
}

```

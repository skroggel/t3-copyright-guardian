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
                1 = list
            }
        }

        view =< plugin.copyrightguardian.view
        persistence =< plugin.copyrightguardian.persistence
        settings =< plugin.copyrightguardian.settings
    }
}

```

# Migration from core_extended
The functionality of this extension was a part of EXT:core_extended before.
It has been moved in order to have a cleaner separation of functionality. The code has also been refactored and improved.

To migrate the relevant database records from EXT:core_extended to this extension execute the following queries in your database:
```
INSERT INTO tx_copyrightguardian_domain_model_mediasource (uid, pid, tstamp, crdate, cruser_id, deleted, hidden, starttime, endtime, sys_language_uid, l10n_parent, l10n_state, l10n_diffsource, t3ver_oid, t3ver_wsid, t3ver_state, t3ver_stage, t3ver_count, t3ver_tstamp, t3ver_move_id, name, url, internal) SELECT uid, pid, tstamp, crdate, cruser_id, deleted, hidden, starttime, endtime, sys_language_uid, l10n_parent, l10n_state, l10n_diffsource, t3ver_oid, t3ver_wsid, t3ver_state, t3ver_stage, t3ver_count, t3ver_tstamp, t3ver_move_id, name, url, internal FROM tx_coreextended_domain_model_mediasources;
UPDATE sys_file_metadata SET tx_copyrightguardian_source = tx_coreextended_source, tx_copyrightguardian_creator = tx_coreextended_publisher WHERE 1 = 1;
```

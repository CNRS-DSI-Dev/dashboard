# Dashboard

Owncloud 7 app that summarizes some global informations on local owncloud instance usage.

![](https://cloud.githubusercontent.com/assets/8192056/4301181/6db9488e-3e4c-11e4-8390-f799b8372ff8.png)

These informations are gathered :
* total used space,
* nb of users,
* global folders nb,
* global files nb,
* global shares nb,
* (mean) file size per user,
* (mean) nb of folders per user,
* (mean) nb of files per user,
* (mean) nb of shares per user,
* (mean) size per folder,
* (mean) files per folder,
* (mean) size per file,
* (standard deviation) nb of files per user,
* (standard deviation) nb of folders per user,
* (standard deviation) nb of shares per user

Stats may be consulted filtered by group. In this last case, standard variations are not provided.

Cron task is used for historization and chart may be displayed for the last week, month, semester or year for one the data in the list above.

## JSON API

JSON API is provided

Real time stats extracting :
`[owncloud]/index.php/apps/dashboard/api/1.0/stats.[format]`

History stats :
`[owncloud]/index.php/apps/dashboard/api/1.0/history_stats/[group_id]/[format]/[dataType]/[nbDays]/[wantHumanreadable]`

where
* `[owncloud]` is the web url to your owncloud instance
* `[group_id]` is the group id (gid) you want to filter on. If you want global infos (all groups), set it to 'none'.
* `[format]` is the format in which you want your stats ('json' or 'xml')
* `[dataType]` is one of totalUsedSpace, nbUsers,nbFolders, nbFiles,nbShares, sizePerUser, foldersPerUser, filesPerUser, sharesPerUser, sizePerFolder, filesPerFolder, sizePerFile, stdvFilesPerUser, stdvFoldersPerUser, stdvSharesPerUser.
* `[nbDays]` is the number of days from todays you want datas.
* `[wantHumanreadable]` allows you to choose if you want human readable values (set to 1) or not (set to 0)

Human readable values are only possible for total used space, file size per user, size per folder or size per file.
For example, 2147483647 bytes will be displayed as  2 GB.
If you set `wantHumanReadable` to 1, a `unit` property will be added to the result, containing the adequate units.

Warning, as for ownCloud 7.0.3RC2, you **must** set all these parameters ([group_id], [format], [dataType], [nbDays], [wantHumanreadable]) in urls.
Like this : `[owncloud]/index.php/apps/dashboard/api/1.0/history_stats/json/none/nbUsers/30/0`

Please note that this json api has public access. A restriction mechanism may be added later but is not present for now. In the meantime, .htaccess may be used to restrict access by IP, for instance.

## Random test datas

A command line utility exists, allowing to populate this app history tables with random datas.

Usage:

```shell
cd [owncloud]/
./occ dashboard:populate
```
where `[owncloud]` is the install folder of your owncloud instance

Warning : datas are added to the table, so you may want to truncate the `*prefix*dashboard_history` and `*prefix*dashboard_history_by_group` tables before running this command. The `-t` option does the job for you.
Cf `./occ help dashboard:populate` for more informations.

## Install

The dashboard app must be put in the owncloud apps directory, with the name "dashboard" (without any number).

Dashboard app can be accessed by anyone. As the app gives informations about your entire owncloud instance, I would advise you to restrict access to choosen groups of users (see app install panel).

The stats will be updated with the next cron run. Stats are timestamped / logged only once a day.

## Contributing

This app is developed for an internal deployement of ownCloud at CNRS (French National Center for Scientific Research).

If you want to be informed about this ownCloud project at CNRS, please contact david.rousse@dsi.cnrs.fr, gilian.gambini@dsi.cnrs.fr or marc.dexet@dsi.cnrs.fr

## License and Author

|                      |                                          |
|:---------------------|:-----------------------------------------|
| **Author:**          | Patrick Paysant (<ppaysant@linagora.com>)
| **Copyright:**       | Copyright (c) 2014 CNRS DSI
| **License:**         | AGPL v3, see the COPYING file.

* Icons are from [GLYPHICONS.com](http://glyphicons.com/) released under [Creative Commons Attribution 3.0 Unported (CC BY 3.0)](http://creativecommons.org/licenses/by/3.0/).
* This app use an angular directive provided on github by [earlonrails](http://earlonrails.github.io/angular-chartjs-directive/).
* [Chart.js](http://www.chartjs.org/) is open source and available under the [MIT license](http://opensource.org/licenses/MIT).

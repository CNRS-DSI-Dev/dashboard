# Dashboard

Owncloud 9 app that summarizes some global informations on local owncloud instance usage.

![](https://cloud.githubusercontent.com/assets/8192056/23512872/7074ce3e-ff62-11e6-940a-11e09e2c7dd0.png)

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

Cron task is used for historization and chart may be displayed for the last week, month, semester or year for one the data in the list above.

## Stats by group (v1.2+)

Stats may be filtered by group. In this last case, standard variations are not provided.

You must set the group list - on admin page - if you want to activate stats by group, and if so, for which groups. These groups will be used by cron job.
The group list filter box on dashboard page is built from what is present in database for the chosen time range.

## JSON API

JSON API is provided

Real time stats extracting :
`[owncloud]/index.php/apps/dashboard/api/1.0/stats.[format]`

History stats :
`[owncloud]/index.php/apps/dashboard/api/1.0/history_stats/[format]/[group_id]/[dataType]/[nbDays]/[wantHumanreadable]`

where
* `[owncloud]` is the web url to your owncloud instance
* `[format]` is the format in which you want your stats ('json' or 'xml')
* `[group_id]` is the group id (gid) you want to filter on. If you want global infos (all groups), set it to 'none'.
* `[dataType]` is one of totalUsedSpace, nbUsers,nbFolders, nbFiles,nbShares, sizePerUser, foldersPerUser, filesPerUser, sharesPerUser, sizePerFolder, filesPerFolder, sizePerFile, stdvFilesPerUser, stdvFoldersPerUser, stdvSharesPerUser.
* `[nbDays]` is the number of days from todays you want datas.
* `[wantHumanreadable]` allows you to choose if you want human readable values (set to 1) or not (set to 0)

You may use 'last' as `[nbDays]` value if you want the last extracted stats (ie the one day stats extracted the more recently)

Human readable values are only possible for total used space, file size per user, size per folder or size per file.
For example, 2147483647 bytes will be displayed as  2 GB.
If you set `wantHumanReadable` to 1, a `unit` property will be added to the result, containing the adequate units.

Warning, as for ownCloud 7.0.3RC2, you **must** set all these parameters ([format], [group_id], [dataType], [nbDays], [wantHumanreadable]) in urls.
Like this : `[owncloud]/index.php/apps/dashboard/api/1.0/history_stats/json/none/nbUsers/30/0`

Please note that this json api has public access. A restriction mechanism may be added later but is not present for now. In the meantime, .htaccess may be used to restrict access by IP, for instance.

## Cron

In some cases, you may want to not use cron to extract the stats, as the cron system will run lot of proccess simultaneously. To ease these cases, you may disable the cron utility for Dashbord with a system conf (in config.php) :

```php
'dashboard_no_cron' => true,
```

Then there is a command line utility to extract the stats

```shell
cd [owncloud]/
./occ dashboard:stats
```

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
* [Chart.js](http://www.chartjs.org/), used in version for OC 7 of this app, is open source and available under the [MIT license](http://opensource.org/licenses/MIT).
* [C3 Angular Directive] (https://github.com/jettro/c3-angular-directive) and [c3js] (https://github.com/c3js/c3), used in version for OC 9 of this app, are also open source and available under [the MIT license] (http://opensource.org/licenses/MIT). [d3js] (https://github.com/d3/d3/) use is [granted by d3js author] (https://github.com/d3/d3/blob/master/LICENSE).

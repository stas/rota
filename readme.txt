=== Basic Rota Management ===
Contributors: sushkov
Tags: app, rota, management, auto, scheduling, timetable, csv, tiff
Requires at least: WordPress 3.0
Tested up to: WordPress 3.2
Stable tag: 0.5
Donate link: http://stas.nerd.ro/pub/donate/

Basic Rota management based on user options.

== Description ==

Basic Rota Management was built to solve basic scheduling based on user options.
It auto-generates a timetable/schedule based on user availability for each day.

We used this to manage dozens of volunteers during [Transilvania International Film Festival](http://www.tiff.ro/)

_Some of the usecases can be:_
* scheduling of timetables
* scheduling of the conference rooms/speakers
* scheduling of locations/volunteers for an event

[youtube http://www.youtube.com/watch?v=_VcSrp1MUdw]

== Installation ==

Please follow the [standard installation procedure for WordPress plugins](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins).

== Frequently Asked Questions ==

Before asking questions, please check the [http://github.com/stas/rota](http://github.com/stas/rota).

== Changelog ==

= 0.5 =
* Added gravatars
* Fixed array key values
* Rised the criterion for busy users list
* Fixed the deletion array index problems. Fixed a typo in unused_first
* Added unused by day, for unique userlist per day
* Added users and availability export
* Added user usages stats
* Escaped values for csv
* Output all user stats instead of only left ones
* Added a mark for the users with more assignments than days
* Fixed the delta size

= 0.4 =
* Added CSV export of the results

= 0.3 =
* Base algorithm mostly rewritten
* The generator now is trying to build a unique list for a day
* Days can have now deltas, a per interval number of users it needs/overwrites

= 0.2 =
* Flexible days/intervals
* Fixed a bug in `::usersByDayInt`
* Cleaned up, moved logic to `::doTheMath`

= 0.1 =
* First stable release.

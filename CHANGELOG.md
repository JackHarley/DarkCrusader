# Changelog

1.0.017
--------------
* Added a new option to the scan route planner
* Restructed all of the statistics area of the site into a new stats & intel section
* Moved out all the different stats & intel lookups on to the one page
* Better, more standardised URLs (/factions/faction?name=FIRE)
* Removed the old theme, it's ugly and I haven't been maintaining it anyway
* Removed the swat info page, it was a waste of a page
* Reanmed personal bank to finance manager
* Added a new system breakdown pie chart
* Made localities easier to lookup by using 4 dropdown boxes
* Paved the way for the new intelligence features

1.0.016
--------------
* Hack a fix to make scan routes work again
* Fixed a bug with the scan route planner that was causing it to miss certain systems
* Use ceil for getting the number of days we need to scrape to update bank records. More accurate than rounding and adding 1
* Fixed maps so that links don't overflow
* Can I Make It? utility added

1.0.015
--------------
* Make the scan plan results look better and allow the checkbox to be ticked if you click on the instruction text
* Added a link to the system pages to view a system on the map, which will make it appear with a border around it

1.0.014
--------------
* Bug fix for if the first time scrape is run a second time, it would previously invalidate all relationships by deleting and inserting instead of updating
* Scan route/plan generator
* Added a stations only map
* Fixed an alignment bug with maps

1.0.013
--------------
* Usability improvements for maps

1.0.012
--------------
* The refresh system stats cronjob now scrapes the following new pieces of info: x coord, y coord, hex colour, oe star id
* For the x and y coords it scrapes a locally stored copy of Talon Karrde's full outer empires map (http://talonkarrde.com), as the galaxy viewer coords are inaccurate
* 2 types of galaxy maps added with adjustable scales

1.0.011
--------------
* Premium
* New market features for premium members
* Local storage of transactions for premium members

1.0.010-1
--------------
* Bug fixes

1.0.010
--------------
* API calls switched to XML via SOAP (much prettier, faster and bug free on the OE end)

1.0.009
--------------
* Instant verification via API keys, no transfer needed

1.0.008
--------------
* Streamlined the character addition process a bit
* Error setting moved into config file

1.0.007-3
--------------
* Bug fixes

1.0.007-2
--------------
* Bug fixes

1.0.007-1
--------------
* Bug fixes

1.0.007
--------------
* Character integration
* Site bank accounts
* New personal bank

1.0.006
--------------
* Completely revamped theme, looks much nicer

1.0.005
--------------
* Added special test_beta_features privilege for testing beta features (like the new personal bank)
* Added permissions check for system stats
* Fixed the latest transactions not showing up for faction bank
* Fixed a locality permissions error

1.0.004
--------------
* Change Google Analytics to be a config setting
* Correctly check authentication for submitting scans
* Fixed dead forums link

1.0.003
--------------
* Fixed a bug with scanner ratings

1.0.002
--------------
* Removed debug stuff from testing a fix in 1.0.001
* If there's no faction bank transactions in DB, loads the Paste Transaction Log page instead of a broken overview

1.0.001
--------------
* ALLOW_INSTALL split into ALLOW_INSTALL and ALLOW_UPGRADE
* Testing a fix for a bug with graph generation
* Added README
* Added CHANGELOG

1.0.000
--------------
* First 1.0 release
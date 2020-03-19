# U232-Uploader
This is a multiple category uploader bot for u232 code sites.

## Requirements
* PHP
* Curl
* mktorrent
* screen (if you're running the bot 24/7)

## Setup for scene axx
1. Activate your quick login option on u232 site (click name, general tab, generate quick link).
2. Make 4 directories and name them error, jobs, scan, and temp.
3. Create a bot.log file.
4. Edit the directory paths, site root, announce url, and quick login (TORRENT_PATH is your rtorrent watch directory, MOVE_PATH is your rtorrent download directory).
5. Your TORRENT_PATH directory should be your rtorrent watch directory.
6. Add tmdb api key if you're going to use the tmdb option.
7. Set scene axx option to true.

## Setup for non scene axx
1. Activate your quick login option on u232 site (click name, general tab, generate quick link).
2. Make 5 directories and name them error, jobs, scan, move and temp.
3. Create a bot.log file.
4. In Rutorrent setup automove plugin. You'll want to hardlink to point to your UPLOAD_PATH.
5. Edit the directory paths, site root, announce url, and quick login (TORRENT_PATH is your rtorrent watch directory, MOVE_PATH will be your delete directory).
6. Your TORRENT_PATH directory should be your rtorrent watch directory.
7. Add tmdb api key if you're going to use the tmdb option.
8. Set scene axx option to false (unless you don't want to remove the duplicate files).

## How to use
1. Follow setup instructions.
2. Run uploader in screen (skip this step if you're not running bot 24/7).
3. Run the script once (your very first login will fail) so it can grab the cookie.

## TODO
* <del>Create error directory for failed uploads.</del>
* <del>Create a bot log file, writes things like "starting on XYZ... blah blah". Can be useful when daemonized.</del>
* <del>Create job log, writes a new log for every upload.  Will write torrent info in log like "Name, nfo, imdb, category...</del>
* <del>Multiple category.</del>
* <del>Auto cleanup MOVE_PATH.</del>
* <del>Add TMDB API for movies and tv show posters (if no api key is added bot will skip this step).</del>
* <del>Setup for scene axx.</del>

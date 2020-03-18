# U232-Uploader
This is a <del>single</del> multiple category uploader bot for u232 code sites.

## Requirements
* PHP
* Curl
* mktorrent
* screen (if you're running the bot 24/7)

## Setup for scene axx
Directions coming soon.

## Setup for non scene axx
1. Activate your quick login option on u232 site.
2. Edit the directory paths, site root, announce url <del>with passkey</del>, quick login, and tmdb api.
3. Create bot.log file.
4. In rutorrent setup automove plugin. You'll want to hardlink to point to your UPLOAD_PATH, then make MOVE_PATH a delete directory(don't sync this directory to your download directory).  Your TORRENT_PATH directory should be your rtorrent watch directory.
5. Set scene axx option to false.(unless you don't want to remove the duplicate files).

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

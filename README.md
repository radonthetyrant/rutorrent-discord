# rutorrent-discord
A rutorrent plugin for discord webhook notifications (https://discordapp.com/developers/docs/resources/webhook)

Sends messages directly into a discord channel with configurable parameters:

![https://i.imgur.com/JMoa8H5.png](https://i.imgur.com/JMoa8H5.png)

And if discord is properly set up, displays desktop notifications the moment a torrent is added, deleted or finished:

![https://i.imgur.com/yXkpbU1.png](https://i.imgur.com/yXkpbU1.png)

# Install

* download/clone repo
* add discordpush to your rutorrent/plugins directory
* reload webinterface

# Usage

* go to settings > Discord
* Enable, tick what evens should be notified
* Paste webhook url you got from your respective channel settings where you want the notifications to appear

# Changelog

* **10.04.2018**
  * Title now contains the torrent name, so that the discord desktop notification contains the complete Torrent title (was just "Torrent Finished/Deleted/Added" before)
  * Reduced the amount of fields in the discord embed (can be re-added in the plugins/discordpush/discord.php file)
  * Tracker now only displays the hostname without the torrent-key portion

* **05.04.2018**
  * Overhauled discord messages, now uses embed rather than a text message
  * Filesize numbers are now properly rounded

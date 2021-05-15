# Music Search

Music Search is a Drupal 9 module made to allow quick and easy insertion of Albums, Artists and Songs
into a database using external APIs. The system currently has 2 sub-modules it can use, one for
searching in the Spotify database and one for the Discogs database.

## Authors
* [Bjartur Þórhallsson](https://github.com/bjartur20)
* [Guðjón Ingi Valdimarsson](https://github.com/GudjonIV)
* [Ýmir Þórleifsson](https://github.com/ymirthor)

## Assumptions
Before you can easily use this module you need to have certain content types, taxonomy terms and
machine_names for the module to work flawlessly.

#### Album
A content type with the machine name "album" needs to exist with the following fields:
* **field_artist** - a reference to an artist content type
* **field_album_cover** - a reference to a media image
* **field_genre** - a reference to a taxonomy term with a machine name genre
* **field_released** - a date object
* **body** - the standard content body

#### Artist
A content type with the machine name **artist** needs to exists with a title field.

#### Genre
A taxonomy vocabulary with with a machine name **genre**


## Installation
Make sure the above assumptions are true for your Drupal website. Then add the repository to your
modules folder under custom. With that done you can go to extend and install the modules, they can be
found under Custom:
![install image](https://i.imgur.com/ufuU7KR.png)
From here you need to do possibly 2 things depending on if you enabled both discogs and spotify.
Select discogs/spotify from the extend page above and go into the configuration forms:
![configure image](https://i.imgur.com/5KJMMsQ.png)
In the Discogs form you need to add your discogs key and secret you can get from [here](http://www.discogs.com/settings/developers) by creating an app.
The same applies for spotify, you need to add your id and secret you can get from [here](https://developer.spotify.com/documentation/web-api/quick-start/) where you also need to make an app.
With the keys set you can start adding and searching for your favorite albums and artists at
/music_search/add-album

## License
[GNU General Public License v3](https://www.gnu.org/licenses/gpl-3.0.html)

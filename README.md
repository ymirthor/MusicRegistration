# Music Registration

Music Registration is a web application for registering all things music.
You can register artists, producers, albums, songs, and ads.
The application also handles user roles; admin, editor, and writer.

This [Drupal](https://www.drupal.org/) website is my implementation for the first project in the T-430-TOVH 3 week course at Reykjavik University.


## Installation
1. Start by downloading the [image files](https://drive.google.com/drive/folders/1cIGdOpSH_25Xo-6d5AzBNRDcC4XciPUp?usp=sharing).

2. Place the downloaded files into the web/sites/default/files directory.

3. Use the package manager [composer](https://getcomposer.org/) to install the dependencies.

```bash
composer install
```

4. Use [ddev](https://www.ddev.com/) to run the docker containers required for Drupal.

```bash
ddev start
```

5. Import the database using ddev.

```bash
ddev import-db --src=database/database.sql.gz
```

6. Navigate to the website [url](https://music-registration.ddev.site/) in your preferred browser.

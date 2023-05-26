# Welcome
Hello, thanks for your interest in contributing!

I would love any help I can get with this! You can add issues, solve issues, improve documentation, etc. If you would like ideas of where to get started, let me know.

As of right now, I am not picky about how people can contribute. If you would like to be added as a contributer, please contact me at sauer319@umn.edu. You can also create a pull request. 
If you would like to make a new branch, feel free to.

Please visit the sections before for advice about how to contribute:
- [How to run wordpress?](#how-to-run-wordpress)
- [How to install and run the plugin?](#how-to-run-and-install-the-plugin)
- [How to edit files in docker?](#how-to-edit-files-in-docker)
- [Coding Standards](#coding-standards)
- [Resources](#resources)

## How to run wordpress
The easiest way to get wordpress running is to use Docker. Docker will also let you have the same environment as me so everything goes smoother.
Please download docker [here](https://www.docker.com/)

To install wordpress in Docker, we will use Docker Compose. You can use Docker Compose in WSL or powershell if you have Windows. Open up the terminal and move to the directory you want your compose file in.

```bash
mkdir ~/coding/CAA-APP/docker
cd ~/coding/CAA-APP/docker
```

Now that you are in the directory, you will need to make a docker compose file:
```bash
touch docker-compose.yml
```
Open up the file and paste in the following code:
```yml
version: "3"
services:
  wordpress:
    image: wordpress:latest
    restart: unless-stopped
    ports:
      - 8880:80
    environment:
      WORDPRESS_DB_HOST: mysql
      WORDPRESS_DB_USER: username
      WORDPRESS_DB_PASSWORD: password
      WORDPRESS_DB_NAME: wordpress
    volumes:
      - wordpress:/var/www/html
  mysql:
    image: mysql:latest
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: wordpress
      MYSQL_USER: username
      MYSQL_PASSWORD: password
      MYSQL_RANDOM_ROOT_PASSWORD: "1"
    volumes:
      - mysql:/var/lib/mysql

volumes:
  wordpress:
  mysql:
```

Now to get wordpress up and running all you have to do is write: `docker compose start` in the directory of the file.

Following that, open up [http://localhost:8880/](http://localhost:8880/) in your browser and you will be greeted with instructions to set up wordpress. 
And after a couple of steps you will have wordpress up and running!

## How to install and run the plugin
Now that you have wordpress running, it is time to install the plugin. Log in to your admin dashboard, then select plugins. Click 'Add New', then 'Upload Plugin'. Here you will be prompted to choose a file, so let's make that file!

Let's clone the repository and zip it into a file:
```bash
mkdir ~/coding/CAA-APP/CAA-Task-Plugin # make the directory for the plugin files
cd ~/coding/CAA-APP/CAA-Task-Plugin # move to the directory
git clone https://github.com/r-sauers/CAA-Task-Plugin.git # clone the repository into the directory
zip -r ../CAA-Task-Plugin.zip ../CAA-Task-Plugin # zip the file into the parent directory
```
Now you can upload the file `~/coding/CAA-App/CAA-Task-Plugin.zip to wordpress`. Following that, wordpress should prompt you to activate the plugin, please do so if you would like to use it now. Otherwise, you can activate it at any time in the plugins page.

## How to edit files in docker

[TODO]

## Coding Standards

[TODO]

## Resources
[TODO]

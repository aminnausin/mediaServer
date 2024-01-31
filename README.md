## !! See 'Light' Branch For Most Recent Release !!

## This is a simple web based media hosting server.

Videos are grouped by folders and listed in a table with the file name and date. 
This is good for a home server and provides fast and easy access to media. 
You can think of it as a fully self hosted Plex server without all the fluff. 
The website is responsive by design and works on mobile devices. HVEC support depends on the device used.

## Installation

### Requirements
- Apache 2.4.x
- PHP 8.x (Thread Safe)

#### Setup
- Install Apache Web Server
- Install PHP 8
- Set your document root to this folder in Apache's httpd.conf file.
- Set up a port in Apache's httpd.conf file.
      - If you want to watch your media while outside your home, set up port forwarding on your router with this port number. 
- Create symbolic links in the resources folder that point to where videos are stored.
    - The default folder is defined on the second line of index.php as RESOURCE_DEFAULT. <br>
    It's shown when the user does not specify a folder in the URL.
- Run apache and the website will show up on your Public IP Address and port number like {ip}:{port}
    - If you did not set up port forwarding, your Local IP Address will work the same way.
- Optionally, you can set up Apache virtual hosts using the snippet provided in doc/setup to add security and support multiple websites on the same Apache server. It will prevent directory indexing, which allows anyone to browse your resources folder directly by default. 

#### Usage
- The URL will be your public IP address, followed by the port used by your Apache server.
- Different folders are accessed with ?dir=foldername after the website's URL.

#### Supported File Types
- MP4
- MKV
<h6>You can support more file types by adding them to the FILE_TYPES array in the config.php file.</h6>

## Demo

Below are screenshots of the current webpage on Desktop and Android.

!
!

|![Dark](./doc/img/DarkMode.png)|![Light](./doc/img/LightMode.png)|
|:-:|:-:|
|Dark Mode on Desktop|Light Mode on Android|

## This is a simple web based media hosting server.

Videos are grouped by folders and listed in a table with the file name and date. 
This is good for a home server and provides fast and easy access to media. 
The website is responsive by design and works on mobile devices. HVEC support depends on the device used. 

### Installation

Rquires Apache and PHP 8.

Create symbolic links in the resources folder that point to where videos are stored.

Different folders are accessed ?dir=foldername after the website's url. 

The default folder is defined on the second line of index.php as RESOURCE_DEFAULT.
This folder is shown when the user does not specify a folder.

Supported File Types:
- MP4
- MKV